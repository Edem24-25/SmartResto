<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant','serveur','caissier']);
$pageTitle = 'Commandes';
$user = current_user();

// Créer une commande
if (($_POST['action'] ?? '') === 'create') {
    csrf_check();
    $tid = (int)$_POST['table_id'];
    $pdo->prepare('INSERT INTO orders (table_id, waiter_id, status) VALUES (?,?,?)')
        ->execute([$tid, $user['id'], 'brouillon']);
    $pdo->prepare('UPDATE tables_resto SET status="occupee" WHERE id=?')->execute([$tid]);
    redirect('/app/commandes.php?edit=' . $pdo->lastInsertId());
}
// Ajout d'un plat
if (($_POST['action'] ?? '') === 'add_item') {
    csrf_check();
    $oid = (int)$_POST['order_id'];
    $did = (int)$_POST['dish_id'];
    $qty = max(1, (int)$_POST['qty']);
    $price = (float)$pdo->query("SELECT price FROM dishes WHERE id=$did")->fetchColumn();
    $pdo->prepare('INSERT INTO order_items (order_id, dish_id, quantity, unit_price) VALUES (?,?,?,?)')
        ->execute([$oid, $did, $qty, $price]);
    redirect('/app/commandes.php?edit=' . $oid);
}
if (($_POST['action'] ?? '') === 'remove_item') {
    csrf_check();
    $pdo->prepare('DELETE FROM order_items WHERE id=?')->execute([(int)$_POST['item_id']]);
    redirect('/app/commandes.php?edit=' . (int)$_POST['order_id']);
}
if (($_POST['action'] ?? '') === 'send') {
    csrf_check();
    $oid = (int)$_POST['order_id'];
    $pdo->prepare("UPDATE orders SET status='envoyee', sent_at=NOW() WHERE id=?")->execute([$oid]);
    redirect('/app/commandes.php?edit=' . $oid);
}

$editId = (int)($_GET['edit'] ?? 0);
if (!$editId && isset($_GET['table'])) {
    // Créer directement
    $tid = (int)$_GET['table'];
    $pdo->prepare('INSERT INTO orders (table_id, waiter_id, status) VALUES (?,?,?)')
        ->execute([$tid, $user['id'], 'brouillon']);
    $pdo->prepare('UPDATE tables_resto SET status="occupee" WHERE id=?')->execute([$tid]);
    redirect('/app/commandes.php?edit=' . $pdo->lastInsertId());
}

