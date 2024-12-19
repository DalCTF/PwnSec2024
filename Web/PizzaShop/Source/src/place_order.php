<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'pizzas.php';
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_ids = $_POST['pizza_id'];
    $quantities = $_POST['quantity'];
    $instructions = $_POST['instructions'];
    $total = 0;
    $selected_pizzas = "";
    $selected_quantities = "";
    $order_id = "";

    for($i = 0; $i < count($pizzas); $i++) {
        if($quantities[$i] > 0) {
            $total += $pizzas[$i]['price'] * $quantities[$i];
            $selected_pizzas .= "{$selected_ids[$i]},";
            $selected_quantities .= "{$quantities[$i]},";
        }
    }

    try {     
        $sql = "INSERT INTO orders (user_id, pizzas, quantities, total, instructions) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("issds", $_SESSION['user_id'], $selected_pizzas, $selected_quantities, $total, $instructions);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
    } catch (Exception $e) {
        echo "Something went wrong while placing your order, please try again later.";  
        exit;
    }

    echo "<h2>Order Placed</h2>";
    echo "<p>ID: ".$order_id."</p>";
    for($i = 0; $i < count($pizzas); $i++) {
        if($quantities[$i] > 0) {
            echo "<p>".$quantities[$i]."x ".$pizzas[$i]['name']." -> $". $pizzas[$i]['price']*$quantities[$i]."</p>";
        }
    }
    echo "<p>Special Instructions: " . htmlspecialchars($instructions) . "</p>";
    echo "<p>Total Price: $" . ($total) . "</p>";
    echo "<p> You can modify or cancel your order within the first 10 minutes. </p>";
    echo "<a href='place_order.php'> Go back</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Your Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
        }
        body > div {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        h2 {            
            color: #333;
        }
        a {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: unset;
        }
        a:hover {
            background-color: #218838;
        }
        a.logout {
            background-color: #dc3545;
        }
        a.logout:hover {
            background-color: #a42632;
        } 
        .pizza-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin: 20px;
        }
        .pizza {
            margin: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            width: 200px;
        }
        .pizza img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .order-form {
            margin-top: 20px;
            display: inline-block;
            text-align: left;
        }
        input[type="number"], textarea {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="number"] {
            text-align: center;
        }
        textarea {
            width: 100%;
        }
        .button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div>
        <a href="view_orders.php">View Orders</a>    
        <h2>Place New Order</h2>
        <a class="logout" href="logout.php">Logout</a>    
    </div>
    <form method="POST" action="place_order.php" class="order-form">
        <div class="pizza-container">
            <?php foreach ($pizzas as $pizza): ?>
                <div class="pizza">
                    <img src="<?= $pizza['image'] ?>" alt="<?= $pizza['name'] ?>">
                    <h3><?= $pizza['name'] ?></h3>
                    <p>$<?= $pizza['price'] ?></p>
                    <input type="hidden" name="pizza_id[]" value="<?= $pizza['id'] ?>" required>
                    <input type="number" name="quantity[]" min="0" value="0" required>
                </div>
            <?php endforeach; ?>
        </div>
        <label for="instructions">Special Instructions:</label>
        <textarea name="instructions" placeholder="Any special requests?" rows="4"></textarea>
        <button type="submit" class="button">Place Order</button>
    </form>
</body>
</html>
