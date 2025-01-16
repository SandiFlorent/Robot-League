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
use App\Form\ImportChampionshipType;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
                $offset = max(0, ($currentPage - 1) * self::ITEMS_PER_PAGE);

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
                    $this->entityManager->flush();
                }
            }
        }
    
        // Sauvegarde toutes les nouvelles rencontres (au cas où)
        $this->entityManager->flush();
    }

    #[Route('/{id}/save_score', name: 'app_championship_save_score', methods: ['POST'])]
    public function saveScore(Request $request, Championship $championship): Response
    {
        // Récupère les données du formulaire
        $blueScore = $request->request->get('blueScore');
        $greenScore = $request->request->get('greenScore');
        $stateName = $request->request->get('state');
        $isLocked = $request->request->get('isLocked');

        // Met à jour les scores
        $championship->setBlueGoal($blueScore);
        $championship->setGreenGoal($greenScore);
        $championship->setLocked($isLocked);

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

    #[Route('/championship/elimination', name: 'app_championship_elimination')]
    public function eliminationPhase(Request $request): Response
    {
        // Récupérer le champ de la query string
        $championshiplistId = $request->query->get('championshiplist_id');
        
        if (!$championshiplistId) {
            throw $this->createNotFoundException('Championship list ID not provided.');
        }
    
        // Trouver le championnat par ID
        $championshipList = $this->entityManager->getRepository(ChampionshipList::class)->find($championshiplistId);
        if (!$championshipList) {
            throw $this->createNotFoundException('Championship list not found.');
        }
        
        // Récupérer tous les matchs d'élimination existants
        $existingMatches = $this->entityManager->getRepository(Championship::class)->findBy([
            'championshipList' => $championshipList,
            'IsElimination' => true
        ]);
    
        // Regrouper les matchs par round
        $matchesByRound = $this->groupMatchesByRound($existingMatches);
    
        // Vérifier si l'utilisateur est un organisateur
        $isOrganisateur = $this->isGranted('ROLE_ORGANISATEUR');
    
        // Vérifier si tous les matchs du round actuel sont terminés
        $currentRound = $this->getCurrentRound($championshipList);
        $matches = $this->entityManager->getRepository(Championship::class)->findBy([
            'championshipList' => $championshipList,
            'round' => $currentRound,
            'IsElimination' => true
        ]);
    
        $allMatchesFinished = true;
        foreach ($matches as $match) {
            if (!in_array($match->getState(), [State::WIN_BLUE, State::WIN_GREEN])) {
                $allMatchesFinished = false;
                break;
            }
        }
    
        // Si tous les matchs sont terminés, générer les matchs pour le round suivant
        if ($allMatchesFinished) {
            // Générer les matchs du round suivant
            $this->generateNextRoundMatches($championshipList, $currentRound);
            // Regrouper les matchs après génération du round suivant
            $existingMatches = $this->entityManager->getRepository(Championship::class)->findBy([
                'championshipList' => $championshipList,
                'IsElimination' => true
            ]);
            $matchesByRound = $this->groupMatchesByRound($existingMatches);
        }
    
        // Rendre la vue avec les matchs d'élimination
        return $this->render('championship/elimination.html.twig', [
            'championship_list' => $championshipList,
            'matches_by_round' => $matchesByRound,
            'is_organisateur' => $isOrganisateur, // Passer l'info sur si c'est un organisateur
            'championship_lists' => $this->entityManager->getRepository(ChampionshipList::class)->findAll(),
            'selected_championship_list' => $championshipList, // Sélectionner la bonne liste de championnats
        ]);
    }
        
    #[Route('/championship/generate-elimination/{championshiplist_id}', name: 'app_championship_generate_elimination')]
    public function generateEliminationMatchesForChampionship(int $championshiplist_id): Response
    {
        // Récupérer le championnat
        $championshipList = $this->entityManager->getRepository(ChampionshipList::class)->find($championshiplist_id);
        if (!$championshipList) {
            throw $this->createNotFoundException('Championship list not found.');
        }
        
        // Vérifier si des matchs existent déjà pour ce championnat
        $existingMatches = $this->entityManager->getRepository(Championship::class)->findBy([
            'championshipList' => $championshipList,
            'IsElimination' => true
        ]);
        
        // Si aucun match n'existe, créer les matchs d'élimination
        if (empty($existingMatches)) {
            $teams = $championshipList->getTeams()->toArray();
            $this->generateEliminationMatches($teams, $championshipList);
        }
        
        // Rediriger l'admin vers la page d'affichage des matchs
        return $this->redirectToRoute('app_championship_elimination', ['championshiplist_id' => $championshipList->getId()]);
    }
    
    private function generateEliminationMatches(array $teams, ChampionshipList $championshipList): array
    {
        // Filtrer les équipes qualifiées
        $qualifiedTeams = array_filter($teams, function ($team) use ($championshipList) {
            return $team->isQualifiedForElimination($championshipList);
        });

        // Réindexer les équipes qualifiées après filtrage
        $qualifiedTeams = array_values($qualifiedTeams);

        // Mélanger les équipes qualifiées pour un tirage au sort aléatoire
        shuffle($qualifiedTeams); 

        $totalTeams = count($qualifiedTeams);
        $matches = [];
        $round = 1;

        // Créer les matchs par paires
        for ($i = 0; $i < $totalTeams; $i += 2) {
            if ($i + 1 < $totalTeams) {
                // Créer un match entre deux équipes qualifiées
                $match = new Championship();
                $match->setBlueTeam($qualifiedTeams[$i]);
                $match->setGreenTeam($qualifiedTeams[$i + 1]);
                $match->setChampionshipList($championshipList);
                $match->setState(State::NOT_STARTED);  // État initial du match
                $match->setElimination(true);  // Indiquer que c'est un match d'élimination
                $match->setRound($round);  // Le round actuel
                $this->entityManager->persist($match);
                $matches[] = $match;
            }
        }

        $this->entityManager->flush();  // Sauvegarder les matchs

        return $matches;
    }
    
    private function groupMatchesByRound(array $matches): array
    {
        $matchesByRound = [];
        
        // Organiser les matchs par round
        foreach ($matches as $match) {
            $round = $match->getRound();
            if (!isset($matchesByRound[$round])) {
                $matchesByRound[$round] = [];
            }
            $matchesByRound[$round][] = $match;
        }
        
        // Trier les rounds par ordre croissant
        ksort($matchesByRound);
        
        return $matchesByRound;
    }
    
    #[Route('/championship/next-round/{championshiplist_id}', name: 'app_championship_next_round')]
    public function nextRound(int $championshiplist_id): Response
    {
        // Récupérer la liste des championnats
        $championshipList = $this->entityManager->getRepository(ChampionshipList::class)->find($championshiplist_id);
        if (!$championshipList) {
            throw $this->createNotFoundException('Championship list not found.');
        }
    
        // Récupérer le round actuel
        $currentRound = $this->getCurrentRound($championshipList);
    
        // Vérifier si tous les matchs du round actuel sont terminés
        $matches = $this->entityManager->getRepository(Championship::class)->findBy([
            'championshipList' => $championshipList,
            'round' => $currentRound,
            'IsElimination' => true
        ]);
    
        $allMatchesFinished = true;
        foreach ($matches as $match) {
            if (!in_array($match->getState(), [State::WIN_BLUE, State::WIN_GREEN])) {
                $allMatchesFinished = false;
                break;
            }
        }
    
        // Si tous les matchs sont terminés, générer les matchs du round suivant
        if ($allMatchesFinished) {
            $matchesForNextRound = $this->generateNextRoundMatches($championshipList, $currentRound);
    
            // Rediriger ou afficher les matchs du round suivant
            return $this->render('championship/elimination.html.twig', [
                'championship_list' => $championshipList,
                'matches_by_round' => $this->groupMatchesByRound($matchesForNextRound),
            ]);
        }
    
        // Si tous les matchs ne sont pas terminés, rediriger
        $this->addFlash('info', 'Tous les matchs du round actuel ne sont pas encore terminés.');
        return $this->redirectToRoute('app_championship_elimination', [
            'championshiplist_id' => $championshipList->getId()
        ]);
    }
    
    private function generateNextRoundMatches(ChampionshipList $championshipList, int $currentRound): array
    {
        $matches = [];
        $round = $currentRound + 1;
    
        // Récupérer les gagnants des matchs précédents
        $winningTeams = $this->entityManager->getRepository(Championship::class)->findBy([
            'championshipList' => $championshipList,
            'round' => $currentRound,
            'state' => [State::WIN_BLUE, State::WIN_GREEN]
        ]);
    
        // Mélanger les gagnants pour créer les matchs du round suivant
        shuffle($winningTeams);
        for ($i = 0; $i < count($winningTeams); $i += 2) {
            if ($i + 1 < count($winningTeams)) {
                $match = new Championship();
                $match->setBlueTeam($winningTeams[$i]->getWinner());
                $match->setGreenTeam($winningTeams[$i + 1]->getWinner());
                $match->setChampionshipList($championshipList);
                $match->setState(State::NOT_STARTED);  // État initial
                $match->setElimination(true);
                $match->setRound($round);
                $this->entityManager->persist($match);
                $matches[] = $match;
            }
        }
    
        $this->entityManager->flush();
    
        return $matches;
    }
    
    private function getCurrentRound(ChampionshipList $championshipList): int
    {
        // Récupérer les matchs d'élimination
        $matches = $this->entityManager->getRepository(Championship::class)->findBy([
            'championshipList' => $championshipList,
            'IsElimination' => true
        ]);
        
        // Vérifier si des matchs existent
        if (empty($matches)) {
            // Aucun match n'a été créé, donc le round actuel est 1
            return 1;
        }
        
        // Extraire les rounds et retourner le round maximum
        $rounds = array_map(fn($match) => $match->getRound(), $matches);
        return max($rounds);
    }

    #[Route('/export', name: 'app_championship_export')]
    public function export(Request $request, ChampionshipListRepository $championshipListRepository): Response
    {
        $championshiplistId = $request->query->get('id');

        // Récupérer les championnats filtrés en fonction des paramètres
        $championshipList = $championshipListRepository->find($championshiplistId);

        $championships = [];
        
        if ($championshipList){
            $championship = $championshipList->getMatches();
        }

        // Convertir les championnats en un tableau de données à exporter
        $championshipData = [];
        foreach ($championships as $championship) {
            $championshipData[] = [
                'id' => $championship->getId(),
                'blueGoal' => $championship->getBlueGoal(),
                'greenGoal' => $championship->getGreenGoal(),
                'state' => $championship->getState(),
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

    #[Route('/import/{idChampionshipList}', name: 'app_championship_import')]
    public function import(Request $request, ChampionshipListRepository $championshipListRepository, int $idChampionshipList): Response
    {
        $form = $this->createForm(ImportChampionshipType::class);
        $form->handleRequest($request);

        $championships = $championshipListRepository->find($idChampionshipList)->getMatches();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            
            // Traiter le fichier JSON
            $this->importChampionshipsFromJson($file, $championships);

            $this->addFlash('success', 'Les championnats ont été importés avec succès!');
            
            return $this->redirectToRoute('app_championship_index'); // rediriger vers une autre page après l'import
        }

        return $this->render('championship/import_championships.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function importChampionshipsFromJson(UploadedFile $file, PersistentCollection $championships): void
    {
        // Lire le contenu du fichier JSON
        $jsonContent = file_get_contents($file->getPathname());
        $championshipsData = json_decode($jsonContent, true);

        if ($championshipsData === null) {
            throw new \Exception("Le fichier JSON est mal formaté.");
        }

        // Parcourir les données et insérer chaque championnat dans la base de données
        foreach ($championshipsData as $data) {
            $championshipRes = null;
            foreach($championships as $championship){
                if ($data['id'] == $championship->getId()){
                    $championshipRes = $championship;
                }
            }

            if ($championshipRes == null or !$championshipRes->isLocked() or $championshipRes->isLocked() == false){
                $championshipRes->SetBlueGoal($data['blueGoal']);
                $championshipRes->SetGreenGoal($data['greenGoal']);
                $championshipRes->SetState(State::from($data['state']));
            }
        }

        // Sauvegarder les données dans la base de données
        $this->entityManager->flush();
    }
}
