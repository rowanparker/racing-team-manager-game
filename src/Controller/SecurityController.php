<?php

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route(path: "/login", name: "app_login",methods: ["post"])]
    public function login(IriConverterInterface $iriConverter): JsonResponse
    {
        if ( ! $this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->json([
                'error' => 'Invalid login request: check that the Content-Type header is "application/json"',
            ], 400);
        }

        return $this->json([
            'user' => $iriConverter->getIriFromItem($this->getUser()),
        ]);
    }

    #[Route(path: "/logout", name: "app_logout")]
    public function logout()
    {
    }

    #[Route(path: "/me", name: "app_me", methods: ["get"])]
    public function me(IriConverterInterface $iriConverter): JsonResponse
    {
        return $this->json([
            'user' => ($this->getUser()) ? $iriConverter->getIriFromItem($this->getUser()) : null
        ]);
    }
}
