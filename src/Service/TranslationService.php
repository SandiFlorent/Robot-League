<?php 

namespace App\Service;

use Symfony\Component\Translation\LocaleSwitcher;

class TranslationService
{
    private LocaleSwitcher $localeSwitcher;

    public function __construct(LocaleSwitcher $localeSwitcher)
    {
        $this->localeSwitcher = $localeSwitcher;
    }

    public function switchLanguage(string $locale): void
    {
        // Set the application locale to the desired language (e.g., 'fr' for French)
        $this->localeSwitcher->setLocale($locale);

        // If you want to do something with this locale, such as rendering templates
        // in this locale, you can do that here, or just rely on the global translation system.
    }
}
