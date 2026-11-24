<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): User {
        if (!$data instanceof User) {
            throw new \LogicException('Une entité User est attendue.');
        }

        $plainPassword = $data->getPlainPassword();

        if ($plainPassword === null || $plainPassword === '') {
            throw new \InvalidArgumentException(
                'Le mot de passe est obligatoire.'
            );
        }

        $data->setPassword(
            $this->passwordHasher->hashPassword($data, $plainPassword)
        );

        $data->setPlainPassword(null);
        $data->setRoles([]);

        return $this->processor->process(
            $data,
            $operation,
            $uriVariables,
            $context,
        );
    }
}
