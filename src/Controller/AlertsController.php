<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\StatusApplication;
use App\Repository\ApplicationRepository;
use App\Service\ActionAlertCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AlertsController extends AbstractController
{
    #[Route('/api/alerts', name: 'app_alerts', methods: ['GET'])]
    public function __invoke(ApplicationRepository $applicationRepository, ActionAlertCalculator $alertCalculator): JsonResponse
    {
        $applications = $applicationRepository->findBy(['user' => $this->getUser()]);

        $items = array_map(
            fn (Application $application) => [
                'applicationId' => $application->getId(),
                'jobTitle' => $application->getJobTitle(),
                'company' => $application->getCompanyName(),
                'applyDate' => $application->getApplyDate(),
                'history' => array_values(
                    $application->getApplicationsStatus()
                        ->map(fn (StatusApplication $statusApplication) => [
                            'status' => $statusApplication->getStatus()?->getLabel(),
                            'date' => $statusApplication->getUpdateDate(),
                        ])
                        ->toArray()
                ),
            ],
            $applications
        );

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        return new JsonResponse($alertCalculator->calculate($items, $now));
    }
}