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
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        EntityManagerInterface $entityManager,
        TeamRepository $teamRepository,
        ChampionshipListRepository $championshipListRepository
    ) {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
        $this->championshipListRepository = $championshipListRepository;
    }

    #[Route(name: 'app_championship_index', methods: ['GET'])]
    public function index(
        ChampionshipRepository $championshipRepository,
        Request $request
    ): Response {
        // Récupérer tous les championnats disponibles
        $championshipLists = $this->championshipListRepository->findAll();

        // Récupérer l'ID du championnat sélectionné
        $championshiplistId = $request->query->get('championshiplist_id');

        $selectedChampionshipList = null;
        $championships = [];
        $pagination = null;

        if ($championshiplistId) {
            $selectedChampionshipList = $this->championshipListRepository->find($championshiplistId);

            if ($selectedChampionshipList) {
                $currentPage = max(1, $request->query->getInt('page', 1));

                // Compter le nombre total de matchs
                $totalItems = $championshipRepository->count(['championshipList' => $selectedChampionshipList]);

                // Calculer le nombre total de pages
                $totalPages = ceil($totalItems / self::ITEMS_PER_PAGE);

                // Assurer que la page courante ne dépasse pas le nombre total de pages
                $currentPage = min($currentPage, $totalPages);

                // Calculer l'offset
                $offset = ($currentPage - 1) * self::ITEMS_PER_PAGE;

                // Récupérer les matchs paginés
                $championships = $championshipRepository->createQueryBuilder('c')
                    ->where('c.championshipList = :championshipList')
                    ->setParameter('championshipList', $selectedChampionshipList)
                    ->orderBy('c.id', 'ASC')
                    ->setFirstResult($offset)
                    ->setMaxResults(self::ITEMS_PER_PAGE)
                    ->getQuery()
                    ->getResult();

                // Créer l'objet pagination
                $pagination = [
                    'currentPage' => $currentPage,
                    'totalPages' => $totalPages,
                    'itemsPerPage' => self::ITEMS_PER_PAGE,
                    'totalItems' => $totalItems,
                    'hasPrevious' => $currentPage > 1,
                    'hasNext' => $currentPage < $totalPages,
                ];
            }
        }

        $states = State::cases();

        return $this->render('championship/index.html.twig', [
            'championship_lists' => $championshipLists,
            'selected_championship_list' => $selectedChampionshipList,
            'championships' => $championships,
            'pagination' => $pagination,
            'states' => $states,
        ]);
    }

    // Les autres méthodes restent les mêmes...


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

        // $seenArray = [];
        // $nbmatchArray = [];
        // foreach ($teams as $team){
        //     $seenArray[$team->getId()] = [];
        //     $nbmatchArray[$team->getId()] = 0; 
        // }

        // // Récupérer la valeur minimale
        // $minValue = min($nbmatchArray);

        // // Récupérer la clé associée à la valeur minimale
        // $minKey = array_search($minValue, $array);

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


                $encounters = $encounterRepository->createQueryBuilder('e')
                    ->leftJoin('e.slot', 's')  // Jointure avec l'entité Slot
                    ->where('e.matches IS NULL')  // Critère matches null
                    ->andWhere('e.myChampionshipList = :championshipList')  // Critère pour le championnat
                    ->setParameter('championshipList', $championshipList)
                    ->orderBy('s.dateDebut', 'ASC')  // Tri par dateDebut de Slot
                    ->getQuery()
                    ->getResult();


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
                    $championship->setLocked(false);

                    if ($encounterRes){
                        $encounterRes->getSlot()->addTeam($team1);
                        $encounterRes->getSlot()->addTeam($team2);
                        $championship->setSlot($encounterRes->getSlot());
                        $championship->setField($encounterRes->getField());
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
            
            if ($championship->getEncounter()){
                $championship->getEncounter()->setMatches(null);
            }
            
            $this->entityManager->remove($championship);
        }


        // Appliquer les modifications
        $this->entityManager->flush();

        // Rediriger vers la page du championnat pour actualiser l'affichage (table vide)
        return $this->redirectToRoute('app_championship_index');
    }

    #[Route('/championship/elimination/{id}', name: 'app_championship_elimination')]
    public function eliminationPhase(int $id, Request $request): Response
    {
        // Trouver la ChampionshipList avec l'ID passé via l'URL
        $championshipList = $this->entityManager->getRepository(ChampionshipList::class)->find($id);

        if (!$championshipList) {
            throw $this->createNotFoundException('Championship list not found.');
        }

        $threshold = $championshipList->getThreshold();
        
        // Récupérer les équipes qualifiées (ajoutez un critère pour la qualification)
        $qualifiedTeams = $championshipList->getTeams()->filter(function($team) {
            return $team->isAccepted() && $team->isQualifiedForElimination(); // Critère de qualification
        })->toArray();
        
        // Limiter le nombre d'équipes qualifiées selon le seuil (threshold)
        if (count($qualifiedTeams) > $threshold) {
            $qualifiedTeams = array_slice($qualifiedTeams, 0, $threshold);
        }

        // Générer les matchs éliminatoires en fonction du nombre d'équipes
        $this->generateEliminationMatches($qualifiedTeams, $championshipList);
        
        // Rendre la vue avec les équipes qualifiées et les matchs générés
        return $this->render('championship/elimination.html.twig', [
            'championship_list' => $championshipList,
            'qualified_teams' => $qualifiedTeams,
        ]);
    }

    private function generateEliminationMatches(array $teams, ChampionshipList $championshipList)
    {
        shuffle($teams);  // Mélange des équipes pour assurer un tirage au sort aléatoire
        $totalTeams = count($teams);
        $matches = [];

        for ($i = 0; $i < $totalTeams; $i += 2) {
            if ($i + 1 < $totalTeams) {
                // Créer un match entre deux équipes
                $match = new Championship();
                $match->setBlueTeam($teams[$i]);
                $match->setGreenTeam($teams[$i + 1]);
                $match->setChampionshipList($championshipList);
                $match->setState(State::NOT_STARTED);
                $match->setElimination(true);  // Marquer comme match éliminatoire
                // Sauvegarde du match
                $this->entityManager->persist($match);
                $matches[] = $match;
            }
        }

        // Appliquer les changements en base
        $this->entityManager->flush();
    }

    private function createEliminationRound(array $teams, ChampionshipList $championshipList, string $roundType)
    {
        // Générer les matchs pour chaque tour (quart, demi-finale ou finale)
        $roundMatches = [];

        if ($roundType === 'Quart de Finale') {
            $roundMatches = $this->generateMatchesForRound($teams, $championshipList);
        } elseif ($roundType === 'Demi-Finale') {
            // Demi-finale entre les gagnants des quarts de finale
            $roundMatches = $this->generateMatchesForRound($teams, $championshipList);
        }

        // Finale
        if ($roundType === 'Demi-Finale') {
            $this->generateFinalMatch($teams, $championshipList);
        }

        // Persist all round matches
        foreach ($roundMatches as $match) {
            $this->entityManager->persist($match);
        }

        return $roundMatches;
    }

    private function generateMatchesForRound(array $teams, ChampionshipList $championshipList)
    {
        $matches = [];
        $count = count($teams);
        for ($i = 0; $i < $count; $i += 2) {
            if ($i + 1 < $count) {
                // Créer un match pour cette paire d'équipes
                $match = new Championship();
                $match->setBlueTeam($teams[$i]);
                $match->setGreenTeam($teams[$i + 1]);
                $match->setChampionshipList($championshipList);
                $match->setState(State::NOT_STARTED);
                $matches[] = $match;
            }
        }
        return $matches;
    }

    private function generateFinalMatch(array $teams, ChampionshipList $championshipList)
    {
        // Final entre les gagnants des demi-finales
        $match = new Championship();
        $match->setBlueTeam($teams[0]);
        $match->setGreenTeam($teams[1]);
        $match->setChampionshipList($championshipList);
        $match->setState(State::NOT_STARTED);
        $this->entityManager->persist($match);
    }


    #[Route('/championship/export', name: 'app_championship_export', methods: ['POST'])]
    public function export(Request $request, ChampionshipListRepository $championshipListRepository): Response
    {
        $championshiplistId = $request->query->get('championshiplist_id');

        // Récupérer les championnats filtrés en fonction des paramètres
        $championships = $championshipListRepository->findOne(["id" => $championshiplistId])->getMatches();

        // Convertir les championnats en un tableau de données à exporter
        $championshipData = [];
        foreach ($championships as $championship) {
            $championshipData[] = [
                'id' => $championship->getId(),
                'blueGoal' => $championship->getBlueGoal(),
                'greenGoal' => $championship->getGreenGoal(),
                'state' => $championship->getState()->getValue(),
            ];
        }

        // Créer la réponse avec les données JSON
        $response = new Response(
            json_encode($championshipData),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );

        // Spécifier l'entête pour forcer le téléchargement
        $response->headers->set('Content-Disposition', 'attachment; filename="championships.json"');

        return $response;
    }
}
