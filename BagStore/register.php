<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Phone number must be exactly 10 digits.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (email, phone, password) VALUES (?, ?, ?)");
            $stmt->execute([$email, $phone, $hashedPassword]);

            // ✅ Redirect after success
            header("Location: login.php?success=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — BagStore</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg: #f7f4ef;
    --text: #1a1a1a;
    --muted: #888;
    --accent: #c8a96e;
    --accent-dark: #a8893e;
    --border: #e8e2d9;
    --card-bg: #ffffff;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

.wrapper {
    width: 100%;
    max-width: 420px;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 0.3rem;
    text-decoration: none;
    display: block;
    color: var(--text);
}

.logo span { color: var(--accent); }

.tagline {
    text-align: center;
    font-size: 0.88rem;
    color: var(--muted);
    margin-bottom: 2rem;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 2.2rem 2rem;
}

.card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    margin-bottom: 1.6rem;
}

.form-group { margin-bottom: 1rem; }

.form-group label {
    display: block;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 0.4rem;
}

.form-group input {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--bg);
}

.error-box {
    background: #fff3f3;
    border: 1px solid #f5c6c6;
    color: #c0392b;
    font-size: 0.85rem;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.success-box {
    background: #eafaf1;
    border: 1px solid #b7e4c7;
    color: #2e7d32;
    font-size: 0.85rem;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

button {
    width: 100%;
    padding: 0.75rem;
    background: var(--text);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
}

button:hover { background: #333; }

.link {
    text-align: center;
    font-size: 0.85rem;
    margin-top: 1.2rem;
}

.link a {
    color: var(--accent-dark);
    text-decoration: none;
}

.link a:hover { text-decoration: underline; }

.error { color:red; font-size:12px; }

.footer {
  background-color: black;
  color: white;
  text-align: center;
  padding: 15px 0;
  position: fixed;
  bottom: 0;
  width: 100%;
  font-size: 14px;
}

.footer p {
  margin: 5px;
}
</style>
</head>

<body>

<div class="wrapper">
    <a class="logo" href="#">Bag<span>Store</span></a>
    <p class="tagline">Create your account</p>

    <div class="card">
        <h2>Register</h2>

        <?php if ($error): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-box"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
                <div id="emailError" class="error"></div>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input 
    type="text" 
    name="phone" 
    id="phone" 
    maxlength="10" 
    required>
                <div id="phoneError" class="error"></div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <div id="passwordError" class="error"></div>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="confirmPassword" required>
                <div id="confirmError" class="error"></div>
            </div>

            <button type="submit">Register</button>
        </form>

        <p class="link">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;

    let email = document.querySelector('[name="email"]').value;
    let phone = document.getElementById('phone').value;
    let password = document.getElementById('password').value;
    let confirmPassword = document.getElementById('confirmPassword').value;

    document.getElementById('emailError').innerText = '';
    document.getElementById('phoneError').innerText = '';
    document.getElementById('passwordError').innerText = '';
    document.getElementById('confirmError').innerText = '';

    if (!email.includes('@')) {
        document.getElementById('emailError').innerText = 'Invalid email';
        valid = false;
    }

    if (!/^[0-9]{10}$/.test(phone)) {
    document.getElementById('phoneError').innerText = 'Phone must be exactly 10 digits';
    valid = false;
}

    if (password.length < 6) {
        document.getElementById('passwordError').innerText = 'Minimum 6 characters';
        valid = false;
    }

    if (password !== confirmPassword) {
        document.getElementById('confirmError').innerText = 'Passwords do not match';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>
<footer class="footer">
  <p>© 2026 Bag Store. All Rights Reserved</p>
  <p>Contact: +94 77 123 4567 | Email: info@bagstore.com</p>
</footer>

</body>
</html>