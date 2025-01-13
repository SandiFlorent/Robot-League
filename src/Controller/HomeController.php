<?php

namespace App\Controller;

use App\Repository\ChampionshipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Championship;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ChampionshipListRepository;

class HomeController extends AbstractController
{
    #[Route('/{_locale}', name: 'app_home')]
    public function index(
        Request $request,
        ChampionshipRepository $championshipRepository,
        ChampionshipListRepository $championshipListRepository
    ): Response {
        // Récupérer l'ID du championnat sélectionné dans la requête
        $championshiplistId = $request->query->get('championshiplist_id');

        // Si un championnat est sélectionné, récupérer les matchs correspondants
        if ($championshiplistId) {
            $championships = $championshipRepository->findBy(['championshipList' => $championshiplistId]);
        } else {
            // Sinon, récupérer tous les matchs
            $championships = $championshipRepository->findAll();
        }

        // Récupérer la liste des championnats disponibles
        $championshipLists = $championshipListRepository->findAll();

        return $this->render('home/index.html.twig', [
            'championships' => $championships,
            'championshipLists' => $championshipLists,
            'selected_championship_id' => $championshiplistId, // Passer l'ID sélectionné (null si non sélectionné)
        ]);
    }

}
