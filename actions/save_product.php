<?php
include '../auth/auth.php';
include '../config/db.php';

// make csrf token if needed
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['name'])) {

    // check csrf token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid request.");
    }

    $name     = $_POST['name'];
    $category = $_POST['category'];
    $quantity = (int) $_POST['quantity'];
    $price    = (float) $_POST['price'];

    $folder = dirname(__DIR__) . "/assets/inventra_uploads/";

    // create the folder if it doesn't exist
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    // check that the uploaded file is actually an image
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    $file_type     = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        die("Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.");
    }

    $filename = time() . "_" . basename($_FILES['image']['name']);
    $img_path = "/stock_db/assets/inventra_uploads/" . $filename;
    $abs_path = $folder . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $abs_path)) {

        // insert product into database
        $stmt = $conn->prepare("INSERT INTO products (name, category, quantity, price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssids", $name, $category, $quantity, $price, $img_path);
        $stmt->execute();
        $stmt->close();

        header("Location: ../admin/products.php");
        exit();
    } else {
        echo "Upload failed. Check that the folder exists: " . $folder;
    }
}
