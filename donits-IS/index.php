<?php
include 'config/db.php';

$totalSales = (float) ($pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM sales')->fetchColumn());
$totalProfit = (float) ($pdo->query('SELECT COALESCE(SUM(interest), 0) FROM sales')->fetchColumn());
$totalItems = (int) ($pdo->query('SELECT COUNT(*) FROM items')->fetchColumn());
$totalStocks = (int) ($pdo->query('SELECT COALESCE(SUM(remaining), 0) FROM items')->fetchColumn());

$salesByDayStmt = $pdo->query(
    'SELECT DATE(created_at) AS sale_date, COALESCE(SUM(total_amount), 0) AS amount
     FROM sales
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
     GROUP BY DATE(created_at)
     ORDER BY sale_date ASC'
);
$salesByDay = $salesByDayStmt->fetchAll();

$labels = array_map(static fn($row) => $row['sale_date'], $salesByDay);
$amounts = array_map(static fn($row) => (float) $row['amount'], $salesByDay);

include 'includes/header.php';
?>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Sales</h6><h4><?= number_format($totalSales, 2) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Profit</h6><h4><?= number_format($totalProfit, 2) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Item Types</h6><h4><?= number_format($totalItems) ?></h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Remaining Stocks</h6><h4><?= number_format($totalStocks) ?></h4></div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Sales (Last 14 Days)</h5>
        <canvas id="salesChart" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Sales Amount',
            data: <?= json_encode($amounts) ?>,
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.2)',
            tension: 0.2,
            fill: true
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
