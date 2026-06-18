<?php
require_once __DIR__ . '/includes/db.php';
$_u      = currentUser();
$_cat    = (int)($_GET['cat'] ?? 0);
$_sort   = $_GET['sort'] ?? 'newest';
$_total  = countProducts($_cat ?: null);
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(SHOP_NAME) ?> — <?= htmlspecialchars(SHOP_SLOGAN) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<?php if (!$_cat): ?>
<section class="hero">
  <div class="hero__bg"></div>
  <div class="hero__body">
    <div class="hero__text">
      <p class="tag">✨ Новая коллекция 2024</p>
      <h1>Найди всё<br><em>что нужно</em><br>в одном месте</h1>
      <p>Тысячи товаров с быстрой доставкой по Казахстану</p>
      <div class="btns">
        <button class="btn btn-primary" onclick="document.getElementById('products-sec').scrollIntoView({behavior:'smooth'})">Смотреть товары</button>
        <?php if (!$_u): ?><button class="btn btn-ghost" id="heroRegBtn">Регистрация</button><?php endif; ?>
      </div>
    </div>
    <div class="hero__cards">
      <div class="hcard"><span>📱</span><div><strong>Электроника</strong><small>от 890 ₸</small></div></div>
      <div class="hcard"><span>👗</span><div><strong>Одежда</strong><small>от 890 ₸</small></div></div>
      <div class="hcard"><span>🚚</span><div><strong>Доставка</strong><small>1–3 дня по РК</small></div></div>
    </div>
  </div>
</section>

<section class="promo">
  <div class="container">
    <div class="promo-grid">
      <div class="pcard pcard--purple">
        <div><div class="ptag">Только сегодня</div><h3>Скидки до 70%</h3><p>На электронику</p><button class="btn btn-white btn-sm" onclick="location.href='<?= BASE_URL ?>/?cat=1'">Перейти</button></div>
        <div class="ei">⚡</div>
      </div>
      <div class="pcard pcard--coral">
        <div><div class="ptag">Новинки</div><h3>Весенняя мода</h3><p>Коллекция 2024</p><button class="btn btn-white btn-sm" onclick="location.href='<?= BASE_URL ?>/?cat=2'">Смотреть</button></div>
        <div class="ei">🌸</div>
      </div>
      <div class="pcard pcard--teal">
        <div><div class="ptag">Доставка</div><h3>Бесплатно</h3><p>При заказе от 5 000 ₸</p><button class="btn btn-white btn-sm">Условия</button></div>
        <div class="ei">🚚</div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="sec" id="products-sec">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <?php if ($_cat):
          $cn = getCategoryById($_cat);
          echo htmlspecialchars($cn ? $cn['icon'] . ' ' . $cn['name'] : 'Товары');
        else: echo 'Популярные товары'; endif; ?>
      </h2>
      <span style="font-size:13px;color:var(--text2)"><?= $_total ?> товаров</span>
    </div>

    <div class="sort-bar">
      <span style="font-size:13px;font-weight:600;color:var(--text2)">Сортировка:</span>
      <?php foreach (['newest'=>'Новинки','popular'=>'Популярные','rating'=>'По рейтингу','price_asc'=>'Дешевле','price_desc'=>'Дороже'] as $k=>$v): ?>
      <button class="sort-btn <?= $_sort===$k?'on':'' ?>" onclick="changeSort('<?= $k ?>')"><?= $v ?></button>
      <?php endforeach; ?>
    </div>

    <div class="pgrid" id="productGrid">
      <?php for ($i=0;$i<8;$i++): ?>
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
    <div id="pagination"></div>
  </div>
</section>

<?php if (!$_cat): ?>
<section class="features">
  <div class="container">
    <div class="feat-grid">
      <div class="feat"><div class="ei">🚀</div><h4>Быстрая доставка</h4><p>По Казахстану 1–3 дня</p></div>
      <div class="feat"><div class="ei">🔒</div><h4>Безопасная оплата</h4><p>Оплата с баланса аккаунта</p></div>
      <div class="feat"><div class="ei">↩️</div><h4>Возврат 14 дней</h4><p>Без вопросов и лишних слов</p></div>
      <div class="feat"><div class="ei">🏆</div><h4>Гарантия качества</h4><p>Только оригинальные товары</p></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?>, logged: <?= $_u ? 'true' : 'false' ?> };
var _initCat  = <?= $_cat ?: 'null' ?>;
var _initSort = <?= json_encode($_sort) ?>;
function changeSort(s) {
  var url = new URL(location.href);
  url.searchParams.set('sort', s);
  location.href = url.toString();
}
document.addEventListener('DOMContentLoaded', function() {
  loadProducts(_initCat, _initSort, 1);
});
</script>
<script src="assets/js/main.js"></script>
</body></html>