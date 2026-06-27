<?php
include '../auth/auth.php';
include '../config/db.php';

// get all customers from database
$res = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");
$customers = array();
while ($r = $res->fetch_assoc()) {
    $customers[] = $r;
}
$total_count = count($customers);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — Customers</title>
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
            --green: #5a8a5a;
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

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 24px;
            transition: border-color 0.2s, transform 0.2s;
        }

        .stat-card:hover {
            border-color: var(--border2);
            transform: translateY(-2px);
        }

        .stat-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text3);
            margin-bottom: 10px;
        }

        .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            font-weight: 900;
            color: var(--text);
            line-height: 1;
        }

        .stat-sub {
            font-size: 12px;
            color: var(--text2);
            margin-top: 6px;
        }

        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .table-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--card2);
        }

        .table-header-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text2);
        }

        .table-count {
            font-size: 12px;
            color: var(--text3);
            background: var(--card);
            border: 1px solid var(--border);
            padding: 3px 10px;
            border-radius: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: var(--card2);
            padding: 12px 16px;
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

        td.id-col {
            color: var(--text3);
            font-size: 12px;
        }

        td.name-col {
            color: var(--text);
            font-weight: 500;
        }

        td.email-col {
            color: var(--text2);
            font-size: 13px;
        }

        td.date-col {
            color: var(--text3);
            font-size: 12px;
        }

        .empty-row td {
            text-align: center;
            padding: 60px;
            color: var(--text3);
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
            <a href="orders.php">Orders</a>
            <a href="customers.php" class="active">Customers</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" onclick="handleLogout(event, this.href)">Logout</a>
        </div>
    </aside>

    <!-- Loading Overlay -->
    <div id="loading">
        <h2>Inventra</h2>
        <div class="dots">
            <span>●</span><span>●</span><span>●</span>
        </div>
        <p>Logging out…</p>
    </div>

    <!-- Main -->
    <div class="main">

        <!-- Topbar -->
        <div class="topbar">
            <h2>Customers</h2>
            <div class="admin-badge">Admin</div>
        </div>

        <!-- Page body -->
        <div class="page-body">

            <div class="page-header">
                <div class="page-header-tag">Manage</div>
                <h1>Customers</h1>
            </div>

            <!-- Stat grid -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value"><?php echo $total_count; ?></div>
                    <div class="stat-sub">Registered in system</div>
                </div>
            </div>

            <!-- Customer table -->
            <div class="table-wrap">
                <div class="table-header">
                    <div class="table-header-title">All Customers</div>
                    <span class="table-count"><?php echo $total_count; ?> total</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Contact No.</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($customers) > 0) { ?>
                            <?php foreach ($customers as $r) { ?>
                                <tr>
                                    <td class="id-col">#<?php echo $r['id']; ?></td>
                                    <td class="name-col"><?php echo htmlspecialchars($r['username']); ?></td>
                                    <td class="email-col"><?php echo htmlspecialchars($r['email'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($r['address'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($r['contact_no'] ?? '—'); ?></td>
                                    <td class="date-col">
                                        <?php
                                        if (isset($r['created_at'])) {
                                            echo date('M d, Y', strtotime($r['created_at']));
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr class="empty-row">
                                <td colspan="6">No customers yet.</td>
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