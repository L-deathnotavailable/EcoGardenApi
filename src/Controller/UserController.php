<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private TagAwareCacheInterface $cache,
    ) {
    }

    #[Route('/api/user', name: 'app_user_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        // 1. Désérialiser le JSON reçu en objet User
        try {
            $user = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json'
            );
        } catch (\Throwable $e) {
            // JSON pourri / format invalide
            return $this->json(
                ['message' => 'Corps de requête invalide (JSON attendu).'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // 2. Valider l'objet User (y compris le code postal)
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // 3. Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );
        $user->setPassword($hashedPassword);

        // 4. Rôle par défaut si rien n'a été donné
        if (empty($user->getRoles())) {
            $user->setRoles(['ROLE_USER']);
        }

        // 5. Sauvegarde en BDD
        $this->em->persist($user);
        $this->em->flush();

        // 6. Invalidation du cache lié aux users
        $this->cache->invalidateTags(['usersCache']);

        // 7. Sérialiser l'utilisateur créé
        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUser']);

        // 8. Retourner l'utilisateur créé en JSON (sans le password grâce aux groups)
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

}
