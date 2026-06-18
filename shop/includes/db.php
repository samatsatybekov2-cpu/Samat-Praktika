<?php
require_once __DIR__ . '/config.php';

/* Подключение */
function db() {
    static $c = null;
    if ($c === null) {
        $c = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $c->set_charset(DB_CHARSET);
        if ($c->connect_error) die('DB Error: ' . $c->connect_error);
    }
    return $c;
}

/*  Запрос PHP 7.2  */
function q($sql, $types = '', ...$args) {
    $st = db()->prepare($sql);
    if (!$st) {
        error_log('DB prepare error: ' . db()->error . ' | SQL: ' . $sql);
        return false;
    }
    if ($types !== '' && count($args) > 0) {
        $st->bind_param($types, ...$args);
    }
    $st->execute();
    $r = $st->get_result();
    return ($r !== false) ? $r : true;
}
function qRow($sql, $types = '', ...$args) {
    $r = q($sql, $types, ...$args);
    if (!$r || $r === true) return null;
    return $r->fetch_assoc() ?: null;
}
function qAll($sql, $types = '', ...$args) {
    $r = q($sql, $types, ...$args);
    if (!$r || $r === true) return [];
    $rows = [];
    while ($row = $r->fetch_assoc()) $rows[] = $row;
    return $rows;
}

