<?php
// app/Mvc/Models/Currencies.php
namespace App\Mvc\Models;

use App\Core\DB;

class Currencies
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM currencies ORDER BY id ASC")->fetchAll();
    }

    public function add(array $data): ?int
    {
        $stmt = $this->db->prepare("INSERT INTO currencies (code, bank) VALUES (?, ?)");
        if ($stmt->execute([$data['code'], $data['bank']])) {
            return $this->db->lastInsertId();
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM currencies WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * ✅ ДОДАНО: Масово оновлює курси для валют, що є в базі.
     * @param array $ratesFromApi - Масив курсів, отриманий з API.
     * @return bool
     */
    public function updateRates(array $ratesFromApi): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "UPDATE currencies SET rate_buy = ?, rate_sale = ?, last_updated = ? WHERE code = ?"
            );

            // Генеруємо поточний час в UTC. PHP вже налаштований на це завдяки DB.php
            $currentTimeUTC = date('Y-m-d H:i:s');

            foreach ($ratesFromApi as $rate) {
                if (isset($rate['ccy']) && isset($rate['buy']) && isset($rate['sale'])) {
                    $stmt->execute([(float)$rate['buy'], (float)$rate['sale'], $currentTimeUTC, $rate['ccy']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Currency rates update failed: " . $e->getMessage());
            return false;
        }
    }
}