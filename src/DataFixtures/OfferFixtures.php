<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Offer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OfferFixtures extends Fixture implements DependentFixtureInterface
{
    public const OFFER_REFERENCE = 'offer';
    public function load(ObjectManager $manager): void
    {
        $offer = new Offer();
        $offer->setJobTitle('Développeur Frontend');
        $offer->setDescription('Une offre de développement Frontend, majoritairement sur Angular');
        $offer->setContractType('CDI');
        $offer->setPublicationDate(new \DateTime('2024-03-28'));
        $offer->setExpirationDate(new \DateTime('2024-06-28'));
        $offer->setUrl("www.exemple.com");
        $offer->setCompany($this->getReference(CompanyFixtures::COMPANY_REFERENCE, Company::class));

        $manager->persist($offer);

        $manager->flush();

        $this->addReference(self::OFFER_REFERENCE, $offer);
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,

        ];
    }
}
