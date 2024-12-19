<?php

$mysqli = new mysqli("db", "user", "password", "pizza_shop");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

?>