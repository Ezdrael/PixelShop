<?php
/**
 * step-2.php - Форма налаштування бази даних (з AJAX-перевіркою).
 */

render_header("Крок 2: Налаштування бази даних", 2);

// Отримуємо збережені дані з сесії, щоб заповнити поля
$db_data = $_SESSION['install_data'] ?? [];

if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
    unset($_SESSION['error']);
}
?>

<form method="post" action="index.php?step=2" id="db-form">
    <p>Введіть дані для підключення до вашої бази даних MySQL. З'єднання буде перевірено автоматично.</p>
    
    <div>
        <label for="db_host">Хост бази даних:</label>
        <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($db_data['db_host'] ?? 'localhost') ?>" required>
    </div>
    
    <div>
        <label for="db_name">Ім'я бази даних:</label>
        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($db_data['db_name'] ?? '') ?>" required>
    </div>
    
    <div>
        <label for="db_user">Ім'я користувача:</label>
        <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($db_data['db_user'] ?? '') ?>" required>
    </div>
    
    <div>
        <label for="db_pass">Пароль:</label>
        <input type="password" id="db_pass" name="db_pass" value="<?= htmlspecialchars($db_data['db_pass'] ?? '') ?>">
    </div>
    
    <div>
        <label for="db_prefix">Префікс таблиць:</label>
        <input type="text" id="db_prefix" name="db_prefix" value="<?= htmlspecialchars($db_data['db_prefix'] ?? 'ps_') ?>" required>
    </div>

    <div id="connection-status" class="status-box"></div>
    
    <button type="button" id="check-db-btn" class="button">Перевірити з'єднання</button>
    <button type="submit" id="continue-btn" class="button" disabled>Далі</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkBtn = document.getElementById('check-db-btn');
    const continueBtn = document.getElementById('continue-btn');
    const form = document.getElementById('db-form');
    const statusBox = document.getElementById('connection-status');

    checkBtn.addEventListener('click', async function() {
        // Показуємо процес перевірки
        statusBox.style.display = 'block';
        statusBox.className = 'status-box';
        statusBox.textContent = 'Перевірка...';
        continueBtn.disabled = true;

        const formData = new FormData(form);

        try {
            const response = await fetch('ajax-check-db.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            statusBox.textContent = result.message;

            if (result.success) {
                statusBox.classList.add('status-success');
                continueBtn.disabled = false; // Розблоковуємо кнопку "Далі"
            } else {
                statusBox.classList.add('status-danger');
            }
        } catch (error) {
            statusBox.className = 'status-box status-danger';
            statusBox.textContent = 'Сталася помилка під час відправки запиту.';
            console.error('Fetch error:', error);
        }
    });
});
</script>

<?php
render_footer();
