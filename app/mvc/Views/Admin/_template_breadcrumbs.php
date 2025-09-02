<?php
// public/app/Mvc/Views/admin/_template_breadcrumbs.php
?>
<nav id="breadcrumbs-container" class="breadcrumbs"></nav>

<script id="breadcrumbs-data" type="application/json">
    <?php echo json_encode($breadcrumbs ?? [], JSON_UNESCAPED_UNICODE); ?>
</script>

<?php if (isset($albumsTree) || isset($albumsTreeData)): ?>
<script id="album-tree-data" type="application/json">
    <?php echo json_encode($albumsTree ?? ($albumsTreeData ?? []), JSON_UNESCAPED_UNICODE); ?>
</script>
<?php endif; ?>