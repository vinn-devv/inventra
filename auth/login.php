<?php
session_start();
include '../config/db.php';

$error = "";

// handle login
if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user']    = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../pages/home.php");
            }
            exit();
        } else {
            $error = "Incorrect username or password.";
        }
    } else {
        $stmt->close();
        $error = "Incorrect username or password.";
    }
}

// handle signup
if (isset($_POST['signup'])) {

    $name       = $_POST['name'];
    $email      = $_POST['email'];
    $password   = $_POST['password'];
    $address    = $_POST['address'];
    $contact_no = $_POST['contact_no'];

    // check if username already exists
    $chk = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $chk->bind_param("s", $name);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows > 0) {
        $chk->close();
        $error = "Username already taken!";
    } else {
        $chk->close();

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $ins = $conn->prepare("INSERT INTO users (username, email, password, role, address, contact_no) VALUES (?, ?, ?, 'user', ?, ?)");
        $ins->bind_param("sssss", $name, $email, $hashed, $address, $contact_no);
        $ins->execute();
        $ins->close();

        $new_id = $conn->insert_id;

        session_regenerate_id(true);
        $_SESSION['user']    = $name;
        $_SESSION['user_id'] = $new_id;
        $_SESSION['role']    = 'user';

        header("Location: ../pages/home.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra - Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f2eb;
            --bg2: #ede6db;
            --text: #2b1f14;
            --text2: #7a6251;
            --text3: #b09a87;
            --primary: #6f4e37;
            --accent: #c8843a;
            --border: rgba(111, 78, 55, 0.22);
            --card: #ffffff;
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
            min-height: 100vh;
            display: flex;
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

        .left {
            flex: 1;
            background: var(--primary);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .left-lines {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(-55deg,
                    transparent, transparent 28px,
                    rgba(247, 242, 235, 0.03) 29px);
            pointer-events: none;
        }

        .left-top,
        .left-middle,
        .left-bottom {
            position: relative;
            z-index: 1;
        }

        .left-middle {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 0;
        }

        .left .logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 900;
            color: white;
            text-decoration: none;
        }

        .left h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 4vw, 54px);
            font-weight: 900;
            line-height: 1.08;
            margin-bottom: 18px;
            letter-spacing: -1.5px;
        }

        .left h1 em {
            font-style: italic;
            color: #f5c887;
            display: block;
        }

        .left p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.58);
            line-height: 1.75;
            max-width: 340px;
        }

        .left-tagline {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 1px;
        }

        .right {
            width: 460px;
            min-height: 100vh;
            background: var(--bg);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 48px;
        }

        .right a.back {
            font-size: 13px;
            color: var(--text2);
            text-decoration: none;
            margin-bottom: 40px;
            display: block;
        }

        .right a.back:hover {
            color: var(--primary);
        }

        .right h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .right .sub {
            font-size: 14px;
            color: var(--text2);
            margin-bottom: 28px;
        }

        .tabs {
            display: flex;
            background: var(--bg2);
            border-radius: 50px;
            padding: 4px;
            margin-bottom: 24px;
        }

        .tabs button {
            flex: 1;
            padding: 9px;
            border: none;
            background: none;
            color: var(--text3);
            border-radius: 50px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tabs button.active {
            background: var(--primary);
            color: white;
        }

        .error {
            background: rgba(200, 60, 60, 0.08);
            border: 1px solid rgba(200, 60, 60, 0.2);
            color: #c84040;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .group {
            margin-bottom: 14px;
        }

        .group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text2);
            margin-bottom: 6px;
        }

        .group input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            background: var(--card);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }

        .group input:focus {
            border-color: var(--primary);
        }

        .group input::placeholder {
            color: var(--text3);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: var(--accent);
        }

        .hidden {
            display: none;
        }

        .pw-wrap {
            position: relative;
        }

        .pw-input {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            background: var(--card);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }

        .pw-input:focus {
            border-color: var(--primary);
        }

        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text3);
            font-size: 12px;
            font-weight: 600;
            padding: 0;
            font-family: 'DM Sans', sans-serif;
        }

        .pw-toggle:hover {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .left {
                display: none;
            }

            .right {
                width: 100%;
                border: none;
            }
        }
    </style>
