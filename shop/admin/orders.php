<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    $status  = $_POST['status'];
    if (in_array($status, $allowed)) {
        updateOrderStatus((int)$_POST['order_id'], $status);
    }
    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    header('Location: orders.php?saved=1'); exit;
}

$statusLabels = [
    'pending'    => ['label' => 'Ожидает',      'cls' => 'os-pending'],
    'processing' => ['label' => 'Обработка',    'cls' => 'os-processing'],
    'shipped'    => ['label' => 'Доставляется', 'cls' => 'os-shipped'],
    'delivered'  => ['label' => 'Доставлен',    'cls' => 'os-delivered'],
    'cancelled'  => ['label' => 'Отменён',      'cls' => 'os-cancelled'],
];

$view = isset($_GET['id']) ? getOrderById((int)$_GET['id']) : null;
$viewUser = $view ? getUserById($view['user_id']) : null;
$viewItems = $view ? getOrderItems($view['id']) : [];

$filterStatus = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['p'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

if ($filterStatus && $filterStatus !== 'all') {
    $ords  = qAll('SELECT o.*,u.name AS user_name,u.email FROM orders o JOIN users u ON o.user_id=u.id WHERE o.status=? ORDER BY o.created_at DESC LIMIT ? OFFSET ?', 'sii', $filterStatus, $limit, $offset);
    $total = (int)(qRow('SELECT COUNT(*) AS c FROM orders WHERE status=?', 's', $filterStatus)['c'] ?? 0);
} else {
    $ords  = getAllOrders($limit, $offset);
    $total = countOrders();
}

$statusCounts = [];
foreach (array_keys($statusLabels) as $s) {
    $r = qRow('SELECT COUNT(*) AS c FROM orders WHERE status=?', 's', $s);
    $statusCounts[$s] = (int)($r['c'] ?? 0);
}
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Заказы — Админ | <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
<style>
.status-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px}
.stab{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid var(--border);background:var(--white);color:var(--text2);transition:all var(--tr);display:flex;align-items:center;gap:5px}
.stab:hover,.stab.on{background:var(--brand-lt);color:var(--brand);border-color:var(--brand-lt)}
.stab .cnt{background:var(--brand);color:#fff;border-radius:10px;padding:0 6px;font-size:10px}
.stab.on .cnt{background:rgba(123,47,190,.2);color:var(--brand)}
.order-detail{background:var(--white);border-radius:var(--r);border:1px solid var(--border);margin-bottom:16px;overflow:hidden}
.od-head{background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.od-head h2{font-family:'Unbounded',sans-serif;font-size:16px;font-weight:700}
.od-body{padding:20px}
.od-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
@media(max-width:700px){.od-grid{grid-template-columns:1fr 1fr}}
.od-field label{font-size:11px;color:rgba(255,255,255,.7);display:block;margin-bottom:2px}
.od-field span{font-size:14px;font-weight:600}
.status-select-wrap{display:flex;gap:8px;align-items:center;margin-top:16px}
.status-select-wrap select{padding:8px 12px;border:2px solid var(--border);border-radius:9px;font-family:inherit;font-size:13px;font-weight:600}
.order-item-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
.order-item-row:last-child{border-bottom:none}
.order-item-row .oi-ico{width:44px;height:44px;border-radius:9px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.alert-ok{background:#E8F5E9;color:#2E7D32;padding:10px 14px;border-radius:9px;margin-bottom:16px}
.tr-click{cursor:pointer}
.tr-click:hover td{background:#f0ebff !important}
</style>
</head>
<body>
<div class="admin-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="admin-main">

    <div class="admin-head">
      <h1>🛒 Заказы</h1>
      <span style="font-size:13px;color:var(--text2)">Всего: <?= countOrders() ?></span>
    </div>

    <?php if (isset($_GET['saved'])): ?><div class="alert-ok">✓ Статус обновлён!</div><?php endif; ?>

    <?php if ($view): ?>
    <div class="order-detail">
      <div class="od-head">
        <div>
          <h2>Заказ #<?= $view['id'] ?></h2>
          <div style="font-size:12px;opacity:.8;margin-top:3px"><?= date('d.m.Y H:i', strtotime($view['created_at'])) ?></div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <span class="ostatus <?= $statusLabels[$view['status']]['cls'] ?? '' ?>"><?= $statusLabels[$view['status']]['label'] ?? $view['status'] ?></span>
          <a href="orders.php<?= $filterStatus ? '?status='.$filterStatus : '' ?>" class="btn btn-ghost btn-sm">← Назад</a>
        </div>
      </div>
      <div class="od-body">
        <div class="od-grid" style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:20px">
          <div>
            <div style="font-size:11px;color:var(--text2);margin-bottom:3px">Покупатель</div>
            <strong><?= htmlspecialchars($viewUser['name'] ?? '—') ?></strong>
            <div style="font-size:12px;color:var(--text2)"><?= htmlspecialchars($viewUser['email'] ?? '') ?></div>
          </div>
          <div>
            <div style="font-size:11px;color:var(--text2);margin-bottom:3px">Сумма заказа</div>
            <strong style="color:var(--brand);font-size:17px"><?= number_format($view['total'], 0, '.', ' ') ?> ₸</strong>
          </div>
          <div>
            <div style="font-size:11px;color:var(--text2);margin-bottom:3px">Адрес доставки</div>
            <strong><?= htmlspecialchars($view['address'] ?? 'Самовывоз') ?></strong>
          </div>
        </div>

        <h4 style="font-weight:700;font-size:14px;margin-bottom:12px">Состав заказа (<?= count($viewItems) ?> поз.)</h4>
        <?php foreach ($viewItems as $it): ?>
        <div class="order-item-row">
          <div class="oi-ico"><?= $it['cat_icon'] ?? '📦' ?></div>
          <div style="flex:1">
            <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($it['name']) ?></div>
            <div style="font-size:12px;color:var(--text2)"><?= $it['quantity'] ?> шт. × <?= number_format($it['price'], 0, '.', ' ') ?> ₸</div>
          </div>
          <div style="font-weight:700;font-size:14px;color:var(--brand)"><?= number_format($it['price'] * $it['quantity'], 0, '.', ' ') ?> ₸</div>
        </div>
        <?php endforeach; ?>

        <div style="border-top:2px solid var(--border);padding-top:12px;margin-top:4px;display:flex;justify-content:flex-end;gap:16px;font-size:15px">
          <span style="color:var(--text2)">Итого:</span>
          <strong style="color:var(--brand);font-size:18px"><?= number_format($view['total'], 0, '.', ' ') ?> ₸</strong>
        </div>

        <div style="border-top:1px solid var(--border);margin-top:18px;padding-top:16px">
          <h4 style="font-weight:700;font-size:13px;margin-bottom:10px">Изменить статус заказа</h4>
          <div class="status-select-wrap">
            <select id="orderStatusSel">
              <?php foreach ($statusLabels as $k => $v): ?>
              <option value="<?= $k ?>" <?= $view['status'] === $k ? 'selected' : '' ?>><?= $v['label'] ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-sm" onclick="changeStatus(<?= $view['id'] ?>)">Обновить статус</button>
            <span id="statusMsg" style="font-size:12px;color:#2E7D32;display:none">✓ Сохранено!</span>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="status-tabs">
      <a href="orders.php" class="stab <?= !$filterStatus || $filterStatus==='all' ? 'on' : '' ?>">
        Все <span class="cnt"><?= countOrders() ?></span>
      </a>
      <?php foreach ($statusLabels as $k => $v): ?>
      <a href="?status=<?= $k ?>" class="stab <?= $filterStatus===$k ? 'on' : '' ?>">
        <?= $v['label'] ?> <span class="cnt"><?= $statusCounts[$k] ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="box">
      <table class="atable">
        <thead>
          <tr><th>#</th><th>Покупатель</th><th>Сумма</th><th>Статус</th><th>Дата</th><th>Действие</th></tr>
        </thead>
        <tbody>
        <?php foreach ($ords as $o): ?>
        <tr class="tr-click" onclick="location.href='?id=<?= $o['id'] ?><?= $filterStatus ? '&status='.$filterStatus : '' ?>'">
          <td><strong>#<?= $o['id'] ?></strong></td>
          <td>
            <div style="font-weight:500;font-size:13px"><?= htmlspecialchars($o['user_name']) ?></div>
            <div style="font-size:11px;color:var(--text2)"><?= htmlspecialchars($o['email']) ?></div>
          </td>
          <td><strong style="color:var(--brand)"><?= number_format($o['total'], 0, '.', ' ') ?> ₸</strong></td>
          <td><span class="ostatus <?= $statusLabels[$o['status']]['cls'] ?? '' ?>"><?= $statusLabels[$o['status']]['label'] ?? $o['status'] ?></span></td>
          <td style="font-size:12px;color:var(--text2)"><?= date('d.m.Y', strtotime($o['created_at'])) ?><br><?= date('H:i', strtotime($o['created_at'])) ?></td>
          <td onclick="event.stopPropagation()">
            <div class="act-btns">
              <a href="?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">Детали</a>
              <select class="status-mini" onchange="quickStatus(<?= $o['id'] ?>, this.value, this)" style="padding:5px 8px;border:1px solid var(--border);border-radius:7px;font-size:11px;font-family:inherit">
                <?php foreach ($statusLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= $o['status']===$k ? 'selected' : '' ?>><?= $v['label'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$ords): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text2)">Заказов нет</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <?php if ($total > $limit): ?>
      <div class="pagination" style="margin-top:16px">
        <?php for ($i=1; $i<=ceil($total/$limit); $i++): ?>
        <a href="?p=<?= $i ?><?= $filterStatus ? '&status='.$filterStatus : '' ?>" class="pag-btn <?= $i===$page?'on':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<div class="toast" id="toast"></div>
<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?> };

function changeStatus(orderId) {
    var sel = document.getElementById('orderStatusSel');
    var msg = document.getElementById('statusMsg');
    fetch('orders.php', {
        method: 'POST',
        body: new URLSearchParams({ order_id: orderId, status: sel.value, ajax: '1' })
    }).then(function(r){ return r.json(); }).then(function(d) {
        if (d.success) {
            msg.style.display = 'inline';
            setTimeout(function(){ msg.style.display = 'none'; }, 2000);
        }
    });
}

function quickStatus(orderId, status, sel) {
    fetch('orders.php', {
        method: 'POST',
        body: new URLSearchParams({ order_id: orderId, status: status, ajax: '1' })
    }).then(function(r){ return r.json(); }).then(function(d) {
        if (d.success) {
            var row  = sel.closest('tr');
            var cell = row.querySelector('.ostatus');
            var labels = <?= json_encode(array_map(function($v){ return $v['label']; }, $statusLabels)) ?>;
            var classes = <?= json_encode(array_map(function($v){ return $v['cls']; }, $statusLabels)) ?>;
            if (cell) {
                cell.textContent = labels[status] || status;
                cell.className   = 'ostatus ' + (classes[status] || '');
            }
            sel.style.background = '#E8F5E9';
            setTimeout(function(){ sel.style.background = ''; }, 1000);
        }
    });
}
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>