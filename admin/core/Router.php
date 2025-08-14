<?php
// ===================================================================
// Файл: core/Router.php 🕰️
// Розміщення: /core/Router.php
// Призначення: Клас для маршрутизації запитів.
// ===================================================================

class Router {
    protected $routes = [];
    protected $params = [];

    public function __construct() {
        // Тут можна було б завантажувати маршрути з окремого файлу
    }

    /**
     * Додає маршрут до таблиці маршрутизації
     * @param string $route (регулярний вираз маршруту)
     * @param array $params (контролер, дія)
     */
    public function add($route, $params = []) {
        $route = '#^' . $route . '$#';
        $this->routes[$route] = $params;
    }

    /**
     * Перевіряє, чи відповідає URL будь-якому маршруту
     * @param string $url
     * @return bool
     */
    public function match($url) {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Отримуємо іменовані параметри з URL (наприклад {id})
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    /**
     * Запускає відповідний контролер та дію
     * @param string $url
     */
    public function dispatch($url) {
        if ($this->match($url)) {
            $controllerName = 'C_' . ucfirst($this->params['controller']);
            $actionName = $this->params['action'] . 'Action';

            $controllerFile = ROOT . '/mvc/' . str_replace('C_', 'c_', $controllerName) . '.php';

            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                if (class_exists($controllerName)) {
                    $controller = new $controllerName($this->params);
                    if (method_exists($controller, $actionName)) {
                        $controller->$actionName();
                    } else {
                        echo "Метод не знайдено: " . $actionName; // Або сторінка 404
                    }
                } else {
                    echo "Клас контролера не знайдено: " . $controllerName; // Або сторінка 404
                }
            } else {
                echo "Файл контролера не знайдено: " . $controllerFile; // Або сторінка 404
            }
        } else {
            // Якщо маршрут не знайдено, можна показати сторінку 404
            require_once ROOT . '/mvc/c_main.php';
            $controller = new C_Main([]);
            $controller->notFoundAction();
        }
    }
}