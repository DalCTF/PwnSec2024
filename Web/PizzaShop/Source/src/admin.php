<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
    </style>
</head>
<body>
    <h2>Admin Dashboard</h2>
    <?php
    include 'flag.php';
    echo $flag;
    ?>
</body>
</html>
