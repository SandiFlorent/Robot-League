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
        // Capture explicite de la sélection du championnat
        $championshipId = $request->get('user')['championship'] ?? null;
        $championship = $championshipId 
            ? $entityManager->getRepository(ChampionshipList::class)->find($championshipId)
            : null;
    
        $userTeam = null;
        if ($championship) {
            $userTeam = $entityManager->getRepository(Team::class)->findOneBy([
                'championshipList' => $championship,
                'creator' => $user
            ]);
        }
    
        $form = $this->createForm(UserType::class, $user, [
            'championship_id' => $championship ? $championship->getId() : null,
            'user' => $user,
            'userTeam' => $userTeam
        ]);
    
        $form->handleRequest($request);
    
        // Gestion du rechargement partiel (Bouton "Afficher les équipes")
        if ($form->isSubmitted() && $request->request->has('reload')) {
            $championshipId = $form->get('championship')->getData()->getId(); 
            return $this->render('user/edit.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
            ]);
        }
    
        // Gestion de la soumission complète
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedTeams = $form->get('myTeams')->getData();
            $removeCreator = $form->get('removeCreator')->getData();
    
            // Suppression et ajout sécurisés
            foreach ($user->getMyTeams() as $team) {
                $user->removeMyTeam($team);
            }
    
            foreach ($selectedTeams as $selectedTeam) {
                $user->addMyTeam($selectedTeam);
                $selectedTeam->setCreator($user);
                $entityManager->persist($selectedTeam);
            }
    
            // Mise à jour des champs utilisateur
            $user->setEmail($form->get('email')->getData());
            if ($form->get('password')->getData()) {
                $user->setPassword(password_hash($form->get('password')->getData(), PASSWORD_BCRYPT));
            }
            $user->setRoles($form->get('roles')->getData());
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_user_index');
        }
    
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
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
