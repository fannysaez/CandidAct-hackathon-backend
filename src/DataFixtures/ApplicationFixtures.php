<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\Company;
use App\Entity\Offer;
use App\Entity\Status;
use App\Entity\StatusApplication;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ApplicationFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $statuses = [];
        foreach (['Envoyée', 'En attente', 'À relancer', 'Entretien', 'Acceptée', 'Refusée'] as $label) {
            $status = (new Status())->setLabel($label);
            $manager->persist($status);
            $statuses[$label] = $status;
        }

        $rows = [
            ['user-test', 'Techwave Studio', 'Clermont-Ferrand', 'Développeur Front-end', 'Envoyée', 5],
            ['user-test', 'Limagrain', 'Riom', 'Développeur Symfony', 'En attente', 8],
            ['user-test', 'Sopra Steria', 'Clermont-Ferrand', 'Développeur Full Stack', 'À relancer', 12],
            ['user-test', 'Capgemini', 'Lyon', 'Concepteur Développeur', 'Entretien', 15],
            ['user-test', 'Orange', 'Paris', 'Développeur Web', 'Acceptée', 18],
            ['user-test', 'Thales', 'Toulouse', 'Développeur PHP', 'Refusée', 22],
            ['user-test', 'Airbus', 'Toulouse', 'Développeur Front', 'En attente', 27],
            ['user-autre', 'Michelin', 'Clermont-Ferrand', 'Boulanger', 'Envoyée', 3],
            ['user-autre', 'Doctolib', 'Paris', 'Développeur Back-end', 'Entretien', 9],
        ];

        foreach ($rows as [$userReference, $companyName, $city, $jobTitle, $statusLabel, $daysAgo]) {
            $company = (new Company())->setName($companyName)->setCity($city);
            $manager->persist($company);

            $offer = (new Offer())
                ->setJobTitle($jobTitle)
                ->setDescription('Offre de démonstration')
                ->setPublicationDate(new \DateTime('-' . ($daysAgo + 5) . ' days'))
                ->setCompany($company);
            $manager->persist($offer);

            $application = (new Application())
                ->setLabel($jobTitle . ' - ' . $companyName)
                ->setApplyDate(new \DateTime('-' . $daysAgo . ' days'))
                ->setUser($this->getReference($userReference, User::class))
                ->setCompany($company)
                ->setOffer($offer);
            $manager->persist($application);

            $statusApplication = (new StatusApplication())
                ->setUpdateDate(new \DateTime('-' . $daysAgo . ' days'))
                ->setApplication($application)
                ->setStatus($statuses[$statusLabel]);
            $manager->persist($statusApplication);
        }

        $manager->flush();
    }
}
