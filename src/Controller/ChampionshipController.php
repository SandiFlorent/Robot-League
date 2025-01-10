<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\ChampionshipList;
use App\Repository\ChampionshipRepository;
use App\Repository\ChampionshipListRepository;
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
    public function index(ChampionshipRepository $championshipRepository, ChampionshipListRepository $championshipListRepository, Request $request): Response
    {
        // On récupère tous les championnats disponibles
        $championshipLists = $championshipListRepository->findAll();

        // On récupère l'ID du championnat sélectionné depuis la requête (si existant)
        $championshiplistId = $request->query->get('championshiplist_id');
        
        // Initialiser la variable qui contiendra le championnat sélectionné
        $selectedChampionshipList = null;
        $championships = [];

        // Si un championnat a été sélectionné, on récupère ce championnat spécifique
        if ($championshiplistId) {
            $selectedChampionshipList = $championshipListRepository->find($championshiplistId);

            // Récupérer les matchs du championnat sélectionné
            if ($selectedChampionshipList) {
                $championships = $selectedChampionshipList->getChampionships();
            }
        }

        // Récupérer les états disponibles (si nécessaire)
        $states = State::cases();

        // Passer toutes les données nécessaires à la vue
        return $this->render('championship/index.html.twig', [
            'championship_lists' => $championshipLists,
            'selected_championship_list' => $selectedChampionshipList,
            'championships' => $championships,
            'states' => $states,
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
                        $championship->setState(State::NOT_STARTED);  // L'état initial peut être "Non Commencé"

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
        $blueScore = $request->request->get('blueScore');
        $greenScore = $request->request->get('greenScore');
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

    // #[Route('/championship', name: 'app_championship_select')]
    // public function selectChampionship(ChampionshipListRepository $championshipListRepository, Request $request): Response
    // {
    //     $championshiplistId = $request->query->get('championshiplist_id');
        
    //     // Récupérer tous les championnats disponibles
    //     $championshiplists= $championshipListRepository->findAll();
        
    //     // Récupérer le championnat sélectionné
    //     $selectedChampionshiplist = null;
    //     if ($championshiplistId) {
    //         $selectedChampionshiplist = $championshipListRepository->find($championshiplistId);
    //     }

    //     // Récupérer les matchs du championnat sélectionné
    //     $championships = [];
    //     if ($selectedChampionshiplist) {
    //         $championships = $selectedChampionshiplist->getMatches();
    //     }

    //     $states = State::cases();

    //     // Passer les données à la vue
    //     return $this->render('championship/index.html.twig', [
    //         'championship_list' => $selectedChampionshiplist,

    //         'championships' => $championships,
    //         'states' => $states, // Exemple pour les états
    //     ]);
    // }
        
}
