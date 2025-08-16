<?php
/**
 * step-3.php - Форма налаштування сайту та адміністратора.
 */

render_header("Крок 3: Налаштування сайту");

if (isset($_SESSION['error'])) {
    echo "<div class='error-box'>". htmlspecialchars($_SESSION['error']). "</div>";
    unset($_SESSION['error']);
}
?>
<form method="post" action="index.php?step=3">
    <p>Тепер налаштуйте основні параметри сайту та створіть обліковий запис адміністратора.</p>
    <div><label>Назва сайту:</label><input type="text" name="site_title" required></div>
    <div><label>Ім'я адміністратора:</label><input type="text" name="admin_user" required></div>
    <div><label>Пароль адміністратора:</label><input type="password" name="admin_pass" required></div>
    <div><label>Підтвердіть пароль:</label><input type="password" name="admin_pass_confirm" required></div>
    <div><label>Email адміністратора:</label><input type="email" name="admin_email" required></div>
    <button type="submit" class="button">Встановити CMS</button>
</form>
<?php
render_footer();
