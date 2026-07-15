<?php
session_start();
require 'db.php';

$success = '';

if (isset($_GET['success'])) {
    $success = 'Registration successful! Please login.';
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Fetch user from MySQL
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // MySQL uses 'id' not '_id'
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — BagStore</title>
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

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.3rem;
            color: var(--text);
            text-decoration: none;
            display: block;
        }

        .login-logo span { color: var(--accent); }

        .login-tagline {
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
            font-weight: 700;
            margin-bottom: 1.6rem;
        }

        .form-group { margin-bottom: 1.1rem; }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            margin-bottom: 0.45rem;
        }

        .form-group input {
            width: 100%;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            padding: 0.7rem 1rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg);
            color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(200, 169, 110, 0.15);
        }

        .error-box {
            background: #fff3f3;
            border: 1px solid #f5c6c6;
            color: #c0392b;
            font-size: 0.85rem;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.2rem;
        }

        .btn-login {
            width: 100%;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.75rem;
            background: var(--text);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.2s;
        }

        .btn-login:hover { background: #333; }

        .register-link {
            text-align: center;
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 1.3rem;
        }

        .register-link a {
            color: var(--accent-dark);
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover { text-decoration: underline; }

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

<div class="login-wrapper">
    <a class="login-logo" href="login.php">Bag<span>Store</span></a>
    <p class="login-tagline">Sign in to your account</p>

    <div class="card">
        <h2>Welcome back</h2>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
                >
            </div>

            <button class="btn-login" type="submit">Login</button>
        </form>

        <p class="register-link">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </div>
</div>
<footer class="footer">
  <p>© 2026 Bag Store. All Rights Reserved</p>
  <p>Contact: +94 77 123 4567 | Email: info@bagstore.com</p>
</footer>

</body>
</html>