<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\ApplicationMethod;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApplicationController extends AbstractController
{
    #[Route('/api/applications', name: 'app_application_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des champs obligatoires
        if (empty($data['label']) || empty($data['applyDate']) || empty($data['companyId'])) {
            return new JsonResponse([
                'error' => 'Les champs label, applyDate et companyId sont obligatoires.',
            ], 422);
        }


        // Vérification du format de la date
        $applyDateString = $data['applyDate'] ?? '';

        $applyDate = \DateTime::createFromFormat(
            '!Y-m-d',
            $applyDateString
        );

        $today = new \DateTime('today');

        if (
            !$applyDate ||
            $applyDate->format('Y-m-d') !== $applyDateString
        ) {
            return new JsonResponse([
                'error' => 'Le format de applyDate est invalide (attendu : YYYY-MM-DD).',
            ], 422);
        }

        // La date de candidature doit être antérieure ou égale à aujourd'hui
        if ($applyDate > $today) {
            return new JsonResponse([
                'error' => 'La date de candidature ne peut pas être dans le futur.',
            ], 422);
        }

        // Vérification que l'entreprise existe
        $company = $entityManager
            ->getRepository(Company::class)
            ->find($data['companyId']);

        if (!$company) {
            return new JsonResponse([
                'error' => 'Entreprise introuvable.',
            ], 422);
        }


        // La méthode de candidature est optionnelle
        $applicationMethod = null;
        if (!empty($data['applicationMethodId'])) {
            $applicationMethod = $entityManager->getRepository(ApplicationMethod::class)->find($data['applicationMethodId']);
            if (!$applicationMethod) {
                return new JsonResponse([
                    'error' => 'Méthode de candidature introuvable.',
                ], 422);
            }
        }

        $application = new Application();
        $application->setLabel($data['label']);
        $application->setApplyDate($applyDate);
        $application->setDescription($data['description'] ?? null);
        $application->setUser($this->getUser());
        $application->setCompany($company);
        $application->setApplicationMethod($applicationMethod);

        $entityManager->persist($application);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $application->getId(),
            'label' => $application->getLabel(),
        ], 201);
    }

    #[Route('/api/applications/{id}', name: 'app_application_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $application = $entityManager->getRepository(Application::class)->find($id);

        if (!$application) {
            return new JsonResponse([
                'error' => 'Candidature introuvable.',
            ], 404);
        }

        // On vérifie que la candidature appartient bien à l'utilisateur connecté
        if ($application->getUser() !== $this->getUser()) {
            return new JsonResponse([
                'error' => 'Candidature introuvable.',
            ], 404);
        }

        // On cherche le statut le plus récent dans l'historique
        $currentStatus = null;
        foreach ($application->getApplicationsStatus() as $statusApplication) {
            if ($currentStatus === null || $statusApplication->getUpdateDate() > $currentStatus->getUpdateDate()) {
                $currentStatus = $statusApplication;
            }
        }

        $data = [
            'id' => $application->getId(),
            'label' => $application->getLabel(),
            'applyDate' => $application->getApplyDate()->format('Y-m-d'),
            'description' => $application->getDescription(),
            'company' => [
                'name' => $application->getCompany()->getName(),
                'city' => $application->getCompany()->getCity(),
            ],
            'applicationMethod' => $application->getApplicationMethod()
                ? $application->getApplicationMethod()->getLabel()
                : null,
            'status' => $currentStatus ? $currentStatus->getStatus()->getLabel() : null,
            'offer' => null,
        ];

        $offer = $application->getOffer();
        if ($offer) {
            $data['offer'] = [
                'jobTitle' => $offer->getJobTitle(),
                'contractType' => $offer->getContractType(),
                'description' => $offer->getDescription(),
                'publicationDate' => $offer->getPublicationDate()->format('Y-m-d'),
                'expirationDate' => $offer->getExpirationDate() ? $offer->getExpirationDate()->format('Y-m-d') : null,
            ];
        }

        return new JsonResponse($data, 200);
    }
}