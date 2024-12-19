<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: place_order.php");
    exit;
}

include 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($_POST['register'])) {
        try {
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $stmt->close();
            header("Location: login.php");
            $error = "User created";
        } catch (Exception $e) {
            $error = "Username already exists";
        }      
    } else if (isset($_POST['login'])) {
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $id = $result->fetch_assoc()['id'];
            $_SESSION['user_id'] = $id;
            if ($username == 'admin') {
                $_SESSION['user'] = 'admin';
                header("Location: admin.php");
            } else {
                $_SESSION['user'] = $username;
                header("Location: place_order.php");
            }
        } else {
            $error = "Invalid username or password";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f8f9fa;
        }
        h2 {
            margin-top: 50px;
            color: #333;
        }
        form {
            display: inline-block;
            text-align: left;
            margin-top: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h2>Login/Register</h2>
    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit" name="login" class="button">Login</button>
        <button type="submit" name="register" class="button">Register</button>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
    </form>
</body>
</html>
