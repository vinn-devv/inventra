<?php
session_start();

// If not logged in, send to login page
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php?redirect=../pages/account.php");
    exit;
}

include '../config/db.php';

$username = $_SESSION['user'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$user_id  = (int)$_SESSION['user_id'];

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_id = (int)$_POST['cancel_order_id'];

    // Make sure the order belongs to the logged in user
    $chk = $conn->prepare("SELECT id, product_id, quantity FROM orders WHERE id = ? AND customer_id = ?");
    $chk->bind_param("ii", $cancel_id, $user_id);
    $chk->execute();
    $chk_result = $chk->get_result();

    if ($chk_result->num_rows > 0) {
        $order = $chk_result->fetch_assoc();
        $chk->close();

        // Give the stock back
        $restore = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $restore->bind_param("ii", $order['quantity'], $order['product_id']);
        $restore->execute();
        $restore->close();

        // Delete the order
        $del = $conn->prepare("DELETE FROM orders WHERE id = ? AND customer_id = ?");
        $del->bind_param("ii", $cancel_id, $user_id);
        $del->execute();
        $del->close();

        $_SESSION['cancel_msg'] = "Order #$cancel_id has been cancelled.";
    } else {
        $chk->close();
    }

    header("Location: account.php");
    exit;
}

// Show cancel message if there is one
$cancel_success = '';
if (isset($_SESSION['cancel_msg'])) {
    $cancel_success = $_SESSION['cancel_msg'];
    unset($_SESSION['cancel_msg']);
}

