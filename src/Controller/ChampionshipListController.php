<?php

namespace App\Controller;

use App\Entity\ChampionshipList;
use App\Form\ChampionshipListType;
use App\Entity\Slot;
use App\Form\SlotType;
use App\Entity\Field;
use App\Form\FieldType;
use App\Entity\Encounter;
use App\Repository\ChampionshipListRepository;
use App\Repository\ChampionshipRepository;
use App\Repository\FieldRepository;
use App\Repository\SlotRepository;
use App\Repository\EncounterRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/{_locale}/championship/list')]
final class ChampionshipListController extends AbstractController
{
    private const ITEMS_PER_PAGE = 10;

    #[Route(name: 'app_championship_list_index', methods: ['GET'])]
    public function index(ChampionshipListRepository $championshipListRepository, Request $request): Response
    {
        $currentPage = max(1, $request->query->getInt('page', 1));

        // Compter le nombre total de championnats
        $totalItems = $championshipListRepository->count([]);

        // Calculer le nombre total de pages
        $totalPages = ceil($totalItems / self::ITEMS_PER_PAGE);

        // Assurer que la page courante ne dépasse pas le nombre total de pages
        $currentPage = min($currentPage, $totalPages);

        // Calculer l'offset
        $offset = max(0, ($currentPage - 1) * self::ITEMS_PER_PAGE);

        // Récupérer les championnats paginés
        $championshipLists = $championshipListRepository->createQueryBuilder('c')
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

        return $this->render('championship_list/index.html.twig', [
            'championship_lists' => $championshipLists,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_championship_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $championshipList = new ChampionshipList();
        $form = $this->createForm(ChampionshipListType::class, $championshipList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($championshipList);
            $entityManager->flush();

            $id = $championshipList->getId();
            return $this->redirectToRoute('app_championship_list_new_field', [
                'id' => $id
            ]);
        }

        return $this->render('championship_list/new.html.twig', [
            'championship_list' => $championshipList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_championship_list_show', methods: ['GET'])]
    public function show(ChampionshipList $championshipList): Response
    {
        return $this->render('championship_list/show.html.twig', [
            'championship_list' => $championshipList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_championship_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ChampionshipList $championshipList, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChampionshipListType::class, $championshipList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_championship_list_edit', ['id' => $championshipList->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('championship_list/edit.html.twig', [
            'championship_list' => $championshipList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_championship_list_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ChampionshipList $championshipList,
        EntityManagerInterface $entityManager,
        ChampionshipRepository $championshipRepository,
        SlotRepository $slotRepository,
        FieldRepository $fieldRepository,
        EncounterRepository $encounterRepository,
        TeamRepository $teamRepository
    ): Response {

        $encounters = $encounterRepository->findBy(['myChampionshipList' => $championshipList]);
        $championships = $championshipRepository->findBy(['championshipList' => $championshipList]);
        $fields = $fieldRepository->findBy(['championshipList' => $championshipList]);
        $slots = $slotRepository->findBy(['championshipList' => $championshipList]);
        $teams = $teamRepository->findBy(['championshipList' => $championshipList]);

        foreach ($encounters as $encounter) {
            $entityManager->remove($encounter);
            $entityManager->flush();
        }

        foreach ($championships as $championship) {
            $entityManager->remove($championship);
            $entityManager->flush();
        }

        foreach ($fields as $field) {
            $entityManager->remove($field);
            $entityManager->flush();
        }

        foreach ($slots as $slot) {
            $entityManager->remove($slot);
            $entityManager->flush();
        }

        foreach ($teams as $team) {
            $entityManager->remove($team);
            $entityManager->flush();
        }

        if ($this->isCsrfTokenValid('delete' . $championshipList->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($championshipList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_championship_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('{id}/newSlot', name: 'app_championship_list_new_slot', methods: ['GET', 'POST'])]
    public function newSlot(Request $request, EntityManagerInterface $entityManager, ChampionshipList $championshipList, FieldRepository $FieldRepository, ChampionshipListRepository $championshipListRepository): Response
    {
        $slot = new Slot();
        $form = $this->createForm(SlotType::class, $slot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); // Récupère l'objet Slot avec les données du formulaire

            if (!$this->isSlotValid($championshipList, $data)) {
                return $this->redirectToRoute('app_championship_list_new_slot', [
                    'id' => $championshipList->getId(),
                ]);
            }

            // Les données du formulaire
            $dateDebut = $data->getDateDebut();  // Date de début
            $dateEnd = $data->getDateEnd();      // Date de fin
            $length = $data->getLength();        // Longueur en minutes

            if ($dateDebut && $dateEnd && $length > 0) {
                $slots = [];

                $currentSlotStart = $dateDebut;
                while ($currentSlotStart < $dateEnd) {
                    $currentSlotEnd = clone $currentSlotStart;
                    $currentSlotEnd->modify("+$length minutes");

                    if ($currentSlotEnd <= $dateEnd) {
                        $slot = new Slot();
                        $slot->setDateDebut($currentSlotStart);
                        $slot->setDateEnd($currentSlotEnd);
                        $slot->setLength($length);
                        $slots[] = $slot;
                    }

                    $currentSlotStart = $currentSlotEnd;
                }

                foreach ($slots as $slo) {
                    $championshipList->addSlot($slo);
                    $entityManager->persist($slo);
                }

                $fields = $FieldRepository->findBy(['championshipList' => $championshipList]);

                foreach ($slots as $slo) {
                    foreach ($fields as $field) {
                        $encounter = new Encounter();
                        $encounter->setField($field);
                        $encounter->setSlot($slo);
                        $encounter->setMyChampionshipList($championshipList);
                        $entityManager->persist($encounter);
                    }
                }

                $entityManager->flush();

                $id = $championshipList->getId();
                return $this->redirectToRoute('app_championship_list_new_slot', [
                    'id' => $id
                ]);
            }
        }

        return $this->render('championship_list/newSlot.html.twig', [
            'championship_list' => $championshipList,
            'form' => $form,
        ]);
    }

    #[Route('{idSlot}/{id}/deleteSlot', name: 'app_championship_list_delete_slot', methods: ['GET', 'POST'])]
    public function deleteSlot(Request $request, ChampionshipList $championshipList, EntityManagerInterface $entityManager, int $idSlot, SlotRepository $slotRepository, EncounterRepository $encounterRepository): Response
    {
        $slot = $slotRepository->findOneBy(['id' => $idSlot]);

        $encounters = $encounterRepository->findBy(['slot' => $idSlot]);

        foreach ($encounters as $encounter) {
            $entityManager->remove($encounter);
        }

        $entityManager->remove($slot);
        $entityManager->flush();

        // Redirection vers la page des slots
        return $this->redirectToRoute('app_championship_list_new_slot', [
            'id' => $championshipList->getId(),
        ]);
    }

    #[Route('{id}/newField', name: 'app_championship_list_new_field', methods: ['GET', 'POST'])]
    public function newField(Request $request, EntityManagerInterface $entityManager, ChampionshipList $championshipList, SlotRepository $slotRepository): Response
    {
        $field = new Field();
        $form = $this->createForm(FieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $championshipList->addField($field);

            $entityManager->persist($field);

            $slots = $slotRepository->findBy(['championshipList' => $championshipList]);

            foreach ($slots as $slo) {
                $encounter = new Encounter();
                $encounter->setField($field);
                $encounter->setSlot($slo);
                $encounter->setMyChampionshipList($championshipList);
                $entityManager->persist($encounter);
            }

            $entityManager->flush();

            $id = $championshipList->getId();
            return $this->redirectToRoute('app_championship_list_new_field', [
                'id' => $id
            ]);
        }

        return $this->render('championship_list/newField.html.twig', [
            'championship_list' => $championshipList,
            'form' => $form,
        ]);
    }

    #[Route('{idField}/{id}/deleteField', name: 'app_championship_list_delete_field', methods: ['GET', 'POST'])]
    public function deleteField(Request $request, ChampionshipList $championshipList, EntityManagerInterface $entityManager, int $idField, FieldRepository $fieldRepository, EncounterRepository $encounterRepository): Response
    {
        $field = $fieldRepository->findOneBy(['id' => $idField]);

        $encounters = $encounterRepository->findBy(['field' => $idField]);

        foreach ($encounters as $encounter) {
            $entityManager->remove($encounter);
        }

        $entityManager->remove($field);
        $entityManager->flush();

        return $this->redirectToRoute('app_championship_list_new_field', [
            'id' => $championshipList->getId()
        ]);
    }

    #[Route('{id}/teams', name: 'app_championship_list_teams', methods: ['GET', 'POST'])]
    public function teamsChange(Request $request, ChampionshipList $championshipList, EntityManagerInterface $entityManager, TeamRepository $teamRepository): Response
    {
        $teams = $championshipList->getTeams();

        $teamsvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => true]);

        $teamsnotvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => false]);

        return $this->render('championship_list/gestionequipe.html.twig', [
            'championship_list' => $championshipList,
            'teamsvalidated' => $teamsvalidated,
            'teamsnotvalidated' => $teamsnotvalidated,
        ]);
    }

    #[Route('{id}-{idTeam}/teamValidate', name: 'app_championship_list_validate_team', methods: ['GET', 'POST'])]
    public function teamValidate(Request $request,  EntityManagerInterface $entityManager, TeamRepository $teamRepository, int $idTeam, ChampionshipList $championshipList): Response
    {
        $team = $teamRepository->findOneBy(['id' => $idTeam]);
        $team->setAccepted(true);
        $entityManager->flush();

        $teams = $championshipList->getTeams();
        $teamsvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => true]);
        $teamsnotvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => false]);

        return $this->render('championship_list/gestionequipe.html.twig', [
            'championship_list' => $championshipList,
            'teamsvalidated' => $teamsvalidated,
            'teamsnotvalidated' => $teamsnotvalidated,
        ]);
    }

    #[Route('{id}-{idTeam}/teamUnvalidate', name: 'app_championship_list_unvalidate_team', methods: ['GET', 'POST'])]
    public function teamUnvalidate(Request $request,  EntityManagerInterface $entityManager, TeamRepository $teamRepository, int $idTeam, ChampionshipList $championshipList): Response
    {
        $team = $teamRepository->findOneBy(['id' => $idTeam]);
        $team->setAccepted(false);
        $entityManager->flush();

        $teams = $championshipList->getTeams();
        $teamsvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => true]);
        $teamsnotvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => false]);

        return $this->render('championship_list/gestionequipe.html.twig', [
            'championship_list' => $championshipList,
            'teamsvalidated' => $teamsvalidated,
            'teamsnotvalidated' => $teamsnotvalidated,
        ]);
    }

    //Function check if the slot doesn't overlap with another slot of the same field in the same championship
    private function isSlotValid(ChampionshipList $championshipList, Slot $slot): bool
    {
        // Check if the slot is not before or after the championship
        $slots = $championshipList->getSlot();
        if ($slot->getDateDebut() < $championshipList->getDateStart() || $slot->getDateEnd() > $championshipList->getDateEnd()) {
            // Build error flash message
            $this->addFlash('error', 'Le créneau horaire ne peut pas être en dehors de la durée du championnat');
            return false;
        }

        $slots = $championshipList->getSlot();
        foreach ($slots as $slot) {
            if (($slot->getDateDebut() >= $slot->getDateDebut() && $slot->getDateDebut() <= $slot->getDateEnd()) ||
                ($slot->getDateEnd() >= $slot->getDateDebut() && $slot->getDateEnd() <= $slot->getDateEnd())
            ) {
                $this->addFlash('error', 'Le créneau horaire ne peut pas se chevaucher avec un autre créneau horaire');
                return false;
            }
        }
        return true;
    }
}
