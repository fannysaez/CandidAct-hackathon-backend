<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthController extends AbstractController
{
    #[Route('/api/register', methods: ['POST'])]
    public function register(
        Request $request,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = $request->toArray();

        $rules = new Assert\Collection(
            fields: [
                'firstname' => [new Assert\NotBlank(message: 'Le prénom est obligatoire'), new Assert\Length(max: 255)],
                'lastname' => [new Assert\NotBlank(message: 'Le nom est obligatoire'), new Assert\Length(max: 255)],
                'email' => [new Assert\NotBlank(message: 'Email est obligatoire'), new Assert\Email(message: 'Email invalide')],
                'password' => [new Assert\NotBlank(message: 'Le mot de passe est obligatoire'), new Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères')],
            ],
            allowExtraFields: true,
            missingFieldsMessage: 'Un champ est manquant',
        );

        $violations = $validator->validate($data, $rules);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], 422);
        }

        if ($em->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
            return $this->json(['errors' => ['Cet email est déjà utilisé']], 409);
        }

        $user = new User();
        $user->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setPassword($hasher->hashPassword($user, $data['password']));

        $em->persist($user);
        $em->flush();

        return $this->json(['id' => $user->getId()], 201);
    }
}