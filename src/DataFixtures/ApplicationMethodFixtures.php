<?php

namespace App\DataFixtures;

use App\Entity\ApplicationMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ApplicationMethodFixtures extends Fixture
{
    public const APPMETHOD_REFERENCE = 'appMethod';
    public function load(ObjectManager $manager): void
    {
        $appMethod = new ApplicationMethod();
        $appMethod->setLabel('email');
        $manager->persist($appMethod);

        $manager->flush();

        $this->addReference(self::APPMETHOD_REFERENCE, $appMethod);
    }
}
