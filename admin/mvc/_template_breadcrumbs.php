<?php
// mvc/_template_breadcrumbs.php
?>
<nav id="breadcrumbs-container" class="breadcrumbs"></nav>

<script id="breadcrumbs-data" type="application/json">
    <?php echo json_encode($breadcrumbs ?? [], JSON_UNESCAPED_UNICODE); ?>
</script>