$orders = $pdo->query("SELECT o.*, t.number AS tnum, u.full_name AS waiter,
    (SELECT COALESCE(SUM(quantity*unit_price),0) FROM order_items WHERE order_id=o.id) AS total
    FROM orders o JOIN tables_resto t ON t.id=o.table_id
    LEFT JOIN users u ON u.id=o.waiter_id
    ORDER BY o.created_at DESC LIMIT 30")->fetchAll();
$tables = $pdo->query('SELECT * FROM tables_resto ORDER BY number')->fetchAll();

$editOrder = null; $items = []; $dishes = [];
if ($editId) {
    $editOrder = $pdo->query("SELECT o.*, t.number AS tnum FROM orders o JOIN tables_resto t ON t.id=o.table_id WHERE o.id=$editId")->fetch();
    $items = $pdo->query("SELECT oi.*, d.name FROM order_items oi JOIN dishes d ON d.id=oi.dish_id WHERE order_id=$editId")->fetchAll();
    $dishes = $pdo->query("SELECT d.*, c.name AS cat FROM dishes d JOIN categories c ON c.id=d.category_id WHERE d.available=1 ORDER BY c.display_order, d.name")->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Commandes</h1></div>

<?php if ($editOrder): $total = array_sum(array_map(fn($i)=>$i['quantity']*$i['unit_price'], $items)); ?>
  <div class="card mb-2">
    <div class="flex-between">
      <div><h2 class="mt-0">Commande #<?= $editOrder['id'] ?> — Table <?= e($editOrder['tnum']) ?></h2>
      <span class="badge badge-<?= e($editOrder['status']) ?>"><?= e(order_status_label($editOrder['status'])) ?></span></div>
      <a href="commandes.php" class="btn btn-ghost">← Retour</a>
    </div>
  </div>

  <div class="grid-2">
    <div class="card">
      <h3 class="mt-0">Articles</h3>
      <?php if (!$items): ?><p class="text-muted">Aucun article. Ajoutez-en depuis le menu à droite.</p><?php endif; ?>
      <?php foreach ($items as $i): ?>
        <div class="flex-between" style="padding:10px 0;border-bottom:1px solid var(--border)">
          <div><b><?= (int)$i['quantity'] ?>×</b> <?= e($i['name']) ?><br><small class="text-muted"><?= money($i['unit_price']) ?> /u</small></div>
          <div class="flex">
            <span><?= money($i['quantity']*$i['unit_price']) ?></span>
            <?php if ($editOrder['status']==='brouillon'): ?>
            <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="remove_item">
              <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
              <input type="hidden" name="order_id" value="<?= $editOrder['id'] ?>">
              <button class="btn btn-sm btn-ghost">✕</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="flex-between mt-2" style="font-size:1.2rem"><b>Total</b><b style="color:var(--primary)"><?= money($total) ?></b></div>

      <?php if ($editOrder['status']==='brouillon' && $items): ?>
      <form method="post" class="mt-2"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="send"><input type="hidden" name="order_id" value="<?= $editOrder['id'] ?>">
        <button class="btn btn-success btn-block">🔥 Envoyer en cuisine</button>
      </form>
      <?php elseif ($editOrder['status']!=='brouillon'): ?>
        <a href="facturation.php?order=<?= $editOrder['id'] ?>" class="btn btn-block mt-2">💳 Facturer</a>
      <?php endif; ?>
    </div>

    <?php if ($editOrder['status']==='brouillon'): ?>
    <div class="card">
      <h3 class="mt-0">Menu</h3>
      <?php $curCat=null; foreach ($dishes as $d):
        if ($d['cat']!==$curCat){ if($curCat!==null) echo '</div>'; echo '<h4 style="color:var(--primary);margin-top:16px">'.e($d['cat']).'</h4><div>'; $curCat=$d['cat']; } ?>
        <form method="post" class="flex-between" style="padding:8px 0;border-bottom:1px dashed var(--border)">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="add_item">
          <input type="hidden" name="order_id" value="<?= $editOrder['id'] ?>">
          <input type="hidden" name="dish_id" value="<?= $d['id'] ?>">
          <div><?= e($d['name']) ?><br><small class="text-muted"><?= money($d['price']) ?></small></div>
          <div class="flex"><input type="number" name="qty" value="1" min="1" class="input" style="width:70px">
          <button class="btn btn-sm">+</button></div>
        </form>
      <?php endforeach; if($curCat) echo '</div>'; ?>
    </div>
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="card mb-2">
    <h3 class="mt-0">Nouvelle commande</h3>
    <form method="post" class="flex">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <select name="table_id" class="input" style="max-width:260px" required>
        <option value="">Choisir une table…</option>
        <?php foreach ($tables as $t): ?>
          <option value="<?= $t['id'] ?>">Table <?= e($t['number']) ?> — <?= (int)$t['capacity'] ?> pl. (<?= e(table_status_label($t['status'])) ?>)</option>
        <?php endforeach; ?>
      </select>
      <button class="btn">Créer</button>
    </form>
  </div>
  <div class="card">
    <h3 class="mt-0">Commandes récentes</h3>
    <table><thead><tr><th>#</th><th>Table</th><th>Serveur</th><th>Statut</th><th class="text-right">Total</th><th>Créée</th><th></th></tr></thead><tbody>
    <?php foreach ($orders as $o): ?>
      <tr><td>#<?= $o['id'] ?></td><td><?= e($o['tnum']) ?></td><td><?= e($o['waiter'] ?? '—') ?></td>
      <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(order_status_label($o['status'])) ?></span></td>
      <td class="text-right"><?= money($o['total']) ?></td>
      <td><small><?= date('H:i', strtotime($o['created_at'])) ?></small></td>
      <td><a href="?edit=<?= $o['id'] ?>" class="btn btn-sm btn-ghost">Ouvrir</a></td></tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
