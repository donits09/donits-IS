<?php
include '../../config/db.php';

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

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Sales</h3>
    <a href="<?= e(app_url('modules/sales/add.php')) ?>" class="btn btn-primary">Add Sale</a>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Search by item name">
    </div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Search</button></div>
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
        <th width="170">Actions</th>
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
                <td><?= number_format((float) $row['sold_price'], 2) ?></td>
                <td><?= number_format((float) $row['total_amount'], 2) ?></td>
                <td><?= number_format((float) $row['interest'], 2) ?></td>
                <td><?= e($row['created_at']) ?></td>
                <td>
                    <a class="btn btn-sm btn-warning" href="<?= e(app_url('modules/sales/edit.php?id=' . (int) $row['id'])) ?>">Edit</a>
                    <a class="btn btn-sm btn-danger" href="<?= e(app_url('modules/sales/delete.php?id=' . (int) $row['id'])) ?>" onclick="return confirm('Delete this sale? Stock will be restored.')">Delete</a>
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

<?php include '../../includes/footer.php'; ?>
