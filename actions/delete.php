<?php
include '../auth/auth.php';
include '../config/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // get the image filename first so we can delete the file
    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // delete the image file if it exists
        if (!empty($row['image'])) {
            $filePath = dirname(__DIR__) . "/assets/inventra_uploads/" . basename($row['image']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // delete the product from the database
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: ../admin/products.php");
exit();
