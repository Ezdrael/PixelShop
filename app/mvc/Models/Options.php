<?php
// app/Mvc/Models/Options.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Options
{
    private $db;
    public function __construct() { $this->db = DB::getInstance(); }

    public function getAllGroupsWithValues(): array
    {
        $groups = $this->db->query("SELECT * FROM option_groups ORDER BY name ASC")->fetchAll();
        $stmt = $this->db->prepare("SELECT id, value FROM option_values WHERE group_id = ?");
        foreach ($groups as $key => $group) {
            $stmt->execute([$group['id']]);
            $groups[$key]['values'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $groups;
    }
}