<?php
include '../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);

$checkSales = $pdo->prepare('SELECT COUNT(*) FROM sales WHERE item_id = :id');
$checkSales->execute(['id' => $id]);

if ((int) $checkSales->fetchColumn() > 0) {
    set_flash('danger', 'Cannot delete item with existing sales records.');
    redirect('modules/items/index.php');
}

$stmt = $pdo->prepare('DELETE FROM items WHERE id = :id');
$stmt->execute(['id' => $id]);

set_flash('success', 'Item deleted successfully.');
redirect('modules/items/index.php');
