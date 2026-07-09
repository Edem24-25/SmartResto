<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant']);
$pageTitle = 'Rapports';

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

$ca = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='payee' AND DATE(created_at) BETWEEN ".$pdo->quote($from)." AND ".$pdo->quote($to))->fetchColumn();
$nb = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE status='payee' AND DATE(created_at) BETWEEN ".$pdo->quote($from)." AND ".$pdo->quote($to))->fetchColumn();
$avg = $nb ? $ca / $nb : 0;

$byMethod = $pdo->query("SELECT p.method, SUM(p.amount) tot FROM payments p JOIN invoices i ON i.id=p.invoice_id
  WHERE DATE(p.created_at) BETWEEN ".$pdo->quote($from)." AND ".$pdo->quote($to)." GROUP BY p.method")->fetchAll();
$topPlats = $pdo->query("SELECT d.name, SUM(oi.quantity) q, SUM(oi.quantity*oi.unit_price) ca
  FROM order_items oi JOIN dishes d ON d.id=oi.dish_id JOIN orders o ON o.id=oi.order_id JOIN invoices i ON i.order_id=o.id
  WHERE i.status='payee' AND DATE(i.created_at) BETWEEN ".$pdo->quote($from)." AND ".$pdo->quote($to)."
  GROUP BY d.id ORDER BY q DESC LIMIT 10")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>📈 Rapports</h1></div>

<div class="card mb-2">
  <form method="get" class="flex">
    <div class="field" style="margin:0"><label>Du</label><input class="input" type="date" name="from" value="<?= e($from) ?>"></div>
    <div class="field" style="margin:0"><label>Au</label><input class="input" type="date" name="to" value="<?= e($to) ?>"></div>
    <button class="btn" style="margin-top:22px">Filtrer</button>
    <a href="?from=<?= e($from) ?>&to=<?= e($to) ?>&export=csv" class="btn btn-ghost" style="margin-top:22px">Exporter CSV</a>
  </form>
</div>

<?php
if (isset($_GET['export']) && $_GET['export']==='csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rapport_'.$from.'_'.$to.'.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['Plat','Quantité','Chiffre affaires']);
    foreach ($topPlats as $p) fputcsv($out, [$p['name'], $p['q'], $p['ca']]);
    exit;
}
?>

<div class="kpi-grid">
  <div class="kpi primary"><div class="label">CA période</div><div class="value"><?= money($ca) ?></div></div>
  <div class="kpi"><div class="label">Tickets payés</div><div class="value"><?= $nb ?></div></div>
  <div class="kpi success"><div class="label">Ticket moyen</div><div class="value"><?= money($avg) ?></div></div>
</div>

<div class="grid-2">
  <div class="card"><h3 class="mt-0">Top plats</h3>
    <?php if(!$topPlats): ?><p class="text-muted">Aucune vente sur la période.</p><?php else: ?>
    <table><thead><tr><th>Plat</th><th class="text-right">Qté</th><th class="text-right">CA</th></tr></thead><tbody>
    <?php foreach ($topPlats as $p): ?>
      <tr><td><?= e($p['name']) ?></td><td class="text-right"><?= (int)$p['q'] ?></td><td class="text-right"><?= money($p['ca']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table><?php endif; ?>
  </div>
  <div class="card"><h3 class="mt-0">Répartition par moyen de paiement</h3>
    <?php if(!$byMethod): ?><p class="text-muted">Aucun paiement sur la période.</p><?php else: ?>
    <table><thead><tr><th>Méthode</th><th class="text-right">Montant</th></tr></thead><tbody>
    <?php foreach ($byMethod as $p): ?>
      <tr><td><?= e($p['method']) ?></td><td class="text-right"><?= money($p['tot']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table><?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
