<?php
require_once __DIR__ . '/includes/db.php';
if (!isLoggedIn()) { header('Location: ' . BASE_URL . '/?need_auth=1'); exit; }
$_u = currentUser();
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Избранное — <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="wish-page">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>/">Главная</a><span>›</span><span>Избранное</span>
    </nav>
    <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:22px">
      <h1 class="page-title" style="margin-bottom:0">❤️ Избранное</h1>
      <span id="wishCountLabel" style="font-size:13px;color:var(--text2)"></span>
    </div>
    <div id="wishlistWrap">
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:13px">
        <?php for($i=0;$i<6;$i++): ?>
        <div class="pcard-item" style="pointer-events:none">
          <div class="pcard-item__img skel skel-img"></div>
          <div class="pcard-item__body">
            <div class="skel skel-line" style="width:80%"></div>
            <div class="skel skel-line" style="width:55%;margin-top:8px"></div>
            <div class="skel skel-line" style="width:65%;margin-top:10px"></div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?>, logged: true };
</script>
<script src="assets/js/main.js"></script>
</body>
</html>