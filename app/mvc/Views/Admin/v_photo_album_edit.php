<?php
// mvc/v_photo_album_edit.php

if (isset($album) && $album) {
    // Підключаємо універсальну форму додавання
    include 'v_photo_album_add.php';
} else {
    echo '<div class="content-card"><h2>Альбом не знайдено</h2></div>';
}