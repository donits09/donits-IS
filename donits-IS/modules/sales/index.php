<?php
require_once __DIR__ . '/../../config/db.php';

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE i.item_name LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$countSql = "SELECT COUNT(*) FROM sales s JOIN items i ON i.id = s.item_id $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$listSql = "SELECT s.*, i.item_name
            FROM sales s
            JOIN items i ON i.id = s.item_id
            $where
            ORDER BY s.created_at DESC
            LIMIT :limit OFFSET :offset";
$listStmt = $pdo->prepare($listSql);
foreach ($params as $key => $value) {
    $listStmt->bindValue(':' . $key, $value);
}
$listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$sales = $listStmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="card app-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="section-title mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Sales</h3>
            <a href="<?= e(app_url('modules/sales/add.php')) ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Add Sale</a>
        </div>

        <form class="row g-2 align-items-end mb-3" method="GET">
            <div class="col-sm-8 col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Search by item name">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>Search</button></div>
        </form>

        <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Sold Price</th>
                <th>Total Amount</th>
                <th>Profit</th>
                <th>Date</th>
                <th width="180">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$sales): ?>
                <tr><td colspan="7" class="text-center">No sales found.</td></tr>
            <?php else: ?>
                <?php foreach ($sales as $row): ?>
                    <tr>
                        <td><?= e($row['item_name']) ?></td>
                        <td><?= (int) $row['quantity'] ?></td>
                        <td><?= e(format_currency((float) $row['sold_price'])) ?></td>
                        <td><?= e(format_currency((float) $row['total_amount'])) ?></td>
                        <td>
                            <span class="badge text-bg-<?= (float) $row['interest'] >= 0 ? 'success' : 'danger' ?>">
                                <?= e(format_currency((float) $row['interest'])) ?>
                            </span>
                        </td>
                        <td><?= e($row['created_at']) ?></td>
                        <td>
                            <a class="btn btn-sm btn-warning" href="<?= e(app_url('modules/sales/edit.php?id=' . (int) $row['id'])) ?>"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                            <a class="btn btn-sm btn-danger" href="<?= e(app_url('modules/sales/delete.php?id=' . (int) $row['id'])) ?>" onclick="return confirm('Delete this sale? Stock will be restored.')"><i class="bi bi-trash me-1"></i>Delete</a>
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
