<?php
require_once __DIR__ . '/../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM items WHERE id = :id');
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('danger', 'Item not found.');
    redirect('modules/items/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = trim($_POST['item_name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $salePrice = (float) ($_POST['sale_price'] ?? 0);
    $totalItems = (int) ($_POST['total_items'] ?? 0);
    $remaining = (int) ($_POST['remaining'] ?? 0);

    $update = $pdo->prepare(
        'UPDATE items
         SET item_name = :item_name,
             price = :price,
             sale_price = :sale_price,
             total_items = :total_items,
             remaining = :remaining
         WHERE id = :id'
    );

    $update->execute([
        'item_name' => $itemName,
        'price' => $price,
        'sale_price' => $salePrice,
        'total_items' => $totalItems,
        'remaining' => $remaining,
        'id' => $id,
    ]);

    set_flash('success', 'Item updated successfully.');
    redirect('modules/items/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h3>Edit Item</h3>
<form method="POST" class="row g-3">
    <div class="col-md-6"><label class="form-label">Item Name</label><input class="form-control" name="item_name" value="<?= e($item['item_name']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Price</label><input class="form-control" name="price" type="number" step="0.01" min="0" value="<?= e((string) $item['price']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Sale Price</label><input class="form-control" name="sale_price" type="number" step="0.01" min="0" value="<?= e((string) $item['sale_price']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Total Items</label><input class="form-control" name="total_items" type="number" min="0" value="<?= (int) $item['total_items'] ?>" required></div>
    <div class="col-md-3"><label class="form-label">Remaining</label><input class="form-control" name="remaining" type="number" min="0" value="<?= (int) $item['remaining'] ?>" required></div>
    <div class="col-12">
        <button class="btn btn-primary">Update</button>
        <a href="<?= e(app_url('modules/items/index.php')) ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
