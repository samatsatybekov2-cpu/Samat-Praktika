<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

$msg = '';

if (isset($_GET['del'])) {
    deleteCategory((int)$_GET['del']);
    header('Location: categories.php?deleted=1'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $icon   = trim($_POST['icon'] ?? '🛍️');
    $sort   = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;
    $slug   = trim($_POST['slug'] ?? '');
    if (!$slug) {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $name)));
        $slug = trim($slug, '-') ?: 'cat-' . time();
    }

    if (!$name) {
        $msg = '<div class="alert-err">Введите название категории</div>';
    } else {
        $eid = (int)($_POST['id'] ?? 0);
        if ($eid > 0) {
            updateCategory($eid, $name, $slug, $icon, $sort, $active);
        } else {
            createCategory($name, $slug, $icon, $sort);
        }
        header('Location: categories.php?saved=1'); exit;
    }
}

$edit = isset($_GET['edit']) ? getCategoryById((int)$_GET['edit']) : null;
$cats = getAllCategories();

$icons = ['📱','👗','👟','🏡','💄','⚽','🧸','🛒','🎮','📚','🍕','🚗','✈️','💻','📷','🎵','🌸','🏋️','🧴','🎁','🔧','💎','🏠','🐾','🌿'];
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Категории — Админ | <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
<style>
.icon-picker{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.icon-opt{width:36px;height:36px;border-radius:8px;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;transition:all var(--tr);background:var(--white)}
.icon-opt:hover,.icon-opt.on{border-color:var(--brand);background:var(--brand-lt)}
.icon-preview{font-size:28px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border:2px solid var(--border);border-radius:10px;flex-shrink:0}
.alert-ok{background:#E8F5E9;color:#2E7D32;padding:10px 14px;border-radius:9px;margin-bottom:16px}
.alert-err{background:#FFEBEE;color:#C62828;padding:10px 14px;border-radius:9px;margin-bottom:16px}
.cat-row-icon{width:36px;height:36px;border-radius:8px;background:var(--brand-lt);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
</style>
</head>
<body>
<div class="admin-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="admin-main">

    <div class="admin-head">
      <h1>🏷️ Категории</h1>
      <button class="btn btn-primary btn-sm" id="toggleFormBtn" onclick="toggleCatForm()">+ Добавить категорию</button>
    </div>

    <?php if (isset($_GET['saved'])): ?><div class="alert-ok">✓ Сохранено!</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert-err">Категория удалена</div><?php endif; ?>
    <?php echo $msg; ?>

    <div class="box" id="catForm" style="margin-bottom:18px;<?= $edit ? '' : 'display:none' ?>">
      <h3 style="font-weight:700;font-size:15px;margin-bottom:18px"><?= $edit ? '✏️ Редактировать категорию' : '+ Новая категория' ?></h3>
      <form method="POST">
        <input type="hidden" name="id" value="<?= $edit ? $edit['id'] : 0 ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="fg">
            <label>Название *</label>
            <input type="text" name="name" id="catName" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required placeholder="Например: Электроника">
          </div>
          <div class="fg">
            <label>Slug (авто)</label>
            <input type="text" name="slug" id="catSlug" value="<?= htmlspecialchars($edit['slug'] ?? '') ?>" placeholder="electronics">
          </div>
          <div class="fg">
            <label>Порядок сортировки</label>
            <input type="number" name="sort_order" min="0" value="<?= $edit['sort_order'] ?? count($cats) + 1 ?>">
          </div>
          <div class="fg">
            <label>Активна</label>
            <div style="padding:9px 0">
              <label class="chk" style="font-size:14px">
                <input type="checkbox" name="is_active" <?= (!$edit || $edit['is_active']) ? 'checked' : '' ?>>
                Показывать в меню
              </label>
            </div>
          </div>
        </div>

        <div class="fg">
          <label>Иконка</label>
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
            <div class="icon-preview" id="iconPreview"><?= $edit['icon'] ?? '🛍️' ?></div>
            <input type="text" name="icon" id="iconInput" value="<?= htmlspecialchars($edit['icon'] ?? '🛍️') ?>" style="width:100px" maxlength="5" placeholder="Emoji">
            <span style="font-size:12px;color:var(--text2)">Вставьте emoji или выберите ниже</span>
          </div>
          <div class="icon-picker">
            <?php foreach ($icons as $ic): ?>
            <button type="button" class="icon-opt <?= ($edit && $edit['icon'] === $ic) ? 'on' : '' ?>" onclick="pickIcon('<?= $ic ?>')"><?= $ic ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="display:flex;gap:9px;margin-top:16px">
          <button type="submit" class="btn btn-primary">💾 Сохранить</button>
          <button type="button" class="btn btn-secondary" onclick="toggleCatForm()">Отмена</button>
        </div>
      </form>
    </div>

    <div class="box">
      <table class="atable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Категория</th>
            <th>Slug</th>
            <th>Порядок</th>
            <th>Статус</th>
            <th>Товаров</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($cats as $c):
          $cnt = qRow('SELECT COUNT(*) AS c FROM products WHERE category_id=? AND is_active=1', 'i', $c['id']);
          $pcount = (int)($cnt['c'] ?? 0);
        ?>
        <tr>
          <td>#<?= $c['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="cat-row-icon"><?= $c['icon'] ?></div>
              <strong style="font-size:13px"><?= htmlspecialchars($c['name']) ?></strong>
            </div>
          </td>
          <td><code style="font-size:12px;background:var(--bg);padding:2px 6px;border-radius:5px"><?= htmlspecialchars($c['slug']) ?></code></td>
          <td><?= $c['sort_order'] ?></td>
          <td>
            <?php if ($c['is_active']): ?>
              <span style="background:#E8F5E9;color:#2E7D32;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700">✓ Активна</span>
            <?php else: ?>
              <span style="background:#F5F5F5;color:#9E9E9E;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700">Скрыта</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= BASE_URL ?>/?cat=<?= $c['id'] ?>" target="_blank" style="color:var(--brand);font-size:13px">
              <?= $pcount ?> товаров ↗
            </a>
          </td>
          <td>
            <div class="act-btns">
              <a href="?edit=<?= $c['id'] ?>" class="btn btn-secondary btn-sm">✏️ Изменить</a>
              <a href="?del=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить категорию «<?= htmlspecialchars($c['name']) ?>»?\nТовары в этой категории останутся, но без категории.')">🗑️</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$cats): ?>
        <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text2)">Категорий пока нет</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<div class="toast" id="toast"></div>
<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?> };

function toggleCatForm() {
    var f = document.getElementById('catForm');
    var b = document.getElementById('toggleFormBtn');
    if (f.style.display === 'none') {
        f.style.display = 'block';
        b.textContent = '✕ Закрыть';
        f.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        f.style.display = 'none';
        b.textContent = '+ Добавить категорию';
    }
}

function pickIcon(ic) {
    document.getElementById('iconInput').value = ic;
    document.getElementById('iconPreview').textContent = ic;
    document.querySelectorAll('.icon-opt').forEach(function(b) { b.classList.remove('on'); });
    event.target.classList.add('on');
}

document.getElementById('iconInput').addEventListener('input', function() {
    document.getElementById('iconPreview').textContent = this.value || '🛍️';
});

document.getElementById('catName').addEventListener('input', function() {
    var slugField = document.getElementById('catSlug');
    if (!slugField.dataset.manual) {
        var map = {'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'yo','ж':'zh','з':'z','и':'i','й':'j','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'ts','ч':'ch','ш':'sh','щ':'sch','ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya',' ':'-'};
        var s = this.value.toLowerCase().split('').map(function(c){ return map[c] !== undefined ? map[c] : c; }).join('');
        slugField.value = s.replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }
});
document.getElementById('catSlug').addEventListener('input', function() {
    this.dataset.manual = '1';
});

<?php if ($edit): ?>
document.getElementById('catForm').style.display = 'block';
document.getElementById('toggleFormBtn').textContent = '✕ Закрыть';
<?php endif; ?>
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>