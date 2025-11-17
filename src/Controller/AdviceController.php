<?php

namespace App\Controller;

use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AdviceController extends AbstractController
{
    #[Route('/api/advices', name: 'api_advices_list', methods: ['GET'])]
    public function list(AdviceRepository $adviceRepository): JsonResponse
    {
        // 1. Récupérer les données en BDD
        $advices = $adviceRepository->findAll();

        // 2. Transformer les entités en tableau "propre" pour le JSON
        $data = [];

        foreach ($advices as $advice) {
            $data[] = [
                'id'          => $advice->getId(),
                'title'       => $advice->getAdvicetext(),
                'month'       => $advice->getMonth(),
            ];
        }

        // 3. Retourner du JSON
        return $this->json($data);
    }
    #[Route('/api/advices/{month}', name: 'app_advice_by_month', methods: ['GET'])]
    public function getAdviceOneByMonth(int $month, AdviceRepository $adviceRepository, SerializerInterface $serializer): JsonResponse {
        
        // 1. Récupérer les données en BDD
        $advice = $adviceRepository->findOneByMonth($month);

        // 2. Gérer le cas où aucun conseil n'est trouvé
        if (!$advice) {
            return new JsonResponse(['message' => 'Aucun conseil trouvé pour ce mois']);
        }
        
        $data = $serializer->serialize($advice, 'json');

        // 3. Retourner du JSON
        return $this->json($data);
    }
}
