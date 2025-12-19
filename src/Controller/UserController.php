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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

        // 2. Forcer ROLE_USER côté backend pour éviter toute escalade de privilèges
        $user->setRoles(['ROLE_USER']);

        // 3. Valider l'objet User (email, password, postCode via contraintes)
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // 4. Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, (string) $user->getPassword());
        $user->setPassword($hashedPassword);

        // 5. Sauvegarde en BDD
        $this->em->persist($user);
        $this->em->flush();

        // 6. Cache
        $this->cache->invalidateTags(['usersCache']);

        // 7. Sérialiser l'utilisateur créé
        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUser']);

        // 8. Retourner l'utilisateur créé en JSON (sans le password grâce aux groups)
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }


    #[Route('/api/user/{id}', name: 'app_user_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un profil utilisateur')]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
        }

        // On lit le payload une fois pour savoir si le password est envoyé
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(
                ['message' => 'Corps de requête invalide (JSON attendu).'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Si le password n’est PAS envoyé, on évite NotBlank(password) en le retirant du JSON
        // (sinon la validation échoue car NotBlank sur password)
        if (!array_key_exists('password', $payload)) {
            unset($payload['password']);
        }

        // Désérialiser en "remplissant" l'objet existant
        try {
            $this->serializer->deserialize(
                json_encode($payload, JSON_UNESCAPED_UNICODE),
                User::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
            );
        } catch (\Throwable) {
            return $this->json(
                ['message' => 'Corps de requête invalide (JSON attendu).'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Sécurité : on empêche toute modification de rôles via l’API
        // (même si quelqu’un envoie "roles": ["ROLE_ADMIN"])
        if (array_key_exists('roles', $payload)) {
            // On garde les rôles actuels en base, sans appliquer ceux du payload
            // on réassigne les roles existants
            $user->setRoles($user->getRoles());
        }

        // Hash password si changé
        if (array_key_exists('password', $payload) && is_string($payload['password']) && $payload['password'] !== '') {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $payload['password']);
            $user->setPassword($hashedPassword);
        }

        // Valider après update
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();
        $this->cache->invalidateTags(['usersCache']);

        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUser']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un utilisateur')]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($user);
        $this->em->flush();

        $this->cache->invalidateTags(['usersCache']);

        return $this->json(['message' => 'Utilisateur supprimé'], Response::HTTP_OK);
    }
}
