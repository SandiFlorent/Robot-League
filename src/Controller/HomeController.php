<?php

namespace App\Controller;

use App\Repository\ChampionshipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/{_locale}', name: 'app_home', methods: ['GET'])]
    public function home(ChampionshipRepository $championshipRepository): Response
    {
        // Récupère toutes les rencontres
        $championships = $championshipRepository->findAll();

        return $this->render('home/index.html.twig', [
            'championships' => $championships,  // Envoie les championnats au template
        ]);
    }

}
