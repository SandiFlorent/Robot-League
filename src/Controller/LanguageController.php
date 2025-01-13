<?php
// src/Controller/LanguageController.php

namespace App\Controller;

use App\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    private TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    #[Route('/change-lang/{locale}', name: 'change_lang')]
    public function changeLanguage(Request $request, string $locale): RedirectResponse
    {
        $this->translationService->switchLanguage($locale);

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
