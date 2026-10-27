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

        $company = $entityManager->getRepository(Company::class)->find($data['companyId'] ?? null);
        $applicationMethod = $entityManager->getRepository(ApplicationMethod::class)->find($data['applicationMethodId'] ?? null);

        $application = new Application();
        $application->setLabel($data['label'] ?? '');
        $application->setApplyDate(new \DateTime($data['applyDate'] ?? 'now'));
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