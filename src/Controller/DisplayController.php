<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DisplayController extends AbstractController
{
    #[Route('{_locale}/display', name: 'app_display')]
    public function index(): Response
    {
        return $this->render('display/index.html.twig', [
            'controller_name' => 'DisplayController',
        ]);
    }
}
