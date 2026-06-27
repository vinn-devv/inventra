<?php
session_start();
include '../config/db.php';

// Make a CSRF token for the buy form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$logged_in = isset($_SESSION['user']);
$username  = $logged_in ? htmlspecialchars($_SESSION['user']) : '';

// Get the category from the URL, default to 'all'
$allowed_cats = ['electronics', 'clothing', 'sports', 'home', 'accessories'];
$cat = 'all';
if (isset($_GET['cat']) && in_array($_GET['cat'], $allowed_cats)) {
    $cat = $_GET['cat'];
}

// Get products from database
if ($cat == 'all') {
    $result = $conn->query("SELECT id, name, category, quantity, price, image FROM products ORDER BY category, name");
} else {
    $stmt = $conn->prepare("SELECT id, name, category, quantity, price, image FROM products WHERE category = ? ORDER BY name");
    $stmt->bind_param("s", $cat);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f2eb;
            --bg2: #efe8dc;
            --bg3: #e8ddd0;
            --text: #2c1f14;
            --text2: #6b5344;
            --text3: #a08878;
            --primary: #6f4e37;
            --accent: #c8843a;
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
            overflow-x: hidden;
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

        .nav-cta {
            background: var(--primary) !important;
            color: #f7f2eb !important;
            padding: 9px 22px;
            border-radius: 8px;
            font-size: 13px !important;
            font-weight: 600 !important;
            transition: opacity .2s !important;
        }

        .nav-cta:hover {
            opacity: .88 !important;
        }

        .page-header {
            padding: 120px 56px 32px;
            max-width: 1300px;
            margin: 0 auto;
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

        .page-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 12px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(32px, 4vw, 52px);
            font-weight: 700;
            letter-spacing: -1.5px;
            margin-bottom: 8px;
        }

        .page-header p {
            font-size: 15px;
            color: var(--text2);
        }

        .flash {
            margin-top: 18px;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .flash-success {
            background: rgba(26, 154, 92, .10);
            border: 1px solid rgba(26, 154, 92, .28);
            color: #1a6640;
        }

        .flash-error {
            background: rgba(192, 50, 50, .08);
            border: 1px solid rgba(192, 50, 50, .22);
            color: #b03030;
        }

        .filter-wrap {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 56px 32px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: 50px;
            border: 1px solid var(--border2);
            background: var(--card);
            color: var(--text2);
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }

        .filter-tab:hover {
            color: var(--primary);
            border-color: var(--primary);
            background: rgba(111, 78, 55, .1);
        }

        .filter-tab.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #f7f2eb;
        }

        .products-wrap {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 56px 100px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: var(--card);
            border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: transform .25s, border-color .25s, box-shadow .25s;
            animation: fadeUp .5s ease both;
        }

        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--border2);
            box-shadow: 0 20px 48px var(--shadow);
        }

        .card-img-wrap {
            height: 180px;
            background: var(--bg3);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border-bottom: 1px solid var(--border);
        }

        .card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .cat-label {
            position: absolute;
            top: 12px;
            left: 12px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 50px;
            border: 1px solid var(--border2);
            color: var(--text2);
            background: rgba(247, 242, 235, .85);
            backdrop-filter: blur(8px);
            z-index: 2;
        }

        .stock-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 50px;
            z-index: 2;
        }

        .stock-badge.in {
            background: rgba(26, 154, 92, .12);
            color: #1a7a50;
            border: 1px solid rgba(26, 154, 92, .25);
        }

        .stock-badge.low {
            background: rgba(200, 132, 58, .12);
            color: #a0601a;
            border: 1px solid rgba(200, 132, 58, .3);
        }

        .stock-badge.out {
            background: rgba(192, 50, 50, .10);
            color: #b03030;
            border: 1px solid rgba(192, 50, 50, .2);
        }

        .card-body {
            padding: 18px 20px;
        }

        .card-name {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 10px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .card-price {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }

        .card-stock {
            font-size: 12px;
            color: var(--text3);
        }

        .card-stock strong {
            color: var(--text2);
        }

        .btn-buy {
            width: 100%;
            padding: 11px;
            border: none;
            border-radius: 10px;
            background: var(--primary);
            color: #f7f2eb;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 16px rgba(111, 78, 55, .25);
        }

        .btn-buy:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .btn-buy.disabled {
            background: var(--bg3);
            color: var(--text3);
            cursor: not-allowed;
            box-shadow: none;
            border: 1px solid var(--border);
        }

        .empty-state {
            grid-column: 1/-1;
            text-align: center;
            padding: 100px 20px;
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--text);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--text2);
            font-size: 14px;
        }

        #buyModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(44, 31, 20, .55);
            backdrop-filter: blur(8px);
            z-index: 500;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        #buyModal.open {
            display: flex;
        }

        .modal-box {
            background: var(--card);
            border: 1px solid var(--border2);
            padding: 40px;
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            position: relative;
            box-shadow: 0 40px 100px rgba(44, 31, 20, .25);
            animation: modalIn .3s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(.9) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: var(--text2);
            transition: all .2s;
        }

        .modal-close:hover {
            background: var(--primary);
            color: #f7f2eb;
            border-color: var(--primary);
        }

        .modal-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -.5px;
            margin-bottom: 28px;
        }

        .modal-field {
            margin-bottom: 14px;
        }

        .modal-field label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text2);
            margin-bottom: 6px;
        }

        .modal-field input {
            width: 100%;
            padding: 11px 14px;
            border-radius: 10px;
            border: 1px solid var(--border2);
            background: var(--bg);
            color: var(--text);
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
        }

        .modal-field input:focus {
            border-color: var(--accent);
        }

        .modal-hint {
            font-size: 12px;
            color: var(--text3);
            margin-top: 5px;
        }

        .btn-confirm {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: var(--primary);
            color: #f7f2eb;
            font-family: 'Jost', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: opacity .2s, transform .2s;
            box-shadow: 0 8px 24px rgba(111, 78, 55, .3);
        }

        .btn-confirm:hover {
            opacity: .88;
            transform: translateY(-1px);
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

        @media (max-width: 768px) {

            nav,
            .filter-wrap,
            .products-wrap {
                padding-left: 20px;
                padding-right: 20px;
            }

            .page-header {
                padding: 100px 20px 24px;
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

    <nav>
        <a href="home.php" class="nav-logo">Inventra</a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="webproducts.php" class="active">Products</a>
            <a href="about.php">About</a>
            <?php if ($logged_in): ?>
                <a href="account.php" class="nav-cta">Account</a>
            <?php else: ?>
                <a href="../auth/login.php" class="nav-cta">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Page heading -->
    <div class="page-header">
        <div class="page-eyebrow">Our Products</div>
        <h1>Browse & Shop.</h1>
        <p>Everything in one place — updated live as orders come in.</p>

        <?php if (isset($_GET['purchased']) && $_GET['purchased'] == '1'): ?>
            <div class="flash flash-success">
                Order placed! You bought <strong><?= (int)$_GET['qty'] ?></strong> x
                <strong><?= htmlspecialchars(urldecode($_GET['product_name'] ?? '')) ?></strong>. Stock updated!
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="flash flash-error">
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Category filter tabs -->
    <div class="filter-wrap">
        <a href="webproducts.php" class="filter-tab <?= $cat == 'all' ? 'active' : '' ?>">All Products</a>
        <a href="webproducts.php?cat=electronics" class="filter-tab <?= $cat == 'electronics' ? 'active' : '' ?>">Electronics</a>
        <a href="webproducts.php?cat=clothing" class="filter-tab <?= $cat == 'clothing' ? 'active' : '' ?>">Clothing</a>
        <a href="webproducts.php?cat=sports" class="filter-tab <?= $cat == 'sports' ? 'active' : '' ?>">Sports</a>
        <a href="webproducts.php?cat=home" class="filter-tab <?= $cat == 'home' ? 'active' : '' ?>">Home & Living</a>
        <a href="webproducts.php?cat=accessories" class="filter-tab <?= $cat == 'accessories' ? 'active' : '' ?>">Accessories</a>
    </div>

    <!-- Product cards -->
    <div class="products-wrap">
        <div class="products-grid">

            <?php if ($result && $result->num_rows > 0): ?>
                <?php $delay = 0; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $qty = (int)$row['quantity'];

                    // Figure out the stock badge
                    if ($qty <= 0) {
                        $badge_class = 'out';
                        $badge_text  = 'Sold Out';
                    } elseif ($qty <= 3) {
                        $badge_class = 'low';
                        $badge_text  = 'Low Stock';
                    } else {
                        $badge_class = 'in';
                        $badge_text  = 'In Stock';
                    }

                    $safe_name = htmlspecialchars(addslashes($row['name']));
                    ?>
                    <div class="product-card" style="animation-delay: <?= $delay * 0.06 ?>s">
                        <div class="card-img-wrap">
                            <div class="cat-label"><?= ucfirst(htmlspecialchars($row['category'])) ?></div>
                            <div class="stock-badge <?= $badge_class ?>"><?= $badge_text ?></div>
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="card-name"><?= htmlspecialchars($row['name']) ?></div>
                            <div class="card-meta">
                                <div class="card-price">₱<?= number_format($row['price'], 2) ?></div>
                                <div class="card-stock">Qty: <strong><?= $qty ?></strong></div>
                            </div>

                            <?php if ($qty <= 0): ?>
                                <button class="btn-buy disabled" disabled>Out of Stock</button>
                            <?php elseif ($logged_in): ?>
                                <button class="btn-buy" onclick="openBuyModal(<?= $row['id'] ?>, '<?= $safe_name ?>', <?= $qty ?>)">Buy Now</button>
                            <?php else: ?>
                                <button class="btn-buy" onclick="openLoginPrompt()">Buy Now</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $delay++; ?>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-state">
                    <h3>Nothing here yet</h3>
                    <p>No products found in this category.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Buy Modal -->
    <div id="buyModal">
        <div class="modal-box">
            <button class="modal-close" onclick="closeBuyModal()">✕</button>
            <div class="modal-eyebrow">Place Order</div>
            <div class="modal-title" id="productTitle">Buy Product</div>

            <?php if ($logged_in): ?>
                <div style="background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:var(--text2);">
                    Ordering as <strong style="color:var(--primary)"><?= $username ?></strong>
                </div>
                <form method="POST" action="../actions/buy.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="product_id" id="product_id">
                    <div class="modal-field">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="buy_quantity" min="1" value="1" required>
                        <div class="modal-hint" id="stock_hint"></div>
                    </div>
                    <button type="submit" class="btn-confirm">Confirm Order</button>
                </form>
            <?php else: ?>
                <div style="text-align:center; padding:10px 0 6px;">
                    <p style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:16px;">Sign in to complete your purchase</p>
                    <img style="border-radius:7px;height:200px;width:200px;" src="../assets/images/gato-cat.gif" alt="cat gif">
                    <p style="font-size:13px;color:var(--text2);margin-bottom:24px;line-height:1.6;">
                        You need an account to buy items.<br>It only takes a minute to sign up!
                    </p>
                    <a href="../auth/login.php" style="display:block;text-align:center;text-decoration:none;background:var(--primary);color:#f7f2eb;padding:13px;border-radius:10px;font-weight:600;font-size:15px;">
                        Sign In to Buy
                    </a>
                    <p style="margin-top:14px;font-size:13px;color:var(--text3);">
                        No account? <a href="../auth/login.php" style="color:var(--accent);font-weight:600;text-decoration:none;">Create one free</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-logo">Inventra</div>
        <div>Stock Management System</div>
    </footer>

    <script>
        function openBuyModal(id, name, stock) {
            document.getElementById('buyModal').classList.add('open');
            document.getElementById('product_id').value = id;
            document.getElementById('productTitle').innerText = name;

            var qty = document.getElementById('buy_quantity');
            if (qty) {
                qty.value = 1;
                qty.max = stock;
            }

            var hint = document.getElementById('stock_hint');
            if (hint) {
                hint.innerText = 'Max available: ' + stock;
            }
        }

        function openLoginPrompt() {
            document.getElementById('buyModal').classList.add('open');
            document.getElementById('productTitle').innerText = 'Sign In to Purchase';
        }

        function closeBuyModal() {
            document.getElementById('buyModal').classList.remove('open');
        }

        // Close modal when clicking the dark background
        document.getElementById('buyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBuyModal();
            }
        });
    </script>

</body>

</html>