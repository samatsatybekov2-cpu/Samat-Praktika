<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$users    = countUsers();
$products = countAllProducts();
$orders   = countOrders();
$revenue  = totalRevenue();
$recent   = getAllOrders(8);
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Админ-панель — <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
<div class="admin-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="admin-main">
    <div class="admin-head">
      <h1>📊 Дашборд</h1>
      <a href="<?= BASE_URL ?>/" class="btn btn-secondary btn-sm">← На сайт</a>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><span class="stat-card__ico">👥</span><div class="stat-card__val"><?= $users ?></div><div class="stat-card__lbl">Покупателей</div></div>
      <div class="stat-card"><span class="stat-card__ico">📦</span><div class="stat-card__val"><?= $products ?></div><div class="stat-card__lbl">Товаров</div></div>
      <div class="stat-card"><span class="stat-card__ico">🛒</span><div class="stat-card__val"><?= $orders ?></div><div class="stat-card__lbl">Заказов</div></div>
      <div class="stat-card"><span class="stat-card__ico">💰</span><div class="stat-card__val"><?= number_format($revenue, 0, '.', ' ') ?> ₸</div><div class="stat-card__lbl">Выручка</div></div>
    </div>

    <div class="box">
      <h3 style="font-family:'Unbounded',sans-serif;font-size:15px;font-weight:700;margin-bottom:16px">Последние заказы</h3>
      <?php if ($recent): ?>
      <table class="atable">
        <thead><tr><th>#</th><th>Покупатель</th><th>Сумма</th><th>Статус</th><th>Дата</th><th>Действие</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $o): ?>
        <tr>
          <td><strong>#<?= $o['id'] ?></strong></td>
          <td><?= htmlspecialchars($o['user_name']) ?><br><small style="color:var(--text2)"><?= htmlspecialchars($o['email']) ?></small></td>
          <td><strong><?= number_format($o['total'], 0, '.', ' ') ?> ₸</strong></td>
          <td><span class="ostatus os-<?= $o['status'] ?>"><?php $sl=['pending'=>'Ожидает','processing'=>'Обработка','shipped'=>'Доставляется','delivered'=>'Доставлен','cancelled'=>'Отменён']; echo $sl[$o['status']]??$o['status']; ?></span></td>
          <td><?= date('d.m.Y', strtotime($o['created_at'])) ?></td>
          <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">Детали</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?><p style="color:var(--text2);text-align:center;padding:24px">Заказов пока нет</p><?php endif; ?>
    </div>
  </div>
</div>
<div class="toast" id="toast"></div>
<script>window.SHOP={base:<?=json_encode(BASE_URL)?>};</script>
<script src="../assets/js/main.js"></script>
</body></html>