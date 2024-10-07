<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Explorer;

class ChiefModel extends BaseModel
{
    // Konstanty pro tabulku chief_locations
    const TABLE_CHIEF_LOCATIONS = 'chief_locations';
    const COLUMN_LOCATION_ID = 'id_location';
    const COLUMN_USER_ID = 'id_user';

    private UserModel $userModel;
    private RoleModel $roleModel;
    private LocationModel $locationModel;
    private WorkerModel $workerModel;
    protected Explorer $database;

    public function __construct(
        UserModel $userModel,
        RoleModel $roleModel,
        LocationModel $locationModel,
        WorkerModel $workerModel,
        Explorer $database    
    )
    {
        parent::__construct($database); // Zavolání konstruktoru BaseModel, který inicializuje $database
        $this->userModel = $userModel;
        $this->roleModel = $roleModel;
        $this->locationModel = $locationModel;
        $this->workerModel = $workerModel;
        $this->database = $database;
    }

    public function addChief(
        string $username,
        string $email,
        string $password,
        string $phone,
        string $locationName,
        string $locationDescription,
        ?string $locationImage = null,
        ?int $addressId = null
    ): void {
        // Přidání uživatele (šéfa)
        $userId = $this->userModel->addUser([
            UserModel::COLUMN_USERNAME => $username,
            UserModel::COLUMN_EMAIL => $email,
            UserModel::COLUMN_PASSWORD => password_hash($password, PASSWORD_DEFAULT),
            UserModel::COLUMN_PHONE => $phone,
        ])->getPrimary();

        // Přidání role 'chief' uživateli
        $this->roleModel->addRoleToUser($userId, RoleModel::ROLE_CHIEF);

        // Přidání nové location
        $locationId = $this->locationModel->addLocation(
            $locationName,
            $addressId,
            $locationImage,
            $locationDescription
        );

        // Zaznamenání location do tabulky chief_locations
        $this->database->table(self::TABLE_CHIEF_LOCATIONS)->insert([
            self::COLUMN_LOCATION_ID => $locationId,
            self::COLUMN_USER_ID => $userId,
        ]);
    }
    
    public function addChiefLocation(
        int $userId,
        string $locationName,
        string $locationDescription,
        ?string $locationImage = null,
        ?int $addressId = null
    ): void {
        // Přidání nové provozovny pro existujícího uživatele (šéfa)
        $locationId = $this->locationModel->addLocation(
            $locationName,
            $addressId,
            $locationImage,
            $locationDescription
        );

        // Zaznamenání location do tabulky chief_locations
        $this->database->table(self::TABLE_CHIEF_LOCATIONS)->insert([
            self::COLUMN_LOCATION_ID => $locationId,
            self::COLUMN_USER_ID => $userId,
        ]);
    }


    public function removeLocation(int $locationId): void
    {
        // Najdi šéfa, který vlastní tuto location
        $chief = $this->database->table(self::TABLE_CHIEF_LOCATIONS)
            ->where(self::COLUMN_LOCATION_ID, $locationId)
            ->fetch();

        if ($chief) {
            // Odstranění příslušnosti všech workerů k této location
            $this->workerModel->removeWorkersFromLocation($locationId);

            // Odstranění location z tabulky locations
            $this->locationModel->deleteLocation($locationId);

            // Odstranění záznamu z tabulky chief_locations
            $this->database->table(self::TABLE_CHIEF_LOCATIONS)
                ->where(self::COLUMN_LOCATION_ID, $locationId)
                ->delete();

            // Pokud šéf nemá žádnou další location, vymazat ho z role 'chief'
            $remainingLocations = $this->database->table(self::TABLE_CHIEF_LOCATIONS)
                ->where(self::COLUMN_USER_ID, $chief[self::COLUMN_USER_ID])
                ->count();

            if ($remainingLocations === 0) {
                $this->roleModel->removeRoleFromUser($chief[self::COLUMN_USER_ID], RoleModel::ROLE_CHIEF);
            }
        }
    }

    public function removeWorkerFromLocation(int $locationId, int $workerId): void
    {
        $this->workerModel->removeWorkerFromLocation($workerId, $locationId);

        // Pokud worker nemá žádnou další location, vymazat ho z role 'worker'
        $remainingLocations = $this->workerModel->getWorkerLocations($workerId);

        if (count($remainingLocations) === 0) {
            $this->roleModel->removeRoleFromUser($workerId, RoleModel::ROLE_WORKER);
        }
    }

    public function getChiefLocations(int $chiefId): array
    {
        // Získání záznamů jako pole
        $rows = $this->database->table(self::TABLE_CHIEF_LOCATIONS)
            ->where(self::COLUMN_USER_ID, $chiefId)
            ->fetchAll();

        // Vytvoření pole s ID provozovny jako klíč a názvem provozovny jako hodnotou
        $locations = [];
        foreach ($rows as $row) {
            $locations[$row[self::COLUMN_LOCATION_ID]] = $row->ref('locations', self::COLUMN_LOCATION_ID)->name;
        }

        return $locations;
    }

    /**
     * Odbanování workera v konkrétní lokaci.
     */
    public function unbanWorker(int $chiefId, int $workerId, int $locationId): void
    {
        if ($this->isChiefOfLocation($chiefId, $locationId)) {
            $this->database->table(WorkerModel::TABLE_WORKER_LOCATIONS)
                ->where(WorkerModel::COLUMN_WORKER_ID, $workerId)
                ->where(WorkerModel::COLUMN_LOCATION_ID_IN_WORKER_LOCATIONS, $locationId)
                ->update([WorkerModel::COLUMN_BANNED => 0]);
        }
    }

    /**
     * Zabanování workera v konkrétní lokaci.
     */
    public function banWorker(int $chiefId, int $workerId, int $locationId): void
    {
        if ($this->isChiefOfLocation($chiefId, $locationId)) {
            $this->database->table(WorkerModel::TABLE_WORKER_LOCATIONS)
                ->where(WorkerModel::COLUMN_WORKER_ID, $workerId)
                ->where(WorkerModel::COLUMN_LOCATION_ID_IN_WORKER_LOCATIONS, $locationId)
                ->update([WorkerModel::COLUMN_BANNED => 1]);
        }
    }

    /**
     * Kontrola, zda je uživatel šéfem dané lokace.
     */
    private function isChiefOfLocation(int $chiefId, int $locationId): bool
    {
        return $this->database->table(self::TABLE_CHIEF_LOCATIONS)
            ->where(self::COLUMN_USER_ID, $chiefId)
            ->where(self::COLUMN_LOCATION_ID, $locationId)
            ->count() > 0;
    }
}

