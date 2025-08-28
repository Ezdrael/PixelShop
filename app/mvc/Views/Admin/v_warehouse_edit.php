<?php 
if (isset($warehouse)) {
    include 'v_warehouse_add.php'; 
} else {
    echo '<div class="content-card"><h2>Склад не знайдено</h2></div>';
}
?>