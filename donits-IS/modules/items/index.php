<?php
require_once __DIR__ . '/../../config/db.php';

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE item_name LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM items $where");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$listStmt = $pdo->prepare("SELECT * FROM items $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $listStmt->bindValue(':' . $key, $value);
}
$listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$items = $listStmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Items</h3>
    <a href="<?= e(app_url('modules/items/add.php')) ?>" class="btn btn-primary">Add Item</a>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Search by item name">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Search</button>
    </div>
</form>

<div class="table-responsive">
<table class="table table-striped table-bordered align-middle">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Price</th>
            <th>Sale Price</th>
            <th>Total Items</th>
            <th>Remaining</th>
            <th width="170">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="6" class="text-center">No items found.</td></tr>
        <?php else: ?>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= e($row['item_name']) ?></td>
                    <td><?= number_format((float) $row['price'], 2) ?></td>
                    <td><?= number_format((float) $row['sale_price'], 2) ?></td>
                    <td><?= (int) $row['total_items'] ?></td>
                    <td><?= (int) $row['remaining'] ?></td>
                    <td>
                        <a class="btn btn-sm btn-warning" href="<?= e(app_url('modules/items/edit.php?id=' . (int) $row['id'])) ?>">Edit</a>
                        <a class="btn btn-sm btn-danger" href="<?= e(app_url('modules/items/delete.php?id=' . (int) $row['id'])) ?>" onclick="return confirm('Delete this item?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>

<nav>
<ul class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>
</ul>
</nav>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
