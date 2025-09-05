<?php
// app/Mvc/Models/Calendar.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Calendar
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує події за вказаний період.
     */
    public function getEvents(string $start, string $end): array
    {
        $sql = "SELECT 
                    id, 
                    title, 
                    description,
                    start_time as `start`, 
                    end_time as `end`,
                    user_id
                FROM calendar_events 
                WHERE start_time BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Отримує одну подію за її ID.
     */
    public function getEventById(int $id)
    {
        $sql = "SELECT e.*, u.name as user_name 
                FROM calendar_events e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Додає нову подію.
     */
    public function addEvent(array $data, int $userId): ?int
    {
        $sql = "INSERT INTO calendar_events (user_id, title, description, start_time, end_time) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $endTime = !empty($data['end_time']) ? $data['end_time'] : null;
        if ($stmt->execute([$userId, $data['title'], $data['description'], $data['start_time'], $endTime])) {
            return $this->db->lastInsertId();
        }
        return null;
    }
    
    /**
     * Оновлює існуючу подію.
     */
    public function updateEvent(int $id, array $data): bool
    {
        $sql = "UPDATE calendar_events SET title = ?, description = ?, start_time = ?, end_time = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $endTime = !empty($data['end_time']) ? $data['end_time'] : null;
        return $stmt->execute([$data['title'], $data['description'], $data['start_time'], $endTime, $id]);
    }
    
    /**
     * Видаляє подію.
     */
    public function deleteEvent(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM calendar_events WHERE id = ?");
        return $stmt->execute([$id]);
    }
}