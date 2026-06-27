<?php
include '../auth/auth.php';
include '../config/db.php';

// make csrf token if needed
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// get the product id from url or form
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} else {
    $id = 0;
}

if (!$id) {
    header("Location: ../admin/products.php");
    exit();
}

// get the product from database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: ../admin/products.php");
    exit();
}

// handle the form submit
if (isset($_POST['update'])) {

    // check csrf token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid request. Please go back and try again.");
    }

    $name     = $_POST['name'];
    $category = $_POST['category'];
    $quantity = (int)$_POST['quantity'];
    $price    = (float)$_POST['price'];

    // check if a new image was uploaded
    if (!empty($_FILES['image']['name'])) {

        // check that the file is actually an image
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $file_type     = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            die("Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.");
        }

        $folder   = dirname(__DIR__) . "/assets/inventra_uploads/";
        $filename = time() . "_" . basename($_FILES['image']['name']);
        $img_path = "/stock_db/assets/inventra_uploads/" . $filename;
        $abs_path = $folder . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $abs_path);
    } else {
        // keep the old image
        $img_path = $data['image'];
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, category=?, quantity=?, price=?, image=? WHERE id=?");
    $stmt->bind_param("ssidsi", $name, $category, $quantity, $price, $img_path, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../admin/products.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Product</title>
    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #15110e;
            color: #f5f0e6;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .card {
            width: 440px;
            background: #2a1e18;
            padding: 30px;
            border-radius: 15px;
            border: 1px solid #3b2a21;
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9e8672;
            margin-bottom: 5px;
            margin-top: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #4e342e;
            background: #1f1612;
            color: #f5f0e6;
            font-size: 14px;
        }

        select option {
            background: #1f1612;
        }

        .current-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 8px;
            border: 1px solid #3b2a21;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #8d6e63;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 15px;
            font-weight: 600;
        }

        button:hover {
            background: #c8843a;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #b0a59a;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h2>Edit Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Product Name</label>
                <input name="name" value="<?php echo htmlspecialchars($data['name']); ?>" required>

                <label>Category</label>
                <select name="category">
                    <?php
                    $categories = array('electronics', 'clothing', 'sports', 'home', 'accessories');
                    foreach ($categories as $cat) {
                        $selected = $data['category'] === $cat ? 'selected' : '';
                        echo "<option value='$cat' $selected>" . ucfirst($cat) . "</option>";
                    }
                    ?>
                </select>

                <label>Quantity</label>
                <input name="quantity" type="number" min="0" value="<?php echo $data['quantity']; ?>">

                <label>Price (₱)</label>
                <input name="price" type="number" step="0.01" min="0" value="<?php echo $data['price']; ?>">

                <label>Product Image</label>
                <?php if (!empty($data['image'])) { ?>
                    <img src="<?php echo htmlspecialchars($data['image']); ?>" class="current-img" alt="Current image">
                <?php } ?>
                <input type="file" name="image" accept="image/*" style="margin-top:8px">

                <button name="update">Update Product</button>
            </form>
            <a class="back" href="../admin/products.php">Back to Products</a>
        </div>
    </div>
</body>

</html>