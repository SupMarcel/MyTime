<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;
use Nette\Localization\ITranslator;

class CategoryModel extends BaseModelWithTranslator
{
    // Definice názvu tabulky a sloupců
    const TABLE_NAME = 'categories';
    const COLUMN_ID = 'id';
    const COLUMN_CATEGORY_NAME = 'category_name';
    const COLUMN_PARENT_ID = 'parent_category_id';

    public function __construct(Explorer $database, ITranslator $translator)
    {
        parent::__construct($database, $translator);
    }
    public function getMainCategories(): Selection
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_PARENT_ID, null)
            ->order(self::COLUMN_NAME);
    }

    /**
     * Získá všechny podkategorie pro zadanou nadřazenou kategorii.
     *
     * @param int $parentId ID nadřazené kategorie.
     * @return Selection Výběr podkategorií pro zadanou nadřazenou kategorii.
     */
    public function getSubcategories(int $parentId): Selection
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_PARENT_ID, $parentId)
            ->order(self::COLUMN_NAME);
    }

    /**
     * Vrací dvouúrovňový seznam kategorií s přeloženými názvy na základě zadaného jazyka.
     *
     * @param string $languageCode Kód jazyka (např. 'en' nebo 'cs').
     * @return array Seznam hlavních kategorií a jejich podkategorií.
     */
    public function getTranslatedCategoriesWithSubcategories(string $languageCode): array
    {
        $categories = [];

        // Získání hlavních kategorií
        $mainCategories = $this->getMainCategories();
        foreach ($mainCategories as $mainCategory) {
            $mainCategoryName = $this->translateCategoryName($mainCategory->{self::COLUMN_NAME}, $languageCode);

            // Přidání hlavní kategorie do výsledného pole
            $categories[] = [
                'id' => $mainCategory->{self::COLUMN_ID},
                'category_name' => $mainCategoryName,
                'subcategories' => $this->getTranslatedSubcategories($mainCategory->{self::COLUMN_ID}, $languageCode),
            ];
        }

        return $categories;
    }

    /**
     * Vrací přeložené podkategorie pro zadanou hlavní kategorii.
     *
     * @param int $parentId ID nadřazené kategorie.
     * @param string $languageCode Kód jazyka (např. 'en' nebo 'cs').
     * @return array Seznam podkategorií s přeloženými názvy.
     */
    private function getTranslatedSubcategories(int $parentId, string $languageCode): array
    {
        $subcategories = [];

        // Získání podkategorií
        $subCategoriesData = $this->getSubcategories($parentId);
        foreach ($subCategoriesData as $subcategory) {
            $subCategoryName = $this->translateCategoryName($subcategory->{self::COLUMN_NAME}, $languageCode);

            // Přidání podkategorie do výsledného pole
            $subcategories[] = [
                'id' => $subcategory->{self::COLUMN_ID},
                'category_name' => $subCategoryName,
            ];
        }

        return $subcategories;
    }

    /**
     * Přeloží klíče kategorií na odpovídající hodnoty v zadaném jazyce.
     *
     * @param string $categoryKey Klíč kategorie (např. 'cars').
     * @param string $languageCode Kód jazyka (např. 'en' nebo 'cs').
     * @return string Přeložený název kategorie.
     */
    private function translateCategoryName(string $categoryKey, string $languageCode): string
    {
        return $this->translator->translate("category.$categoryKey", [], $languageCode);
    }
    /**
     * Získá kategorii podle ID.
     * @param int $id ID kategorie.
     * @return ActiveRow|null
     */
    public function getCategoryById(int $id): ?ActiveRow
    {
        return $this->database->table(self::TABLE_NAME)
            ->get($id);
    }

    /**
     * Přidá novou kategorii.
     * @param string $categoryName Název kategorie.
     * @param int|null $parentId ID nadřazené kategorie (nepovinné).
     * @return ActiveRow
     */
    public function addCategory(string $categoryName, ?int $parentId = null): ActiveRow
    {
        return $this->database->table(self::TABLE_NAME)->insert([
            self::COLUMN_CATEGORY_NAME => $categoryName,
            self::COLUMN_PARENT_ID => $parentId,
        ]);
    }

    /**
     * Aktualizuje název kategorie.
     * @param int $id ID kategorie.
     * @param string $newName Nový název kategorie.
     * @return void
     */
    public function updateCategoryName(int $id, string $newName): void
    {
        $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ID, $id)
            ->update([self::COLUMN_CATEGORY_NAME => $newName]);
    }

    /**
     * Vymaže kategorii podle ID. Pokud má podkategorie, tyto podkategorie zůstanou.
     * @param int $id ID kategorie.
     * @return void
     */
    public function deleteCategory(int $id): void
    {
        $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ID, $id)
            ->delete();
    }

    /**
     * Získá úplný seznam kategorií včetně podkategorií.
     * @return Selection
     */
    public function getAllCategories(): Selection
    {
        return $this->database->table(self::TABLE_NAME)
            ->order(self::COLUMN_CATEGORY_NAME);
    }

    /**
     * Zkontroluje, zda daná kategorie má podkategorie.
     * @param int $id ID kategorie.
     * @return bool
     */
    public function hasSubcategories(int $id): bool
    {
        return (bool) $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_PARENT_ID, $id)
            ->count('*');
    }
}
