<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'database.php';
include 'pizzas.php';

$sql = "SELECT order_id, pizzas, quantities, order_date, total, instructions
        FROM orders
        WHERE user_id = {$_SESSION['user_id']}
        ORDER BY order_date DESC";

$result = $mysqli->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding-bottom: 30px;
        }
        h2 {
            margin-top: 30px;
            color: #333;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        td:nth-child(6) {
            text-align: center;
        }
        a {
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            background-color: #28a745;
            text-decoration: unset;
            font-size: medium;
        }
        a:hover {
            background-color: #218838;
        }
        a.red {
           background-color: #dc3545;
           margin-right: 10px;
        }
        a.red:hover {
            background-color: #a42632;
        } 

    </style>
</head>
<body>
    <h2>All Past Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Pizzas</th>
                <th>Total</th>
                <th>Special Instructions</th>
                <th>Order Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['order_id'] ?></td>
                        <td><?php 
                            $ids = explode(",", $row['pizzas']); 
                            $quantities = explode(",", $row['quantities']);
                            for($i = 1; $i <= count($pizzas); $i++) {
                                $index = array_search($i, $ids);
                                if($index != "") {
                                    echo "<p>".$quantities[$index]."x ".$pizzas[$i-1]['name']." -> $". $pizzas[$i-1]['price']*(int)$quantities[$index]."</p>";
                                }
                            }
                        ?></td>
                        <td><?= "$".$row['total'] ?></td> 
                        <td><?= htmlspecialchars($row['instructions']) ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td>
                            <a class="red" href="cancel_order.php?id=<?= $row['order_id'] ?>"> Cancel </a>
                            <a href="update_order.php?id=<?= $row['order_id'] ?>"> Edit </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href='place_order.php'> Go back</a> 
</body>
</html>