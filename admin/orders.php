<?php
include '../auth/auth.php';
include '../config/db.php';

// make csrf token if needed
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// handle cancel order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid request.");
    }

    $id = (int)$_POST['delete'];

    $sel = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $sel->bind_param("i", $id);
    $sel->execute();
    $order = $sel->get_result()->fetch_assoc();
    $sel->close();

    if ($order) {
        // restore the stock
        $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $stmt->bind_param("ii", $order['quantity'], $order['product_id']);
        $stmt->execute();
        $stmt->close();

        // delete the order
        $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        header("Location: orders.php?msg=cancelled");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — Orders</title>
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
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--card2);
            border: 1px solid var(--border);
            padding: 7px 14px;
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

        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .table-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header-title {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: var(--card2);
            padding: 12px 14px;
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
            padding: 12px 14px;
            font-size: 13px;
            color: var(--text2);
            vertical-align: middle;
        }

        td:first-child {
            color: var(--text3);
            font-size: 12px;
        }

        .empty-msg {
            text-align: center;
            padding: 60px;
            color: var(--text3);
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 7px;
            background: rgba(192, 57, 43, 0.15);
            color: #e07060;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid rgba(192, 57, 43, 0.2);
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s, border-color 0.2s;
        }

        .btn-cancel:hover {
            background: rgba(192, 57, 43, 0.3);
            border-color: rgba(192, 57, 43, 0.4);
        }

        .cancel-form {
            display: inline;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">Inventra</div>
        <nav>
            <div class="sidebar-section">Main</div>
            <a href="dashboard.php">Dashboard</a>
            <div class="sidebar-section">Manage</div>
            <a href="products.php">Products</a>
            <a href="orders.php" class="active">Orders</a>
            <a href="customers.php">Customers</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" onclick="handleLogout(event, this.href)">Logout</a>
        </div>
    </aside>

    <!-- Loading overlay -->
    <div id="loading">
        <h2>Inventra</h2>
        <div class="dots"><span>●</span><span>●</span><span>●</span></div>
        <p>Logging out…</p>
    </div>

    <div class="main">

        <div class="topbar">
            <h2>Orders</h2>
            <div class="admin-badge">Admin</div>
        </div>

        <div class="page-body">
            <div class="page-header">
                <div class="page-header-tag">Management</div>
                <h1>Orders</h1>
            </div>

            <!-- Orders table -->
            <div class="table-wrap">
                <div class="table-header">
                    <div class="table-header-title">Order List</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $data = $conn->query("
                            SELECT o.id, p.name AS product, u.username AS customer,
                                u.address, u.contact_no AS contact,
                                o.quantity, o.total,
                                (p.price * o.quantity) AS computed_total,
                                o.created_at, o.product_id
                            FROM orders o
                            LEFT JOIN products p ON o.product_id = p.id
                            LEFT JOIN users u ON o.customer_id = u.id
                            ORDER BY o.id DESC
                        ");

                        if ($data && $data->num_rows > 0) {
                            $row_num = 1;
                            while ($row = $data->fetch_assoc()) {
                        ?>
                                <tr>
                                    <td>#<?php echo $row_num++; ?></td>
                                    <td style="color:var(--text);font-weight:500"><?php echo htmlspecialchars($row['product']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['address'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact'] ?? '—'); ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td style="color:var(--accent);font-weight:600">
                                        ₱<?php echo number_format($row['total'] ?? $row['computed_total'], 2); ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" action="orders.php" class="cancel-form"
                                            onsubmit="return confirm('Cancel this order? Stock will be restored.')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn-cancel">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="9" class="empty-msg">No orders yet.</td>
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