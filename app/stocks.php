<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant']);
$pageTitle = 'Stocks';
$user = current_user();

$act = $_POST['action'] ?? '';
if ($act === 'add_ing') {
    csrf_check();
    $pdo->prepare('INSERT INTO ingredients (name,unit,stock,threshold) VALUES (?,?,?,?)')
        ->execute([trim($_POST['name']), trim($_POST['unit']), (float)$_POST['stock'], (float)$_POST['threshold']]);
    redirect('/app/stocks.php');
}
if ($act === 'move') {
    csrf_check();
    $iid = (int)$_POST['ingredient_id'];
    $qty = (float)$_POST['quantity'];
    $type = $_POST['type'];
    $delta = $type==='entree' ? $qty : -$qty;
    $pdo->prepare('INSERT INTO stock_movements (ingredient_id,quantity,type,note,user_id) VALUES (?,?,?,?,?)')
        ->execute([$iid, $qty, $type, trim($_POST['note']??''), $user['id']]);
    $pdo->prepare('UPDATE ingredients SET stock = stock + ? WHERE id = ?')->execute([$delta, $iid]);
    redirect('/app/stocks.php');
}
if ($act === 'threshold') {
    csrf_check();
    $pdo->prepare('UPDATE ingredients SET threshold=? WHERE id=?')
        ->execute([(float)$_POST['threshold'], (int)$_POST['id']]);
    redirect('/app/stocks.php');
}

$ings = $pdo->query('SELECT * FROM ingredients ORDER BY name')->fetchAll();
$moves = $pdo->query('SELECT m.*, i.name, i.unit, u.full_name FROM stock_movements m JOIN ingredients i ON i.id=m.ingredient_id LEFT JOIN users u ON u.id=m.user_id ORDER BY m.created_at DESC LIMIT 20')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>📦 Gestion des stocks</h1></div>

<div class="grid-2 mb-2">
  <div class="card">
    <h3 class="mt-0">Nouvel ingrédient</h3>
    <form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="add_ing">
      <div class="row"><div class="col field"><label>Nom</label><input class="input" name="name" required></div>
      <div class="col field"><label>Unité</label><input class="input" name="unit" value="kg"></div></div>
      <div class="row"><div class="col field"><label>Stock initial</label><input class="input" type="number" step="0.01" name="stock" value="0"></div>
      <div class="col field"><label>Seuil alerte</label><input class="input" type="number" step="0.01" name="threshold" value="0"></div></div>
      <button class="btn">Ajouter</button>
    </form>
  </div>
  <div class="card">
    <h3 class="mt-0">Mouvement de stock</h3>
    <form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="move">
      <div class="field"><label>Ingrédient</label>
        <select class="input" name="ingredient_id" required>
          <?php foreach ($ings as $i): ?><option value="<?= $i['id'] ?>"><?= e($i['name']) ?> (<?= $i['stock'] ?> <?= e($i['unit']) ?>)</option><?php endforeach; ?>
        </select></div>
      <div class="row"><div class="col field"><label>Type</label>
        <select class="input" name="type"><option value="entree">Entrée (livraison)</option><option value="sortie">Sortie</option></select></div>
      <div class="col field"><label>Quantité</label><input class="input" type="number" step="0.01" name="quantity" required></div></div>
      <div class="field"><label>Note</label><input class="input" name="note" placeholder="Ex : livraison fournisseur XYZ"></div>
      <button class="btn btn-success">Enregistrer</button>
    </form>
  </div>
</div>

<div class="card mb-2">
  <h3 class="mt-0">Ingrédients (<?= count($ings) ?>)</h3>
  <table><thead><tr><th>Nom</th><th class="text-right">Stock</th><th>Seuil</th><th>État</th></tr></thead><tbody>
  <?php foreach ($ings as $i): $alert = $i['stock'] <= $i['threshold']; ?>
    <tr><td><?= e($i['name']) ?></td>
    <td class="text-right"><b><?= $i['stock'] ?></b> <?= e($i['unit']) ?></td>
    <td><form method="post" class="flex"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="threshold"><input type="hidden" name="id" value="<?= $i['id'] ?>">
      <input class="input" type="number" step="0.01" name="threshold" value="<?= $i['threshold'] ?>" style="width:90px">
      <button class="btn btn-sm btn-ghost">✓</button></form></td>
    <td><?php if($alert): ?><span class="badge badge-occupee">⚠ Stock bas</span><?php else: ?><span class="badge badge-libre">OK</span><?php endif; ?></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</div>

<div class="card">
  <h3 class="mt-0">Mouvements récents</h3>
  <table><thead><tr><th>Date</th><th>Ingrédient</th><th>Type</th><th class="text-right">Qté</th><th>Par</th><th>Note</th></tr></thead><tbody>
  <?php foreach ($moves as $m): ?>
    <tr><td><?= date('d/m H:i', strtotime($m['created_at'])) ?></td>
    <td><?= e($m['name']) ?></td>
    <td><span class="badge <?= $m['type']==='entree'?'badge-libre':'badge-occupee' ?>"><?= e($m['type']) ?></span></td>
    <td class="text-right"><?= $m['quantity'] ?> <?= e($m['unit']) ?></td>
    <td><?= e($m['full_name'] ?? '—') ?></td>
    <td><small class="text-muted"><?= e($m['note']) ?></small></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
