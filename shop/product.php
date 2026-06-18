<?php
require_once __DIR__ . '/includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$p  = getProductById($id);
if (!$p) { header('Location: ' . BASE_URL . '/'); exit; }
$_u     = currentUser();
$inWish = $_u ? wishHas($_u['id'], $id) : false;
$disc   = $p['old_price'] > 0 ? round((1 - $p['price'] / $p['old_price']) * 100) : 0;
$em     = $p['cat_icon'] ?? '🛍️';
$rel    = array_filter(getProducts(6, 0, $p['category_id']), function($r) use ($id){ return $r['id'] != $id; });
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($p['name']) ?> — <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="page-wrap">
<div class="container">

  <nav class="breadcrumb">
    <a href="<?= BASE_URL ?>/">Главная</a><span>›</span>
    <a href="<?= BASE_URL ?>/?cat=<?= $p['category_id'] ?>"><?= htmlspecialchars($p['cat_name'] ?? '') ?></a><span>›</span>
    <span><?= htmlspecialchars($p['name']) ?></span>
  </nav>

  <div class="prod-layout">
    <div class="prod-gallery">
      <?php if ($disc > 0): ?><span class="disc-badge">-<?= $disc ?>%</span><?php endif; ?>
      <?php if ($p['image']): ?>
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
      <?php else: ?>
        <span class="big-ei"><?= $em ?></span>
      <?php endif; ?>
    </div>

    <div class="prod-info">
      <p class="cat-tag"><?= htmlspecialchars($p['cat_name'] ?? '') ?></p>
      <h1><?= htmlspecialchars($p['name']) ?></h1>
      <div class="prod-rat">
        <span class="stars">⭐⭐⭐⭐⭐</span>
        <strong><?= $p['rating'] ?></strong>
        <span>(<?= number_format($p['reviews'], 0, '.', ' ') ?> отзывов)</span>
      </div>
      <div style="margin-bottom:13px">
        <span class="prod-price"><?= number_format($p['price'], 0, '.', ' ') ?> ₸</span>
        <?php if ($p['old_price'] > 0): ?>
          <span class="prod-old"><?= number_format($p['old_price'], 0, '.', ' ') ?> ₸</span>
          <div><span class="prod-save">Экономия: <?= number_format($p['old_price'] - $p['price'], 0, '.', ' ') ?> ₸</span></div>
        <?php endif; ?>
      </div>
      <div class="prod-stock <?= $p['stock'] > 0 ? 'in' : 'out' ?>">
        <?= $p['stock'] > 0 ? '✓ В наличии (' . $p['stock'] . ' шт.)' : '✗ Нет в наличии' ?>
      </div>
      <?php if ($p['description']): ?>
        <p class="prod-desc"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
      <?php endif; ?>

      <div class="qty-row">
        <label>Количество:</label>
        <div class="qty-ctrl">
          <button id="qMinus">−</button>
          <span id="qVal">1</span>
          <button id="qPlus">+</button>
        </div>
      </div>

      <div class="prod-actions">
        <button class="btn btn-primary btn-lg" id="addCartBtn" data-pid="<?= $p['id'] ?>">🛒 В корзину</button>
        <button class="btn-wish <?= $inWish ? 'on' : '' ?>" id="wishBtn" data-pid="<?= $p['id'] ?>"><?= $inWish ? '❤️' : '🤍' ?></button>
      </div>

      <div class="prod-delivery">
        <div class="pdel-item">🚚 <strong>Доставка:</strong> 1–3 дня по Казахстану</div>
        <div class="pdel-item">↩️ <strong>Возврат:</strong> 14 дней без вопросов</div>
        <div class="pdel-item">🔒 <strong>Оплата:</strong> Безопасно с баланса аккаунта</div>
      </div>
    </div>
  </div>

  <?php if ($rel): ?>
  <div style="margin-top:44px">
    <div class="sec-head"><h2 class="sec-title">Похожие товары</h2></div>
    <div class="pgrid">
      <?php foreach (array_slice($rel, 0, 4) as $r):
        $re = $r['cat_icon'] ?? '🛍️';
        $rd = $r['old_price'] > 0 ? round((1 - $r['price'] / $r['old_price']) * 100) : 0;
      ?>
      <div class="pcard-item" onclick="location.href='<?= BASE_URL ?>/product.php?id=<?= $r['id'] ?>'">
        <?php if ($rd > 0): ?><span class="disc-badge">-<?= $rd ?>%</span><?php endif; ?>
        <div class="pcard-item__img">
          <?php if ($r['image']): ?><img src="<?= htmlspecialchars($r['image']) ?>" loading="lazy"><?php else: ?><span class="ei"><?= $re ?></span><?php endif; ?>
        </div>
        <div class="pcard-item__body">
          <p class="pcard-item__name"><?= htmlspecialchars($r['name']) ?></p>
          <div class="prow"><span class="pprice"><?= number_format($r['price'], 0, '.', ' ') ?> ₸</span><?php if ($r['old_price'] > 0): ?><span class="pold"><?= number_format($r['old_price'], 0, '.', ' ') ?> ₸</span><?php endif; ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?>, logged: <?= $_u ? 'true' : 'false' ?> };
var _qty = 1;
document.getElementById('qMinus').onclick = function(){ if(_qty>1){_qty--;document.getElementById('qVal').textContent=_qty;} };
document.getElementById('qPlus').onclick  = function(){ _qty++;document.getElementById('qVal').textContent=_qty; };
document.getElementById('addCartBtn').onclick = function(){
  if(!SHOP.logged){openAuth();return;}
  var b=this;
  fetch(SHOP.base+'/pages/cart.php',{method:'POST',body:new URLSearchParams({action:'add',product_id:b.dataset.pid,qty:_qty})})
  .then(r=>r.json()).then(d=>{if(d.auth){openAuth();return;}if(d.success){setCartBadge(d.count);toast('Добавлено в корзину 🛒','ok');}else toast(d.message,'err');});
};
document.getElementById('wishBtn').onclick = function(){
  if(!SHOP.logged){openAuth();return;}
  var b=this;
  fetch(SHOP.base+'/pages/wishlist.php',{method:'POST',body:new URLSearchParams({action:'toggle',product_id:b.dataset.pid})})
  .then(r=>r.json()).then(d=>{if(d.auth){openAuth();return;}if(d.success){b.textContent=d.added?'❤️':'🤍';b.classList.toggle('on',d.added);setWishBadge(d.count);toast(d.added?'Добавлено в избранное':'Удалено из избранного');}});
};
</script>
<script src="assets/js/main.js"></script>
</body></html>