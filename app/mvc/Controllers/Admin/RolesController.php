<?php
// ===================================================================
// Файл: mvc/Controller/Admin/RolesController.php
// ===================================================================
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Roles;

class RolesController extends BaseController {
    protected $mRoles;

    public function __construct($params) {
        parent::__construct($params);
        $this->mRoles = new Roles();
    }

    public function indexAction() {
        if (!$this->hasPermission('roles', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Керування ролями';
        $roles = $this->mRoles->getAll();
        $this->render('v_roles_list', ['roles' => $roles]);
    }

    public function watchAction() {
        if (!$this->hasPermission('roles', 'v')) {
            return $this->showAccessDenied();
        }

        $id = (int)($this->params['id'] ?? 0);
        $role = $this->mRoles->getById($id);
        
        // Отримуємо список користувачів для цієї ролі
        $usersInRole = $this->mRoles->getUsersByRoleId($id);

        $this->title = $role ? 'Перегляд ролі: ' . htmlspecialchars($role['role_name']) : 'Роль не знайдено';

        // Передаємо у вид дані про роль та список користувачів
        $this->render('v_role_single', [
            'role' => $role,
            'usersInRole' => $usersInRole // Новий масив з користувачами
        ]);
    }
    
    public function addAction() {
        if (!$this->hasPermission('roles', 'a')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Додавання нової ролі';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('CSRF token validation failed!');
            }

            $data = $this->prepareRoleDataFromPost();
            
            if (!empty($data['role_name'])) {
                if ($this->mRoles->add($data)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Нову роль успішно додано.'];
                    header('Location: ' . BASE_URL . '/roles');
                    exit();
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося додати нову роль.'];
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва ролі не може бути порожньою.'];
            }
        }
        $this->render('v_role_add', []);
    }
    
    public function editAction() {
        if (!$this->hasPermission('roles', 'e')) {
            return $this->showAccessDenied();
        }

        $id = $this->params['id'] ?? 0;
        $this->title = 'Редагування ролі';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('CSRF token validation failed!');
            }

            $data = $this->prepareRoleDataFromPost();

            if (!empty($data['role_name'])) {
                if ($this->mRoles->update($id, $data)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Дані ролі успішно оновлено.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося оновити дані.'];
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва ролі не може бути порожньою.'];
            }
            header('Location: ' . BASE_URL . '/roles/edit/' . $id);
            exit();
        }

        $role = $this->mRoles->getById($id);
        $this->render('v_role_edit', ['role' => $role]);
    }
    
    public function deleteAction() {
        header('Content-Type: application/json');

        if (!$this->hasPermission('roles', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав для видалення.']);
            exit();
        }

        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? '';
        if (empty($tokenFromHeader) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            echo json_encode(['success' => false, 'message' => 'CSRF-помилка.']);
            exit();
        }

        $id = $this->params['id'] ?? 0;
        if ((int)$id === 1) {
            echo json_encode(['success' => false, 'message' => 'Цю роль не можна видалити.']);
            exit();
        }

        if ($this->mRoles->deleteById($id)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Роль успішно видалено.'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не вдалося видалити роль.']);
        }
        exit();
    }

    private function prepareRoleDataFromPost() {
        $perms = [
            'chat' => $_POST['perms']['chat'] ?? [],
            'users' => $_POST['perms']['users'] ?? [],
            'categories' => $_POST['perms']['categories'] ?? [],
            'goods' => $_POST['perms']['goods'] ?? [],
            'roles' => $_POST['perms']['roles'] ?? [],
            'warehouses' => $_POST['perms']['warehouses'] ?? [],
            'arrivals' => $_POST['perms']['arrivals'] ?? [],
            'transfers' => $_POST['perms']['transfers'] ?? [],
            'albums' => $_POST['perms']['albums'] ?? [],
            'currencies' => $_POST['perms']['currencies'] ?? [],
            'writeoffs' => $_POST['perms']['writeoffs'] ?? [],
            'settings' => $_POST['perms']['settings'] ?? [],
            'notes' => $_POST['perms']['notes'] ?? [],
            'clipboard' => $_POST['perms']['clipboard'] ?? [],
        ];

        return [
            'role_name' => trim($_POST['role_name'] ?? ''),
            'perm_chat' => implode('', $perms['chat']),
            'perm_users' => implode('', $perms['users']),
            'perm_categories' => implode('', $perms['categories']),
            'perm_goods' => implode('', $perms['goods']),
            'perm_roles' => implode('', $perms['roles']),
            'perm_warehouses' => implode('', $perms['warehouses']),
            'perm_arrivals' => implode('', $perms['arrivals']),
            'perm_transfers' => implode('', $perms['transfers']),
            'perm_albums' => implode('', $perms['albums']),
            'perm_currencies' => implode('', $perms['currencies']),
            'perm_writeoffs' => implode('', $perms['writeoffs']),
            'perm_settings' => implode('', $perms['settings']),
            'perm_notes' => implode('', $perms['notes']),
            'perm_clipboard' => implode('', $perms['clipboard']),
        ];
    }
}