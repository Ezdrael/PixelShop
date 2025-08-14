<?php
// ===================================================================
// Файл: authorisation.php 🕰️
// Розміщення: / (коренева папка сайту)
// Призначення: Сторінка та логіка імітації входу.
// ===================================================================

$errorMessage = '';

// ОБРОБКА POST-ЗАПИТУ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mUsers = new M_Users();
        $user = $mUsers->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $mUsers->updateToken($user['id'], $token);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_token'] = $token;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Генеруємо CSRF-токен
            // Перезавантажуємо сторінку, щоб index.php міг знову перевірити сесію
            header('Location: ' . BASE_URL . '/');
            exit();
        } else {
            $errorMessage = 'Користувача з таким email не знайдено.';
        }
    } else {
        $errorMessage = 'Будь ласка, введіть коректний email.';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сторінка входу</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/resources/css/main.css">
</head>
<body class="login-page">

    <div class="login-card">
        <h1>Вхід в панель</h1>
        <p>Введіть email для імітації входу</p>
        
        <form method="POST" action="">
            <div class="form-group" style="text-align: left;">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <?php if ($errorMessage): ?>
                <p style="color: var(--danger-color); margin-top: -0.5rem; margin-bottom: 1rem;"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <br>
            <button type="submit" class="social-btn" style="background-color: var(--success-color); width: 100%;">
                <i class="fas fa-sign-in-alt"></i>
                <span>Імітувати вхід</span>
            </button>
        </form>

    </div>

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

.login-card:hover {
    transform: translateY(-5px);
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