/* Сессия */
function isLoggedIn() { return !empty($_SESSION['uid']); }
function isAdmin()    { return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function currentUser() {
    if (!isLoggedIn()) return null;
    return qRow('SELECT * FROM users WHERE id=? LIMIT 1', 'i', (int)$_SESSION['uid']);
}
function requireLogin() {
    if (!isLoggedIn()) { header('Location: ' . BASE_URL . '/?need_auth=1'); exit; }
}
function requireAdmin() {
    if (!isAdmin()) { header('Location: ' . BASE_URL . '/'); exit; }
}

/* Пользователи */
function getUserByEmail($email) {
    return qRow('SELECT * FROM users WHERE email=? LIMIT 1', 's', $email);
}
function getUserById($id) {
    return qRow('SELECT * FROM users WHERE id=? LIMIT 1', 'i', (int)$id);
}
function getAllUsers($limit = 100, $offset = 0) {
    return qAll('SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?', 'ii', (int)$limit, (int)$offset);
}
function countUsers() {
    $r = qRow('SELECT COUNT(*) AS c FROM users WHERE role="user"');
    return (int)($r['c'] ?? 0);
}
function createUser($name, $email, $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    q('INSERT INTO users (name,email,password_hash,created_at) VALUES (?,?,?,NOW())', 'sss', $name, $email, $hash);
    return db()->insert_id;
}
function deleteUser($id) {
    q('DELETE FROM users WHERE id=? AND role!="admin"', 'i', (int)$id);
}

/* Баланс */
function getBalance($uid) {
    $r = qRow('SELECT balance FROM users WHERE id=?', 'i', (int)$uid);
    return floatval($r['balance'] ?? 0);
}
function topupBalance($uid, $amount) {
    q('UPDATE users SET balance=balance+? WHERE id=?', 'di', floatval($amount), (int)$uid);
    q('INSERT INTO balance_log (user_id,amount,type,note,created_at) VALUES (?,?,"topup","Пополнение",NOW())', 'id', (int)$uid, floatval($amount));
}
function chargeBalance($uid, $amount, $note = '') {
    q('UPDATE users SET balance=balance-? WHERE id=?', 'di', floatval($amount), (int)$uid);
    q('INSERT INTO balance_log (user_id,amount,type,note,created_at) VALUES (?,?,"charge",?,NOW())', 'ids', (int)$uid, floatval($amount), $note);
}
function getBalanceLog($uid) {
    return qAll('SELECT * FROM balance_log WHERE user_id=? ORDER BY created_at DESC LIMIT 30', 'i', (int)$uid);
}

/* Категории */
function getCategories() {
    return qAll('SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order ASC');
}
function getAllCategories() {
    return qAll('SELECT * FROM categories ORDER BY sort_order ASC');
}
function getCategoryById($id) {
    return qRow('SELECT * FROM categories WHERE id=?', 'i', (int)$id);
}
function createCategory($name, $slug, $icon, $sort) {
    $existing = qRow('SELECT id FROM categories WHERE slug=?', 's', $slug);
    if ($existing) $slug = $slug . '-' . time();
    q('INSERT INTO categories (name,slug,icon,sort_order,is_active) VALUES (?,?,?,?,1)', 'sssi', $name, $slug, $icon, (int)$sort);
    return db()->insert_id;
}
function updateCategory($id, $name, $slug, $icon, $sort, $active) {
    q('UPDATE categories SET name=?,slug=?,icon=?,sort_order=?,is_active=? WHERE id=?', 'sssiii', $name, $slug, $icon, (int)$sort, (int)$active, (int)$id);
}
function deleteCategory($id) {
    q('DELETE FROM categories WHERE id=?', 'i', (int)$id);
}

/* Товары */
function getProducts($limit = 20, $offset = 0, $cat = null, $sort = 'newest') {
    $ord = ['newest'=>'p.id DESC','price_asc'=>'p.price ASC','price_desc'=>'p.price DESC','rating'=>'p.rating DESC','popular'=>'p.reviews DESC'];
    $o   = $ord[$sort] ?? 'p.id DESC';
    $sql = "SELECT p.*,c.name AS cat_name,c.icon AS cat_icon FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.is_active=1";
    if ($cat) return qAll("$sql AND p.category_id=? ORDER BY $o LIMIT ? OFFSET ?", 'iii', (int)$cat, (int)$limit, (int)$offset);
    return qAll("$sql ORDER BY $o LIMIT ? OFFSET ?", 'ii', (int)$limit, (int)$offset);
}
function countProducts($cat = null) {
    if ($cat) $r = qRow('SELECT COUNT(*) AS c FROM products WHERE is_active=1 AND category_id=?', 'i', (int)$cat);
    else      $r = qRow('SELECT COUNT(*) AS c FROM products WHERE is_active=1');
    return (int)($r['c'] ?? 0);
}
function getFeatured($limit = 8) {
    return qAll('SELECT p.*,c.name AS cat_name,c.icon AS cat_icon FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.is_featured=1 AND p.is_active=1 ORDER BY RAND() LIMIT ?', 'i', (int)$limit);
}
function getProductById($id) {
    return qRow('SELECT p.*,c.name AS cat_name,c.icon AS cat_icon FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.id=? LIMIT 1', 'i', (int)$id);
}
function searchProducts($term, $limit = 20) {
    $l = '%' . $term . '%';
    return qAll('SELECT p.*,c.name AS cat_name,c.icon AS cat_icon FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE (p.name LIKE ? OR p.description LIKE ?) AND p.is_active=1 LIMIT ?', 'ssi', $l, $l, (int)$limit);
}
function createProduct($d) {
    q('INSERT INTO products (category_id,name,description,price,old_price,image,stock,is_featured,is_active,created_at) VALUES (?,?,?,?,?,?,?,?,1,NOW())',
      'issddsii',
      (int)$d['category_id'],
      $d['name'],
      $d['description'],
      floatval($d['price']),
      floatval($d['old_price'] ?? 0),
      $d['image'] ?? '',
      (int)$d['stock'],
      (int)($d['is_featured'] ?? 0)
    );
    return db()->insert_id;
}
function updateProduct($id, $d) {
    q('UPDATE products SET category_id=?,name=?,description=?,price=?,old_price=?,image=?,stock=?,is_featured=?,is_active=? WHERE id=?',
      'issddsiiii',
      (int)$d['category_id'],
      $d['name'],
      $d['description'],
      floatval($d['price']),
      floatval($d['old_price'] ?? 0),
      $d['image'] ?? '',
      (int)$d['stock'],
      (int)($d['is_featured'] ?? 0),
      (int)($d['is_active'] ?? 1),
      (int)$id
    );
}
function deleteProduct($id) {
    q('UPDATE products SET is_active=0 WHERE id=?', 'i', (int)$id);
}
function countAllProducts() {
    $r = qRow('SELECT COUNT(*) AS c FROM products WHERE is_active=1');
    return (int)($r['c'] ?? 0);
}

/* Корзина */
function getCart($uid) {
    return qAll('SELECT c.*,p.name,p.price,p.old_price,p.image,p.stock,p.is_active,cat.name AS cat_name,cat.icon AS cat_icon FROM cart c JOIN products p ON c.product_id=p.id LEFT JOIN categories cat ON p.category_id=cat.id WHERE c.user_id=? ORDER BY c.id DESC', 'i', (int)$uid);
}
function cartAdd($uid, $pid, $qty = 1) {
    $r = qRow('SELECT id,quantity FROM cart WHERE user_id=? AND product_id=?', 'ii', (int)$uid, (int)$pid);
    if ($r) q('UPDATE cart SET quantity=quantity+? WHERE id=?', 'ii', (int)$qty, (int)$r['id']);
    else    q('INSERT INTO cart (user_id,product_id,quantity) VALUES (?,?,?)', 'iii', (int)$uid, (int)$pid, (int)$qty);
}
function cartUpdate($uid, $pid, $qty) {
    if ($qty <= 0) q('DELETE FROM cart WHERE user_id=? AND product_id=?', 'ii', (int)$uid, (int)$pid);
    else           q('UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?', 'iii', (int)$qty, (int)$uid, (int)$pid);
}
function cartRemove($uid, $pid) { q('DELETE FROM cart WHERE user_id=? AND product_id=?', 'ii', (int)$uid, (int)$pid); }
function cartClear($uid)        { q('DELETE FROM cart WHERE user_id=?', 'i', (int)$uid); }
function cartCount($uid)        { $r = qRow('SELECT SUM(quantity) AS c FROM cart WHERE user_id=?', 'i', (int)$uid); return (int)($r['c'] ?? 0); }
function cartTotal($uid)        { $r = qRow('SELECT SUM(p.price*c.quantity) AS t FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?', 'i', (int)$uid); return floatval($r['t'] ?? 0); }

/* Получить id товаров в корзине пользователя */
function getCartProductIds($uid) {
    $rows = qAll('SELECT product_id FROM cart WHERE user_id=?', 'i', (int)$uid);
    return array_column($rows, 'product_id');
}

/* Избранное */
function getWishlist($uid) {
    return qAll('SELECT w.*,p.name,p.price,p.old_price,p.image,p.rating,p.reviews,cat.name AS cat_name,cat.icon AS cat_icon FROM wishlist w JOIN products p ON w.product_id=p.id LEFT JOIN categories cat ON p.category_id=cat.id WHERE w.user_id=? ORDER BY w.id DESC', 'i', (int)$uid);
}
function wishToggle($uid, $pid) {
    $r = qRow('SELECT id FROM wishlist WHERE user_id=? AND product_id=?', 'ii', (int)$uid, (int)$pid);
    if ($r) {
        q('DELETE FROM wishlist WHERE user_id=? AND product_id=?', 'ii', (int)$uid, (int)$pid);
        return false;
    }
    q('INSERT INTO wishlist (user_id,product_id) VALUES (?,?)', 'ii', (int)$uid, (int)$pid);
    return true;
}
function wishHas($uid, $pid) { return (bool)qRow('SELECT id FROM wishlist WHERE user_id=? AND product_id=?', 'ii', (int)$uid, (int)$pid); }
function wishCount($uid)     { $r = qRow('SELECT COUNT(*) AS c FROM wishlist WHERE user_id=?', 'i', (int)$uid); return (int)($r['c'] ?? 0); }

/* Получить id товаров в избранном */
function getWishlistProductIds($uid) {
    $rows = qAll('SELECT product_id FROM wishlist WHERE user_id=?', 'i', (int)$uid);
    return array_column($rows, 'product_id');
}

/* Заказы */
function createOrder($uid, $address, $items, $total) {
    q('INSERT INTO orders (user_id,total,status,address,created_at) VALUES (?,?,"pending",?,NOW())', 'ids', (int)$uid, floatval($total), $address);
    $oid = db()->insert_id;
    foreach ($items as $it) {
        q('INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)', 'iiid', (int)$oid, (int)$it['product_id'], (int)$it['quantity'], floatval($it['price']));
    }
    return $oid;
}
function getOrders($uid)        { return qAll('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC', 'i', (int)$uid); }
function getOrderById($id)      { return qRow('SELECT * FROM orders WHERE id=?', 'i', (int)$id); }
function getOrderItems($oid)    { return qAll('SELECT oi.*,p.name,p.image,cat.icon AS cat_icon FROM order_items oi JOIN products p ON oi.product_id=p.id LEFT JOIN categories cat ON p.category_id=cat.id WHERE oi.order_id=?', 'i', (int)$oid); }
function getAllOrders($l=50,$o=0){ return qAll('SELECT or2.*,u.name AS user_name,u.email FROM orders or2 JOIN users u ON or2.user_id=u.id ORDER BY or2.created_at DESC LIMIT ? OFFSET ?', 'ii', (int)$l, (int)$o); }
function updateOrderStatus($id, $s) { q('UPDATE orders SET status=? WHERE id=?', 'si', $s, (int)$id); }
function countOrders()          { $r = qRow('SELECT COUNT(*) AS c FROM orders'); return (int)($r['c'] ?? 0); }
function totalRevenue()         { $r = qRow('SELECT SUM(total) AS t FROM orders WHERE status!="cancelled"'); return floatval($r['t'] ?? 0); }