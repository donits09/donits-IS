<?php
require_once __DIR__ . '/../../config/db.php';

$items = $pdo->query('SELECT id, item_name, price, sale_price, remaining FROM items ORDER BY item_name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $soldPrice = (float) ($_POST['sold_price'] ?? 0);

    $itemStmt = $pdo->prepare('SELECT * FROM items WHERE id = :id');
    $itemStmt->execute(['id' => $itemId]);
    $item = $itemStmt->fetch();

    if (!$item) {
        set_flash('danger', 'Invalid item selected.');
    } elseif ($quantity > (int) $item['remaining']) {
        set_flash('danger', 'Insufficient stock for this sale.');
    } else {
        $totalAmount = $soldPrice * $quantity;
        $interest = ($soldPrice - (float) $item['price']) * $quantity;

        $pdo->beginTransaction();
        try {
            $saleStmt = $pdo->prepare(
                'INSERT INTO sales (item_id, quantity, sold_price, total_amount, interest)
                 VALUES (:item_id, :quantity, :sold_price, :total_amount, :interest)'
            );
            $saleStmt->execute([
                'item_id' => $itemId,
                'quantity' => $quantity,
                'sold_price' => $soldPrice,
                'total_amount' => $totalAmount,
                'interest' => $interest,
            ]);

            $stockStmt = $pdo->prepare('UPDATE items SET remaining = remaining - :quantity WHERE id = :id');
            $stockStmt->execute(['quantity' => $quantity, 'id' => $itemId]);

            $pdo->commit();
            set_flash('success', 'Sale added successfully.');
            redirect('modules/sales/index.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            set_flash('danger', 'Failed to save sale.');
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h3>Add Sale</h3>
<form method="POST" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Item</label>
        <select name="item_id" class="form-select" required>
            <option value="">Select Item</option>
            <?php foreach ($items as $item): ?>
                <option value="<?= (int) $item['id'] ?>" data-sale-price="<?= e((string) $item['sale_price']) ?>">
                    <?= e($item['item_name']) ?> (Remaining: <?= (int) $item['remaining'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3"><label class="form-label">Quantity</label><input class="form-control" type="number" name="quantity" min="1" value="1" required></div>
    <div class="col-md-3"><label class="form-label">Sold Price</label><input id="soldPrice" class="form-control" type="number" step="0.01" min="0" name="sold_price" required></div>
    <div class="col-12">
        <button class="btn btn-primary">Save</button>
        <a href="<?= e(app_url('modules/sales/index.php')) ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
const itemSelect = document.querySelector('select[name="item_id"]');
const soldPriceInput = document.getElementById('soldPrice');
itemSelect?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    soldPriceInput.value = option?.dataset.salePrice || '';
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
