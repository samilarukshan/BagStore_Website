
<?php
session_start();
$bagsList = [];
require_once 'db.php';

$bagsList = [];

$wishlistIds = [];

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT item_id FROM wishlist WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $wishlistIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$colorOptions = [];

$stmt = $pdo->query("SELECT colors FROM items");

$allColors = [];

while ($row = $stmt->fetch()) {
    if (!empty($row['colors'])) {
        $colors = explode(';', strtolower($row['colors']));
        $allColors = array_merge($allColors, $colors);
    }
}

// Remove duplicates + clean
$colorOptions = array_unique(array_map('trim', $allColors));
sort($colorOptions);

$query = "SELECT * FROM items WHERE 1=1";
$params = [];

// Category filter
if (!empty($_GET['category'])) {
    $query .= " AND category = :category";
    $params['category'] = $_GET['category'];
}

// Gender filter
if (!empty($_GET['gender'])) {
    $query .= " AND gender = :gender";
    $params['gender'] = $_GET['gender'];
}

// Color filter (since stored as red;blue)
if (!empty($_GET['color'])) {
    $query .= " AND LOWER(colors) LIKE :color";
    $params['color'] = "%" . strtolower($_GET['color']) . "%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$bagsList = $stmt->fetchAll();


// Redirect to login if not logged in
$isLoggedIn = isset($_SESSION['user_id']);



// Build filter based on GET params
$filter = [];

if (!empty($_GET['category'])) {
    $filter['category'] = $_GET['category'];
}

if (!empty($_GET['color'])) {
    $filter['color'] = $_GET['color'];
}

if (!empty($_GET['gender'])) {
    $filter['gender'] = $_GET['gender'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BagStore</title>
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
            --card-bg: #ffffff;
            --border: #e8e2d9;
        }

        .btn-wishlist-card.added {
    background: red;
    color: #fff;
    border-color: red;
    cursor: not-allowed;
}

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* NAV */
        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 2.5rem;
            background: #fff;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--text);
            text-decoration: none;
        }

        .nav-logo span {
            color: var(--accent);
        }

        .nav-links {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text);
            padding: 0.5rem 1.1rem;
            border-radius: 50px;
            transition: background 0.2s, color 0.2s;
        }

        .nav-links a:hover {
            background: var(--bg);
        }

        .nav-links a.btn-logout {
            border: 1px solid var(--border);
        }

        .nav-links a.btn-wishlist {
            background: var(--accent);
            color: #fff;
        }

        .nav-links a.btn-wishlist:hover {
            background: var(--accent-dark);
        }

        /* FILTER BAR */
        .filter-bar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 1rem 2.5rem;
        }

        .filter-bar form {
            display: flex;
            gap: 0.8rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-bar label {
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 0.2rem;
        }

        .filter-bar select {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            padding: 0.5rem 2rem 0.5rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 0.7rem center;
            -webkit-appearance: none;
            appearance: none;
            color: var(--text);
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .filter-bar select:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn-filter {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 0.5rem 1.4rem;
            background: var(--text);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
            margin-left: auto;
        }

        .btn-filter:hover {
            background: #333;
        }

        /* MAIN */
        main {
            padding: 2rem 2.5rem;
        }

        .section-header {
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
        }

        .section-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .section-header .count {
            font-size: 0.85rem;
            color: var(--muted);
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1.5rem;
        }

        /* CARD */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            transition: transform 0.25s, box-shadow 0.25s;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.08);
        }

        .card-img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            background: var(--bg);
            display: block;
        }

        .card-body {
            padding: 1rem 1.1rem 1.2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .card-name {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.3;
        }

        .card-price {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--accent-dark);
            margin-top: 0.1rem;
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.8rem;
        }

        .btn-view {
            flex: 1;
            text-align: center;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 500;
            padding: 0.5rem 0;
            border-radius: 8px;
            background: var(--text);
            color: #fff;
            transition: background 0.2s;
        }

        .btn-view:hover {
            background: #333;
        }

        .btn-wishlist-card {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            padding: 0.5rem 0.9rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-wishlist-card:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        /* EMPTY STATE */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 1rem;
            color: var(--muted);
        }

        .empty-state p {
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        /* TOAST */
        #toast {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            background: var(--text);
            color: #fff;
            padding: 0.75rem 1.3rem;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none;
            z-index: 999;
        }

        #toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 600px) {
            nav, .filter-bar, main { padding-left: 1.2rem; padding-right: 1.2rem; }
            .filter-bar form { gap: 0.5rem; }
            .btn-filter { margin-left: 0; width: 100%; }
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



