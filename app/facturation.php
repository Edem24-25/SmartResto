<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant','caissier']);
$pageTitle = 'Facturation';
$user = current_user();

// Génération facture
if (($_POST['action'] ?? '') === 'generate') {
    csrf_check();
    $oid = (int)$_POST['order_id'];
    $sub = (float)$pdo->query("SELECT COALESCE(SUM(quantity*unit_price),0) FROM order_items WHERE order_id=$oid")->fetchColumn();
    $tax = $sub * TAX_RATE;
    $tot = $sub + $tax;
    $pdo->prepare('INSERT INTO invoices (order_id, subtotal, tax, total, cashier_id) VALUES (?,?,?,?,?)')
        ->execute([$oid, $sub, $tax, $tot, $user['id']]);
    redirect('/app/facturation.php?invoice=' . $pdo->lastInsertId());
}
// Encaissement
if (($_POST['action'] ?? '') === 'pay') {
    csrf_check();
    $iid = (int)$_POST['invoice_id'];
    $method = $_POST['method'];
    $amount = (float)$_POST['amount'];
    $ref = trim($_POST['reference'] ?? '');
    $pdo->prepare('INSERT INTO payments (invoice_id, method, amount, reference) VALUES (?,?,?,?)')
        ->execute([$iid, $method, $amount, $ref]);
    $paid = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id=$iid")->fetchColumn();
    $inv = $pdo->query("SELECT * FROM invoices WHERE id=$iid")->fetch();
    if ($paid >= $inv['total']) {
        $pdo->prepare("UPDATE invoices SET status='payee' WHERE id=?")->execute([$iid]);
        $pdo->prepare("UPDATE orders SET status='encaissee' WHERE id=?")->execute([$inv['order_id']]);
        $pdo->prepare("UPDATE tables_resto SET status='nettoyer' WHERE id=(SELECT table_id FROM orders WHERE id=?)")->execute([$inv['order_id']]);
    } else {
        $pdo->prepare("UPDATE invoices SET status='partielle' WHERE id=?")->execute([$iid]);
    }
    redirect('/app/facturation.php?invoice=' . $iid);
}

$orderId = (int)($_GET['order'] ?? 0);
$invoiceId = (int)($_GET['invoice'] ?? 0);

// Facturer une commande existante
$facturable = null;
if ($orderId) {
    $existing = $pdo->query("SELECT id FROM invoices WHERE order_id=$orderId")->fetchColumn();
    if ($existing) redirect('/app/facturation.php?invoice=' . $existing);
    $facturable = $pdo->query("SELECT o.*, t.number AS tnum FROM orders o JOIN tables_resto t ON t.id=o.table_id WHERE o.id=$orderId")->fetch();
}

$invoice = null; $items = []; $payments = [];
if ($invoiceId) {
    $invoice = $pdo->query("SELECT i.*, o.table_id, t.number AS tnum FROM invoices i JOIN orders o ON o.id=i.order_id JOIN tables_resto t ON t.id=o.table_id WHERE i.id=$invoiceId")->fetch();
    $items = $pdo->query("SELECT oi.*, d.name FROM order_items oi JOIN dishes d ON d.id=oi.dish_id WHERE oi.order_id={$invoice['order_id']}")->fetchAll();
    $payments = $pdo->query("SELECT * FROM payments WHERE invoice_id=$invoiceId ORDER BY created_at")->fetchAll();
}

