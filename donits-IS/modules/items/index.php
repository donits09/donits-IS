<?php include '../../config/db.php'; ?>
<?php include '../../includes/header.php'; ?>

<h3>Items</h3>

<a href="add.php">Add Item</a>

<table border="1">
<tr>
    <th>Name</th>
    <th>Price</th>
    <th>Sale Price</th>
    <th>Stock</th>
    <th>Remaining</th>
</tr>

<?php
$items = $pdo->query("SELECT * FROM items");

foreach ($items as $row):
?>
<tr>
    <td><?= $row['item_name'] ?></td>
    <td><?= $row['price'] ?></td>
    <td><?= $row['sale_price'] ?></td>
    <td><?= $row['total_items'] ?></td>
    <td><?= $row['remaining'] ?></td>
</tr>
<?php endforeach; ?>

</table>

<?php include '../../includes/footer.php'; ?>