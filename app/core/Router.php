<?php
// app/Core/Router.php
namespace App\Core;

class Router {
    protected $routes = [];
    protected $params = [];
    protected $namespace = 'Public'; // Простір імен за замовчуванням

    public function add($route, $params = []) {
        $route = '#^' . str_replace('/', '\/', $route) . '$#';
        $this->routes[$route] = $params;
    }

    public function match($url) {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
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

    // Метод тепер приймає 'зону' сайту (Admin або Public)
    public function dispatch($url, $namespace = 'Public') {
        $this->namespace = $namespace;

        if ($this->match($url)) {
            $controllerName = 'App\\Mvc\\Controllers\\' . $this->namespace . '\\' . ucfirst($this->params['controller']) . 'Controller';

            if (class_exists($controllerName)) {
                // ✅ ДОДАНО: Передаємо "чистий" URL в параметри контролера
                $this->params['current_route'] = $url;
                
                $controller = new $controllerName($this->params);
                $actionName = ($this->params['action'] ?? 'index') . 'Action';
                
                if (method_exists($controller, $actionName)) {
                    $controller->$actionName();
                } else {
                    $this->trigger404("Метод не знайдено: " . $actionName);
                }
            } else {
                $this->trigger404("Клас контролера не знайдено: " . $controllerName);
            }
        } else {
            $this->trigger404();
        }
    }

    private function trigger404($message = null) {
        if ($message) {
            error_log($message);
        }
        http_response_code(404);
        
        $controllerName = 'App\\Mvc\\Controllers\\' . $this->namespace . '\\' . ($this->namespace === 'Admin' ? 'MainController' : 'HomeController');
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName([]);
            if (method_exists($controller, 'notFoundAction')) {
                $controller->notFoundAction();
            } else {
                 echo "<h1>404 Not Found</h1><p>Method notFoundAction is missing.</p>";
            }
        } else {
            echo "<h1>404 Not Found</h1><p>The 404 handler controller was not found.</p>";
        }
        exit();
    }
}