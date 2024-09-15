<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class WorkerModel extends BaseModel {

    // Konstanty pro tabulku worker_locations
    const TABLE_WORKER_LOCATIONS = 'worker_locations';
    const COLUMN_WORKER_ID = 'worker_id';
    const COLUMN_LOCATION_ID_IN_WORKER_LOCATIONS = 'location_id';
    const COLUMN_BANNED = 'banned'; // Přidána konstanta pro sloupec banned

    protected UserModel $userModel;
    protected LocationModel $locationModel;
    protected RoleModel $roleModel;

    public function __construct(\Nette\Database\Explorer $database, UserModel $userModel, LocationModel $locationModel, RoleModel $roleModel) {
        parent::__construct($database);
        $this->userModel = $userModel;
        $this->locationModel = $locationModel;
        $this->roleModel = $roleModel;
    }

    /**
     * Přidání nového pracovníka do databáze.
     */
    public function addWorker(
        string $username, 
        string $email, 
        string $password, 
        string $phone, 
        ?string $image, 
        ?string $description, 
        ?int $locationId
    ): void {
        $userId = $this->userModel->addUser([
            UserModel::COLUMN_USERNAME => $username,
            UserModel::COLUMN_EMAIL => $email,
            UserModel::COLUMN_PASSWORD => password_hash($password, PASSWORD_DEFAULT),
            UserModel::COLUMN_PHONE => $phone, // Přidání telefonního čísla
            UserModel::COLUMN_IMAGE => $image,
            UserModel::COLUMN_DESCRIPTION => $description,
            UserModel::COLUMN_LOCATION_ID => $locationId,
        ])->getPrimary();

        $this->roleModel->addRoleToUser($userId, RoleModel::ROLE_WORKER);

        if ($locationId !== null) {
            $this->addWorkerToLocation($userId, $locationId);
        }
    }

    /**
     * Přidání pracovníka do konkrétní lokace.
     */
    public function addWorkerToLocation(int $workerId, int $locationId): void {
        $location = $this->locationModel->getById($locationId);

        if ($location) {
            $isChief = $this->roleModel->userHasRole($workerId, RoleModel::ROLE_CHIEF);
            $isDefaultLocation = $locationId === LocationModel::DEFAULT_LOCATION;

            // Pokud worker není šéfem nebo se nejedná o defaultní lokaci, bude zabanován
            $banned = !($isChief || $isDefaultLocation);

            $this->database->table(self::TABLE_WORKER_LOCATIONS)->insert([
                self::COLUMN_WORKER_ID => $workerId,
                self::COLUMN_LOCATION_ID_IN_WORKER_LOCATIONS => $locationId,
                self::COLUMN_BANNED => $banned ? 1 : 0,
            ]);
        }
    }

    /**
     * Vymazání pracovníka z konkrétní lokace.
     */
    public function removeWorkerFromLocation(int $workerId, int $locationId): void {
        $this->database->table(self::TABLE_WORKER_LOCATIONS)
            ->where(self::COLUMN_WORKER_ID, $workerId)
            ->where(self::COLUMN_LOCATION_ID_IN_WORKER_LOCATIONS, $locationId)
            ->delete();

        // Kontrola, zda má pracovník ještě nějaké lokace
        $remainingLocations = $this->database->table(self::TABLE_WORKER_LOCATIONS)
            ->where(self::COLUMN_WORKER_ID, $workerId)
            ->count();

        if ($remainingLocations === 0) {
            $this->roleModel->removeRoleFromUser($workerId, RoleModel::ROLE_WORKER);
        }
    }

    /**
     * Nalezení všech pracovníků včetně jejich dat.
     */
    public function findAllWorkers(): array {
        $workers = $this->database->table(UserModel::TABLE_NAME)
            ->where(':user_roles.role_id', RoleModel::ROLE_WORKER)
            ->fetchAll();

        $result = [];
        foreach ($workers as $worker) {
            $result[] = $worker->toArray();
        }

        return $result;
    }
    
    public function getWorkerLocation(int $workerId): ?ActiveRow
    {
        $workerLocation = $this->database->table(self::TABLE_WORKER_LOCATIONS)
            ->where(self::COLUMN_WORKER_ID, $workerId)
            ->where(self::COLUMN_BANNED, 0) // Zajišťuje, že vybíráme pouze nezabanovanou lokaci
            ->fetch();

        if ($workerLocation) {
            return $this->locationModel->getLocationById($workerLocation->{self::COLUMN_LOCATION_ID_IN_WORKER_LOCATIONS});
        }

        return null;
    }
    
        /**
     * Zjistí, zda uživatel s daným ID má roli "worker".
     * @param int $userId
     * @return bool
     */
    public function isUserWorker(int $userId): bool
    {
        return $this->roleModel->userHasRole($userId, RoleModel::ROLE_WORKER);
    }
    
    private function doesWorkerHaveMultipleLocations(int $workerId): bool
    {
        // Zjistíme počet lokací, ve kterých pracovník působí
        $locationCount = $this->database->table(self::TABLE_WORKER_LOCATIONS)
            ->where(self::COLUMN_WORKER_ID, $workerId)
            ->where(self::COLUMN_BANNED, 0) // Pouze nezabanované lokace
            ->count();

        // Pokud má pracovník více než jednu lokaci, vrátíme true
        return $locationCount > 1;
    }
    
    public function getSingleWorkerLocation(int $workerId): ?ActiveRow
    {
        if ($this->doesWorkerHaveMultipleLocations($workerId)) {
            throw new \Exception('Worker is assigned to multiple locations. Please specify a location.');
        }

        // Získání lokace pracovníka (pokud má pouze jednu lokaci)
        return $this->getWorkerLocation($workerId);
    }


}

