<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MobileController extends AbstractController
{
    // Serve the mobile SPA shell. The SPA will handle its own client-side routing.
    #[Route('/mobile', name: 'mobile_index')]
    #[Route('/mobile/{reactRouting}', name: 'mobile_index_wildcard', requirements: ['reactRouting' => '.+'])]
    public function index(): Response
    {
        return $this->render('mobile/index.html.twig');
    }
}

