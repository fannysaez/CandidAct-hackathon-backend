<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\ApplicationMethod;
use App\Entity\Company;
use App\Entity\Status;
use App\Entity\StatusApplication;
use App\Entity\Offer;
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

        // L'offre est facultative : si elle est fournie, on la valide.
        $offer = null;

        if (array_key_exists('offer', $data)) {
            $offerData = $data['offer'];

            if (
                !is_array($offerData)
                || !isset(
                $offerData['jobTitle'],
                $offerData['description'],
                $offerData['publicationDate']
            )
                || !is_string($offerData['jobTitle'])
                || trim($offerData['jobTitle']) === ''
                || !is_string($offerData['description'])
                || trim($offerData['description']) === ''
                || !is_string($offerData['publicationDate'])
            ) {
                return new JsonResponse([
                    'error' => 'L’intitulé, la description et la date de publication de l’offre sont obligatoires.',
                ], 422);
            }
        }
        $publicationDateString = $offerData['publicationDate'];
        $publicationDate = \DateTime::createFromFormat(
            '!Y-m-d',
            $publicationDateString
        );
        $dateErrors = \DateTime::getLastErrors();

        if (
            !$publicationDate
            || $publicationDate->format('Y-m-d') !== $publicationDateString
            || ($dateErrors !== false && (
                $dateErrors['warning_count'] > 0
                || $dateErrors['error_count'] > 0
            ))
        ) {
            return new JsonResponse([
                'error' => 'La date de publication est invalide.',
            ], 422);
        }

        $expirationDate = null;

        if (
            isset($offerData['expirationDate'])
            && $offerData['expirationDate'] !== ''
            && $offerData['expirationDate'] !== null
        ) {
            if (!is_string($offerData['expirationDate'])) {
                return new JsonResponse([
                    'error' => 'La date d’expiration est invalide.',
                ], 422);
            }

            $expirationDateString = $offerData['expirationDate'];
            $expirationDate = \DateTime::createFromFormat(
                '!Y-m-d',
                $expirationDateString
            );
            $dateErrors = \DateTime::getLastErrors();

            if (
                !$expirationDate
                || $expirationDate->format('Y-m-d') !== $expirationDateString
                || ($dateErrors !== false && (
                    $dateErrors['warning_count'] > 0
                    || $dateErrors['error_count'] > 0
                ))
            ) {
                return new JsonResponse([
                    'error' => 'La date d’expiration est invalide.',
                ], 422);
            }

            if ($expirationDate < $publicationDate) {
                return new JsonResponse([
                    'error' => 'La date d’expiration doit être postérieure ou égale à la date de publication.',
                ], 422);
            }
        }

        $offer = new Offer();
        $offer->setJobTitle(trim($offerData['jobTitle']));
        $offer->setDescription(trim($offerData['description']));
        $offer->setContractType(
            isset($offerData['contractType']) && $offerData['contractType'] !== ''
            ? trim((string) $offerData['contractType'])
            : null
        );
        $offer->setPublicationDate($publicationDate);
        $offer->setExpirationDate($expirationDate);
        $offer->setUrl(
            isset($offerData['url']) && trim((string) $offerData['url']) !== ''
            ? trim((string) $offerData['url'])
            : null
        );
        $offer->setCompany($company);


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

        // Recherche du statut initial "Envoyé"
        $status = $entityManager
            ->getRepository(Status::class)
            ->findOneBy(['label' => 'Envoyée']);

        if (!$status) {
            return new JsonResponse([
                'error' => 'Le statut "Envoyée" est introuvable.',
            ], 500);
        }

        // Création de l'historique du statut
        $statusApplication = new StatusApplication();
        $statusApplication->setApplication($application);
        $statusApplication->setStatus($status);
        $statusApplication->setUpdateDate(new \DateTime());

        if ($offer !== null) {
            $application->setOffer($offer);
        }

        // Enregistrement de la candidature et de son statut
        $connection = $entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $entityManager->persist($application);
            $entityManager->persist($statusApplication);

            if ($offer !== null) {
                $entityManager->persist($offer);
            }

            $entityManager->flush();
            $connection->commit();
        } catch (\Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }

        return new JsonResponse([
            'id' => $application->getId(),
            'label' => $application->getLabel(),
            'jobTitle' => $application->getLabel(),
            'applyDate' => $application->getApplyDate()->format('Y-m-d\TH:i:sP'),
            'company' => $company->getName(),
            'status' => $status->getLabel(),
            'offer' => $offer ? [
                'id' => $offer->getId(),
                'jobTitle' => $offer->getJobTitle(),
                'contractType' => $offer->getContractType(),
                'description' => $offer->getDescription(),
                'url' => $offer->getUrl(),
                'publicationDate' => $offer->getPublicationDate()->format('Y-m-d'),
                'expirationDate' => $offer->getExpirationDate()?->format('Y-m-d'),
            ] : null,
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