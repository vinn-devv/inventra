<?php
session_start();
$logged_in = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra — Shop Everything</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f2eb;
            --bg2: #efe8dc;
            --text: #2c1f14;
            --text2: #6b5344;
            --text3: #a08878;
            --primary: #6f4e37;
            --accent: #c8843a;
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
            min-height: 100vh;
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

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 56px 50px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(0deg, transparent, transparent 79px, rgba(111, 78, 55, 0.04) 80px);
            pointer-events: none;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 680px;
            animation: fadeUp 0.8s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(32px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(52px, 7vw, 90px);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -2px;
            color: var(--text);
            margin-bottom: 24px;
        }

        .hero h1 .highlight {
            color: var(--accent);
            display: block;
            font-style: italic;
        }

        .hero h1 .sub-label {
            display: block;
            font-size: 0.38em;
            font-family: 'Jost', sans-serif;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text3);
            margin-top: 14px;
        }

        .hero-desc {
            font-size: 17px;
            color: var(--text2);
            line-height: 1.75;
            max-width: 460px;
            margin-bottom: 44px;
        }

        .hero-buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary);
            color: #f7f2eb;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
            box-shadow: 0 8px 28px rgba(111, 78, 55, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 36px rgba(111, 78, 55, 0.4);
        }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            color: var(--text2);
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 15px;
            border: 1px solid var(--border2);
            transition: all 0.2s;
        }

        .btn-ghost:hover {
            color: var(--primary);
            border-color: var(--primary);
            background: rgba(111, 78, 55, 0.1);
        }

        .categories {
            padding: 100px 56px;
            border-top: 1px solid var(--border);
            background: var(--bg2);
        }

        .section-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 14px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 3.5vw, 44px);
            font-weight: 700;
            letter-spacing: -1px;
            color: var(--text);
            margin-bottom: 56px;
        }

        .cat-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }

        .cat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px 20px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }

        .cat-card:hover {
            transform: translateY(-5px);
            border-color: var(--border2);
            box-shadow: 0 16px 40px var(--shadow);
        }

        .cat-name {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }

        .cat-count {
            font-size: 12px;
            color: var(--text3);
        }

        .banner {
            margin: 100px 56px;
            border-radius: 24px;
            background: var(--primary);
            padding: 56px 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            overflow: hidden;
        }

        .banner h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(24px, 3vw, 38px);
            font-weight: 700;
            font-style: italic;
            color: #f7f2eb;
            letter-spacing: -0.5px;
            margin-bottom: 10px;
        }

        .banner p {
            font-size: 15px;
            color: rgba(247, 242, 235, 0.65);
            max-width: 400px;
        }

        .banner-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
        }

        .banner-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(200, 132, 58, 0.45);
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 32px 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text3);
            font-size: 13px;
            background: var(--bg2);
        }

        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--primary);
        }

        @media (max-width: 900px) {
            nav {
                padding: 16px 20px;
            }

            .hero {
                padding: 120px 24px 60px;
            }

            .categories {
                padding: 60px 24px;
            }

            .cat-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .banner {
                margin: 0 24px 60px;
                padding: 36px 28px;
                flex-direction: column;
            }

            footer {
                padding: 24px 20px;
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <nav>
        <a href="home.php" class="nav-logo">Inventra</a>
        <div class="nav-links">
            <a href="home.php" class="active">Home</a>
            <a href="webproducts.php">Products</a>
            <a href="about.php">About</a>
            <?php if ($logged_in) { ?>
                <a href="account.php" class="nav-cta">Account</a>
            <?php } else { ?>
                <a href="../auth/login.php" class="nav-cta">Sign In</a>
            <?php } ?>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-inner">
            <h1>
                Shop
                <span class="highlight">Everything.</span>
                <span class="sub-label">Tech · Fashion · Sports · Home · More</span>
            </h1>
            <p class="hero-desc">Browse hundreds of products across 5 categories. Stock updates live the moment you order.</p>
            <div class="hero-buttons">
                <a href="webproducts.php" class="btn-primary">Browse Products</a>
                <a href="about.php" class="btn-ghost">Learn More</a>
            </div>
        </div>
    </section>

    <section class="categories">
        <div style="max-width:1200px;margin:0 auto;">
            <div class="section-eyebrow">Shop by Category</div>
            <div class="section-title">What are you looking for?</div>
            <div class="cat-grid">
                <a href="webproducts.php?cat=electronics" class="cat-card">
                    <div class="cat-name">Electronics</div>
                    <div class="cat-count">Laptops, Cameras & more</div>
                </a>
                <a href="webproducts.php?cat=clothing" class="cat-card">
                    <div class="cat-name">Clothing</div>
                    <div class="cat-count">Shirts, Jackets & Shoes</div>
                </a>
                <a href="webproducts.php?cat=sports" class="cat-card">
                    <div class="cat-name">Sports</div>
                    <div class="cat-count">Gear, Equipment & more</div>
                </a>
                <a href="webproducts.php?cat=home" class="cat-card">
                    <div class="cat-name">Home & Living</div>
                    <div class="cat-count">Furniture, Decor & more</div>
                </a>
                <a href="webproducts.php?cat=accessories" class="cat-card">
                    <div class="cat-name">Accessories</div>
                    <div class="cat-count">Bags, Watches & more</div>
                </a>
            </div>
        </div>
    </section>

    <div class="banner">
        <div>
            <h2>Stock runs out fast.<br>Order before it's gone.</h2>
            <p>Quantities are limited and update in real time. Browse now and grab what's still available.</p>
        </div>
        <a href="webproducts.php" class="banner-btn">Shop Now</a>
    </div>

    <footer>
        <div class="footer-logo">Inventra</div>
        <div>Stock Management System</div>
    </footer>
</body>

</html>