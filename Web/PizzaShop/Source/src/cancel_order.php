<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'database.php';
include 'pizzas.php';

$sql = "SELECT * FROM orders WHERE user_id = ? AND order_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $_SESSION['user_id'], $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "This order doesn't belong to your account";
    echo "<a href='view_orders.php'> Go back</a>";
    exit;
}

$order = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_GET['id'];

    try {     
        $sql = "DELETE FROM orders WHERE order_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        echo "Something went wrong while canceling your order, please try again later.";   
        exit;
    }

    echo "<h2>Order Canceled Successfully</h2>";
    echo "<a href='view_orders.php'> Go back</a>";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f8f9fa;
        }
        h2 {
            margin-top: 30px;
            color: #333;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 50%;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #dc3545;
            color: white;
        }
        .button {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: none;
        }
        .button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h2>Confirm Order Cancelation</h2>
    <p>Are you sure you want to cancel this order?</p>
    <table>
        <tr>
            <th>Order ID</th>
            <td><?= $order['order_id'] ?></td>
        </tr>
        <tr>
            <th>Pizzas</th>
            <td><?php 
                $ids = explode(",", $order['pizzas']); 
                $quantities = explode(",", $order['quantities']);
                for($i = 1; $i <= count($pizzas); $i++) {
                    $index = array_search($i, $ids);
                    if($index != "") {
                        echo "<p>".$quantities[$index]."x ".$pizzas[$i-1]['name']." -> $". $pizzas[$i-1]['price']*(int)$quantities[$index]."</p>";
                    }
                }
            ?></td>
        </tr>
        <tr>
            <th>Total</th>
            <td><?= '$'.$order['total'] ?></td>
        </tr>
        <tr>
            <th>Special Instructions</th>
            <td><?= htmlspecialchars($order['instructions']) ?></td>
        </tr>
        <tr>
            <th>Order Date</th>
            <td><?= $order['order_date'] ?></td>
        </tr>
    </table>
    <form method="POST" action="">
        <button type="submit" class="button">Confirm Cancelation</button>
    </form>
    <p><a href="view_orders.php">Cancel</a></p>
</body>
</html>
