<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SvelteController extends AbstractController
{
    #[Route(path: "/", name: "app_svelte")]
    public function index(): Response
    {
        return $this->render('svelte.html.twig');
    }
}
