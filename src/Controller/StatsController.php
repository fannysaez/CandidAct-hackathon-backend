<?php

namespace App\Controller;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\Service\StatsCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StatsController extends AbstractController
{
    #[Route('/api/stats', name: 'app_stats', methods: ['GET'])]
    public function __invoke(ApplicationRepository $applicationRepository, StatsCalculator $statsCalculator): JsonResponse
    {
        $applications = $applicationRepository->findBy(['user' => $this->getUser()]);

        $items = array_map(
            fn (Application $application) => [
                'applyDate' => $application->getApplyDate(),
                'status' => $application->getCurrentStatus(),
                'history' => $application->getApplicationsStatus()
                    ->map(fn ($statusApplication) => $statusApplication->getStatus()?->getLabel())
                    ->toArray(),
            ],
            $applications
        );

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        return new JsonResponse($statsCalculator->calculate($items, $now));
    }
}