<?php include '../../config/db.php'; ?>
<?php include '../../includes/header.php'; ?>

<h3>Sales</h3>

<a href="add.php">Add Sale</a>

<table border="1">
<tr>
    <th>Item</th>
    <th>Qty</th>
    <th>Total</th>
    <th>Profit</th>
</tr>

<?php
$sql = "SELECT s.*, i.item_name 
        FROM sales s
        JOIN items i ON s.item_id = i.id";

$sales = $pdo->query($sql);

foreach ($sales as $row):
?>
<tr>
    <td><?= $row['item_name'] ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= $row['total_amount'] ?></td>
    <td><?= $row['interest'] ?></td>
</tr>
<?php endforeach; ?>

</table>

<?php include '../../includes/footer.php'; ?>