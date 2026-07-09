<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant','cuisinier']);
$pageTitle = 'Cuisine (KDS)';

if (($_POST['action'] ?? '') === 'update_status') {
    csrf_check();
    $oid = (int)$_POST['order_id'];
    $st = $_POST['status'];
    $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$st, $oid]);
    redirect('/app/cuisine.php');
}

$orders = $pdo->query("SELECT o.*, t.number AS tnum FROM orders o
    JOIN tables_resto t ON t.id=o.table_id
    WHERE o.status IN ('envoyee','en_preparation','pret')
    ORDER BY o.urgent DESC, o.sent_at ASC")->fetchAll();

$items = [];
if ($orders) {
    $ids = implode(',', array_map(fn($o)=>(int)$o['id'], $orders));
    $rs = $pdo->query("SELECT oi.*, d.name FROM order_items oi JOIN dishes d ON d.id=oi.dish_id WHERE order_id IN ($ids)")->fetchAll();
    foreach ($rs as $r) $items[$r['order_id']][] = $r;
}
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>🔥 Écran cuisine (KDS)</h1><span class="text-muted">Rafraîchissement auto toutes les 15 s</span></div>

<div class="kds-grid" data-autorefresh="15">
  <?php if (!$orders): ?><p class="text-muted">Aucune commande en cours.</p><?php endif; ?>
  <?php foreach ($orders as $o): ?>
    <div class="ticket" data-sent="<?= e($o['sent_at'] ?? $o['created_at']) ?>">
      <div class="head">
        <div><div class="table-no">🍽 <?= e($o['tnum']) ?></div>
        <span class="badge badge-<?= e($o['status']) ?>"><?= e(order_status_label($o['status'])) ?></span></div>
        <div class="timer">⏱</div>
      </div>
      <ul>
      <?php foreach ($items[$o['id']] ?? [] as $i): ?>
        <li><span><?= e($i['name']) ?></span><span class="qty">×<?= (int)$i['quantity'] ?></span></li>
      <?php endforeach; ?>
      </ul>
      <?php if ($o['note']): ?><p class="text-muted"><small>📝 <?= e($o['note']) ?></small></p><?php endif; ?>
      <form method="post" class="flex">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
        <?php if ($o['status']==='envoyee'): ?>
          <button class="btn btn-block" name="status" value="en_preparation">▶ Commencer</button>
        <?php elseif ($o['status']==='en_preparation'): ?>
          <button class="btn btn-success btn-block" name="status" value="pret">✓ Prêt</button>
        <?php else: ?>
          <button class="btn btn-block" name="status" value="servie">🍽 Servi</button>
        <?php endif; ?>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
