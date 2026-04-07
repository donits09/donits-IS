<?php
require_once __DIR__ . '/../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
$saleStmt = $pdo->prepare('SELECT * FROM sales WHERE id = :id');
$saleStmt->execute(['id' => $id]);
$sale = $saleStmt->fetch();

if (!$sale) {
    set_flash('danger', 'Sale not found.');
    redirect('modules/sales/index.php');
}

$items = $pdo->query('SELECT id, item_name, price, remaining FROM items ORDER BY item_name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $soldPrice = (float) ($_POST['sold_price'] ?? 0);

    $itemStmt = $pdo->prepare('SELECT * FROM items WHERE id = :id');
    $itemStmt->execute(['id' => $itemId]);
    $item = $itemStmt->fetch();

    if (!$item) {
        set_flash('danger', 'Invalid item selected.');
    } else {
        $pdo->beginTransaction();
        try {
            $restoreStock = $pdo->prepare('UPDATE items SET remaining = remaining + :qty WHERE id = :id');
            $restoreStock->execute(['qty' => (int) $sale['quantity'], 'id' => (int) $sale['item_id']]);

            $currentStockStmt = $pdo->prepare('SELECT remaining FROM items WHERE id = :id');
            $currentStockStmt->execute(['id' => $itemId]);
            $availableStock = (int) $currentStockStmt->fetchColumn();

            if ($quantity > $availableStock) {
                throw new RuntimeException('Insufficient stock after adjustment.');
            }

            $updateSale = $pdo->prepare(
                'UPDATE sales
                 SET item_id = :item_id,
                     quantity = :quantity,
                     sold_price = :sold_price,
                     total_amount = :total_amount,
                     interest = :interest
                 WHERE id = :id'
            );

            $updateSale->execute([
                'item_id' => $itemId,
                'quantity' => $quantity,
                'sold_price' => $soldPrice,
                'total_amount' => $quantity * $soldPrice,
                'interest' => ($soldPrice - (float) $item['price']) * $quantity,
                'id' => $id,
            ]);

            $deductStock = $pdo->prepare('UPDATE items SET remaining = remaining - :qty WHERE id = :id');
            $deductStock->execute(['qty' => $quantity, 'id' => $itemId]);

            $pdo->commit();
            set_flash('success', 'Sale updated successfully.');
            redirect('modules/sales/index.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            set_flash('danger', 'Failed to update sale. ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h3>Edit Sale</h3>
<form method="POST" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Item</label>
        <select name="item_id" class="form-select" required>
            <?php foreach ($items as $item): ?>
                <option value="<?= (int) $item['id'] ?>" <?= (int) $sale['item_id'] === (int) $item['id'] ? 'selected' : '' ?>>
                    <?= e($item['item_name']) ?> (Remaining: <?= (int) $item['remaining'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3"><label class="form-label">Quantity</label><input class="form-control" type="number" name="quantity" min="1" value="<?= (int) $sale['quantity'] ?>" required></div>
    <div class="col-md-3"><label class="form-label">Sold Price</label><input class="form-control" type="number" step="0.01" min="0" name="sold_price" value="<?= e((string) $sale['sold_price']) ?>" required></div>
    <div class="col-12">
        <button class="btn btn-primary">Update</button>
        <a href="<?= e(app_url('modules/sales/index.php')) ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
