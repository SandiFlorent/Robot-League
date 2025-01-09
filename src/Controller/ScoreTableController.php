<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TeamRepository;

class ScoreTableController extends AbstractController
{
    #[Route('/score/table', name: 'app_score_table')]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('score_table/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }
}
