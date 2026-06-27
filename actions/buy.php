<?php
session_start();
include '../config/db.php';

// make a csrf token if there isn't one yet
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// check csrf token
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header("Location: ../pages/webproducts.php?error=invalid_request");
    exit;
}

$product_id  = (int) $_POST['product_id'];
$qty         = (int) $_POST['quantity'];
$customer_id = (int) $_SESSION['user_id'];

// make sure quantity is valid
if ($qty <= 0) {
    header("Location: ../pages/webproducts.php?error=invalid_qty");
    exit;
}

// get the product from database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result  = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// check if product exists
if (!$product) {
    header("Location: ../pages/webproducts.php?error=Product not found");
    exit;
}

// check if there is enough stock
if ($product['quantity'] < $qty) {
    header("Location: ../pages/webproducts.php?error=Not enough stock");
    exit;
}

// calculate total price
$total = $product['price'] * $qty;

// save the order
$stmt = $conn->prepare("INSERT INTO orders (product_id, customer_id, quantity, total, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiid", $product_id, $customer_id, $qty, $total);
$stmt->execute();
$stmt->close();

// subtract the stock
$stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
$stmt->bind_param("ii", $qty, $product_id);
$stmt->execute();
$stmt->close();

$product_name = urlencode($product['name']);
header("Location: ../pages/webproducts.php?purchased=1&product_name=$product_name&qty=$qty");
exit;
