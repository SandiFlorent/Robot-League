<?php

namespace App\Controller;

use App\Entity\ChampionshipList;
use App\Form\ChampionshipListType;
use App\Entity\Slot;
use App\Form\SlotType;
use App\Entity\Field;
use App\Form\FieldType;
use App\Repository\ChampionshipListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function newSlot(Request $request, EntityManagerInterface $entityManager , ChampionshipList $championshipList): Response
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

            
                $championshipList->addSlot($slot);

                $entityManager->persist($slot);
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

    #[Route('{id}/newField', name: 'app_championship_list_new_field', methods: ['GET', 'POST'])]
    public function newField(Request $request, EntityManagerInterface $entityManager, ChampionshipList $championshipList): Response
    {
        $field = new Field();
        $form = $this->createForm(FieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $championshipList->addField($field);

            $entityManager->persist($field);
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
}
