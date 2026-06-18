<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

if (isset($_GET['del']) && (int)$_GET['del'] !== (int)$_SESSION['uid']) {
    deleteUser((int)$_GET['del']);
    header('Location: users.php?deleted=1'); exit;
}

$page  = max(1, (int)($_GET['p'] ?? 1));
$users = getAllUsers(20, ($page-1)*20);
$total = qRow('SELECT COUNT(*) AS c FROM users');
$total = (int)($total['c'] ?? 0);
?><!DOCTYPE html>
<html lang="ru"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Пользователи — Админ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
</head><body>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-head">
    <h1>👥 Пользователи</h1>
    <span style="font-size:13px;color:var(--text2)">Всего: <?= $total ?></span>
  </div>

  <?php if (isset($_GET['deleted'])): ?><div style="background:#FFEBEE;color:#C62828;padding:10px 14px;border-radius:9px;margin-bottom:16px">Пользователь удалён</div><?php endif; ?>

  <div class="box">
    <table class="atable">
      <thead><tr><th>ID</th><th>Пользователь</th><th>Email</th><th>Роль</th><th>Баланс</th><th>Дата</th><th>Действия</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td>#<?= $u['id'] ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:8px">
            <div class="uav" style="width:32px;height:32px;font-size:13px;flex-shrink:0"><?= mb_strtoupper(mb_substr($u['name'],0,1)) ?></div>
            <strong style="font-size:13px"><?= htmlspecialchars($u['name']) ?></strong>
          </div>
        </td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td>
          <?php if ($u['role']==='admin'): ?>
            <span style="background:#FFF3E0;color:#E65100;padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700">⚙️ Админ</span>
          <?php else: ?>
            <span style="background:var(--brand-lt);color:var(--brand);padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700">👤 Юзер</span>
          <?php endif; ?>
        </td>
        <td><strong><?= number_format($u['balance'], 0, '.', ' ') ?> ₸</strong></td>
        <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
        <td>
          <?php if ($u['role'] !== 'admin'): ?>
          <a href="?del=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить пользователя <?= htmlspecialchars($u['name']) ?>?')">🗑️</a>
          <?php else: ?><span style="font-size:12px;color:var(--text2)">—</span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ($total > 20): ?>
    <div class="pagination" style="margin-top:16px">
      <?php for ($i=1; $i<=ceil($total/20); $i++): ?>
      <a href="?p=<?= $i ?>" class="pag-btn <?= $i===$page?'on':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
</div>
<div class="toast" id="toast"></div>
<script>window.SHOP={base:<?=json_encode(BASE_URL)?>};</script>
<script src="../assets/js/main.js"></script>
</body></html>