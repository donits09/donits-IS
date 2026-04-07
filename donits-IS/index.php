<?php include 'config/db.php'; ?>
<?php include 'includes/header.php'; ?>

<h3>Dashboard</h3>

<?php
$totalSales = $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn();
$totalProfit = $pdo->query("SELECT SUM(interest) FROM sales")->fetchColumn();
?>

<p>Total Sales: <?= number_format($totalSales ?? 0, 2) ?></p>
<p>Total Profit: <?= number_format($totalProfit ?? 0, 2) ?></p>

<?php include 'includes/footer.php'; ?>