$invoices = $pdo->query("SELECT i.*, t.number AS tnum FROM invoices i JOIN orders o ON o.id=i.order_id JOIN tables_resto t ON t.id=o.table_id ORDER BY i.created_at DESC LIMIT 20")->fetchAll();
$toBill = $pdo->query("SELECT o.*, t.number AS tnum, (SELECT SUM(quantity*unit_price) FROM order_items WHERE order_id=o.id) AS total
  FROM orders o JOIN tables_resto t ON t.id=o.table_id
  WHERE o.status IN ('servie','pret') AND NOT EXISTS(SELECT 1 FROM invoices WHERE order_id=o.id) ORDER BY o.created_at DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>💳 Facturation & encaissement</h1></div>

<?php if ($facturable): ?>
  <div class="card">
    <h3 class="mt-0">Générer une facture — Commande #<?= $facturable['id'] ?> (Table <?= e($facturable['tnum']) ?>)</h3>
    <form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="generate">
      <input type="hidden" name="order_id" value="<?= $facturable['id'] ?>">
      <button class="btn">Générer la facture</button>
      <a href="facturation.php" class="btn btn-ghost">Annuler</a>
    </form>
  </div>
<?php elseif ($invoice): $paid = array_sum(array_column($payments,'amount')); $due = $invoice['total']-$paid; ?>
  <div class="grid-2">
    <div class="card">
      <div class="flex-between"><h3 class="mt-0">Facture #<?= $invoice['id'] ?></h3>
      <span class="badge badge-<?= $invoice['status']==='payee'?'servie':'envoyee' ?>"><?= e($invoice['status']) ?></span></div>
      <p>Table <b><?= e($invoice['tnum']) ?></b> — <?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></p>
      <table><thead><tr><th>Plat</th><th class="text-right">Qté</th><th class="text-right">P.U.</th><th class="text-right">Total</th></tr></thead><tbody>
      <?php foreach ($items as $i): ?>
        <tr><td><?= e($i['name']) ?></td><td class="text-right"><?= (int)$i['quantity'] ?></td>
        <td class="text-right"><?= money($i['unit_price']) ?></td>
        <td class="text-right"><?= money($i['quantity']*$i['unit_price']) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
      <div class="mt-2">
        <div class="flex-between"><span>Sous-total</span><b><?= money($invoice['subtotal']) ?></b></div>
        <?php if ($invoice['tax']>0): ?><div class="flex-between"><span>Taxes</span><b><?= money($invoice['tax']) ?></b></div><?php endif; ?>
        <div class="flex-between" style="font-size:1.3rem;color:var(--primary);margin-top:10px"><b>Total</b><b><?= money($invoice['total']) ?></b></div>
        <?php if ($paid>0): ?><div class="flex-between"><span>Payé</span><b class="text-muted"><?= money($paid) ?></b></div>
        <div class="flex-between"><span>Restant</span><b><?= money($due) ?></b></div><?php endif; ?>
      </div>
      <button class="btn btn-ghost mt-2" onclick="window.print()">🖨 Imprimer le reçu</button>
    </div>

    <div class="card">
      <h3 class="mt-0">Encaissement</h3>
      <?php if ($invoice['status']==='payee'): ?>
        <div class="alert alert-success">✓ Facture entièrement réglée.</div>
      <?php else: ?>
      <form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="pay">
        <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
        <div class="field"><label>Moyen de paiement</label>
          <select name="method" class="input" id="pm">
            <option value="especes">💵 Espèces</option>
            <option value="mtn_momo">📱 MTN Mobile Money</option>
            <option value="moov_money">📱 Moov Money</option>
            <option value="celtiis">📱 Celtiis Cash</option>
            <option value="carte">💳 Carte bancaire</option>
          </select>
        </div>
        <div class="field"><label>Montant reçu</label>
          <input class="input" type="number" step="1" name="amount" value="<?= $due ?>" required></div>
        <div class="field"><label>Référence (n° transaction Mobile Money…)</label>
          <input class="input" name="reference" placeholder="Optionnel"></div>
        <button class="btn btn-block btn-success">Valider l'encaissement</button>
      </form>
      <?php endif; ?>

      <?php if ($payments): ?>
        <h4 class="mt-2">Paiements</h4>
        <table><tbody>
        <?php foreach ($payments as $p): ?>
          <tr><td><?= e($p['method']) ?></td><td><?= money($p['amount']) ?></td>
          <td><small class="text-muted"><?= e($p['reference'] ?: '—') ?></small></td></tr>
        <?php endforeach; ?>
        </tbody></table>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="card mb-2">
    <h3 class="mt-0">Commandes à facturer (<?= count($toBill) ?>)</h3>
    <?php if (!$toBill): ?><p class="text-muted">Aucune commande en attente de facturation.</p><?php else: ?>
    <table><thead><tr><th>Cmd</th><th>Table</th><th class="text-right">Total</th><th></th></tr></thead><tbody>
    <?php foreach ($toBill as $o): ?>
      <tr><td>#<?= $o['id'] ?></td><td><?= e($o['tnum']) ?></td><td class="text-right"><?= money($o['total']) ?></td>
      <td><a href="?order=<?= $o['id'] ?>" class="btn btn-sm">Facturer</a></td></tr>
    <?php endforeach; ?>
    </tbody></table><?php endif; ?>
  </div>

  <div class="card">
    <h3 class="mt-0">Factures récentes</h3>
    <table><thead><tr><th>#</th><th>Table</th><th class="text-right">Total</th><th>Statut</th><th>Date</th><th></th></tr></thead><tbody>
    <?php foreach ($invoices as $i): ?>
      <tr><td>#<?= $i['id'] ?></td><td><?= e($i['tnum']) ?></td>
      <td class="text-right"><?= money($i['total']) ?></td>
      <td><span class="badge badge-<?= $i['status']==='payee'?'servie':'envoyee' ?>"><?= e($i['status']) ?></span></td>
      <td><?= date('d/m H:i', strtotime($i['created_at'])) ?></td>
      <td><a href="?invoice=<?= $i['id'] ?>" class="btn btn-sm btn-ghost">Voir</a></td></tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
