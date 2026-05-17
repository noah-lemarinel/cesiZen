<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ApiAuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        $email = isset($data['email']) ? trim((string) $data['email']) : null;
        $password = isset($data['password']) ? (string) $data['password'] : null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Missing credentials (email and password required)'], 400);
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        if (method_exists($user, 'isActive') && !$user->isActive()) {
            return new JsonResponse(['error' => 'Account disabled'], 403);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        return $this->jwtResponse($user, $jwtManager);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Veuillez fournir une adresse email valide.'], 400);
        }

        if (strlen($password) < 6) {
            return new JsonResponse(['error' => 'Le mot de passe doit contenir au moins 6 caractères.'], 400);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['error' => 'Un compte existe déjà avec cette adresse email.'], 409);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name ?: null);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        return $this->jwtResponse($user, $jwtManager, 201);
    }

    private function jwtResponse(User $user, JWTTokenManagerInterface $jwtManager, int $status = 200): JsonResponse
    {
        $token = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles(),
            ],
        ], $status);
    }
}
