<?php
session_start();
$logged_in = isset($_SESSION['user']);
$username  = $logged_in ? htmlspecialchars($_SESSION['user']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — About</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f2eb;
            --bg2: #efe8dc;
            --bg3: #e8ddd0;
            --text: #2c1f14;
            --text2: #6b5344;
            --text3: #a08878;
            --primary: #6f4e37;
            --primary-light: rgba(111, 78, 55, 0.1);
            --primary-mid: rgba(111, 78, 55, 0.18);
            --accent: #c8843a;
            --accent-dim: rgba(200, 132, 58, 0.12);
            --card: #faf6f0;
            --border: rgba(111, 78, 55, 0.12);
            --border2: rgba(111, 78, 55, 0.22);
            --shadow: rgba(44, 31, 20, 0.08);
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Jost', sans-serif;
            background: var(--bg);
            color: var(--text);
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
            background: rgba(247, 242, 235, 0.88);
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
            transition: color 0.2s;
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
            transition: opacity 0.2s, transform 0.2s !important;
        }

        .nav-cta:hover {
            opacity: 0.88 !important;
            transform: translateY(-1px);
        }

        .page-hero {
            padding: 150px 56px 80px;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            animation: fadeUp 0.7s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
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
            margin-bottom: 18px;
        }

        .page-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(38px, 5vw, 62px);
            font-weight: 700;
            letter-spacing: -2px;
            line-height: 1.05;
            color: var(--text);
            margin-bottom: 20px;
        }

        .page-hero h1 em {
            font-style: italic;
            color: var(--accent);
        }

        .page-hero p {
            font-size: 16px;
            color: var(--text2);
            line-height: 1.75;
        }

        .cat-showcase {
            background: var(--card);
            border-radius: 20px;
            border: 1px solid var(--border2);
            padding: 24px;
            box-shadow: 0 8px 32px var(--shadow);
        }

        .showcase-title {
            font-family: 'Playfair Display', serif;
            font-size: 14px;
            font-weight: 700;
            color: var(--text2);
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .showcase-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .showcase-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 14px;
            background: var(--bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .showcase-item:hover {
            border-color: var(--border2);
            box-shadow: 0 4px 12px var(--shadow);
        }

        .showcase-name {
            font-family: 'Playfair Display', serif;
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .showcase-sub {
            font-size: 12px;
            color: var(--text3);
            margin-top: 2px;
        }

        .showcase-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: var(--accent-dim);
            color: var(--accent);
            padding: 3px 10px;
            border-radius: 50px;
            border: 1px solid rgba(200, 132, 58, 0.2);
        }

        .info-section {
            border-top: 1px solid var(--border);
            background: var(--bg2);
            padding: 80px 56px;
        }

        .info-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .info-card {
            background: var(--card);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border);
            transition: border-color 0.25s, transform 0.25s, box-shadow 0.25s;
        }

        .info-card:hover {
            border-color: var(--border2);
            transform: translateY(-4px);
            box-shadow: 0 16px 40px var(--shadow);
        }

        .info-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text);
        }

        .info-card p,
        .info-card ul {
            font-size: 14px;
            color: var(--text2);
            line-height: 1.75;
        }

        .info-card ul {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mission {
            background: var(--primary);
            padding: 80px 56px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .mission::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 700px 400px at 50% 50%, rgba(200, 132, 58, 0.2), transparent);
        }

        .mission-inner {
            position: relative;
            z-index: 1;
        }

        .mission h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 4vw, 50px);
            font-weight: 700;
            font-style: italic;
            color: #f7f2eb;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        .mission p {
            font-size: 16px;
            color: rgba(247, 242, 235, 0.65);
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.75;
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

        @media (max-width: 900px) {
            nav {
                padding: 15px 20px;
            }

            .page-hero {
                grid-template-columns: 1fr;
                padding: 120px 24px 60px;
                gap: 40px;
            }

            .info-section {
                padding: 60px 24px;
            }

            .info-inner {
                grid-template-columns: 1fr;
            }

            .mission {
                padding: 60px 24px;
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
            <a href="webproducts.php">Products</a>
            <a href="about.php" class="active">About</a>
            <?php if ($logged_in) { ?>
                <a href="account.php" class="nav-cta">Account</a>
            <?php } else { ?>
                <a href="../auth/login.php" class="nav-cta">Sign In</a>
            <?php } ?>
        </div>
    </nav>

    <div class="page-hero">
        <div>
            <div class="page-eyebrow">About Us</div>
            <h1>One store.<br><em>Everything</em><br>you need.</h1>
            <p>Inventra is a real-time stock management system built for general retail — from electronics to fashion to sports gear. Browse, order, and watch the stock update live.</p>
        </div>

        <div class="cat-showcase">
            <div class="showcase-title">Our 5 Categories</div>
            <div class="showcase-list">
                <div class="showcase-item">
                    <div>
                        <div class="showcase-name">Electronics</div>
                        <div class="showcase-sub">Laptops, Cameras, Parts</div>
                    </div>
                    <div class="showcase-badge">Tech</div>
                </div>
                <div class="showcase-item">
                    <div>
                        <div class="showcase-name">Clothing & Shoes</div>
                        <div class="showcase-sub">Shirts, Jackets, Footwear</div>
                    </div>
                    <div class="showcase-badge">Fashion</div>
                </div>
                <div class="showcase-item">
                    <div>
                        <div class="showcase-name">Sports & Outdoors</div>
                        <div class="showcase-sub">Gear, Equipment, Apparel</div>
                    </div>
                    <div class="showcase-badge">Active</div>
                </div>
                <div class="showcase-item">
                    <div>
                        <div class="showcase-name">Home & Living</div>
                        <div class="showcase-sub">Furniture, Decor, Essentials</div>
                    </div>
                    <div class="showcase-badge">Home</div>
                </div>
                <div class="showcase-item">
                    <div>
                        <div class="showcase-name">Accessories</div>
                        <div class="showcase-sub">Bags, Watches, Jewelry</div>
                    </div>
                    <div class="showcase-badge">Style</div>
                </div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-inner">
            <div class="info-card">
                <h3>What We Offer</h3>
                <p>A wide range of products across 5 major categories — all managed through a single real-time stock system. No confusion, no overselling.</p>
            </div>
            <div class="info-card">
                <h3>Why Choose Us</h3>
                <ul>
                    <li>Live stock on every product</li>
                    <li>Fast, simple checkout</li>
                    <li>Secure, session-based accounts</li>
                </ul>
            </div>
            <div class="info-card">
                <h3>Powered by Inventra</h3>
                <p>Our admin dashboard tracks every order, adjusts stock in real time, and gives full visibility into what's selling — built for demo and real use.</p>
            </div>
        </div>
    </div>

    <div class="mission">
        <div class="mission-inner">
            <h2>Shop smart. Shop live.</h2>
            <p>Our mission is to show what a real-time inventory system looks like in action.</p>
        </div>
    </div>

    <footer>
        <div class="footer-logo">Inventra</div>
        <div>Stock Management System</div>
    </footer>

</body>

</html>