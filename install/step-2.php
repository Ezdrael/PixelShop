<?php
/**
 * step-2.php - Форма налаштування бази даних.
 */

render_header("Крок 2: Налаштування бази даних");

if (isset($_SESSION['error'])) {
    echo "<div class='error-box'>". htmlspecialchars($_SESSION['error']). "</div>";
    unset($_SESSION['error']);
}
?>
<form method="post" action="index.php?step=2">
    <p>Введіть дані для підключення до вашої бази даних MySQL.</p>
    <div><label>Хост бази даних:</label><input type="text" name="db_host" value="localhost" required></div>
    <div><label>Ім'я бази даних:</label><input type="text" name="db_name" required></div>
    <div><label>Ім'я користувача:</label><input type="text" name="db_user" required></div>
    <div><label>Пароль:</label><input type="password" name="db_pass"></div>
    <div><label>Префікс таблиць:</label><input type="text" name="db_prefix" value="cms_" required></div>
    <button type="submit" class="button">Перевірити та продовжити</button>
</form>
<?php
render_footer();
