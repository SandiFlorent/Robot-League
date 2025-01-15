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
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/{_locale}/impersonate/{id}', name: 'app_impersonate_user', methods: ['POST'])]
    public function impersonateUser(int $id): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANISATEUR');

        $user = $this->userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'userNotFound');
            return $this->redirectToRoute('app_home');
        }
        
        return $this->redirectToRoute('app_home', ['_switch_user' => $user->getEmail()]);
    }
}