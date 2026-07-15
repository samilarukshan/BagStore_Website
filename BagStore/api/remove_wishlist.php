require '../db.php';

$wishlist->deleteOne([
    '_id' => new MongoDB\BSON\ObjectId($_POST['id'])
]);


// =========================
// 11. JAVASCRIPT
// =========================
// assets/script.js
function addWishlist(id) {
    fetch('api/add_wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'bag_id=' + id
    });
}

function removeWishlist(id) {
    fetch('api/remove_wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    }).then(()=>location.reload());
}