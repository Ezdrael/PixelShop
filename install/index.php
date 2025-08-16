<?php
/**
 * index.php - Головний контролер інсталятора.
 */
require_once 'common.php';

// Запобігання повторному встановленню
if (file_exists('../install.lock')) {
    header("HTTP/1.1 403 Forbidden");
    die("Помилка: Інсталятор вже заблоковано. Видаліть файл 'install.lock' у кореневому каталозі, щоб продовжити.");
}

$step = isset($_GET['step'])? (int)$_GET['step'] : 1;

// Обробка POST-запитів
if ($_SERVER === 'POST') {
    switch ($step) {
        case 2: // Обробка форми БД
            $_SESSION['install_data']['db_host'] = $_POST['db_host'];
            $_SESSION['install_data']['db_name'] = $_POST['db_name'];
            $_SESSION['install_data']['db_user'] = $_POST['db_user'];
            $_SESSION['install_data']['db_pass'] = $_POST['db_pass'];
            $_SESSION['install_data']['db_prefix'] = $_POST['db_prefix'];

            try {
                $dsn = "mysql:host={$_POST['db_host']};dbname={$_POST['db_name']}";
                new PDO($dsn, $_POST['db_user'], $_POST['db_pass']);
                header('Location: index.php?step=3');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error'] = "Не вдалося підключитися до бази даних: ". $e->getMessage();
                header('Location: index.php?step=2');
                exit;
            }
            break;

        case 3: // Обробка форми сайту/адміністратора
            if ($_POST['admin_pass']!== $_POST['admin_pass_confirm']) {
                $_SESSION['error'] = "Паролі не співпадають.";
                header('Location: index.php?step=3');
                exit;
            } else {
                $_SESSION['install_data']['site_title'] = $_POST['site_title'];
                $_SESSION['install_data']['admin_user'] = $_POST['admin_user'];
                $_SESSION['install_data']['admin_pass'] = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);
                $_SESSION['install_data']['admin_email'] = $_POST['admin_email'];
                header('Location: index.php?step=4');
                exit;
            }
            break;
    }
}

// Відображення відповідного кроку
switch ($step) {
    case 1:
        include 'step-1.php';
        break;
    case 2:
        include 'step-2.php';
        break;
    case 3:
        include 'step-3.php';
        break;
    case 4:
        include 'step-4.php';
        break;
    default:
        include 'step-1.php';
}
