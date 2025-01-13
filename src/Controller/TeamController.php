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
use App\Repository\ChampionshipListRepository;

#[Route('{_locale}/team')]
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
            // L'utilisateur qui crée l'équipe (si applicable)
            $user = $this->getUser();
            $team->setCreator($user);

            // Récupérer le championnat sélectionné et l'assigner à l'équipe
            $championshipList = $form->get('championshipList')->getData();
            $team->setChampionshipList($championshipList);

            $entityManager->persist($team);
            $entityManager->flush();

            //Message de succès après la création de l'équipe
            $this->addFlash('notice', 'Team successfully created');

            // Rediriger vers la page de gestion des membres de l'équipe
            $id = $team->getId();

            return $this->redirectToRoute('app_team_member', [
                'id' => $id
            ]);
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }
 
    #[Route('/choose-championship-member', name: 'app_choose_championship_member', methods: ['GET', 'POST'])]
    public function chooseChampionshipForMember(Request $request, TeamRepository $teamRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté et une instance valide de User
        if (!$user instanceof \App\Entity\User) {
            // Si l'utilisateur n'est pas authentifié ou n'est pas une instance valide, on génère une exception
            throw $this->createAccessDeniedException('Access denied. You must be logged in as a valid user.');
        }

        // Récupérer l'ID du championnat à partir de la requête (si disponible)
        $championshipId = $request->query->get('championship');

        // Si un championnat a été sélectionné
        if ($championshipId) {
            // Récupérer les équipes de l'utilisateur dans ce championnat
            $teams = $teamRepository->findBy([
                'championshipList' => $championshipId,
                'creator' => $user // Assurez-vous que l'utilisateur est le créateur de l'équipe
            ]);

            // Si des équipes sont trouvées pour l'utilisateur dans ce championnat
            if (!empty($teams)) {
                // On redirige vers la gestion des membres de la première équipe de l'utilisateur dans ce championnat
                return $this->redirectToRoute('app_team_member', ['id' => $teams[0]->getId()]);
            } else {
                // Si l'utilisateur n'a pas d'équipe dans ce championnat, afficher un message d'erreur
                $this->addFlash('error', 'Vous n\'avez aucune équipe dans ce championnat.');
            }
        }

        // Récupérer les équipes de l'utilisateur et leurs championnats associés
        $teams = $user->getMyTeams();

        // Récupérer les championnats uniques associés à l'utilisateur
        $championships = [];
        foreach ($teams as $team) {
            $championship = $team->getChampionshipList();
            if ($championship && !in_array($championship, $championships, true)) {
                $championships[] = $championship;
            }
        }

        // Retourner la vue avec les championnats associés à l'utilisateur
        return $this->render('team/choose_championship_member.html.twig', [
            'championships' => $championships,
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
        // Vérifier si l'utilisateur connecté est le créateur de l'équipe
        $user = $this->getUser();
        if ($team->getCreator() !== $user) {
            // Si ce n'est pas le créateur, on renvoie une exception "Access Denied"
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette équipe.');
        }
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
        public function show(Request $request, TeamRepository $teamRepository, ChampionshipListRepository $championshipListRepository): Response
    {
        // Récupérer tous les championnats pour le formulaire de filtre
        $championshipList = $championshipListRepository->findAll();

        // Vérifier si un championnat a été sélectionné
        $selectedChampionship = null;
        $teams = [];

        if ($request->query->get('championship')) {
            $selectedChampionship = $championshipListRepository->find($request->query->get('championship'));
            
            if ($selectedChampionship) {
                // Filtrer les équipes qui appartiennent au championnat sélectionné
                $teams = $teamRepository->findBy(['championshipList' => $selectedChampionship]);
            }
        }

        if (!$selectedChampionship) {
            // Si aucun championnat n'est sélectionné, afficher toutes les équipes
            $teams = $teamRepository->findAll();
        }

        return $this->render('team/show.html.twig', [
            'teams' => $teams,
            'championshipList' => $championshipList,
            'selectedChampionship' => $selectedChampionship,
        ]);
    }
}
