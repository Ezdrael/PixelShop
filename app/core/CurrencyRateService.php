<?php
// app/Core/CurrencyRateService.php
namespace App\Core;

class CurrencyRateService
{
    // URL до публічного API готівкового курсу ПриватБанку
    private const API_URL = 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5';

    /**
     * Отримує курси валют з API.
     * @return array|null - Повертає масив з курсами або null у разі помилки.
     */
    public function fetchRates(): ?array
    {
        try {
            // Використовуємо cURL для більш надійного запиту
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Важливо для локальних серверів
            $json_data = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code !== 200 || $json_data === false) {
                error_log('Failed to fetch currency rates from API. HTTP Code: ' . $http_code);
                return null;
            }

            return json_decode($json_data, true);

        } catch (\Exception $e) {
            error_log('Exception while fetching currency rates: ' . $e->getMessage());
            return null;
        }
    }
}