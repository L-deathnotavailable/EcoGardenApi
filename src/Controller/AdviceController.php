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
            return $this->json([
                'message' => 'Le mois doit être un entier entre 1 et 12.',
            ]);
        }

        // 1. Récupérer les données en BDD
        $advices = $this->adviceRepository->findBy(['month' => $month]);

        if (!$advices) {
            return $this->json([
                'message' => 'Aucun conseil trouvé pour ce mois.',
            ]);
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

        // 3.  Retourner la réponse JSON
        return $this->json($data);
    }

    #[Route('/api/advices', name: 'api_advices_current_month', methods: ['GET'])]
    public function getCurrentMonthAdvices(): JsonResponse
    {
        // 1. Déterminer le mois en cours
        $currentMonth = (int) (new \DateTimeImmutable())->format('n');

        // 2. Récupérer les conseils pour le mois en cours
        $advices = $this->adviceRepository->findBy(['month' => $currentMonth]);

        if (!$advices) {
            return $this->json([
                'message' => 'Aucun conseil trouvé pour le mois en cours.',
            ]);
        }
        // 3. Préparer les données pour la réponse JSON
        $data = [];
        foreach ($advices as $advice) {
            $data[] = [
                'id'    => $advice->getId(),
                'title' => $advice->getAdvicetext(),
                'month' => $advice->getMonth(),
            ];
        }
        // 4. Retourner la réponse JSON
        return $this->json($data);
    }
    #[Route('/api/advices/add', name: 'advice_create', methods: ['POST'])]
    public function createAdvice(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): JsonResponse {

        $advice = $serializer->deserialize($request->getContent(), Advice::class, 'json');

        $errors = $validator->validate($advice);
        if (count($errors) > 0) {
            return $this->json($errors);
        }
        
        $em->persist($advice);
        $em->flush();

        return $this->json(['message' => 'Advice created']);
    }

}
