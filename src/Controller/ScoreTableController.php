<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TeamRepository;
use App\Repository\ChampionshipListRepository;
use App\Repository\ChampionshipRepository;

class ScoreTableController extends AbstractController
{
    #[Route('/score/table', name: 'app_score_table')]
    public function index(TeamRepository $teamRepository, ChampionshipListRepository $championshipListRepository): Response
    {
        $emptyArray = [];

        $championshiplists = $championshipListRepository->findAll();

        $selectedChampionshipList = $championshipListRepository->findOneBy([]);

        return $this->render('score_table/index.html.twig', [
            'selected_championship_list' => $selectedChampionshipList,
            'teams' => $emptyArray,
            'championshiplists' => $championshiplists,
        ]);
    }

    #[Route('/score/table/{idlist}', name: 'app_score_table_id')]
    public function indexid(TeamRepository $teamRepository, ChampionshipListRepository $championshipListRepository, int $idlist): Response
    {
        $championshipList = $championshipListRepository->findOneBy((['id' => $idlist]));

        $teams = $teamRepository->findAllOrdered($championshipList);

        $championshiplists = $championshipListRepository->findAll();

        return $this->render('score_table/index.html.twig', [
            'selected_championship_list' => $championshipList,
            'teams' => $teams,
            'championshiplists' => $championshiplists,
        ]);
    }
}
