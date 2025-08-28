<?php
// core/Controller.php

namespace App\Core;

/**
 * Базовий клас контролера.
 * Містить основну логіку, спільну для всіх контролерів.
 */
abstract class Controller
{
    /**
     * @var array Параметри маршруту з URL.
     */
    protected $params;

    /**
     * Конструктор приймає параметри маршруту.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }
}