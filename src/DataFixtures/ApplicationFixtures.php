<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\ApplicationMethod;
use App\Entity\Company;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ApplicationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $application = new Application();
        $application->setLabel('Développeur Frontend');
        $application->setApplyDate(new \DateTime('2024-03-28'));
        $application->setDescription('Une offre de développement Frontend, majoritairement sur Angular');
        $application->setUser($this->getReference(UserFixtures::USER_REFERENCE, User::class));
        $application->setOffer($this->getReference(OfferFixtures::OFFER_REFERENCE, Offer::class));
        $application->setCompany($this->getReference(CompanyFixtures::COMPANY_REFERENCE, Company::class));
        $application->setApplicationMethod($this->getReference(ApplicationMethodFixtures::APPMETHOD_REFERENCE, ApplicationMethod::class));

        $manager->persist($application);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
            OfferFixtures::class,
            ApplicationMethodFixtures::class,
            UserFixtures::class

        ];
    }
}
