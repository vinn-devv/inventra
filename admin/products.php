<?php
include '../auth/auth.php';
include '../config/db.php';

// make csrf token if needed
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f0c09;
            --sidebar: #160f09;
            --card: #1e1510;
            --card2: #261a12;
            --border: rgba(200, 132, 58, 0.14);
            --border2: rgba(200, 132, 58, 0.25);
            --text: #f0ebe2;
            --text2: #9e8672;
            --text3: #5a4535;
            --primary: #6f4e37;
            --accent: #c8843a;
            --danger: #c0392b;
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 50;
        }

        .sidebar-logo {
            padding: 28px 24px 24px;
            border-bottom: 1px solid var(--border);
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 900;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-section {
            padding: 20px 16px 8px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text3);
        }

        .sidebar nav {
            flex: 1;
            padding: 8px 0;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text2);
            padding: 11px 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 10px;
            margin: 2px 10px;
            transition: all 0.2s;
            position: relative;
        }

        .sidebar a:hover {
            background: rgba(200, 132, 58, 0.08);
            color: var(--text);
        }

        .sidebar a.active {
            background: rgba(200, 132, 58, 0.12);
            color: var(--accent);
        }

        .sidebar a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
            margin-left: -10px;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text3);
            padding: 10px 12px;
            text-decoration: none;
            font-size: 13px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .sidebar-footer a:hover {
            color: var(--danger);
            background: rgba(192, 57, 43, 0.08);
        }

        #loading {
            display: none;
            position: fixed;
            inset: 0;
            background: var(--bg);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }

        #loading.show {
            display: flex;
        }

        #loading h2 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--text);
        }

        #loading p {
            font-size: 14px;
            color: var(--text2);
        }

        .dots span {
            display: inline-block;
            color: var(--accent);
            animation: pulse 1.2s ease-in-out infinite;
        }

        .dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(0.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        .main {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(15, 12, 9, 0.9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar h2 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
        }

        .admin-badge {
            background: var(--card2);
            border: 1px solid var(--border);
            padding: 7px 16px;
            border-radius: 50px;
            font-size: 13px;
            color: var(--text2);
        }

        .page-body {
            padding: 32px;
            flex: 1;
            animation: fadeUp 0.5s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-header-tag {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 6px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.8px;
            color: var(--text);
        }

        .layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
            align-items: start;
        }

        .add-form-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            position: sticky;
            top: 88px;
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .input-group {
            margin-bottom: 12px;
        }

        .input-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--text3);
            margin-bottom: 6px;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 11px 14px;
            background: var(--card2);
            border: 1.5px solid var(--border);
            border-radius: 9px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border 0.2s, box-shadow 0.2s;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(200, 132, 58, 0.1);
        }

        .input-group input::placeholder {
            color: var(--text3);
        }

        .input-group select option {
            background: var(--card2);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-submit:hover {
            background: var(--accent);
            transform: translateY(-1px);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
        }

        .product-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.2s;
            animation: fadeUp 0.4s ease both;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.3);
            border-color: var(--border2);
        }

        .product-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: var(--card2);
            display: block;
        }

        .product-body {
            padding: 16px;
        }

        .product-name {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 3px;
        }

        .product-category {
            font-size: 11px;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .product-price {
            font-size: 17px;
            font-weight: 700;
            color: var(--accent);
        }

        .product-qty {
            font-size: 12px;
            color: var(--text2);
        }

        .product-actions {
            display: flex;
            gap: 8px;
        }

        .pa-btn {
            flex: 1;
            padding: 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
        }

        .pa-edit {
            background: rgba(111, 78, 55, 0.2);
            color: #c8a27c;
        }

        .pa-delete {
            background: rgba(192, 57, 43, 0.15);
            color: #e07060;
        }

        .pa-edit:hover {
            background: rgba(111, 78, 55, 0.35);
        }

        .pa-delete:hover {
            background: rgba(192, 57, 43, 0.3);
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-logo">Inventra</div>
        <nav>
            <div class="sidebar-section">Main</div>
            <a href="dashboard.php">Dashboard</a>
            <div class="sidebar-section">Manage</div>
            <a href="products.php" class="active">Products</a>
            <a href="orders.php">Orders</a>
            <a href="customers.php">Customers</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" onclick="handleLogout(event, this.href)">Logout</a>
        </div>
    </aside>

    <!-- Loading Overlay (logout) -->
    <div id="loading">
        <h2>Inventra</h2>
        <div class="dots">
            <span>●</span><span>●</span><span>●</span>
        </div>
        <p>Logging out…</p>
    </div>

    <div class="main">

        <div class="topbar">
            <h2>Products</h2>
            <div class="admin-badge">Admin</div>
        </div>

        <div class="page-body">

            <div class="page-header">
                <div class="page-header-tag">Inventory</div>
                <h1>Manage Products</h1>
            </div>

            <div class="layout">

                <!-- Add Form -->
                <div class="add-form-wrap">
                    <div class="form-title">+ Add New Product</div>
                    <form method="POST" action="../actions/save_product.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="input-group">
                            <label>Product Name</label>
                            <input name="name" placeholder="e.g. Hoodie" required>
                        </div>
                        <div class="input-group">
                            <select name="category">
                                <option value="electronics">Electronics</option>
                                <option value="clothing">Clothing</option>
                                <option value="sports">Sports</option>
                                <option value="home">Home</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Quantity</label>
                            <input name="quantity" type="number" placeholder="0" min="0">
                        </div>
                        <div class="input-group">
                            <label>Price (₱)</label>
                            <input name="price" type="number" placeholder="0.00" step="0.01">
                        </div>
                        <div class="input-group">
                            <label>Product Image</label>
                            <input type="file" name="image" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn-submit">Add Product</button>
                    </form>
                </div>

                <!-- Products grid -->
                <div class="products-grid">
                    <?php
                    $r = $conn->query("SELECT * FROM products");
                    $d = 0;
                    while ($p = $r->fetch_assoc()) {
                    ?>
                        <div class="product-card" style="animation-delay:<?php echo $d * 0.06; ?>s">
                            <img class="product-img"
                                src="<?php echo !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://placehold.co/400x300/1e1510/9e8672?text=No+Image'; ?>"
                                alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <div class="product-body">
                                <div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="product-category"><?php echo htmlspecialchars($p['category']); ?></div>
                                <div class="product-meta">
                                    <div class="product-price">₱<?php echo number_format($p['price'], 2); ?></div>
                                    <div class="product-qty">Qty: <strong style="color:var(--text)"><?php echo $p['quantity']; ?></strong></div>
                                </div>
                                <div class="product-actions">
                                    <a href="../actions/edit.php?id=<?php echo $p['id']; ?>" class="pa-btn pa-edit">Edit</a>
                                    <a href="../actions/delete.php?id=<?php echo $p['id']; ?>" class="pa-btn pa-delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php
                        $d++;
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>

    <script>
        function handleLogout(e, href) {
            e.preventDefault();
            document.getElementById('loading').classList.add('show');
            setTimeout(function() {
                window.location.href = href;
            }, 700);
        }
    </script>
</body>

</html>