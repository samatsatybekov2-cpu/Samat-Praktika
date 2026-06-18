<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

if (isset($_GET['del'])) {
    deleteProduct((int)$_GET['del']);
    header('Location: products.php?deleted=1'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $d  = [
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'name'        => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price'       => floatval($_POST['price'] ?? 0),
        'old_price'   => floatval($_POST['old_price'] ?? 0),
        'stock'       => (int)($_POST['stock'] ?? 0),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active'   => isset($_POST['is_active'])   ? 1 : 0,
        'image'       => trim($_POST['image'] ?? ''),
    ];
    if ($d['name'] && $d['price'] > 0) {
        if ($id > 0) updateProduct($id, $d);
        else         createProduct($d);
        header('Location: products.php?saved=1'); exit;
    }
    $err = 'Заполните обязательные поля: Название и Цена';
}

$edit = isset($_GET['edit']) ? getProductById((int)$_GET['edit']) : null;
$cats = getAllCategories();
$page = max(1, (int)($_GET['p'] ?? 1));
$prods = qAll(
    'SELECT p.*,c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.is_active=1 ORDER BY p.id DESC LIMIT 20 OFFSET ?',
    'i', ($page - 1) * 20
);
$total = countAllProducts();
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Товары — Админ | <?= htmlspecialchars(SHOP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Golos+Text:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
<style>
.img-preview{width:80px;height:80px;border-radius:10px;object-fit:cover;border:2px solid var(--border);display:none}
.img-preview.show{display:block}
.alert-ok{background:#E8F5E9;color:#2E7D32;padding:10px 14px;border-radius:9px;margin-bottom:16px}
.alert-err{background:#FFEBEE;color:#C62828;padding:10px 14px;border-radius:9px;margin-bottom:16px}
</style>
</head>
<body>
<div class="admin-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="admin-main">

    <div class="admin-head">
      <h1>📦 Товары</h1>
      <div style="display:flex;gap:8px;align-items:center">
        <span style="font-size:13px;color:var(--text2)">Всего: <?= $total ?></span>
        <button class="btn btn-primary btn-sm" id="toggleProdBtn" onclick="toggleProdForm()">+ Добавить товар</button>
      </div>
    </div>

    <?php if (isset($_GET['saved'])): ?><div class="alert-ok">✓ Товар сохранён!</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert-err">Товар удалён (скрыт из каталога)</div><?php endif; ?>
    <?php if (!empty($err)): ?><div class="alert-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="box" id="prodForm" style="margin-bottom:18px;<?= $edit ? '' : 'display:none' ?>">
      <h3 style="font-weight:700;font-size:15px;margin-bottom:18px"><?= $edit ? '✏️ Редактировать товар' : '+ Добавить новый товар' ?></h3>
      <form method="POST" id="productForm">
        <input type="hidden" name="id" value="<?= $edit ? $edit['id'] : 0 ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="fg" style="grid-column:1/-1">
            <label>Название товара *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required placeholder="Например: Смартфон XPro 12">
          </div>

          <div class="fg">
            <label>Категория</label>
            <select name="category_id">
              <option value="0">— Без категории —</option>
              <?php foreach ($cats as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($edit && $edit['category_id'] == $c['id']) ? 'selected' : '' ?>>
                <?= $c['icon'] ?> <?= htmlspecialchars($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="fg">
            <label>Цена (₸) *</label>
            <input type="number" name="price" step="1" min="0" value="<?= $edit['price'] ?? '' ?>" required placeholder="0">
          </div>

          <div class="fg">
            <label>Старая цена (₸) <span style="color:var(--text2);font-size:11px">для зачёркивания</span></label>
            <input type="number" name="old_price" step="1" min="0" value="<?= $edit['old_price'] ?? '' ?>" placeholder="0 — не показывать">
          </div>

          <div class="fg">
            <label>Остаток (шт.)</label>
            <input type="number" name="stock" min="0" value="<?= $edit['stock'] ?? 0 ?>">
          </div>

          <div class="fg">
            <label>URL изображения</label>
            <div style="display:flex;gap:10px;align-items:flex-start">
              <div style="flex:1">
                <input type="text" name="image" id="imgUrl" placeholder="https://example.com/image.jpg" value="<?= htmlspecialchars($edit['image'] ?? '') ?>" oninput="previewImg(this.value)">
              </div>
              <img id="imgPreview" class="img-preview <?= !empty($edit['image']) ? 'show' : '' ?>" src="<?= htmlspecialchars($edit['image'] ?? '') ?>" alt="">
            </div>
          </div>
        </div>

        <div class="fg" style="margin-top:4px">
          <label>Описание</label>
          <textarea name="description" rows="3" placeholder="Подробное описание товара..."><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;gap:24px;margin-bottom:16px;flex-wrap:wrap">
          <label class="chk">
            <input type="checkbox" name="is_featured" <?= ($edit && $edit['is_featured']) ? 'checked' : '' ?>>
            ⭐ Рекомендуемый (показывать на главной)
          </label>
          <label class="chk">
            <input type="checkbox" name="is_active" <?= (!$edit || $edit['is_active']) ? 'checked' : '' ?>>
            ✓ Активен (показывать в каталоге)
          </label>
        </div>

        <div style="display:flex;gap:9px">
          <button type="submit" class="btn btn-primary">💾 Сохранить товар</button>
          <button type="button" class="btn btn-secondary" onclick="toggleProdForm()">Отмена</button>
          <?php if ($edit): ?>
          <a href="products.php" class="btn btn-secondary">+ Новый товар</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="box">
      <table class="atable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Товар</th>
            <th>Категория</th>
            <th>Цена</th>
            <th>Остаток</th>
            <th>Статус</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($prods as $p): ?>
        <tr>
          <td style="color:var(--text2);font-size:12px">#<?= $p['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:40px;height:40px;border-radius:8px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;overflow:hidden">
                <?php if ($p['image']): ?>
                  <img src="<?= htmlspecialchars($p['image']) ?>" style="width:100%;height:100%;object-fit:cover" onerror="this.parentNode.textContent='📦'">
                <?php else: ?>📦<?php endif; ?>
              </div>
              <div>
                <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($p['name']) ?></div>
                <?php if ($p['description']): ?>
                <div style="font-size:11px;color:var(--text2);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['description']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td style="font-size:13px"><?= htmlspecialchars($p['cat_name'] ?? '—') ?></td>
          <td>
            <strong style="color:var(--brand)"><?= number_format($p['price'], 0, '.', ' ') ?> ₸</strong>
            <?php if ($p['old_price'] > 0): ?>
            <div style="font-size:11px;text-decoration:line-through;color:var(--text2)"><?= number_format($p['old_price'], 0, '.', ' ') ?> ₸</div>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($p['stock'] <= 0): ?>
              <span style="color:#C62828;font-size:12px">✗ Нет</span>
            <?php elseif ($p['stock'] <= 5): ?>
              <span style="color:#E65100;font-size:12px">⚠️ <?= $p['stock'] ?></span>
            <?php else: ?>
              <span style="color:#2E7D32;font-size:12px">✓ <?= $p['stock'] ?></span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($p['is_featured']): ?>
              <span style="background:#FFF3E0;color:#E65100;padding:2px 7px;border-radius:5px;font-size:11px;font-weight:700">⭐ Топ</span>
            <?php else: ?>
              <span style="color:var(--text2);font-size:12px">Обычный</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="act-btns">
              <a href="?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" title="Редактировать">✏️</a>
              <a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-secondary btn-sm" title="Смотреть">👁️</a>
              <a href="?del=<?= $p['id'] ?>" class="btn btn-danger btn-sm" title="Удалить" onclick="return confirm('Скрыть товар «<?= htmlspecialchars(addslashes($p['name'])) ?>» из каталога?')">🗑️</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$prods): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2)">
          Товаров пока нет. <button class="btn btn-primary btn-sm" onclick="toggleProdForm()" style="margin-left:10px">Добавить первый</button>
        </td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <?php if ($total > 20): ?>
      <div class="pagination" style="margin-top:16px">
        <?php for ($i = 1; $i <= ceil($total / 20); $i++): ?>
        <a href="?p=<?= $i ?>" class="pag-btn <?= $i === $page ? 'on' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<div class="toast" id="toast"></div>
<script>
window.SHOP = { base: <?= json_encode(BASE_URL) ?> };

function toggleProdForm() {
    var f = document.getElementById('prodForm');
    var b = document.getElementById('toggleProdBtn');
    if (f.style.display === 'none' || f.style.display === '') {
        f.style.display = 'block';
        b.textContent = '✕ Закрыть';
        f.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        f.style.display = 'none';
        b.textContent = '+ Добавить товар';
    }
}

function previewImg(url) {
    var img = document.getElementById('imgPreview');
    if (url && url.startsWith('http')) {
        img.src = url;
        img.classList.add('show');
    } else {
        img.classList.remove('show');
    }
}

<?php if ($edit): ?>
document.getElementById('prodForm').style.display = 'block';
document.getElementById('toggleProdBtn').textContent = '✕ Закрыть';
<?php endif; ?>
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>