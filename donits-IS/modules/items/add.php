<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = trim($_POST['item_name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $salePrice = (float) ($_POST['sale_price'] ?? 0);
    $totalItems = (int) ($_POST['total_items'] ?? 0);
    $remaining = (int) ($_POST['remaining'] ?? 0);

    if ($itemName === '') {
        set_flash('danger', 'Item name is required.');
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO items (item_name, price, sale_price, total_items, remaining)
             VALUES (:item_name, :price, :sale_price, :total_items, :remaining)'
        );
        $stmt->execute([
            'item_name' => $itemName,
            'price' => $price,
            'sale_price' => $salePrice,
            'total_items' => $totalItems,
            'remaining' => $remaining,
        ]);

        set_flash('success', 'Item added successfully.');
        redirect('modules/items/index.php');
    }
}

include '../../includes/header.php';
?>

<h3>Add Item</h3>
<form method="POST" class="row g-3">
    <div class="col-md-6"><label class="form-label">Item Name</label><input class="form-control" name="item_name" required></div>
    <div class="col-md-3"><label class="form-label">Price</label><input class="form-control" name="price" type="number" step="0.01" min="0" required></div>
    <div class="col-md-3"><label class="form-label">Sale Price</label><input class="form-control" name="sale_price" type="number" step="0.01" min="0" required></div>
    <div class="col-md-3"><label class="form-label">Total Items</label><input class="form-control" name="total_items" type="number" min="0" required></div>
    <div class="col-md-3"><label class="form-label">Remaining</label><input class="form-control" name="remaining" type="number" min="0" required></div>
    <div class="col-12">
        <button class="btn btn-primary">Save</button>
        <a href="<?= e(app_url('modules/items/index.php')) ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include '../../includes/footer.php'; ?>
