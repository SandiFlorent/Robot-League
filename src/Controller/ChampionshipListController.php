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

#[Route('/championship/list')]
final class ChampionshipListController extends AbstractController
{
    #[Route(name: 'app_championship_list_index', methods: ['GET'])]
    public function index(ChampionshipListRepository $championshipListRepository): Response
    {
        return $this->render('championship_list/index.html.twig', [
            'championship_lists' => $championshipListRepository->findAll(),
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

            return $this->redirectToRoute('app_championship_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('championship_list/edit.html.twig', [
            'championship_list' => $championshipList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_championship_list_delete', methods: ['POST'])]
    public function delete(Request $request, ChampionshipList $championshipList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$championshipList->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($championshipList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_championship_list_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('{id}/newSlot', name: 'app_championship_list_new_slot', methods: ['GET', 'POST'])]
    public function newSlot(Request $request, EntityManagerInterface $entityManager , ChampionshipList $championshipList, FieldRepository $FieldRepository): Response
    {
        $slot = new Slot();
        $form = $this->createForm(SlotType::class, $slot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); // Récupère l'objet Slot avec les données du formulaire

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
                    foreach($fields as $field){
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
    public function deleteField(Request $request, ChampionshipList $championshipList, EntityManagerInterface $entityManager , int $idField, FieldRepository $fieldRepository, EncounterRepository $encounterRepository): Response
    {
        $field = $fieldRepository->findOneBy(['id' => $idField]);

        $encounters = $encounterRepository->findBy(['field' => $idField]);

        foreach ($encounters as $encounter){
            $entityManager->remove($encounter);
        }

        $entityManager->remove($field);
        $entityManager->flush();

        return $this->redirectToRoute('app_championship_list_new_field', [
            'id' => $championshipList->getId()
        ]);
    }

    #[Route('{id}/teams', name: 'app_championship_list_teams', methods: ['GET', 'POST'])]
    public function teamsChange(Request $request, ChampionshipList $championshipList, EntityManagerInterface $entityManager , TeamRepository $teamRepository): Response
    {

        $teams = $championshipList->getTeams();

        $teamsvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => true]);

        $teamsnotvalidated = $teamRepository->findBy(['championshipList' => $championshipList, 'isAccepted' => false]);

        return $this->render('championship_list/gestionequipe.html.twig', [
            'championship_list' => $championshipList,
            'teamsvalidated' => $teamsvalidated,
            'teamsvalidated' => $teamsnotvalidated,
        ]);
    }
}
