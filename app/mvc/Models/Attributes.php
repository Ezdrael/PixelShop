<?php
// app/Mvc/Models/Attributes.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Attributes
{
    private $db;
    public function __construct() { $this->db = DB::getInstance(); }

    public function getAll() {
        return $this->db->query("SELECT * FROM attributes ORDER BY name ASC")->fetchAll();
    }

    public function getByProductId(int $goodId): array {
        $sql = "SELECT a.name, pa.value 
                FROM product_attributes pa
                JOIN attributes a ON pa.attribute_id = a.id
                WHERE pa.good_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$goodId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}