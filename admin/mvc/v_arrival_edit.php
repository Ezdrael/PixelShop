<?php
if (isset($arrival) && $arrival) {
    // Підключаємо універсальну форму додавання
    include 'v_arrival_add.php';
} else {
    echo '<div class="content-card"><h2>Постачання не знайдено</h2></div>';
}