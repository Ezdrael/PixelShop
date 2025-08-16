<?php
/**
 * step-3.php - Форма налаштування сайту та адміністратора.
 */
render_header("Крок 3: Налаштування сайту", 3);

if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
    unset($_SESSION['error']);
}
?>
<form method="post" action="index.php?step=3">
    <div class="input-group"><i class="fas fa-globe-europe"></i><input type="text" name="site_title" placeholder="Назва сайту" required></div>
    <div class="input-group"><i class="fas fa-user-shield"></i><input type="text" name="admin_user" placeholder="Ім'я адміністратора" required></div>
    <div class="input-group"><i class="fas fa-key"></i><input type="password" name="admin_pass" placeholder="Пароль адміністратора" required></div>
    <div class="input-group"><i class="fas fa-key"></i><input type="password" name="admin_pass_confirm" placeholder="Підтвердіть пароль" required></div>
    <div class="input-group"><i class="fas fa-at"></i><input type="email" name="admin_email" placeholder="Email адміністратора" required></div>

    <div class="checkbox-group">
        <input type="checkbox" id="install_demo_data" name="install_demo_data" value="1" checked>
        <label for="install_demo_data">Встановити демонстраційні дані (рекомендовано для першого знайомства)</label>
    </div>
    
    <button type="submit" class="button">Встановити <i class="fas fa-check"></i></button>
</form>
<?php render_footer(); ?>
