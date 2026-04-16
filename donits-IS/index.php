<?php
include 'config/db.php';

$totalSales = (float) ($pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM sales')->fetchColumn());
$totalProfit = (float) ($pdo->query('SELECT COALESCE(SUM(interest), 0) FROM sales')->fetchColumn());
$totalItems = (int) ($pdo->query('SELECT COUNT(*) FROM items')->fetchColumn());
$totalStocks = (int) ($pdo->query('SELECT COALESCE(SUM(remaining), 0) FROM items')->fetchColumn());
$totalInventoryCost = (float) ($pdo->query('SELECT COALESCE(SUM(price * remaining), 0) FROM items')->fetchColumn());
$lowStockItems = (int) ($pdo->query('SELECT COUNT(*) FROM items WHERE remaining BETWEEN 1 AND 5')->fetchColumn());

$initialCapital = (float) get_setting($pdo, 'capital', '0');
$currentCapital = (float) get_setting($pdo, 'current_capital', '0');
$basketAmount = $totalSales + $currentCapital;
$totalExpenses = (float) ($pdo->query('SELECT COALESCE(SUM(amount), 0) FROM capital_expenses')->fetchColumn());

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

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-2"><i class="bi bi-cash-coin me-1"></i>Total Sales</p><h4 class="mb-0"><?= e(format_currency($totalSales)) ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-2"><i class="bi bi-graph-up-arrow me-1"></i>Total Profit</p><h4 class="mb-0"><?= e(format_currency($totalProfit)) ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-2"><i class="bi bi-box-seam me-1"></i>Item Types</p><h4 class="mb-0"><?= number_format($totalItems) ?></h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-2"><i class="bi bi-stack me-1"></i>Remaining Stocks</p><h4 class="mb-0"><?= number_format($totalStocks) ?></h4></div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card app-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-bank me-2"></i>Inventory Cost on Hand</h5>
                <p class="display-6 mb-0"><?= e(format_currency($totalInventoryCost)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card app-card h-100">
            <div class="card-body">
                <h5 class="section-title"><i class="bi bi-basket3 me-2"></i>Basket Amount</h5>
                <p class="display-6 mb-0"><?= e(format_currency($basketAmount)) ?></p>
            </div>
        </div>
    </div>
</div>


<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-2"><i class="bi bi-wallet2 me-1"></i>Initial Capital</p><h4 class="mb-0"><?= e(format_currency($initialCapital)) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-2"><i class="bi bi-cash-stack me-1"></i>Current Capital</p><h4 class="mb-0"><?= e(format_currency($currentCapital)) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-2"><i class="bi bi-credit-card me-1"></i>Tracked Expenses</p><h4 class="mb-0"><?= e(format_currency($totalExpenses)) ?></h4></div></div></div>
</div>

<div class="card app-card">
    <div class="card-body">
        <h5 class="section-title"><i class="bi bi-bar-chart-line me-2"></i>Sales (Last 14 Days)</h5>
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
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.18)',
            tension: 0.25,
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
