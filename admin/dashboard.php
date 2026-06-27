<?php
include '../auth/auth.php';
include '../config/db.php';

// handle search
$search = "";
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $_GET['search'];
    $search_param = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM products");
}

// get dashboard stats
$total = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc();
$stock = $conn->query("SELECT SUM(quantity) as total_stock FROM products")->fetch_assoc();
$low   = $conn->query("SELECT COUNT(*) as low FROM products WHERE quantity <= 20")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — Dashboard</title>
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

        * {
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
            animation: fadeUp 0.4s ease both;
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

        .page-tag {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text3);
            margin-bottom: 6px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 28px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 24px;
        }

        .stat-card.warn {
            border-color: rgba(192, 57, 43, 0.35);
        }

        .stat-card .label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text3);
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 900;
            color: var(--text);
            margin-bottom: 6px;
        }

        .stat-card .sub {
            font-size: 13px;
            color: var(--text2);
        }

        .alert {
            background: rgba(192, 57, 43, 0.1);
            border: 1px solid rgba(192, 57, 43, 0.3);
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 14px;
            color: #e07060;
            margin-bottom: 24px;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-wrap {
            flex: 1;
            position: relative;
        }

        .search-wrap input {
            width: 100%;
            background: var(--card);
            border: 1px solid var(--border2);
            border-radius: 10px;
            padding: 11px 14px 11px 38px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s;
        }

        .search-wrap input:focus {
            border-color: var(--accent);
        }

        .search-wrap input::placeholder {
            color: var(--text3);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text3);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 20px;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent);
        }

        .btn-search {
            background: var(--card2);
            color: var(--text2);
            border: 1px solid var(--border2);
        }

        .btn-search:hover {
            background: var(--card);
            color: var(--text);
        }

        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: var(--card2);
            padding: 13px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text3);
            border-bottom: 1px solid var(--border);
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(200, 132, 58, 0.04);
        }

        td {
            padding: 14px 16px;
            font-size: 14px;
            color: var(--text2);
            vertical-align: middle;
        }

        td:first-child {
            color: var(--text3);
            font-size: 12px;
        }

        td.name {
            color: var(--text);
            font-weight: 500;
        }

        .qty-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }

        .qty-pill.ok {
            background: rgba(90, 138, 90, 0.15);
            color: #7dc07d;
        }

        .qty-pill.low {
            background: rgba(192, 57, 43, 0.15);
            color: #e07060;
        }

        .price {
            color: var(--accent);
            font-weight: 600;
        }

        .empty td {
            text-align: center;
            padding: 60px;
            color: var(--text3);
        }
    </style>
</head>

<body>

    <!-- Loading Overlay (logout) -->
    <div id="loading">
        <h2>Inventra</h2>
        <div class="dots">
            <span>●</span><span>●</span><span>●</span>
        </div>
        <p>Logging out…</p>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">Inventra</div>
        <nav>
            <div class="sidebar-section">Main</div>
            <a href="dashboard.php" class="active">Dashboard</a>
            <div class="sidebar-section">Manage</div>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="customers.php">Customers</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" onclick="handleLogout(event, this.href)">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main">

        <!-- Topbar -->
        <div class="topbar">
            <h2>Dashboard</h2>
            <div class="admin-badge">Admin</div>
        </div>

        <!-- Page Body -->
        <div class="page-body">

            <div class="page-tag">Overview</div>
            <h1>Stock Dashboard</h1>

            <!-- Stat Cards -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="label">Total Products</div>
                    <div class="value"><?php echo $total['count']; ?></div>
                    <div class="sub">Across all categories</div>
                </div>
                <div class="stat-card">
                    <div class="label">Total Stock</div>
                    <div class="value"><?php echo number_format($stock['total_stock']); ?></div>
                    <div class="sub">Units available</div>
                </div>
                <div class="stat-card <?php echo $low['low'] > 0 ? 'warn' : ''; ?>">
                    <div class="label">Low Stock</div>
                    <div class="value"><?php echo $low['low']; ?></div>
                    <div class="sub"><?php echo $low['low'] > 0 ? 'Needs attention' : 'All good'; ?></div>
                </div>
            </div>

            <!-- Low stock alert -->
            <?php if ($low['low'] > 0) { ?>
                <div class="alert">
                    <?php echo $low['low']; ?> product(s) are running low on stock.
                </div>
            <?php } ?>

            <!-- Search + Add -->
            <form method="GET">
                <div class="toolbar">
                    <div class="search-wrap">
                        <span class="search-icon">⌕</span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products…">
                    </div>
                    <button type="submit" class="btn btn-search">Search</button>
                    <a href="../admin/products.php" class="btn btn-primary">Add Product</a>
                </div>
            </form>

            <!-- Products Table -->
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0) { ?>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td class="name"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>
                                        <span class="qty-pill <?php echo $row['quantity'] <= 20 ? 'low' : 'ok'; ?>">
                                            <?php echo $row['quantity']; ?>
                                        </span>
                                    </td>
                                    <td class="price">₱<?php echo number_format($row['price'], 2); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr class="empty">
                                <td colspan="5">No products found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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