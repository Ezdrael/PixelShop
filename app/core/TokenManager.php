<?php
// app/Core/TokenManager.php
namespace App\Core;

class TokenManager {
    private static $tokenStorePath = BASE_PATH . '/storage/ws_tokens.json';

    private static function getStore() {
        if (!file_exists(self::$tokenStorePath)) {
            return [];
        }
        $content = file_get_contents(self::$tokenStorePath);
        return json_decode($content, true) ?: [];
    }

    private static function saveStore(array $store) {
        $dir = dirname(self::$tokenStorePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(self::$tokenStorePath, json_encode($store, JSON_PRETTY_PRINT));
    }

    /**
     * Генерує унікальний одноразовий токен для користувача.
     */
    public static function generateForUser(int $userId): string {
        $token = bin2hex(random_bytes(32));
        $store = self::getStore();

        // Зберігаємо токен з ID користувача та часом життя (24 години)
        $store[$token] = [
            'user_id' => $userId,
            'expires' => time() + 86400 // Токен дійсний 24 години
        ];

        self::saveStore($store);
        return $token;
    }

    /**
     * Перевіряє токен. Якщо він валідний, повертає ID користувача і видаляє токен.
     */
    public static function validate(string $token): ?int {
        $store = self::getStore();
        self::cleanup(); // Очищуємо застарілі токени

        if (!isset($store[$token]) || time() > $store[$token]['expires']) {
            return null; // Токен не знайдено або він прострочений
        }

        $userId = $store[$token]['user_id'];

        // Токен є одноразовим, тому ми видаляємо його після першої ж перевірки
        unset($store[$token]);
        self::saveStore($store);

        return $userId;
    }

    /**
     * Допоміжний метод для очищення старих токенів зі сховища.
     */
    private static function cleanup() {
        $store = self::getStore();
        $currentTime = time();
        $changed = false;

        foreach ($store as $token => $data) {
            if ($currentTime > $data['expires']) {
                unset($store[$token]);
                $changed = true;
            }
        }

        if ($changed) {
            self::saveStore($store);
        }
    }
}