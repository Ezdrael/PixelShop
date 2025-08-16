<?php
/**
 * step-1.php - Перевірка вимог до сервера.
 */

$errors =;
// Перевірка версії PHP
if (!version_compare(PHP_VERSION, REQUIRED_PHP_VERSION, '>=')) {
    $errors = "Необхідна версія PHP ". REQUIRED_PHP_VERSION. " або вище. Ваша версія: ". PHP_VERSION;
}
// Перевірка розширень
if (!extension_loaded('curl')) $errors = "Розширення 'curl' не знайдено.";
if (!class_exists('ZipArchive')) $errors = "Клас 'ZipArchive' (розширення zip) не знайдено.";
if (!extension_loaded('pdo_mysql')) $errors = "Розширення 'pdo_mysql' не знайдено.";
// Перевірка прав на запис
if (!is_writable(__DIR__. '/..')) $errors = "Кореневий каталог недоступний для запису. Перевірте права доступу.";

render_header("Крок 1: Перевірка системи");

if (!empty($errors)) {
    echo '<div class="error-box">';
    echo '<h3>Виявлено проблеми з конфігурацією сервера:</h3><ul>';
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo '</ul><p>Будь ласка, виправте ці проблеми перед продовженням встановлення.</p>';
    echo '</div>';
} else {
    echo '<h2>Ласкаво просимо до інсталятора вашої CMS!</h2>';
    echo '<p>Система готова до встановлення. Цей майстер проведе вас через процес налаштування.</p>';
    echo '<p>Натисніть "Далі", щоб почати.</p>';
    echo '<a href="index.php?step=2" class="button">Далі</a>';
}

render_footer();
