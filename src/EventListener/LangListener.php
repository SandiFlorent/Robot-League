<?php 

// src/EventListener/LanguageListener.php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LangListener
{
    private $session;
    private $translator;

    public function __construct(SessionInterface $session, TranslatorInterface $translator)
    {
        $this->session = $session;
        $this->translator = $translator;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $locale = $request->get('locale');

        if ($locale) {
            $availableLocales = ['en', 'fr'];

            if (in_array($locale, $availableLocales)) {
                $this->session->set('_locale', $locale); 
                $this->translator->setLocale($locale); 
            }
        } else {
            $locale = $this->session->get('_locale', 'en');
            $this->translator->setLocale($locale); 
        }
    }
}
