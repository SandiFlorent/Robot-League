<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Repository\ChampionshipRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\State;

#[Route('/championship')]
final class ChampionshipController extends AbstractController
{
    private $entityManager;
    private $teamRepository;

    public function __construct(EntityManagerInterface $entityManager, TeamRepository $teamRepository)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
    }

    #[Route(name: 'app_championship_index', methods: ['GET'])]
    public function index(ChampionshipRepository $championshipRepository): Response
    {
        // Récupère toutes les équipes
        $teams = $this->teamRepository->findAll();

        // Génère les championnats si elles n'ont pas encore été créées
        $this->generateChampionships($teams);

        // Récupère toutes les rencontres
        $championships = $championshipRepository->findAll();

        return $this->render('championship/index.html.twig', [
            'championships' => $championships,
        ]);
    }

    private function generateChampionships(array $teams)
{
    // On récupère toutes les rencontres existantes
    $existingChampionships = $this->entityManager->getRepository(Championship::class)->findAll();

    // Supprimer les anciens matchs retour
    foreach ($existingChampionships as $championship) {
        $blueTeam = $championship->getBlueTeam();
        $greenTeam = $championship->getGreenTeam();

        // Vérifier si l'équipe blue a un ID supérieur à l'équipe green
        if ($blueTeam->getId() > $greenTeam->getId()) {
            $this->entityManager->remove($championship);  // Supprimer le match retour
        }
    }

    // Sauvegarde des modifications après la suppression des matchs retour
    $this->entityManager->flush();

    // Recréer les rencontres sans matchs retour
    foreach ($teams as $team1) {
        foreach ($teams as $team2) {
            // Vérifie que l'équipe 1 n'est pas la même que l'équipe 2 et que l'équipe 1 a un ID inférieur à celui de l'équipe 2
            if ($team1 !== $team2 && $team1->getId() < $team2->getId()) {
                // Vérifie si cette rencontre existe déjà
                $existingChampionship = $this->entityManager->getRepository(Championship::class)
                    ->findOneBy(['blueTeam' => $team1, 'greenTeam' => $team2]);

                // Si la rencontre n'existe pas, on la crée
                if (!$existingChampionship) {
                    $championship = new Championship();
                    $championship->setBlueTeam($team1);
                    $championship->setGreenTeam($team2);
                    $championship->setState(State::NotStarted);  // L'état initial peut être "Non Commencé"

                    // Sauvegarde la rencontre
                    $this->entityManager->persist($championship);
                }
            }
        }
    }

    // Sauvegarde toutes les nouvelles rencontres
    $this->entityManager->flush();
}

        
    #[Route('/{id}/save_score', name: 'app_championship_save_score', methods: ['POST'])]
    public function saveScore(Request $request, Championship $championship): Response
    {
        // Récupère les scores envoyés par le formulaire
        $blueScore = $request->request->get('blueScore');
        $greenScore = $request->request->get('greenScore');

        // Met à jour les scores
        $championship->setBlueGoal($blueScore);
        $championship->setGreenGoal($greenScore);

        // Récupère et met à jour l'état
        $newState = $request->request->get('state');
        if (State::tryFrom($newState)) { // Vérifie que l'état est valide
            $championship->setState(State::from($newState));
        } else {
            throw $this->createNotFoundException('État invalide.');
        }

        // Sauvegarde des modifications
        $this->entityManager->flush();

        // Redirige vers la liste des championnats
        return $this->redirectToRoute('app_championship_index');
    }


}
