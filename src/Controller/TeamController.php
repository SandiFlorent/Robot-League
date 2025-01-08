<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Member;
use App\Entity\User;
use App\Form\TeamType;
use App\Form\MemberType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team')]
final class TeamController extends AbstractController
{
    #[Route(name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $team->setCreator($user);

            $entityManager->persist($team);
            $entityManager->flush();

            //Notice message when a team is created
            $this->addFlash(
                'notice',
                'Team successfully created'
            );

            $id = $team->getId();
            return $this->redirectToRoute('app_team_member', [
                'id' => $id
            ]);
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            //Notice message when a team is edited
            $this->addFlash(
                'notice',
                'Team successfully edited'
            );

            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();

            //Notice message when a team is deleted
            $this->addFlash(
                'notice',
                'Team successfully deleted'
            );

        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/teammember', name: 'app_team_member', methods: ['GET', 'POST'])]
    public function member(Request $request, Team $team ,EntityManagerInterface $entityManager): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team->addTeamMember($member);

            $entityManager->persist($member);
            $entityManager->flush();

            //Notice message when a new member is added
            $this->addFlash(
                'notice',
                'Member successfully added'
            );

            $id = $team->getId();
            return $this->redirectToRoute('app_team_member', [
                'id' => $id
            ]);
        }

        return $this->render('team/teammember.html.twig', [
            'team' => $team,
            'member' => $member,
            'form' => $form,
        ]);

        
    }

    #[Route('/show', name: 'app_team_show', methods: ['GET'])]
    public function show(TeamRepository $teamRepository): Response
    {
        return $this->render('team/show.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }
}
