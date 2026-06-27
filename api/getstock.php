<?php
include '../auth/auth.php';
include '../config/db.php';

$res  = $conn->query("SELECT id, quantity FROM products");
$data = array();

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
