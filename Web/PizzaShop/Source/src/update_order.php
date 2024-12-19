<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'pizzas.php';
include 'database.php';

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
print_r($order);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_ids = $_POST['pizzas'];
    $quantities = $_POST['quantities'];
    $instructions = $_POST['instructions'];
    $total = 0;
    $selected_pizzas = "";
    $selected_quantities = "";

    for($i = 0; $i < count($pizzas); $i++) {
        if($quantities[$i] > 0) {
            $total += $pizzas[$i]['price'] * $quantities[$i];
            $selected_pizzas .= "{$selected_ids[$i]},";
            $selected_quantities .= "{$quantities[$i]},";
        }
    }

    foreach($_POST as $key => $value) {
        $new_order[$key] = $value;
    }

    $new_order['total'] = $total;
    $new_order['pizzas'] = $selected_pizzas;
    $new_order['quantities'] = $selected_quantities;
    $new_order['instructions'] = $instructions;


    $sql = "UPDATE orders SET order_date = CURRENT_TIMESTAMP";
    $updated_data = array();
    $updated_data_types = "";

    foreach ($new_order as $key => $value) {
        if($order[$key] != $new_order[$key]) {
            $sql .= ", $key = ?";
            array_push($updated_data, $new_order[$key]);
            $updated_data_types .=  gettype($new_order[$key]) == 'double' ? 'd': 's'; 
        }
    }

    $sql .= " WHERE order_id = ?";
    array_push($updated_data, $order['order_id']);
    $updated_data_types .= 'i';
    echo $sql;

    try {     
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($updated_data_types, ...$updated_data);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        echo "Something went wrong while updating your order, please try again later.";   
        exit;
    }

    echo "<h2>Order Updated Successfully</h2>";
    echo "<p>ID: ".$order['order_id']."</p>";
    for($i = 0; $i < count($pizzas); $i++) {
        if($quantities[$i] > 0) {
            echo "<p>".$quantities[$i]."x ".$pizzas[$i]['name']." -> $". $pizzas[$i]['price']*$quantities[$i]."</p>";
        }
    }
    echo "<p>Special Instructions: " . htmlspecialchars($instructions) . "</p>";
    echo "<p>Total Price: $" . ($total) . "</p>";
    echo "<a href='view_orders.php'> Go back</a>";
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
        h2 {
            margin-top: 30px;
            color: #333;
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
        .a, .button {
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: unset;
            font-size: medium;
        }
        .button {
            background-color: #007bff;
        }
        .a {
            background-color: #dc3545;
        }
        .a:hover {
            background-color: #a42632;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Update Order</h2>
    <form method="POST" action="update_order.php?id=<?= $_GET['id'] ?>" class="order-form">
        <div class="pizza-container">
            <?php foreach ($pizzas as $pizza): ?>
                <div class="pizza">
                    <img src="<?= $pizza['image'] ?>" alt="<?= $pizza['name'] ?>">
                    <h3><?= $pizza['name'] ?></h3>
                    <p>$<?= $pizza['price'] ?></p>
                    <input type="hidden" name="pizzas[]" value="<?= $pizza['id'] ?>" required>
                    <?php 
                            $ids = explode(",", $order['pizzas']); 
                            $quantities = explode(",", $order['quantities']);
                            $index = array_search($pizza['id'], $ids);
                            if($index != "") {
                                $order_quantitiy = (int)$quantities[$index];
                            } else {
                                $order_quantitiy = 0;
                            }
                            echo "<input type='number' name='quantities[]' min='0' value='{$order_quantitiy}' required>";
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        <label for="instructions">Special Instructions:</label>
        <textarea name="instructions" placeholder="Any special requests?" rows="4"><?= $order['instructions'] ?></textarea>
        <button type="submit" class="button">Update Order</button>
        <a href="view_orders.php" class="a">Go back</a>
    </form>
</body>
</html>
