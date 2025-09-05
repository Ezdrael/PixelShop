<?php
// app/Mvc/Models/Dashboard.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;
use GuzzleHttp\Client; // <-- ДОДАНО: Імпортуємо клієнт Guzzle
use GuzzleHttp\Exception\RequestException; // <-- ДОДАНО: Імпортуємо виключення Guzzle


class Dashboard
{
    private $db;

    public function __construct() { $this->db = DB::getInstance(); }

    public function getWidgets(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT widget_type, position, settings FROM dashboard_widgets WHERE user_id = ? ORDER BY position ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ✅ НОВИЙ МЕТОД: Отримує загальну статистику по сайту.
     */
    public function getGlobalStats(): array
    {
        $stats = [];
        $queries = [
            'total_goods' => "SELECT COUNT(*) FROM goods",
            'active_goods' => "SELECT COUNT(*) FROM goods WHERE is_active = 1",
            'total_users' => "SELECT COUNT(*) FROM users",
            'total_categories' => "SELECT COUNT(*) FROM categories",
            'total_warehouses' => "SELECT COUNT(*) FROM warehouses"
        ];

        foreach ($queries as $key => $sql) {
            $stats[$key] = $this->db->query($sql)->fetchColumn();
        }
        return $stats;
    }

    /**
     * ✅ НОВИЙ МЕТОД: Отримує останні N подій календаря.
     */
    public function getRecentCalendarEvents(int $limit = 5): array
    {
        $sql = "SELECT id, title, start_time 
                FROM calendar_events 
                WHERE start_time >= CURDATE() 
                ORDER BY start_time ASC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ✅ НОВИЙ МЕТОД: Отримує останні N операцій (надходження, списання).
     */
    public function getRecentStockOperations(int $limit = 5): array
    {
        $sql = "SELECT document_type, transaction_date, user_id, u.name as user_name
                FROM product_transactions pt
                JOIN users u ON pt.user_id = u.id
                WHERE pt.document_type IN ('arrival_form', 'writeoff_form', 'transfer_form')
                GROUP BY pt.document_id, pt.transaction_date, pt.user_id, u.name
                ORDER BY pt.transaction_date DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ✅ НОВИЙ МЕТОД: Отримує N товарів, яких найменше на складах.
     */
    public function getLowStockGoods(int $limit = 5): array
    {
        $sql = "SELECT g.id, g.name, SUM(ps.quantity) as total_stock
                FROM product_stock ps
                JOIN goods g ON ps.good_id = g.id
                GROUP BY g.id, g.name
                HAVING total_stock > 0
                ORDER BY total_stock ASC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ✅ ОНОВЛЕНА ВЕРСІЯ: Отримує новини за допомогою Guzzle.
     */
    public function getMarketplaceNews(): array
    {
        $cacheFile = BASE_PATH . '/storage/cache/marketplace_news.json';
        $cacheTime = 86400; 
        $newsUrl = 'https://raw.githubusercontent.com/Ezdrael/pixelshop-news-api/main/news.json?raw=true';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
            return json_decode(file_get_contents($cacheFile), true) ?: [];
        }

        try {
            // Створюємо нового клієнта Guzzle
            $client = new Client([
                'timeout'  => 5.0, // Тайм-аут запиту
                'verify'   => false // Відключаємо перевірку SSL (для локального сервера)
            ]);

            // Робимо GET-запит
            $response = $client->request('GET', $newsUrl);

            // Перевіряємо, чи успішний запит
            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();
                $news = json_decode($body, true);

                if (!is_dir(dirname($cacheFile))) {
                    mkdir(dirname($cacheFile), 0755, true);
                }
                file_put_contents($cacheFile, $body);
                
                return $news ?: [];
            }
        } catch (RequestException $e) {
            // Логуємо помилку, якщо запит не вдався
            error_log('Помилка завантаження новин через Guzzle: ' . $e->getMessage());
        }
        
        return []; // Повертаємо пустий масив у разі будь-якої помилки
    }
}