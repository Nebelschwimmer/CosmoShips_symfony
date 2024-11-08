<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;





class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse([
                'message' => 'missing credentials',
            ], 401);
        }

        $user->setLastLogin(new \DateTimeImmutable( 'now' ));

        return $this->json(
            $user
        );
    }
    #[Route(path: '/api/logout', name: 'api_logout')]
    public function logout(): JsonResponse
    {
        return $this->json(['message' => 'You have been logged out!']);
    }
}
