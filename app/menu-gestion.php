<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant']);
$pageTitle = 'Menu';

$act = $_POST['action'] ?? '';
if ($act === 'save_dish') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $data = [trim($_POST['name']), (int)$_POST['category_id'], trim($_POST['description']),
             (float)$_POST['price'], isset($_POST['available'])?1:0];
    if ($id) {
        $pdo->prepare('UPDATE dishes SET name=?,category_id=?,description=?,price=?,available=? WHERE id=?')
            ->execute([...$data, $id]);
    } else {
        $pdo->prepare('INSERT INTO dishes (name,category_id,description,price,available) VALUES (?,?,?,?,?)')
            ->execute($data);
    }
    redirect('/app/menu-gestion.php');
}
if ($act === 'delete_dish') { csrf_check(); $pdo->prepare('DELETE FROM dishes WHERE id=?')->execute([(int)$_POST['id']]); redirect('/app/menu-gestion.php'); }
if ($act === 'save_cat') {
    csrf_check();
    $pdo->prepare('INSERT INTO categories (name, display_order) VALUES (?,?)')
        ->execute([trim($_POST['name']), (int)($_POST['display_order']??0)]);
    redirect('/app/menu-gestion.php');
}

$cats = $pdo->query('SELECT * FROM categories ORDER BY display_order')->fetchAll();
$dishes = $pdo->query('SELECT d.*, c.name AS cat FROM dishes d JOIN categories c ON c.id=d.category_id ORDER BY c.display_order, d.name')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>🍽️ Gestion du menu</h1></div>

<div class="grid-2 mb-2">
  <div class="card">
    <h3 class="mt-0">Nouveau plat</h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="save_dish">
      <div class="field"><label>Nom</label><input class="input" name="name" required></div>
      <div class="row">
        <div class="col field"><label>Catégorie</label>
          <select class="input" name="category_id" required>
            <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col field"><label>Prix (FCFA)</label><input class="input" type="number" step="1" name="price" required></div>
      </div>
      <div class="field"><label>Description</label><textarea class="input" name="description" rows="2"></textarea></div>
      <div class="field"><label><input type="checkbox" name="available" checked> Disponible</label></div>
      <button class="btn">Ajouter le plat</button>
    </form>
  </div>
  <div class="card">
    <h3 class="mt-0">Nouvelle catégorie</h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="save_cat">
      <div class="field"><label>Nom</label><input class="input" name="name" required></div>
      <div class="field"><label>Ordre d'affichage</label><input class="input" type="number" name="display_order" value="10"></div>
      <button class="btn btn-ghost">Ajouter</button>
    </form>
    <h4 class="mt-2">Catégories</h4>
    <ul style="padding-left:16px">
      <?php foreach ($cats as $c): ?><li><?= e($c['name']) ?> <small class="text-muted">(ordre <?= (int)$c['display_order'] ?>)</small></li><?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="card">
  <h3 class="mt-0">Plats (<?= count($dishes) ?>)</h3>
  <table><thead><tr><th>Nom</th><th>Catégorie</th><th class="text-right">Prix</th><th>État</th><th></th></tr></thead><tbody>
  <?php foreach ($dishes as $d): ?>
    <tr><td><b><?= e($d['name']) ?></b><br><small class="text-muted"><?= e($d['description']) ?></small></td>
    <td><?= e($d['cat']) ?></td>
    <td class="text-right"><?= money($d['price']) ?></td>
    <td><?php if($d['available']): ?><span class="badge badge-servie">Disponible</span><?php else: ?><span class="badge badge-nettoyer">Rupture</span><?php endif; ?></td>
    <td><form method="post" style="display:inline">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="delete_dish">
      <input type="hidden" name="id" value="<?= $d['id'] ?>">
      <button class="btn btn-sm btn-danger" data-confirm="Supprimer ce plat ?">Supprimer</button>
    </form></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
