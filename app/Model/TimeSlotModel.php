<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class TimeSlotModel
{
    private Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    // Metoda pro generování a organizaci časových slotů
    public function generateAndOrganizeSlots(int $workerId): array
    {
        // Generování časových slotů
        $this->generateTimeSlots();
        // Získání a organizace časových slotů
        return $this->getTimeSlotsForNextTwoWeeks($workerId);
    }

    // Funkce pro generování časových slotů
        public function generateTimeSlots(): void
    {
        // Zjistí nejnovější záznam v tabulce
        $latestSlot = $this->getLatestTimeSlot();

        
        // Pokud existuje platný záznam a jeho datum je novější než dnes, pokračujeme od něj
        if (!empty($latestSlot) && new \DateTime($latestSlot->end_time) > new \DateTime('today')) {
            $startDate = new \DateTime($latestSlot->end_time);
        } else {
            // Jinak začínáme od dnešního dne
            $startDate = new \DateTime('today');
        }

        // Generování časových slotů na 14 dní dopředu
        $endDate = (clone $startDate)->modify('+14 days');

        $interval = new \DateInterval('PT30M');
        $current = clone $startDate;

        while ($current < $endDate) {
            $endTime = (clone $current)->add($interval);

            $slotIdentifier = $current->format('Ymd_His') . '_' . $endTime->format('His');

            // Zkontrolujte, zda slot již existuje
            $existingSlot = $this->database->table('time_slots')->where('slot_identifier', $slotIdentifier)->fetch();
            if ($existingSlot) {
                // Přeskočte tento slot a pokračujte dalším
                $current = $endTime;
                continue;
            }

            $this->database->table('time_slots')->insert([
                'start_time' => $current->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'slot_identifier' => $slotIdentifier,
            ]);

            $current = $endTime;
        }
    }


    public function getTimeSlotsForNextTwoWeeks(int $workerId): array
    {
        $today = new \DateTime('today');
        $endDate = (clone $today)->modify('+14 days');

        $slots = $this->database->table('time_slots')
            ->where('start_time >= ?', $today->format('Y-m-d H:i:s'))
            ->where('end_time < ?', $endDate->format('Y-m-d H:i:s'))
            ->order('start_time ASC')
            ->fetchAll();

        return $this->organizeSlotsIntoWeeks($slots, $workerId);
    }



    // Funkce pro získání nejnovějšího časového slotu
    private function getLatestTimeSlot(): ?ActiveRow
    {
        return $this->database->table('time_slots')
            ->order('end_time DESC')
            ->fetch();
    }

    // Funkce pro získání časových slotů na 14 dní dopředu
    private function organizeSlotsIntoWeeks(array $slots, int $workerId): array
    {
        $organizedSlots = [];

        // Ošetření prázdného pole
        if (empty($slots)) {
            return $organizedSlots; // Vrátí prázdné pole, pokud nejsou žádné sloty
        }

        // Získání prvního prvku pole bez ohledu na jeho klíč
        $firstSlot = reset($slots);

        if ($firstSlot) {
            $startDate = new \DateTime($firstSlot->start_time);
            $endDate = new \DateTime(end($slots)->end_time);

            // Hlavní klíč pro celé pole s informacemi o workerovi a časovém rozsahu
            $mainKey = sprintf('worker_%d_%s_to_%s', $workerId, $startDate->format('Ymd'), $endDate->format('Ymd'));

            foreach ($slots as $slot) {
                $dateTime = new \DateTime($slot->start_time);

                // Týdenní číslo (klíč)
                $weekNumber = $dateTime->format('W');
                $year = $dateTime->format('Y');

                // Denní klíč (např. 'Monday_20230814')
                $dayOfWeek = sprintf('%s_%s', $dateTime->format('l'), $dateTime->format('Ymd'));

                // Časový úsek jako klíč (např. '09:00-09:30')
                $timeSlotKey = $dateTime->format('H:i') . '-' . $dateTime->modify('+30 minutes')->format('H:i');

                // Organizace do strukturovaného pole
                $organizedSlots[$mainKey][$year . '_week_' . $weekNumber][$dayOfWeek][$timeSlotKey] = $slot;
            }
        }

        return $organizedSlots;
    }



}

