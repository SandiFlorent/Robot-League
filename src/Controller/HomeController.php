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
    #[Route('/{_locale}/', name: 'app_home')]
    public function index(
        Request $request,
        ChampionshipRepository $championshipRepository,
        ChampionshipListRepository $championshipListRepository,
        FieldRepository $fieldRepository,  // Ajout de FieldRepository
        SlotRepository $slotRepository     // Ajout de SlotRepository
    ): Response {
        // Récupérer les paramètres de la requête
        $championshiplistId = $request->query->get('championshiplist_id');
        $fieldId = $request->query->get('field_id');
        $slotId = $request->query->get('slot_id');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 6; // Nombre de résultats par page
    
        // Récupérer la liste des championnats
        $championshipLists = $championshipListRepository->findAll();
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
        ]);
    }

}

