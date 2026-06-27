<?php
$conn = new mysqli("localhost", "root", "", "stock_db", 3307);

if ($conn->connect_error) {
    die("DB CONNECTION FAILED: " . $conn->connect_error);
}
