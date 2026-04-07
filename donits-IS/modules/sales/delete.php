<?php
include '../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM sales WHERE id = :id');
$stmt->execute(['id' => $id]);
$sale = $stmt->fetch();

if (!$sale) {
    set_flash('danger', 'Sale not found.');
    redirect('modules/sales/index.php');
}

$pdo->beginTransaction();
try {
    $restoreStmt = $pdo->prepare('UPDATE items SET remaining = remaining + :quantity WHERE id = :id');
    $restoreStmt->execute([
        'quantity' => (int) $sale['quantity'],
        'id' => (int) $sale['item_id'],
    ]);

    $deleteStmt = $pdo->prepare('DELETE FROM sales WHERE id = :id');
    $deleteStmt->execute(['id' => $id]);

    $pdo->commit();
    set_flash('success', 'Sale deleted and stock restored successfully.');
} catch (Throwable $e) {
    $pdo->rollBack();
    set_flash('danger', 'Failed to delete sale.');
}

redirect('modules/sales/index.php');