// Get all orders for this user
$stmt = $conn->prepare("
    SELECT o.id AS order_id, o.quantity, o.total, o.created_at,
        p.name AS product_name, p.category, p.price
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Count totals for the stats section
$order_count = count($rows);
$total_items = 0;
$total_value = 0;

foreach ($rows as $r) {
    $total_items += $r['quantity'];
    $total_value += $r['total'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — My Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f2eb;
            --bg2: #efe8dc;
            --bg3: #e8ddd0;
            --bg4: #221b14;
            --text: #2c1f14;
            --text2: #6b5344;
            --text3: #a08878;
            --text4: #f0ebe2;
            --primary: #6f4e37;
            --primary-light: rgba(180, 180, 180, .1);
            --accent: #c8843a;
            --accent-dim: rgba(200, 132, 58, .12);
            --card: #faf6f0;
            --border: rgba(111, 78, 55, .12);
            --border2: rgba(111, 78, 55, .22);
            --shadow: rgba(44, 31, 20, .08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Jost', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 300 300' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9999;
        }

        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 56px;
            background: rgba(247, 242, 235, .88);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--border);
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text2);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color .2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .nav-logout {
            background: var(--primary) !important;
            color: #f7f2eb !important;
            padding: 9px 22px;
            border-radius: 8px;
            font-size: 13px !important;
            font-weight: 600 !important;
            transition: opacity .2s !important;
        }

        .nav-logout:hover {
            opacity: .8 !important;
        }

        .page-wrap {
            max-width: 1000px;
            margin: 0 auto;
            padding: 120px 40px 350px;
            animation: fadeUp .6s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 20px;
        }

        .profile-card {
            background: var(--card);
            border: 1px solid var(--border2);
            border-radius: 20px;
            padding: 32px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px var(--shadow);
        }

        .profile-info h2 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .profile-info p {
            font-size: 14px;
            color: var(--text2);
        }

        .badge {
            display: inline-block;
            margin-top: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: var(--accent-dim);
            color: var(--accent);
            padding: 3px 12px;
            border-radius: 50px;
            border: 1px solid rgba(200, 132, 58, .25);
        }

        .profile-stats {
            display: flex;
            gap: 150px;
            flex-shrink: 0;
            align-items: center;
        }

        .stat-card {
            text-align: center;
            min-width: 80px;
        }

        .stat-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text3);
            margin-bottom: 6px;
        }

        .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .section-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
        }

        .orders-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--bg2);
        }

        th {
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text2);
            border-bottom: 1px solid var(--border2);
        }

        td {
            padding: 16px 20px;
            font-size: 14px;
            color: var(--text);
            border-bottom: 1px solid var(--border);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background: var(--bg2);
        }

        .td-product {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .td-cat {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            background: var(--primary-light);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 50px;
            border: 1px solid rgba(111, 78, 55, .15);
        }

        .td-total {
            font-weight: 700;
            color: var(--primary);
        }

        .td-date {
            color: var(--text3);
            font-size: 13px;
        }

        .empty-orders {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-orders h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: var(--text);
            margin-bottom: 8px;
        }

        .empty-orders p {
            font-size: 14px;
            color: var(--text2);
            margin-bottom: 20px;
        }

        .btn-shop {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #f7f2eb;
            text-decoration: none;
            padding: 12px 26px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: opacity .2s;
            box-shadow: 0 6px 20px rgba(111, 78, 55, .3);
        }

        .btn-shop:hover {
            opacity: .85;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: transparent;
            color: #c0392b;
            border: 1px solid rgba(192, 57, 43, .35);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Jost', sans-serif;
            cursor: pointer;
            transition: background .2s, border-color .2s;
            letter-spacing: .3px;
        }

        .btn-cancel:hover {
            background: rgba(192, 57, 43, .08);
            border-color: rgba(192, 57, 43, .6);
        }

        .toast {
            position: fixed;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #2d6a4f;
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 6px 24px rgba(0, 0, 0, .18);
            opacity: 0;
            transition: opacity .3s, transform .3s;
            z-index: 5000;
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(34, 27, 20, .55);
            backdrop-filter: blur(4px);
            z-index: 4000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: var(--card);
            border: 1px solid var(--border2);
            border-radius: 20px;
            padding: 36px 40px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(44, 31, 20, .2);
            text-align: center;
            animation: modalIn .25s ease both;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(.94) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
        }

        .modal p {
            font-size: 14px;
            color: var(--text2);
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .modal-confirm {
            background: #c0392b;
            color: #fff;
            border: none;
            padding: 11px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Jost', sans-serif;
            cursor: pointer;
            transition: opacity .2s;
        }

        .modal-confirm:hover {
            opacity: .85;
        }

        .modal-dismiss {
            background: var(--bg2);
            color: var(--text2);
            border: 1px solid var(--border2);
            padding: 11px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Jost', sans-serif;
            cursor: pointer;
            transition: background .2s;
        }

        .modal-dismiss:hover {
            background: var(--bg3);
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 28px 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text3);
            font-size: 13px;
            background: var(--bg2);
        }

        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
        }

        #loading {
            display: none;
            position: fixed;
            inset: 0;
            background: var(--bg4);
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
            color: var(--text4);
        }

        #loading p {
            font-size: 14px;
            color: var(--text3);
        }

        .dots span {
            display: inline-block;
            color: var(--accent);
            animation: pulse 1.2s ease-in-out infinite;
        }

        .dots span:nth-child(2) {
            animation-delay: .2s;
        }

        .dots span:nth-child(3) {
            animation-delay: .4s;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: .3;
                transform: scale(.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        @media (max-width: 768px) {
            nav {
                padding: 15px 20px;
            }

            .page-wrap {
                padding: 100px 20px 60px;
            }

            .profile-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-stats {
                width: 100%;
            }

            .stat-card {
                flex: 1;
            }

            table {
                font-size: 13px;
            }

            th,
            td {
                padding: 12px 14px;
            }

            footer {
                padding: 20px;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Logout loading screen -->
    <div id="loading">
        <h2>Inventra</h2>
        <div class="dots"><span>●</span><span>●</span><span>●</span></div>
        <p>Logging out…</p>
    </div>

    <nav>
        <a href="home.php" class="nav-logo">Inventra</a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="webproducts.php">Products</a>
            <a href="about.php">About</a>
            <a href="account.php" class="active nav-logout">Account</a>
            <a href="../auth/logout.php" onclick="handleLogout(event, this.href)" style="color:var(--text3);font-size:13px;">Sign Out</a>
        </div>
    </nav>

    <div class="page-wrap">

        <div class="profile-title">My Account</div>

        <!-- Profile card -->
        <div class="profile-card">
            <div class="profile-info">
                <h2>
                    <?= htmlspecialchars($name) ?>
                    <span class="badge">Customer</span>
                </h2>
                <p>@<?= htmlspecialchars($username) ?>.inventra</p>
            </div>
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-label">Orders</div>
                    <div class="stat-value"><?= $order_count ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Items</div>
                    <div class="stat-value"><?= $total_items ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Value</div>
                    <div class="stat-value">₱<?= number_format($total_value, 0) ?></div>
                </div>
            </div>
        </div>

        <div class="section-header">
            <div class="section-title">My Orders</div>
        </div>

        <!-- Orders table -->
        <div class="orders-card">
            <?php if ($order_count > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $row_num = 1; ?>
                        <?php foreach ($rows as $o): ?>
                            <tr>
                                <td style="color:var(--text3)"><?= $row_num++ ?></td>
                                <td class="td-product"><?= htmlspecialchars($o['product_name']) ?></td>
                                <td><span class="td-cat"><?= ucfirst(htmlspecialchars($o['category'])) ?></span></td>
                                <td><?= (int)$o['quantity'] ?></td>
                                <td>₱<?= number_format($o['price'], 2) ?></td>
                                <td class="td-total">₱<?= number_format($o['total'], 2) ?></td>
                                <td class="td-date"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <button class="btn-cancel" onclick="confirmCancel(<?= $o['order_id'] ?>, '<?= htmlspecialchars(addslashes($o['product_name'])) ?>')">
                                        Cancel
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-orders">
                    <h3>No orders yet</h3>
                    <p>You haven't purchased anything. Head over to the store to get started!</p>
                    <a href="webproducts.php" class="btn-shop">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <footer>
        <div class="footer-logo">Inventra</div>
        <div>Stock Management System</div>
    </footer>

    <!-- Success toast message -->
    <?php if ($cancel_success): ?>
        <div class="toast show" id="toast"><?= htmlspecialchars($cancel_success) ?></div>
    <?php endif; ?>

    <!-- Cancel confirmation modal -->
    <div class="modal-overlay" id="cancelModal">
        <div class="modal">
            <h3>Cancel this order?</h3>
            <img style="border-radius:7px;height:150px;width:150px;" src="../assets/images/sadhamstergirl.gif" alt="sad hamster">
            <p id="modalText"></p>
            <div class="modal-actions">
                <button class="modal-dismiss" onclick="closeModal()">Keep Order</button>
                <form method="POST" id="cancelForm" style="display:inline;">
                    <input type="hidden" name="cancel_order_id" id="cancelOrderId">
                    <button type="submit" class="modal-confirm">Yes, Cancel</button>
                </form>
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

        function confirmCancel(orderId, productName) {
            document.getElementById('cancelOrderId').value = orderId;
            document.getElementById('modalText').textContent =
                'Are you sure you want to remove your order for "' + productName + '"? This cannot be undone.';
            document.getElementById('cancelModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('cancelModal').classList.remove('show');
        }

        // Close modal when clicking the dark backdrop
        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Auto hide the toast after 3.5 seconds
        var toast = document.getElementById('toast');
        if (toast) {
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3500);
        }
    </script>

</body>

</html>