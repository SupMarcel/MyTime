<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Localization\ITranslator;

abstract class BaseModelWithTranslator extends BaseModel
{
    protected ITranslator $translator;

    public function __construct(Explorer $database, ITranslator $translator)
    {
        parent::__construct($database);
        $this->translator = $translator;
    }

    /**
     * Překlad textu s volitelnými parametry.
     *
     * @param string $message Klíč zprávy pro překlad.
     * @param array $parameters Parametry pro zprávu (např. hodnoty nahrazující zástupné znaky).
     * @return string Přeložený text.
     */
    protected function translate(string $message, array $parameters = []): string
    {
        return $this->translator->translate($message, $parameters);
    }

    /**
     * Překlad textu s možností zadání množného čísla a parametrů.
     *
     * @param string $message Klíč zprávy pro překlad.
     * @param int $count Počet pro určení správné formy množného čísla.
     * @param array $parameters Parametry pro zprávu (např. hodnoty nahrazující zástupné znaky).
     * @return string Přeložený text s množným číslem.
     */
    protected function translatePlural(string $message, int $count, array $parameters = []): string
    {
        return $this->translator->translate($message, $count, $parameters);
    }

    /**
     * Vrátí aktuální jazyk nastavený v translatoru.
     *
     * @return string Kód jazyka (např. 'en', 'cs').
     */
    public function getCurrentLanguage(): string
    {
        // Předpokládáme, že translator má metodu pro získání aktuálního jazyka, což nemusí být vždy standardní.
        // Je třeba zajistit, že translator tuto metodu podporuje, jinak je nutné ji implementovat nebo přizpůsobit.
        if (method_exists($this->translator, 'getLocale')) {
            return $this->translator->getLocale();
        }
        throw new \Exception("Translator does not support retrieving the current language.");
    }
}
