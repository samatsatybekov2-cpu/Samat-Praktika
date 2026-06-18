<?php
if (!function_exists('isLoggedIn')) require_once __DIR__ . '/db.php';
$_u    = currentUser();
$_cc   = $_u ? cartCount($_u['id']) : 0;
$_wc   = $_u ? wishCount($_u['id']) : 0;
$_bal  = $_u ? getBalance($_u['id']) : 0;
$_cats = getCategories();
$_cur  = basename($_SERVER['PHP_SELF']);
$_ccat = (int)($_GET['cat'] ?? 0);
?>
<header class="header" id="header">
  <div class="header__inner">

    <button class="burger" id="burger" aria-label="Меню">
      <span></span><span></span><span></span>
    </button>

    <a href="<?= BASE_URL ?>/" class="logo">
      <span class="logo__icon"><?= htmlspecialchars(SHOP_LETTER) ?></span>
      <span class="logo__name"><?= htmlspecialchars(SHOP_NAME) ?></span>
    </a>

    <div class="search-wrap">
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Найти товары, бренды...">
        <button id="searchBtn" aria-label="Найти">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </button>
      </div>
      <div class="search-drop" id="searchDrop"></div>
    </div>

    <nav class="header__actions">

      <?php if ($_u): ?>
      <div class="bal-chip">
        💳 <span id="hdrBalance"><?= number_format($_bal, 0, '.', ' ') ?></span> ₸
      </div>
      <?php endif; ?>

      <a href="<?= BASE_URL ?>/wishlist.php" class="hbtn <?= $_cur === 'wishlist.php' ? 'active' : '' ?>" title="Избранное">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <?php if ($_wc > 0): ?><span class="badge" id="wishBadge"><?= $_wc ?></span><?php else: ?><span class="badge" id="wishBadge" style="display:none">0</span><?php endif; ?>
        <span class="lbl">Избранное</span>
      </a>

      <a href="<?= BASE_URL ?>/cart.php" class="hbtn <?= $_cur === 'cart.php' ? 'active' : '' ?>" title="Корзина">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <?php if ($_cc > 0): ?><span class="badge" id="cartBadge"><?= $_cc ?></span><?php else: ?><span class="badge" id="cartBadge" style="display:none">0</span><?php endif; ?>
        <span class="lbl">Корзина</span>
      </a>

      <?php if ($_u): ?>
      <div class="prof-wrap" id="profWrap">
        <button class="prof-btn" id="profBtn" title="Профиль">
          <div class="uav"><?= mb_strtoupper(mb_substr($_u['name'], 0, 1)) ?></div>
          <span class="lbl"><?= htmlspecialchars(explode(' ', $_u['name'])[0]) ?></span>
        </button>
        <div class="prof-drop" id="profDrop">
          <div class="prof-drop__head">
            <strong><?= htmlspecialchars($_u['name']) ?></strong>
            <small><?= htmlspecialchars($_u['email']) ?></small>
            <div class="bal">💳 <?= number_format($_bal, 0, '.', ' ') ?> ₸</div>
          </div>
          <a href="<?= BASE_URL ?>/profile.php" class="pdrop-item">👤 Мой профиль</a>
          <a href="<?= BASE_URL ?>/profile.php?tab=orders" class="pdrop-item">📦 Мои заказы</a>
          <a href="<?= BASE_URL ?>/profile.php?tab=balance" class="pdrop-item">💳 Баланс</a>
          <a href="<?= BASE_URL ?>/wishlist.php" class="pdrop-item">❤️ Избранное</a>
          <?php if ($_u['role'] === 'admin'): ?>
          <div class="pdrop-div"></div>
          <a href="<?= BASE_URL ?>/admin/" class="pdrop-item adm">⚙️ Админ-панель</a>
          <?php endif; ?>
          <div class="pdrop-div"></div>
          <button class="pdrop-item red" id="logoutBtn">🚪 Выйти</button>
        </div>
      </div>
      <?php else: ?>
      <button class="hbtn" id="profileOpenAuth" title="Войти">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <span class="lbl">Войти</span>
      </button>
      <?php endif; ?>

    </nav>
  </div>

  <div class="cat-nav">
    <div class="cat-nav__inner">
      <a href="<?= BASE_URL ?>/" class="cat-lnk <?= (!$_ccat && $_cur === 'index.php') ? 'on' : '' ?>">Все товары</a>
      <?php foreach ($_cats as $c): ?>
      <a href="<?= BASE_URL ?>/?cat=<?= $c['id'] ?>" class="cat-lnk <?= ($_ccat == $c['id']) ? 'on' : '' ?>">
        <?= $c['icon'] ?> <?= htmlspecialchars($c['name']) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</header>

<div class="drawer-overlay" id="drawerOv"></div>
<div class="drawer" id="drawer">
  <div class="drawer__head">
    <h3><?= htmlspecialchars(SHOP_NAME) ?></h3>
    <button class="drawer__close" id="drawerClose">✕</button>
  </div>

  <?php if ($_u): ?>
  <div class="drawer__user">
    <div class="uav"><?= mb_strtoupper(mb_substr($_u['name'], 0, 1)) ?></div>
    <div class="info">
      <strong><?= htmlspecialchars($_u['name']) ?></strong>
      <small><?= htmlspecialchars($_u['email']) ?></small>
      <div class="bal">💳 <?= number_format($_bal, 0, '.', ' ') ?> ₸</div>
    </div>
  </div>
  <div class="drawer__nav">
    <a href="<?= BASE_URL ?>/profile.php"><span class="icon">👤</span> Мой профиль</a>
    <a href="<?= BASE_URL ?>/profile.php?tab=orders"><span class="icon">📦</span> Заказы</a>
    <a href="<?= BASE_URL ?>/profile.php?tab=balance"><span class="icon">💳</span> Баланс</a>
    <a href="<?= BASE_URL ?>/wishlist.php"><span class="icon">❤️</span> Избранное
      <?php if ($_wc > 0): ?> (<?= $_wc ?>)<?php endif; ?></a>
    <a href="<?= BASE_URL ?>/cart.php"><span class="icon">🛒</span> Корзина
      <?php if ($_cc > 0): ?> (<?= $_cc ?>)<?php endif; ?></a>
    <?php if ($_u['role'] === 'admin'): ?>
    <a href="<?= BASE_URL ?>/admin/" class="adm"><span class="icon">⚙️</span> Админ-панель</a>
    <?php endif; ?>
    <button id="drawerLogout" class="red"><span class="icon">🚪</span> Выйти</button>
  </div>
  <?php else: ?>
  <div class="drawer__nav">
    <a href="#" onclick="closeDrawer && closeDrawer(); openAuth('login'); return false;"><span class="icon">🔑</span> Войти</a>
    <a href="#" onclick="closeDrawer && closeDrawer(); openAuth('register'); return false;"><span class="icon">✨</span> Регистрация</a>
  </div>
  <?php endif; ?>

  <div class="drawer__cats">Категории</div>
  <div class="drawer__nav">
    <a href="<?= BASE_URL ?>/"><span class="icon">🛍️</span> Все товары</a>
    <?php foreach ($_cats as $c): ?>
    <a href="<?= BASE_URL ?>/?cat=<?= $c['id'] ?>"><span class="icon"><?= $c['icon'] ?></span> <?= htmlspecialchars($c['name']) ?></a>
    <?php endforeach; ?>
  </div>
</div>