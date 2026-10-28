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
use App\Entity\Offer;
use App\Entity\Status;
use App\Entity\StatusApplication;

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

    #[Route('/api/applications/{id}', name: 'app_application_update', methods: ['PATCH'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $application = $entityManager->getRepository(Application::class)->find($id);

        if (!$application) {
            return new JsonResponse(['error' => 'Candidature introuvable.'], 404);
        }

        if ($application->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Candidature introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return new JsonResponse(['error' => 'Corps de requête invalide.'], 422);
        }

        // Intitulé
        if (array_key_exists('label', $data)) {
            if (empty($data['label'])) {
                return new JsonResponse(['error' => 'Le champ label ne peut pas être vide.'], 422);
            }
            $application->setLabel($data['label']);
        }

        // Date de candidature
        if (array_key_exists('applyDate', $data)) {
            $applyDate = \DateTime::createFromFormat('Y-m-d', $data['applyDate']);
            if (!$applyDate) {
                return new JsonResponse(['error' => 'Le format de applyDate est invalide (attendu : YYYY-MM-DD).'], 422);
            }
            if ($applyDate > new \DateTime('today')) {
                return new JsonResponse(['error' => 'La date de candidature ne peut pas être dans le futur.'], 422);
            }
            $application->setApplyDate($applyDate);
        }

        // Description de la candidature
        if (array_key_exists('description', $data)) {
            $application->setDescription($data['description']);
        }

        // Méthode de candidature
        if (array_key_exists('applicationMethodId', $data)) {
            $applicationMethod = null;
            if (!empty($data['applicationMethodId'])) {
                $applicationMethod = $entityManager->getRepository(ApplicationMethod::class)->find($data['applicationMethodId']);
                if (!$applicationMethod) {
                    return new JsonResponse(['error' => 'Méthode de candidature introuvable.'], 422);
                }
            }
            $application->setApplicationMethod($applicationMethod);
        }

        // Entreprise & ville : on corrige l'entreprise déjà liée (pas de changement d'entreprise)
        $company = $application->getCompany();
        if (array_key_exists('companyName', $data)) {
            if (empty($data['companyName'])) {
                return new JsonResponse(['error' => "Le nom de l'entreprise ne peut pas être vide."], 422);
            }
            $company->setName($data['companyName']);
        }
        if (array_key_exists('companyCity', $data)) {
            if (empty($data['companyCity'])) {
                return new JsonResponse(['error' => 'La ville ne peut pas être vide.'], 422);
            }
            $company->setCity($data['companyCity']);
        }

        // Statut : ajoute une nouvelle ligne d'historique (le statut courant = la plus récente)
        if (array_key_exists('statusId', $data)) {
            $status = $entityManager->getRepository(Status::class)->find($data['statusId']);
            if (!$status) {
                return new JsonResponse(['error' => 'Statut introuvable.'], 422);
            }
            $statusApplication = new StatusApplication();
            $statusApplication->setApplication($application);
            $statusApplication->setStatus($status);
            $statusApplication->setUpdateDate(new \DateTime());
            $entityManager->persist($statusApplication);
        }

        // Offre d'emploi liée (optionnelle)
        if (array_key_exists('offer', $data) && is_array($data['offer'])) {
            $offerData = $data['offer'];
            $offer = $application->getOffer();

            if (!$offer) {
                if (empty($offerData['jobTitle']) || empty($offerData['publicationDate'])) {
                    return new JsonResponse(['error' => 'jobTitle et publicationDate sont obligatoires pour créer une offre.'], 422);
                }
                $offer = new Offer();
                $offer->setCompany($company);
                $application->setOffer($offer);
            }

            if (array_key_exists('jobTitle', $offerData)) {
                if (empty($offerData['jobTitle'])) {
                    return new JsonResponse(['error' => 'Le poste visé ne peut pas être vide.'], 422);
                }
                $offer->setJobTitle($offerData['jobTitle']);
            }
            if (array_key_exists('contractType', $offerData)) {
                $offer->setContractType($offerData['contractType']);
            }
            if (array_key_exists('description', $offerData)) {
                $offer->setDescription($offerData['description']);
            }
            if (array_key_exists('publicationDate', $offerData)) {
                $publicationDate = \DateTime::createFromFormat('Y-m-d', $offerData['publicationDate']);
                if (!$publicationDate) {
                    return new JsonResponse(['error' => 'Le format de publicationDate est invalide (attendu : YYYY-MM-DD).'], 422);
                }
                $offer->setPublicationDate($publicationDate);
            }
            if (array_key_exists('expirationDate', $offerData)) {
                if (empty($offerData['expirationDate'])) {
                    $offer->setExpirationDate(null);
                } else {
                    $expirationDate = \DateTime::createFromFormat('Y-m-d', $offerData['expirationDate']);
                    if (!$expirationDate) {
                        return new JsonResponse(['error' => 'Le format de expirationDate est invalide (attendu : YYYY-MM-DD).'], 422);
                    }
                    $offer->setExpirationDate($expirationDate);
                }
            }

            $entityManager->persist($offer);
        }

        $entityManager->flush();

        return new JsonResponse(['id' => $application->getId(), 'label' => $application->getLabel()], 200);
    }
}
