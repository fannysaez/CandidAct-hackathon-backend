<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            ['user-test', 'test@candidact.com', 'Bernard', 'Lechat'],
            ['user-autre', 'autre@candidact.com', 'Alice', 'Martin'],
        ];

        foreach ($users as [$reference, $email, $firstname, $lastname]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'test1234'));

            $manager->persist($user);
            $this->addReference($reference, $user);
        }

        $manager->flush();
    }
}