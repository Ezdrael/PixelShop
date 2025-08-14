<?php 
if (isset($category)) {
    include 'v_category_add.php'; 
} else {
    echo '<div class="content-card"><h2>Категорію не знайдено</h2></div>';
}
?>