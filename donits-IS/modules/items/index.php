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

<div class="card app-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="section-title mb-0"><i class="bi bi-archive me-2"></i>Items</h3>
            <a href="<?= e(app_url('modules/items/add.php')) ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Add Item</a>
        </div>

        <form class="row g-2 align-items-end mb-3" method="GET">
            <div class="col-sm-8 col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Search by item name">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>Search</button>
            </div>
        </form>

        <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Price</th>
                    <th>Sale Price</th>
                    <th>Markup %</th>
                    <th>Total Items</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="8" class="text-center">No items found.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $row): ?>
                        <?php $remaining = (int) $row['remaining']; ?>
                        <tr>
                            <td><?= e($row['item_name']) ?></td>
                            <td><?= e(format_currency((float) $row['price'])) ?></td>
                            <td><?= e(format_currency((float) $row['sale_price'])) ?></td>
                            <td><?= number_format(calculate_markup_percent((float) $row['price'], (float) $row['sale_price']), 1) ?>%</td>
                            <td><?= (int) $row['total_items'] ?></td>
                            <td><?= $remaining ?></td>
                            <td>
                                <span class="badge text-bg-<?= e(stock_health_badge_class($remaining)) ?>">
                                    <?= e(stock_health_label($remaining)) ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-warning" href="<?= e(app_url('modules/items/edit.php?id=' . (int) $row['id'])) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                                <a class="btn btn-sm btn-danger" href="<?= e(app_url('modules/items/delete.php?id=' . (int) $row['id'])) ?>" onclick="return confirm('Delete this item?')"><i class="bi bi-trash me-1"></i>Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <nav>
        <ul class="pagination mb-0">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
        </nav>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
