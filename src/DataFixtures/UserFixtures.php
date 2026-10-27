<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public const USER_REFERENCE = 'user';

    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setEmail('test@candidact.fr');
        $user->setFirstname('Bernard');
        $user->setLastname('Lechat');
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'test1234'
        );

        $user->setPassword($hashedPassword);


        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
    }
}