</head>

<body>

    <!-- Loading overlay -->
    <div id="loading">
        <h2>Inventra</h2>
        <div class="dots">
            <span>&#9679;</span><span>&#9679;</span><span>&#9679;</span>
        </div>
        <p>Please wait...</p>
    </div>

    <!-- Left branding panel -->
    <div class="left">
        <div class="left-lines"></div>

        <div class="left-top">
            <a href="../pages/home.php" class="logo">Inventra</a>
        </div>

        <div class="left-middle">
            <h1>
                Your stock,
                <em>under control.</em>
            </h1>
            <p>An all-in-one web platform for managing inventory and orders with ease.</p>
        </div>

        <div class="left-bottom">
            <div class="left-tagline">Inventra &middot; Stock Management</div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="right">

        <a href="../pages/home.php" class="back">&larr; Back to home</a>

        <h2>Welcome back.</h2>
        <p class="sub">Enter your credentials to continue.</p>

        <?php if ($error != "") { ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <!-- Tabs -->
        <div class="tabs">
            <button class="<?php echo !isset($_POST['signup']) ? 'active' : ''; ?>" onclick="switchTab('login', this)">Login</button>
            <button class="<?php echo isset($_POST['signup']) ? 'active' : ''; ?>" onclick="switchTab('signup', this)">Sign Up</button>
        </div>

        <!-- Login Form -->
        <form id="login" method="POST" class="<?php echo isset($_POST['signup']) ? 'hidden' : ''; ?>" onsubmit="handleSubmit(event, this)">
            <div class="group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="group">
                <label>Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password" id="login_password" placeholder="Your password" required class="pw-input">
                    <button type="button" onclick="togglePassword('login_password', this)" class="pw-toggle">Show</button>
                </div>
            </div>
            <button type="submit" name="login" class="btn-submit">Sign In</button>
        </form>

        <!-- Signup Form -->
        <form id="signup" method="POST" class="<?php echo isset($_POST['signup']) ? '' : 'hidden'; ?>" onsubmit="handleSubmit(event, this)">
            <div class="group">
                <label>Full Name / Username</label>
                <input type="text" name="name" placeholder="Your name"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>
            <div class="group">
                <label>Email</label>
                <input type="email" name="email" placeholder="e.g. john@gmail.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="group">
                <label>Address</label>
                <input type="text" name="address" placeholder="Street, City, Province"
                    value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
            </div>
            <div class="group">
                <label>Contact No.</label>
                <input type="tel" name="contact_no" placeholder="e.g. 09XXXXXXXXX"
                    value="<?php echo htmlspecialchars($_POST['contact_no'] ?? ''); ?>"
                    pattern="[0-9]{11}"
                    maxlength="11"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <div class="group">
                <label>Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password" id="signup_password" placeholder="Min. 6 characters" required minlength="6" class="pw-input">
                    <button type="button" onclick="togglePassword('signup_password', this)" class="pw-toggle">Show</button>
                </div>
            </div>
            <button type="submit" name="signup" class="btn-submit">Create Account</button>
        </form>

    </div>

    <script>
        function togglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Hide';
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
            }
        }

        function switchTab(formId, clickedBtn) {
            document.getElementById('login').classList.add('hidden');
            document.getElementById('signup').classList.add('hidden');
            document.getElementById(formId).classList.remove('hidden');

            var allBtns = document.querySelectorAll('.tabs button');
            for (var i = 0; i < allBtns.length; i++) {
                allBtns[i].classList.remove('active');
            }
            clickedBtn.classList.add('active');
        }

        function handleSubmit(e, form) {
            e.preventDefault();
            var submitBtn = form.querySelector('button[type="submit"]');
            var btnName = submitBtn.getAttribute('name');

            var hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = btnName;
            hiddenInput.value = '1';
            form.appendChild(hiddenInput);

            document.getElementById('loading').classList.add('show');
            setTimeout(function() {
                form.submit();
            }, 700);
        }
    </script>

</body>

</html>