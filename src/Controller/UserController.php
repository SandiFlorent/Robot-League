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
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TeamRepository;

#[Route('/{_locale}/user')]
final class UserController extends AbstractController
{
    private const ITEMS_PER_PAGE = 10;

    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $currentPage = max(1, $request->query->getInt('page', 1));

        // Compter le nombre total d'utilisateurs
        $totalItems = $userRepository->count([]);

        // Calculer le nombre total de pages
        $totalPages = ceil($totalItems / self::ITEMS_PER_PAGE);

        // Assurer que la page courante ne dépasse pas le nombre total de pages
        $currentPage = min($currentPage, $totalPages);

        // Calculer l'offset
        $offset = max(0, ($currentPage - 1) * self::ITEMS_PER_PAGE);

        // Récupérer les utilisateurs paginés
        $users = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
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

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/user/new', name: 'app_user_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'show_championship' => false // Ne pas afficher le champ de sélection du championnat
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'userSuccessfullyCreated');
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

            // Si l'utilisateur n'a pas d'équipe dans ce championnat, rechercher les équipes sans créateur
            if (!$userTeam) {
                $teamsWithoutCreator = $entityManager->getRepository(Team::class)->findBy([
                    'championshipList' => $championship,
                    'creator' => null
                ]);
            }
        }

        // Création du formulaire
        $form = $this->createForm(UserType::class, $user, [
            'championship_id' => $championship ? $championship->getId() : null,
            'user' => $user,
            'userTeam' => $userTeam,
            'show_championship' => true // Afficher le champ de sélection du championnat
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
                    $this->addFlash('success', 'creatorSuccessfullyDeleted');
                    return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
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

                    $this->addFlash('success', 'teamSuccessfullyAttributed');
                    return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
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

                    $this->addFlash('success', 'teamSuccessfullyRemoved');
                    return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
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

                $this->addFlash('success', 'userSuccessfullyModified');
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
                $this->addFlash('notice', 'userSuccessfullyDeleted');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}