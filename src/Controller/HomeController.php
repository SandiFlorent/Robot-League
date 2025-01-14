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
        FieldRepository $fieldRepository,
        SlotRepository $slotRepository
    ): Response {
        $championshiplistId = $request->query->get('championshiplist_id');
        $fieldId = $request->query->get('field_id');
        $slotId = $request->query->get('slot_id');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 6; // Nombre de résultats par page

        $championshipLists = $championshipListRepository->findAll();
        $fields = [];
        $slots = [];

        if ($championshiplistId) {
            $championshipList = $championshipListRepository->find($championshiplistId);
            $fields = $fieldRepository->findBy(['championshipList' => $championshipList]);
            $slots = $slotRepository->findBy(['championshipList' => $championshipList]);

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
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

}

