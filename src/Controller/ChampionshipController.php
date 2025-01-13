<?php
namespace App\Controller;

use App\Entity\Championship;
use App\Entity\ChampionshipList;
use App\Entity\Slot;
use App\Repository\ChampionshipRepository;
use App\Repository\ChampionshipListRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\State;
use App\Repository\EncounterRepository;

#[Route('/{_locale}/championship')]
final class ChampionshipController extends AbstractController
{
    private $entityManager;
    private $teamRepository;
    private $championshipListRepository;

    // Injection du repository ChampionshipListRepository
    public function __construct(EntityManagerInterface $entityManager, TeamRepository $teamRepository, ChampionshipListRepository $championshipListRepository)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
        $this->championshipListRepository = $championshipListRepository;
    }

    #[Route(name: 'app_championship_index', methods: ['GET'])]
    public function index(ChampionshipRepository $championshipRepository, Request $request): Response
    {
        // On récupère tous les championnats disponibles
        $championshipLists = $this->championshipListRepository->findAll();

        // On récupère l'ID du championnat sélectionné depuis la requête (si existant)
        $championshiplistId = $request->query->get('championshiplist_id');
        
        // Initialiser la variable qui contiendra le championnat sélectionné
        $selectedChampionshipList = null;
        $championships = [];

        // Si un championnat a été sélectionné, on récupère ce championnat spécifique
        if ($championshiplistId) {
            $selectedChampionshipList = $this->championshipListRepository->find($championshiplistId);

            // Récupérer les matchs du championnat sélectionné
            if ($selectedChampionshipList) {
                $championships = $selectedChampionshipList->getMatches();
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

    #[Route('/championship/generate', name: 'app_championship_generate', methods: ['POST'])]
    public function generateChampionshipsPost(Request $request, EncounterRepository $encounterRepository): Response
    {
        // Récupérer l'ID du championnat sélectionné depuis la requête
        $championshipListId = $request->get('championship_list_id');
        
        // Trouver le championnat sélectionné
        $championshipList = $this->entityManager->getRepository(ChampionshipList::class)->find($championshipListId);

        if (!$championshipList) {
            throw $this->createNotFoundException('Championnat non trouvé');
        }

        // Récupérer toutes les équipes du championnat sélectionné
        $teams = $championshipList->getTeams()->toArray();  // Conversion en tableau

        $filteredTeams = array_filter($teams, function($team) {
            return $team->isAccepted() === true;
        });

        $filteredTeams = array_values($filteredTeams);

        // Générer les matchs
        $this->generateChampionships($filteredTeams, $championshipList, $encounterRepository);

        // Rediriger vers la page des matchs
        return $this->redirectToRoute('app_championship_index', [
            'championshiplist_id' => $championshipListId
        ]);
    }
    
    // Fonction pour générer les matchs
    private function generateChampionships(array $teams, ChampionshipList $championshipList, EncounterRepository $encounterRepository)
    {
        // Génère les matchs sans matchs retour (évite les doublons)
        foreach ($teams as $index1 => $team1) {
            for ($index2 = $index1 + 1; $index2 < count($teams); $index2++) {
                $team2 = $teams[$index2];
    
                // Vérifie si cette rencontre existe déjà
                $existingChampionship = $this->entityManager->getRepository(Championship::class)
                    ->findOneBy([
                        'blueTeam' => $team1,
                        'greenTeam' => $team2,
                        'championshipList' => $championshipList
                    ]);


                $encounters = $encounterRepository->findBy(['matches' => null, 'myChampionshipList' => $championshipList]);
                $encounterRes = null;
                foreach ($encounters as $encounter) {
                    $s = $encounter->getSlot();
                    $teamsBad = $s->getTeams()->toArray();
                    if (!in_array($team1, $teamsBad, true) and !in_array($team2, $teamsBad, true))
                    {
                        $encounterRes = $encounter;
                        break;
                    }
                }

    
                // Si la rencontre n'existe pas, on la crée
                if (!$existingChampionship) {
                    $championship = new Championship();
                    $championship->setBlueTeam($team1);
                    $championship->setGreenTeam($team2);
                    $championship->setChampionshipList($championshipList); // Associe le championnat à la liste
                    $championship->setState(State::NOT_STARTED);  // L'état initial peut être "Non Commencé"
                    $championship->setEncounter($encounterRes);

                    if ($encounterRes){
                        $encounterRes->getSlot()->addTeam($team1);
                        $encounterRes->getSlot()->addTeam($team2);
                    }
    
                    // Sauvegarde la rencontre
                    $this->entityManager->persist($championship);
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

        
        // Rediriger vers la même page avec la sélection du championnat déjà en place
        return $this->redirectToRoute('app_championship_index', [
            'championshiplist_id' => $championship->getChampionshipList()->getId()
        ]);
    }

    #[Route('/championship/delete_all', name: 'app_championship_delete_all', methods: ['POST'])]
    public function deleteAllChampionships(): Response
    {
        // Récupérer tous les matchs existants
        $championships = $this->entityManager->getRepository(Championship::class)->findAll();

        // Supprimer tous les matchs
        foreach ($championships as $championship) {

            if ($championship->getEncounter()){
                $slot = $championship->getEncounter()->getSlot();
                foreach ($slot->getTeams() as $team){
                    $slot->removeTeam($team);
                }
            }
            
            $this->entityManager->remove($championship);
        }


        // Appliquer les modifications
        $this->entityManager->flush();

        // Rediriger vers la page du championnat pour actualiser l'affichage (table vide)
        return $this->redirectToRoute('app_championship_index');
    }
}
