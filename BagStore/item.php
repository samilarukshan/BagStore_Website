<?php
session_start();
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Get item id
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id");
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found");
}

// Load images from folder
$imgDir = "images/" . $item['id'] . "/";
$images = glob($imgDir . "*.{jpg,jpeg,png}", GLOB_BRACE);

if (empty($images)) {
    $images = ["images/default.jpg"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($item['name']) ?></title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'DM Sans', sans-serif;
    background: #f7f4ef;
    margin: 0;
}

/* NAV (same as index) */
nav {
    display: flex;
    justify-content: space-between;
    padding: 1.2rem 2.5rem;
    background: #fff;
    border-bottom: 1px solid #e8e2d9;
}

.nav-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    text-decoration: none;
    color: #1a1a1a;
}

.nav-logo span { color: #c8a96e; }

.nav-links a {
    margin-left: 10px;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
}

.btn-wishlist {
    background: #c8a96e;
    color: #fff;
}

.btn-logout {
    border: 1px solid #ddd;
}

/* MAIN */
.container {
    padding: 2rem 2.5rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

/* IMAGES */
.image-box img {
    width: 100%;
    border-radius: 12px;
}

.thumbnail {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.thumbnail img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    cursor: pointer;
    border-radius: 8px;
}

/* DETAILS */
.details h1 {
    font-family: 'Playfair Display', serif;
}

.price {
    color: #a8893e;
    font-size: 1.2rem;
    margin: 10px 0;
}

.meta {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 10px;
}

.desc {
    margin-top: 15px;
    line-height: 1.5;
}

/* BUTTON */
.btn {
    margin-top: 15px;
    padding: 10px 15px;
    border: none;
    background: #1a1a1a;
    color: #fff;
    cursor: pointer;
    border-radius: 6px;
}

@media (max-width: 768px) {
    .container {
        grid-template-columns: 1fr;
    }
}

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

<nav>
    <a class="nav-logo" href="index.php">Bag<span>Store</span></a>
    <div class="nav-links">
        <?php if ($isLoggedIn): ?>
            <a href="wishlist.php" class="btn-wishlist">♡ Wishlist</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn-wishlist">Login</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">

    <!-- IMAGES -->
    <div class="image-box">
        <img id="mainImage" src="<?= $images[0] ?>">

        <div class="thumbnail">
            <?php foreach ($images as $img): ?>
                <img src="<?= $img ?>" onclick="changeImage('<?= $img ?>')">
            <?php endforeach; ?>
        </div>
    </div>

    <!-- DETAILS -->
    <div class="details">
        <h1><?= htmlspecialchars($item['name']) ?></h1>

        <div class="price">$<?= htmlspecialchars($item['price']) ?></div>

        <div class="meta">
            Category: <?= htmlspecialchars($item['category']) ?><br>
            Gender: <?= htmlspecialchars($item['gender']) ?><br>
            Colors: <?= htmlspecialchars($item['colors']) ?>
        </div>

        <div class="desc">
            <?= nl2br(htmlspecialchars($item['description'])) ?>
        </div>

        <button class="btn" onclick="handleWishlist('<?= $item['id'] ?>')">
            Add to Wishlist
        </button>
    </div>

</div>

<script>
function changeImage(src) {
    document.getElementById('mainImage').src = src;
}

const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

function handleWishlist(id) {
    if (!isLoggedIn) {
        window.location.href = "login.php";
        return;
    }

    fetch('wishlist_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(res => res.json())
    .then(data => alert(data.message));
}
</script>
<footer class="footer">
  <p>© 2026 Bag Store. All Rights Reserved</p>
  <p>Contact: +94 77 123 4567 | Email: info@bagstore.com</p>
</footer>

</body>
</html>