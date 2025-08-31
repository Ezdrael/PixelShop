<?php
// public/authorisation.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/vendor/autoload.php';
if (file_exists(BASE_PATH . '/config.php')) {
    require_once BASE_PATH . '/config.php';
}

use App\Mvc\Models\Users;

$errorMessage = '';

// --- ОНОВЛЕНА ЛОГІКА ПЕРЕВІРКИ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Перевіряємо, чи заповнені обидва поля
    if (!empty($email) && !empty($password) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mUsers = new Users();
        $user = $mUsers->findByEmail($email);

        // Перевіряємо, чи знайдено користувача І чи співпадає пароль
        if ($user && password_verify($password, $user['password'])) {
            // Успішна авторизація
            session_regenerate_id(true); // Захист від фіксації сесії

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Видаляємо старий токен (він більше не потрібен для входу)
            $mUsers->clearToken($user['id']); 

            $redirectUrl = (defined('PROJECT_URL') ? PROJECT_URL : '') . '/admin/';
            header('Location: ' . $redirectUrl);
            exit();
        } else {
            // Неправильний email або пароль
            $errorMessage = 'Неправильний email або пароль.';
        }
    } else {
        $errorMessage = 'Будь ласка, заповніть обидва поля коректно.';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в панель керування</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    
    <link rel="stylesheet" href="<?php echo defined('PROJECT_URL') ? PROJECT_URL : ''; ?>/resources/css/admin/ADMIN-MAIN.css">
    <style>
        /* Стилі для сторінки входу (залишаються без змін) */
        body.login-page { font-family: 'Montserrat', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { background-color: var(--card-bg); padding: 2.5rem 2rem; border-radius: 16px; box-shadow: 0 10px 25px var(--shadow-color); text-align: center; width: 100%; max-width: 400px; }
        .login-card h1 { margin: 0 0 1.5rem; font-weight: 600; font-size: 1.8rem; }
        .login-error-message { color: var(--danger-color); margin-bottom: 1rem; }
    </style>
</head>
<body class="login-page">

    <div class="login-card">
        <h1>Вхід в панель</h1>
        
        <form method="POST" action="">
            <div class="form-group-inline">
                <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
            </div>
            
            <div class="form-group-inline" style="margin-top: 1rem;">
                <input type="password" name="password" id="password" class="form-control" placeholder="Пароль" required>
            </div>

            <?php if ($errorMessage): ?>
                <p class="login-error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
            
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1.5rem;">
                <i class="fas fa-sign-in-alt"></i>
                <span style="margin-left: 0.5rem;">Увійти</span>
            </button>
        </form>
    </div>
<?php
/* !!! ВАЖЛИВО: Замініть 'YourNewSecurePassword123' на ваш новий, надійний пароль !!!
$newPassword = 'Soto1408';

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

echo "Ваш новий хеш пароля:<br><br>";
echo "<textarea rows='4' cols='70' readonly onclick='this.select();' style='font-size: 14px; padding: 10px;'>" . htmlspecialchars($hashedPassword) . "</textarea>";
echo "<p>Скопіюйте цей хеш. Після використання не забудьте видалити цей файл (`generate_hash.php`).</p>";
*/
?>
</body>
</html>




<style>
/* --- Сторінка авторизації --- */
body.login-page {
    font-family: 'Poppins', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.login-card {
    background-color: var(--card-bg);
    padding: 2.5rem 2rem;
    border-radius: 16px;
    box-shadow: 0 10px 25px var(--shadow-color);
    text-align: center;
    transition: transform 0.3s ease;
    width: 100%;
    max-width: 400px;
}

.login-card h1 {
    margin: 0 0 1rem;
    font-weight: 600;
    font-size: 1.8rem;
}

.login-card p {
    margin: 0 0 2rem;
    color: #777;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    color: #fff;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.social-btn:hover {
    opacity: 0.9;
}

.social-btn i {
    margin-right: 0.75rem;
    font-size: 1.2rem;
}
</style>