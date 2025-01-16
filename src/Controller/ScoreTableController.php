<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TeamRepository;
use App\Repository\ChampionshipListRepository;
use App\Repository\ChampionshipRepository;
use Symfony\Component\HttpFoundation\Request;

class ScoreTableController extends AbstractController
{
    #[Route('{_locale}/score/table/', name: 'app_score_table')]
    public function indexid(TeamRepository $teamRepository, ChampionshipListRepository $championshipListRepository, Request $request): Response
    {
        $idlist = $request->query->get('championshiplist_id');
        
        if ($idlist)
        {
            $championshipList = $championshipListRepository->find($idlist);
            $teams = $championshipList ? $teamRepository->findAllOrdered($championshipList) : [];
        }
        else{
            $championshipList = $championshipListRepository->findOneBy([]) ?: null;
            $teams = $championshipList ? $teamRepository->findAllOrdered($championshipList) : [];
        }
        $championshiplists = $championshipListRepository->findAll();



        return $this->render('score_table/index.html.twig', [
            'selected_championship_list' => $championshipList,
            'teams' => $teams,
            'championshiplists' => $championshiplists,
        ]);
    }
}
