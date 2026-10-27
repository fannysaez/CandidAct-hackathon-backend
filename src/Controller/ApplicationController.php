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
        $applyDate = \DateTime::createFromFormat('Y-m-d', $data['applyDate']);
        if (!$applyDate) {
            return new JsonResponse([
                'error' => 'Le format de applyDate est invalide (attendu : YYYY-MM-DD).',
            ], 422);
        }

        // La date de candidature doit être antérieure ou égale à aujourd'hui
        $today = new \DateTime('today');
        if ($applyDate > $today) {
            return new JsonResponse([
                'error' => 'La date de candidature ne peut pas être dans le futur.',
            ], 422);
        }

        // Vérification que l'entreprise existe
        $company = $entityManager->getRepository(Company::class)->find($data['companyId']);
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
}