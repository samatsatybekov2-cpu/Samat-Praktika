<?php
require_once __DIR__ . '/includes/db.php';
if (!isLoggedIn()) { header('Location: ' . BASE_URL . '/?need_auth=1'); exit; }
$_u   = currentUser();
$tab  = $_GET['tab'] ?? 'profile';
$bal  = getBalance($_u['id']);
$blog = getBalanceLog($_u['id']);
$ords = getOrders($_u['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_profile') {
    $name = trim($_POST['name'] ?? '');
    if ($name) {
        q('UPDATE users SET name=? WHERE id=?', 'si', $name, (int)$_u['id']);
        $_SESSION['name'] = $name;
        header('Location: ' . BASE_URL . '/profile.php?tab=profile&saved=1'); exit;
    }
}
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Профиль — <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="page-wrap">
<div class="container">

  <div class="prof-header">
    <div class="prof-avatar-big"><?= mb_strtoupper(mb_substr($_u['name'], 0, 1)) ?></div>
    <div class="prof-header__info">
      <h2><?= htmlspecialchars($_u['name']) ?></h2>
      <p><?= htmlspecialchars($_u['email']) ?></p>
      <span class="role-badge <?= $_u['role'] === 'admin' ? 'admin' : '' ?>"><?= $_u['role'] === 'admin' ? '⚙️ Администратор' : '👤 Покупатель' ?></span>
    </div>
  </div>

  <div class="tab-nav">
    <a href="?tab=profile"  class="<?= $tab==='profile'?'on':'' ?>">👤 Профиль</a>
    <a href="?tab=balance"  class="<?= $tab==='balance'?'on':'' ?>">💳 Баланс</a>
    <a href="?tab=orders"   class="<?= $tab==='orders'?'on':'' ?>">📦 Заказы</a>
  </div>

  <?php if ($tab === 'profile'): ?>
  <div class="box">
    <?php if (isset($_GET['saved'])): ?><div class="fmsg ok" style="display:block;margin-bottom:14px">✓ Данные сохранены</div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="save_profile">
      <div class="fg"><label>Имя</label><input type="text" name="name" value="<?= htmlspecialchars($_u['name']) ?>" required></div>
      <div class="fg"><label>Email</label><input type="email" value="<?= htmlspecialchars($_u['email']) ?>" disabled style="opacity:.6"></div>
      <div class="fg"><label>Дата регистрации</label><input type="text" value="<?= date('d.m.Y', strtotime($_u['created_at'])) ?>" disabled style="opacity:.6"></div>
      <button type="submit" class="btn btn-primary">Сохранить</button>
    </form>
  </div>

  <?php elseif ($tab === 'balance'): ?>
  <div class="bal-block">
    <div class="bal-block__lbl">Ваш баланс</div>
    <div class="bal-block__amt" id="balAmt"><?= number_format($bal, 0, '.', ' ') ?> ₸</div>
    <div class="topup-row">
      <input type="number" id="topupAmt" placeholder="Введите сумму" min="1" max="9999999" style="border-radius:9px;border:none;padding:9px 13px;font-size:14px;color:var(--text);flex:1">
      <button class="btn btn-white" onclick="doTopup()"><span>Пополнить</span><div class="spin"></div></button>
    </div>
    <div class="quick-amts">
      <?php foreach ([1000,5000,10000,50000] as $a): ?>
      <button class="qa-btn" onclick="document.getElementById('topupAmt').value=<?= $a ?>"><?= number_format($a, 0, '.', ' ') ?> ₸</button>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="box">
    <h3 style="font-weight:700;font-size:15px;margin-bottom:14px">История операций</h3>
    <?php if ($blog): ?>
    <div class="bal-log">
      <?php foreach ($blog as $b): ?>
      <div class="bal-log-item">
        <div>
          <div style="font-weight:500;font-size:13px"><?= htmlspecialchars($b['note'] ?? '') ?></div>
          <div style="font-size:11px;color:var(--text2)"><?= date('d.m.Y H:i', strtotime($b['created_at'])) ?></div>
        </div>
        <div class="amt <?= $b['type']==='topup'?'p':'m' ?>"><?= $b['type']==='topup'?'+':'-' ?><?= number_format($b['amount'], 0, '.', ' ') ?> ₸</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--text2);text-align:center;padding:20px">Операций пока нет</p>
    <?php endif; ?>
  </div>

  <?php elseif ($tab === 'orders'): ?>
  <?php if ($ords): ?>
    <?php foreach ($ords as $o):
      $oi = getOrderItems($o['id']);
    ?>
    <div class="order-card">
      <div class="order-card__head">
        <div>
          <div class="order-card__id">Заказ #<?= $o['id'] ?></div>
          <div style="font-size:12px;color:var(--text2)"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></div>
        </div>
        <span class="ostatus os-<?= $o['status'] ?>"><?php
          $sl = ['pending'=>'Ожидает','processing'=>'Обработка','shipped'=>'Доставляется','delivered'=>'Доставлен','cancelled'=>'Отменён'];
          echo $sl[$o['status']] ?? $o['status'];
        ?></span>
      </div>
      <div class="oitems-prev">
        <?php foreach ($oi as $it): ?>
        <div class="oip"><?= $it['cat_icon'] ?? '🛍️' ?></div>
        <?php endforeach; ?>
      </div>
      <div class="order-footer">
        <span><?= count($oi) ?> товар(а)</span>
        <strong><?= number_format($o['total'], 0, '.', ' ') ?> ₸</strong>
      </div>
      <?php if ($o['address'] && $o['address'] !== 'Самовывоз'): ?>
      <div style="font-size:12px;color:var(--text2);margin-top:6px">📍 <?= htmlspecialchars($o['address']) ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
  <div class="box" style="text-align:center;padding:44px">
    <div style="font-size:48px;margin-bottom:12px">📦</div>
    <h3 style="margin-bottom:8px">Заказов пока нет</h3>
    <a href="<?= BASE_URL ?>/" class="btn btn-primary" style="margin-top:10px">Перейти к покупкам</a>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?>, logged: true };
function doTopup() {
  var amt = parseFloat(document.getElementById('topupAmt').value);
  var btn = document.querySelector('.bal-block .btn-white');
  if (!amt || amt <= 0) { toast('Введите сумму', 'err'); return; }
  btn.disabled = true;
  btn.querySelector('.spin').style.display = 'block';
  btn.querySelector('span').style.display  = 'none';
  fetch(SHOP.base + '/pages/balance.php', { method:'POST', body: new URLSearchParams({action:'topup', amount:amt}) })
  .then(r=>r.json()).then(d=>{
    btn.disabled = false;
    btn.querySelector('.spin').style.display = 'none';
    btn.querySelector('span').style.display  = '';
    if (d.success) {
      document.getElementById('balAmt').textContent = Number(d.balance).toLocaleString('ru-KZ') + ' ₸';
      setBalance(d.balance);
      toast('Баланс пополнен! ✓', 'ok');
      document.getElementById('topupAmt').value = '';
      setTimeout(function(){ location.reload(); }, 1200);
    } else { toast(d.message || 'Ошибка', 'err'); }
  });
}
document.getElementById('topupAmt') && document.getElementById('topupAmt').addEventListener('keydown', function(e){ if(e.key==='Enter') doTopup(); });
</script>
<script src="assets/js/main.js"></script>
</body></html>