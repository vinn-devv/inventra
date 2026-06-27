<?php
include '../auth/auth.php';
include '../config/db.php';

$id   = (int) $_POST['id'];
$type = $_POST['type'];

// get current stock
$stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    echo json_encode(array("error" => "Product not found"));
    exit;
}

$current = $data['quantity'];

// add or subtract 1 from stock
if ($type == "add") {
    $new = $current + 1;
} else {
    $new = $current - 1;
    if ($new < 0) {
        $new = 0;
    }
}

// save the new stock
$stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
$stmt->bind_param("ii", $new, $id);
$stmt->execute();
$stmt->close();

echo $new;
