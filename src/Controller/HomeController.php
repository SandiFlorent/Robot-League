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
use App\Repository\FieldRepository;
use App\Repository\SlotRepository;

class HomeController extends AbstractController
{   

    #[Route('/', name: 'app_route')]
    public function route(){
        return $this->redirectToRoute('app_home');
    }


    #[Route('/{_locale}/', name: 'app_home')]
public function index(
    Request $request,
    ChampionshipRepository $championshipRepository,
    ChampionshipListRepository $championshipListRepository,
    FieldRepository $fieldRepository, 
    SlotRepository $slotRepository
): Response {
    // Récupérer les paramètres de la requête
    $championshiplistId = $request->query->get('championshiplist_id');
    $fieldId = $request->query->get('field_id');
    $slotId = $request->query->get('slot_id');
    $statusFilter = $request->query->get('status', 'present'); // 'past', 'present', or 'future'
    $page = max(1, (int) $request->query->get('page', 1));
    $limit = 6; // Nombre de résultats par page


    // Filtrer les championship lists en fonction de leur statut
    switch ($statusFilter) {
        case 'past':
            $championshipLists = $championshipListRepository->findPastChampionshipLists();
            break;
        case 'future':
            $championshipLists = $championshipListRepository->findFutureChampionshipLists();
            break;
        case 'present':
            $championshipLists = $championshipListRepository->findActiveAndPastChampionshipLists();
            break;
        default:
            $championshipLists = $championshipListRepository->findAll();
            break;
    }
    

    $fields = [];
    $slots = [];

    if ($championshiplistId) {
        // Récupérer les terrains et créneaux associés au championnat sélectionné
        $championshipList = $championshipListRepository->find($championshiplistId);
        $fields = $fieldRepository->findBy(['championshipList' => $championshipList]);
        $slots = $slotRepository->findBy(['championshipList' => $championshipList]);

        // Récupérer les matchs filtrés par le championnat, terrain et créneau
        $criteria = ['championshipList' => $championshiplistId];

        if ($fieldId) {
            $criteria['field'] = $fieldId;
        }
        if ($slotId) {
            $criteria['slot'] = $slotId;
        }

        $championships = $championshipRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            ($page - 1) * $limit
        );
        $totalItems = $championshipRepository->count($criteria);

    } else {
        // Si aucun championnat n'est sélectionné, récupérer tous les matchs
        $championships = $championshipRepository->findBy(
            [],
            ['id' => 'ASC'],
            $limit,
            ($page - 1) * $limit
        );
        $totalItems = $championshipRepository->count([]);

    }

    $totalPages = (int) ceil($totalItems / $limit);

    return $this->render('home/index.html.twig', [
        'championships' => $championships,
        'championshipLists' => $championshipLists,
        'fields' => $fields,
        'slots' => $slots,
        'selected_championship_id' => $championshiplistId,
        'selected_field_id' => $fieldId,
        'selected_slot_id' => $slotId,
        'statusFilter' => $statusFilter,
        'page' => $page,
        'totalPages' => $totalPages

    ]);
}

    #[Route('/export/ical', name: 'export_ical')]
    public function exportToICal(ChampionshipRepository $championshipRepository, Request $request,): Response
    {

        $championshiplistId = $request->query->get('championshiplist_id');
        $fieldId = $request->query->get('field_id');
        $slotId = $request->query->get('slot_id');

        $criteria = [];

        if ($championshiplistId) {
            $criteria['championshipList'] =  $championshiplistId;
        }
        if ($fieldId) {
            $criteria['field'] = $fieldId;
        }
        if ($slotId) {
            $criteria['slot'] = $slotId;
        }

        // Récupérer les matchs ou événements depuis la base de données
        $championships = $championshipRepository->findBy($criteria);

        // Créer le contenu du fichier iCal
        $icalContent = "BEGIN:VCALENDAR\r\n";
        $icalContent .= "VERSION:2.0\r\n";
        $icalContent .= "CALSCALE:GREGORIAN\r\n";

        foreach ($championships as $championship) {
            if($championship->getSlot()){
                $icalContent .= "BEGIN:VEVENT\r\n";
                $icalContent .= "SUMMARY:" . $championship->getGreenTeam()->getName() . " vs " . $championship->getBlueTeam()->getName() . "\r\n";  // Le nom du championnat ou de l'événement
                $icalContent .= "DESCRIPTION:" . $championship->getChampionshipList()->getChampionshipName() . " : " . $championship->getGreenTeam()->getName() . " vs " . $championship->getBlueTeam()->getName()  . "\r\n";  // La description de l'événement
                if ($championship->getField()){
                    $icalContent .= "LOCATION:" . $championship->getField()->getName() . "\r\n";  // L'emplacement
                }
                $icalContent .= "DTSTART:" . $championship->getSlot()->getDateDebut()->format('Ymd\THis\Z') . "\r\n";  // La date de début formatée en iCal
                $icalContent .= "DTEND:" . $championship->getSlot()->getDateEnd()->format('Ymd\THis\Z') . "\r\n";  // La date de fin formatée en iCal
                $icalContent .= "END:VEVENT\r\n";
            }
        }

        $icalContent .= "END:VCALENDAR\r\n";

        // Créer la réponse avec un fichier .ics
        return new Response(
            $icalContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar',
                'Content-Disposition' => 'attachment; filename="events.ics"',
            ]
        );
    }

   
