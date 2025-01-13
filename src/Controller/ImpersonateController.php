<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ImpersonateController extends AbstractController
{
    #[Route('/impersonate', name: 'app_impersonate')]
    public function index(): Response
    {
        return $this->render('impersonate/index.html.twig', [
            'controller_name' => 'ImpersonateController',
        ]);
    }

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/find-user-id", name="app_find_user_id", methods={"POST"})
     */
    public function findUserId(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANISATEUR');

        $email = $request->request->get('email');
        if (!$email) {
            $this->addFlash('error', 'Email is required.');
            return $this->redirectToRoute('app_home');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('error', "No user found with email: {$email}");
            return $this->redirectToRoute('app_home');
        }

        // Redirect to impersonate route with the `_switch_user` query parameter
        return $this->redirectToRoute('app_home', ['_switch_user' => $user->getEmail()]);
    }
}
