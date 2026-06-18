<?php
$cp = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-side">
  <div class="lw">
    <div class="logo">
      <span class="logo__icon"><?= htmlspecialchars(SHOP_LETTER) ?></span>
      <span class="logo__name"><?= htmlspecialchars(SHOP_NAME) ?></span>
    </div>
  </div>
  <nav class="aside-nav">
    <a href="index.php"     class="<?= $cp==='index.php'?'on':'' ?>"><span class="ic">📊</span> Дашборд</a>
    <a href="products.php"  class="<?= $cp==='products.php'?'on':'' ?>"><span class="ic">📦</span> Товары</a>
    <a href="categories.php" class="<?= $cp==='categories.php'?'on':'' ?>"><span class="ic">🏷️</span> Категории</a>
    <a href="orders.php"    class="<?= $cp==='orders.php'?'on':'' ?>"><span class="ic">🛒</span> Заказы</a>
    <a href="users.php"     class="<?= $cp==='users.php'?'on':'' ?>"><span class="ic">👥</span> Пользователи</a>
    <a href="<?= BASE_URL ?>/" style="margin-top:20px;opacity:.6"><span class="ic">←</span> На сайт</a>
  </nav>
</div>