#[Route('{_locale}/display/', name: 'app_display')]
public function display(
    Request $request,
    ChampionshipRepository $championshipRepository,
    ChampionshipListRepository $championshipListRepository,
    FieldRepository $fieldRepository, 
    SlotRepository $slotRepository
): Response {

    // Récupérer les paramètres de la requête
    $championshiplistId = $request->query->get('championshiplist_id');
    $fieldId = $request->query->get('field_id');
    $slotId = $request->query->get('slot_id');
    $statusFilter = $request->query->get('status', 'present'); // 'past', 'present', or 'future'
    $page = max(1, (int) $request->query->get('page', 1));
    $limit = 6; // Nombre de résultats par page


    // Filtrer les championship lists en fonction de leur statut
    switch ($statusFilter) {
        case 'past':
            $championshipLists = $championshipListRepository->findPastChampionshipLists();
            break;
        case 'future':
            $championshipLists = $championshipListRepository->findFutureChampionshipLists();
            break;
        case 'present':
            $championshipLists = $championshipListRepository->findActiveAndPastChampionshipLists();
            break;
        default:
            $championshipLists = $championshipListRepository->findAll();
            break;
    }
    

    $fields = [];
    $slots = [];

    if ($championshiplistId) {
        // Récupérer les terrains et créneaux associés au championnat sélectionné
        $championshipList = $championshipListRepository->find(2);
        $fields = $fieldRepository->findBy(['championshipList' => $championshipList]);
        $slots = $slotRepository->findBy(['championshipList' => $championshipList]);
        

        // Récupérer les matchs filtrés par le championnat, terrain et créneau
        $criteria = ['championshipList' => $championshiplistId];

        if ($fieldId) {
            $criteria['field'] = $fieldId;
        }
        if ($slotId) {
            $criteria['slot'] = $slotId;
        }

        $championships = $championshipRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            ($page - 1) * $limit
        );
        $totalItems = $championshipRepository->count($criteria);

    } else {
        // Si aucun championnat n'est sélectionné, récupérer tous les matchs
        $championships = $championshipRepository->findBy(
            [],
            ['id' => 'ASC'],
            $limit,
            ($page - 1) * $limit
        );
        $totalItems = $championshipRepository->count([]);

    }

    $totalPages = (int) ceil($totalItems / $limit);
    return $this->render('display/index.html.twig', [
 
        'championships' => $championships,
        'championshipLists' => $championshipLists,
        'fields' => $fields,
        'slots' => $slots,
        'selected_championship_id' => $championshiplistId,
        'selected_field_id' => $fieldId,
        'selected_slot_id' => $slotId,
        'statusFilter' => $statusFilter,  // Passer le filtre pour la vue
        'page' => $page,
        'totalPages' => $totalPages

    ]);
}



}