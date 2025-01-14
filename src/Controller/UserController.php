<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Team;
use App\Form\UserType;
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
        $championshipId = $request->get('user')['championship'] ?? null;

        // Récupérer l'équipe de l'utilisateur dans le championnat sélectionné
        $userTeam = $entityManager->getRepository(Team::class)->findOneBy([
            'championshipList' => $championshipId,
            'creator' => $user
        ]);

        $form = $this->createForm(UserType::class, $user, [
            'championship_id' => $championshipId,
            'user' => $user,
            'userTeam' => $userTeam  // Ajout de l'équipe existante dans les options
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedTeam = $form->get('myTeams')->getData();
            $removeCreator = $form->get('removeCreator')->getData();

            // Si l'utilisateur veut retirer son rôle de créateur
            if ($userTeam && $removeCreator) {
                $userTeam->setCreator(null);
            } elseif ($selectedTeam instanceof Team) {
                $selectedTeam->setCreator($user);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');
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