<div class="filter-bar">
    <form method="GET">
        <label for="category">Category</label>
        <select name="category" id="category">
            <option value="">All</option>
            <option value="school bag"  <?= ($_GET['category'] ?? '') === 'School bag'  ? 'selected' : '' ?>>School bag</option>
            <option value="travel bag"  <?= ($_GET['category'] ?? '') === 'Travel bag'  ? 'selected' : '' ?>>Travel bag</option>
            <option value="hand bag"    <?= ($_GET['category'] ?? '') === 'Hand bag'    ? 'selected' : '' ?>>Hand bag</option>
        </select>

        <label for="color">Color</label>
<select name="color" id="color">
    <option value="">All</option>

    <?php foreach ($colorOptions as $color): ?>
        <option 
            value="<?= ucfirst($color) ?>" 
            <?= ($_GET['color'] ?? '') === ucfirst($color) ? 'selected' : '' ?>
        >
            <?= ucfirst($color) ?>
        </option>
    <?php endforeach; ?>
</select>

        <label for="gender">Gender</label>
        <select name="gender" id="gender">
            <option value="">All</option>
            <option value="male"  <?= ($_GET['gender'] ?? '') === 'male'  ? 'selected' : '' ?>>Mens</option>
            <option value="female" <?= ($_GET['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Women</option>
            <option value="child" <?= ($_GET['gender'] ?? '') === 'child' ? 'selected' : '' ?>>Child</option>
        </select>

        <button class="btn-filter" type="submit">Filter</button>
    </form>
</div>
<main>
    <div class="section-header">
        <h1>All Bags</h1>
        <span class="count"><?= count($bagsList) ?> item<?= count($bagsList) !== 1 ? 's' : '' ?></span>
    </div>

    <div class="grid">
        <?php if (empty($bagsList)): ?>
            <div class="empty-state">
                <strong>No bags found</strong>
                <p>Try adjusting your filters.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bagsList as $bag): ?>

            <?php
                // Load first image from images/{id}/
                $imgDir = "images/" . $bag['id'] . "/";
                $images = glob($imgDir . "*.{jpg,jpeg,png}", GLOB_BRACE);
                $imagePath = !empty($images) ? $images[0] : "images/default.jpg";
            ?>

            <div class="card">
                <img 
                    class="card-img" 
                    src="<?= htmlspecialchars($imagePath) ?>" 
                    alt="<?= htmlspecialchars($bag['name']) ?>"
                >

                <div class="card-body">
                    <div class="card-name"><?= htmlspecialchars($bag['name']) ?></div>
                    <div class="card-price">$<?= htmlspecialchars($bag['price']) ?></div>

                    <div class="card-actions">
                        <a class="btn-view" href="item.php?id=<?= $bag['id'] ?>">View</a>
                        <?php $isInWishlist = in_array($bag['id'], $wishlistIds); ?>

<button 
    class="btn-wishlist-card <?= $isInWishlist ? 'added' : '' ?>" 
    onclick="<?= $isInWishlist ? '' : "handleWishlist('{$bag['id']}')" ?>"
    <?= $isInWishlist ? 'disabled' : '' ?>
>
    ♥
</button>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<div id="toast"></div>

<script>

function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}
</script>
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