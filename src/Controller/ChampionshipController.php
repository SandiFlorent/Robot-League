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

        // Récupère toutes les rencontres
        $championships = $championshipRepository->findAll();

        // Envoie les états à afficher dans le formulaire
        $states = State::cases();

        // Envoie les états à afficher dans le formulaire
        $states = State::cases();

        return $this->render('championship/index.html.twig', [
            'championships' => $championships,
            'states' => $states,  // Envoie les valeurs de l'énumération
            'states' => $states,  // Envoie les valeurs de l'énumération
        ]);
    }

    private function generateChampionships(array $teams)
    {
        // On récupère toutes les rencontres existantes
        $existingChampionships = $this->entityManager->getRepository(Championship::class)->findAll();

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
        // Récupère les données du formulaire
        // Récupère les données du formulaire
        $blueScore = $request->request->get('blueScore');
        $greenScore = $request->request->get('greenScore');
        $stateName = $request->request->get('state');  // Récupère le nom de la constante (par exemple, "Canceled")
        $stateName = $request->request->get('state');  // Récupère le nom de la constante (par exemple, "Canceled")

        // Met à jour les scores
        $championship->setBlueGoal($blueScore);
        $championship->setGreenGoal($greenScore);

        // Convertit la valeur de l'état en une instance de l'énumération State
        try {
            $championship->setState(State::from($stateName));
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("L'état sélectionné est invalide : $stateName");
        }

        // Sauvegarde les modifications
        $this->entityManager->flush();

        // Redirige vers la liste des matchs
        return $this->redirectToRoute('app_championship_index');
    }

    #[Route('/championship/delete_all', name: 'app_championship_delete_all', methods: ['POST'])]
    public function deleteAllChampionships(): Response
    {
        // Récupérer tous les matchs existants
        $championships = $this->entityManager->getRepository(Championship::class)->findAll();

        // Supprimer tous les matchs
        foreach ($championships as $championship) {
            $this->entityManager->remove($championship);
        }

        // Appliquer les modifications
        $this->entityManager->flush();

        // Rediriger vers la page du championnat pour actualiser l'affichage (table vide)
        return $this->redirectToRoute('app_championship_index');
    }
    
    #[Route('/championship/generate', name: 'app_championship_generate', methods: ['POST'])]
    public function generateChampionshipsPost(): Response
    {
        // Récupère toutes les équipes
        $teams = $this->teamRepository->findAll();

        // Génère les nouveaux matchs
        $this->generateChampionships($teams);

        // Redirige vers la page du championnat pour actualiser l'affichage
        return $this->redirectToRoute('app_championship_index');
    }
        
}
