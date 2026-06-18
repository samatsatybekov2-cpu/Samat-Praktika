<?php
require_once __DIR__ . '/includes/db.php';
if (!isLoggedIn()) { header('Location: ' . BASE_URL . '/?need_auth=1'); exit; }
$_u  = currentUser();
$bal = getBalance($_u['id']);
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Корзина — <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="cart-page">
<div class="container">
  <nav class="breadcrumb"><a href="<?= BASE_URL ?>/">Главная</a><span>›</span><span>Корзина</span></nav>
  <h1 class="page-title">🛒 Корзина</h1>
  <div id="cartWrap"><div style="text-align:center;padding:40px;color:var(--text2)">Загрузка...</div></div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?>, logged: true };
window.USER_BALANCE = <?= $bal ?>;
document.addEventListener('DOMContentLoaded', function(){ loadCartPage(); });
</script>
<script src="assets/js/main.js"></script>
</body></html>