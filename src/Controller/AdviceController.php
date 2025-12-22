<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdviceController extends AbstractController
{
    public function __construct(
        private AdviceRepository $adviceRepository,
    ) {
    }

    #[Route('/api/advices/{month}', name: 'api_advices_by_month', methods: ['GET'])]
    public function getAdvicesByMonth(int $month): JsonResponse
    {
        // Vérification du paramètre month si invalide
        if ($month < 1 || $month > 12) {
            return $this->json(
                ['message' => 'Le mois doit être un entier entre 1 et 12.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // 1. Récupérer les données en BDD
        $advices = $this->adviceRepository->findBy(['month' => $month]);

        if (!$advices) {
            return $this->json(
                ['message' => 'Aucun conseil trouvé pour ce mois.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        // 2. Préparer les données pour la réponse JSON
        $data = [];
        foreach ($advices as $advice) {
            $data[] = [
                'id'    => $advice->getId(),
                'title' => $advice->getAdvicetext(),
                'month' => $advice->getMonth(),
            ];
        }

        // 3.  Retourner la réponse JSON (200 OK)
        return $this->json($data, JsonResponse::HTTP_OK);
    }

    #[Route('/api/advices', name: 'api_advices_current_month', methods: ['GET'])]
    public function getCurrentMonthAdvices(): JsonResponse
    {
        // 1. Déterminer le mois en cours
        $currentMonth = (int) (new \DateTimeImmutable())->format('n');

        // 2. Récupérer les conseils pour le mois en cours
        $advices = $this->adviceRepository->findBy(['month' => $currentMonth]);

        // 404 : aucun conseil pour le mois courant
        if (!$advices) {
            return $this->json(
                ['message' => 'Aucun conseil trouvé pour le mois en cours.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $data = [];
        foreach ($advices as $advice) {
            $data[] = [
                'id'    => $advice->getId(),
                'title' => $advice->getAdvicetext(),
                'month' => $advice->getMonth(),
            ];
        }
        // 4. Retourner la réponse JSON
        return $this->json($data, JsonResponse::HTTP_OK);
    }

    // ADMIN ONLY
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un conseil')]
    #[Route('/api/advices/add', name: 'advice_create', methods: ['POST'])]
    public function createAdvice(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): JsonResponse {
        // 400 : JSON invalide
        try {
            /** @var Advice $advice */
            $advice = $serializer->deserialize($request->getContent(), Advice::class, 'json');
        } catch (\Throwable) {
            return $this->json(
                ['message' => 'Corps de requête invalide (JSON attendu).'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // 400 : validation KO
        $errors = $validator->validate($advice);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($advice);
        $em->flush();

        // 201 : créé
        return $this->json(
            ['message' => 'Conseil créé avec succès !'],
            JsonResponse::HTTP_CREATED
        );
    }

    // ADMIN ONLY
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un conseil')]
    #[Route('/api/advices/{id}', name: 'advice_update', methods: ['PUT'])]
    public function updateAdvice(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): JsonResponse {
        $advice = $this->adviceRepository->find($id);

        // 404 : ressource introuvable
        if (!$advice) {
            return $this->json(
                ['message' => 'Conseil non trouvé'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        // 1) Lire le JSON
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            // 400 : JSON invalide
            return $this->json(
                ['message' => 'Corps de requête invalide (JSON attendu).'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // 2) Mise à jour partielle : on ne change QUE ce qui est envoyé
        $hasAnyField = false;

        if (array_key_exists('advicetext', $data)) {
            $advice->setAdvicetext((string) $data['advicetext']);
            $hasAnyField = true;
        }

        if (array_key_exists('month', $data)) {
            // Si month est envoyé vide/null → la validation NotNull/Range gérera l'erreur
            $advice->setMonth((int) $data['month']);
            $hasAnyField = true;
        }

        // 3) 400 : rien à mettre à jour
        if (!$hasAnyField) {
            return $this->json(
                ['message' => 'Aucune donnée à mettre à jour. Champs acceptés : advicetext, month.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // 4) Valider l'entité après modifications
        $errors = $validator->validate($advice);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        // 5) Sauvegarde
        $em->flush();

        // 200 : OK
        return $this->json(
            ['message' => 'Le conseil a été mis à jour avec succès !'],
            JsonResponse::HTTP_OK
        );
    }

    // ADMIN ONLY
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un conseil')]
    #[Route('/api/advices/{id}', name: 'advice_delete', methods: ['DELETE'])]
    public function deleteAdvice(int $id, EntityManagerInterface $em): JsonResponse
    {
        $advice = $this->adviceRepository->find($id);

        // 404 : ressource introuvable
        if (!$advice) {
            return $this->json(
                ['message' => 'Conseil non trouvé'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $em->remove($advice);
        $em->flush();

        // 200 : OK
        return $this->json(
            ['message' => 'Conseil supprimé'],
            JsonResponse::HTTP_OK
        );
    }
}
