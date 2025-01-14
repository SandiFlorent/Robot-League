<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Team;
use App\Form\UserType;
use App\Entity\ChampionshipList;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TeamRepository;


#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/user/new', name: 'app_user_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $championshipId = $request->get('user')['championship'] ?? null;

        $form = $this->createForm(UserType::class, $user, [
            'championship_id' => $championshipId
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isMethod('GET')) {
            // Associer l'utilisateur comme creator si une équipe a été sélectionnée
            $selectedTeam = $form->get('myTeams')->getData();
            if ($selectedTeam) {
                $selectedTeam->setCreator($user);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/edit', name: 'app_user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'ID du championnat à partir de la requête
        $championshipId = $request->get('user')['championship'] ?? null;
        $championship = $championshipId
            ? $entityManager->getRepository(ChampionshipList::class)->find($championshipId)
            : null;
    
        // Initialisation des variables pour l'équipe de l'utilisateur et les équipes sans créateur
        $userTeam = null;
        $teamsWithoutCreator = [];
        if ($championship) {
            // Recherche de l'équipe existante de l'utilisateur dans ce championnat
            $userTeam = $entityManager->getRepository(Team::class)->findOneBy([
                'championshipList' => $championship,
                'creator' => $user
            ]);
    
            // Recherche des équipes sans créateur dans ce championnat
            $teamsWithoutCreator = $entityManager->getRepository(Team::class)->findBy([
                'championshipList' => $championship,
                'creator' => null
            ]);
        }
    
        // Création du formulaire
        $form = $this->createForm(UserType::class, $user, [
            'championship_id' => $championship ? $championship->getId() : null,
            'user' => $user,
            'userTeam' => $userTeam
        ]);
    
        // Traitement de la soumission du formulaire
        $form->handleRequest($request);
    
        // Vérification de la soumission du formulaire et gestion des actions spécifiques pour attribuer ou retirer une équipe
        if ($form->isSubmitted()) {
            // Action pour "Afficher les équipes"
            if ($request->request->has('reload') && $request->get('reload') == 1) {
                // Recharge les équipes sans créateur pour afficher les équipes possibles dans le championnat
                $teamsWithoutCreator = $entityManager->getRepository(Team::class)->findBy([
                    'championshipList' => $championship,
                    'creator' => null
                ]);
            }
    
            // Suppression du créateur de l'équipe de l'utilisateur
            if ($request->request->has('removeCreator') && $request->get('removeCreator') == '1') {
                if ($userTeam) {
                    $userTeam->setCreator(null);
                    $entityManager->persist($userTeam);
                    $entityManager->flush();
                    $this->addFlash('success', 'Créateur retiré avec succès.');
                }
            }
    
            // Attribution d'une équipe à l'utilisateur
            if ($request->request->has('assign_team')) {
                $teamId = $request->get('assign_team');
                $team = $entityManager->getRepository(Team::class)->find($teamId);
                if ($team) {
                    // Ajouter l'équipe à l'utilisateur
                    $user->addMyTeam($team);
                    $entityManager->persist($team);
                    $entityManager->persist($user);
                    $entityManager->flush();
    
                    $this->addFlash('success', 'Team attribuée avec succès.');
                }
            }
    
            // Retrait d'une équipe de l'utilisateur
            if ($request->request->has('remove_team')) {
                $teamId = $request->get('remove_team');
                $team = $entityManager->getRepository(Team::class)->find($teamId);
                if ($team) {
                    // Retirer l'équipe de l'utilisateur
                    $user->removeMyTeam($team);
                    $entityManager->persist($team);
                    $entityManager->persist($user);
                    $entityManager->flush();
    
                    $this->addFlash('success', 'Team retirée avec succès.');
                }
            }
    
            // Traitement du reste du formulaire (mise à jour des données utilisateur)
            if ($request->request->has('submit') && $form->isValid()) {
                // Mise à jour des informations de l'utilisateur
                $user->setEmail($form->get('email')->getData());
                if ($form->get('password')->getData()) {
                    $user->setPassword(password_hash($form->get('password')->getData(), PASSWORD_BCRYPT));
                }
                $user->setRoles($form->get('roles')->getData());
    
                // Persister l'utilisateur et les changements dans la base de données
                $entityManager->persist($user);
                $entityManager->flush();
    
                $this->addFlash('success', 'Utilisateur modifié avec succès.');
                return $this->redirectToRoute('app_user_index');
            }
        }
    
        // Rendu du template avec le formulaire, l'utilisateur, et les équipes
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'teamsWithoutCreator' => $teamsWithoutCreator,
            'userTeam' => $userTeam,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->get('_token'))) {
                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('notice', 'Utilisateur supprimé avec succès');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
