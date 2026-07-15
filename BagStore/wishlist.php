<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php'; // your PDO connection

$userId = $_SESSION['user_id'];

// Fetch wishlist items using JOIN
$stmt = $pdo->prepare("
    SELECT i.id, i.name, i.price
    FROM wishlist w
    JOIN items i ON w.item_id = i.id
    WHERE w.user_id = :user_id
");

$stmt->execute(['user_id' => $userId]);
$bagsList = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Wishlist</title>
<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    margin: 0;
}

nav {
    background: #fff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    border-bottom: 1px solid #ddd;
}

nav a {
    text-decoration: none;
    margin-left: 15px;
    color: #333;
}

.container {
    padding: 30px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.card {
    background: #fff;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
}

.card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.btn {
    margin-top: 10px;
    padding: 8px 12px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

.btn-remove {
    background: red;
    color: #fff;
}

.empty {
    text-align: center;
    margin-top: 50px;
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
    <div><strong>My Wishlist</strong></div>
    <div>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

<?php if (empty($bagsList)): ?>
    <div class="empty">
        <h2>Your wishlist is empty</h2>
        <p>Add some bags to see them here.</p>
    </div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($bagsList as $bag): ?>
            <div class="card">
                <?php
$imgDir = "images/" . $bag['id'] . "/";
$images = glob($imgDir . "*.{jpg,jpeg,png}", GLOB_BRACE);
$imagePath = !empty($images) ? $images[0] : "images/default.jpg";
?>

<img src="<?= htmlspecialchars($imagePath) ?>">
                <h3><?= htmlspecialchars($bag['name']) ?></h3>
                <p>$<?= htmlspecialchars($bag['price']) ?></p>
                <button class="btn btn-remove" onclick="removeItem('<?= $bag['id'] ?>')">
                    Remove
                </button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>

<script>
async function removeItem(id) {
    if (!confirm("Remove from wishlist?")) return;

    const res = await fetch('wishlist_remove.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    });

    const data = await res.json();
    alert(data.message);
    location.reload();
}
</script>
<footer class="footer">
  <p>© 2026 Bag Store. All Rights Reserved</p>
  <p>Contact: +94 77 123 4567 | Email: info@bagstore.com</p>
</footer>

</body>
</html>