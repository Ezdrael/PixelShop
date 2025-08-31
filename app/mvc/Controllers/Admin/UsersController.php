<?php
// ===================================================================
// Файл: mvc/c_users.php 🕰️
// Розміщення: /mvc/c_users.php
// ===================================================================
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Roles; 
use App\Mvc\Models\Users;

class UsersController  extends BaseController {

    protected $mUsers;
    protected $mRoles;

    public function __construct($params) {
        parent::__construct($params);
        $this->mUsers = new Users();
        $this->mRoles = new Roles();
    }

    public function indexAction() {
        if (!$this->hasPermission('users', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Керування користувачами';
        $users = $this->mUsers->getAll();
        $this->render('v_users_list', ['users' => $users]);
    }

    public function watchAction() {
        if (!$this->hasPermission('users', 'v')) {
            return $this->showAccessDenied();
        }
        $id = $this->params['id'] ?? 0;
        $user = $this->mUsers->getById((int)$id);
        $this->title = $user ? 'Профіль: ' . $user['name'] : 'Користувача не знайдено';
        $this->render('v_user_single', ['user' => $user]);
    }

    public function editAction() {
        if (!$this->hasPermission('users', 'e')) {
            return $this->showAccessDenied();
        }

        $id = (int)($this->params['id'] ?? 0);
        $redirectUrl = BASE_URL . '/users/edit/' . $id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('Не вдалося перевірити CSRF токен!');
            }

            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'role_id' => (int)($_POST['role_id'] ?? 0),
                'avatar_url' => filter_var(trim($_POST['avatar_url'] ?? ''), FILTER_VALIDATE_URL) ? trim($_POST['avatar_url']) : '' // Валідація URL
            ];

            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (!empty($password)) {
                if ($password !== $password_confirm) {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Паролі не співпадають.'];
                    header('Location: ' . $redirectUrl);
                    exit();
                }
                $data['password'] = $password;
            }

            if (!empty($data['name']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                if ($this->mUsers->emailExists($data['email'], $id)) {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Користувач з таким email вже існує.'];
                } else {
                    if ($this->mUsers->update($id, $data)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Дані користувача успішно оновлено.'];
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося оновити дані користувача.'];
                    }
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Будь ласка, заповніть поля "Ім\'я" та "Email" коректно.'];
            }

            header('Location: ' . $redirectUrl);
            exit();
        }

        $user = $this->mUsers->getById($id);
        $roles = $this->mRoles->getAll();
        $this->title = 'Редагування користувача';
        $this->render('v_user_edit', ['user' => $user, 'roles' => $roles]);
    }

    public function addAction() {
        if (!$this->hasPermission('users', 'a')) {
            return $this->showAccessDenied();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('Не вдалося перевірити CSRF токен!');
            }
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role_id' => (int)($_POST['role_id'] ?? 0),
                'avatar_url' => filter_var(trim($_POST['avatar_url'] ?? ''), FILTER_VALIDATE_URL) ? trim($_POST['avatar_url']) : '' // Валідація URL
            ];

            if (!empty($data['name']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && !empty($data['password']) && $data['role_id'] > 0) {
                if ($this->mUsers->emailExists($data['email'])) {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Користувач з таким email вже існує.'];
                } else {
                    if ($this->mUsers->add($data)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Нового користувача успішно додано.'];
                        header('Location: ' . BASE_URL . '/users');
                        exit();
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося додати нового користувача.'];
                    }
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Будь ласка, заповніть всі обов\'язкові поля.'];
            }
            header('Location: ' . BASE_URL . '/users/add');
            exit();
        }

        $this->title = 'Додавання нового користувача';
        $roles = $this->mRoles->getAll();
        $this->render('v_user_add', ['roles' => $roles]);
    }

    public function deleteAction() {
        header('Content-Type: application/json');

        if (!$this->hasPermission('users', 'd')) {
            echo json_encode(['success' => false, 'message' => 'У вас немає прав для виконання цієї дії.']);
            exit(); // Зупиняємо скрипт
        }

        $id = $this->params['id'] ?? 0;
        
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Ви не можете видалити самі себе.']);
            exit(); // Зупиняємо скрипт
        }

        // Встановлюємо флеш-повідомлення ДО відправки відповіді
        if ($this->mUsers->deleteById((int)$id)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Користувача успішно видалено.'];
            echo json_encode(['success' => true]);
            exit(); // Зупиняємо скрипт
        } else {
            // Теоретично, цей блок не має створювати флеш-повідомлення, бо сторінка не перезавантажиться
            echo json_encode(['success' => false, 'message' => 'Не вдалося видалити користувача.']);
            exit(); // Зупиняємо скрипт
        }
    }
}