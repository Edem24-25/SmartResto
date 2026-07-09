<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pageTitle = 'Tableau de bord';

$today = date('Y-m-d');
$caJour = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='payee' AND DATE(created_at)='$today'")->fetchColumn();
$nbTickets = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE DATE(created_at)='$today'")->fetchColumn();
$nbCmdEnCours = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('envoyee','en_preparation','pret')")->fetchColumn();
$stockAlertes = $pdo->query("SELECT * FROM ingredients WHERE stock <= threshold ORDER BY stock ASC")->fetchAll();
$topPlats = $pdo->query("SELECT d.name, SUM(oi.quantity) AS q, SUM(oi.quantity*oi.unit_price) AS ca
  FROM order_items oi JOIN dishes d ON d.id=oi.dish_id
  JOIN orders o ON o.id=oi.order_id
  WHERE o.status IN ('servie','encaissee')
  GROUP BY d.id ORDER BY q DESC LIMIT 5")->fetchAll();
$tables = $pdo->query("SELECT status, COUNT(*) c FROM tables_resto GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

include __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
  <h1>Tableau de bord</h1>
  <span class="text-muted"><?= date('l d F Y', strtotime($today)) ?></span>
</div>

<div class="kpi-grid">
  <div class="kpi primary"><div class="label">CA du jour</div><div class="value"><?= money($caJour) ?></div></div>
  <div class="kpi"><div class="label">Tickets du jour</div><div class="value"><?= $nbTickets ?></div></div>
  <div class="kpi success"><div class="label">Commandes en cours</div><div class="value"><?= $nbCmdEnCours ?></div></div>
  <div class="kpi danger"><div class="label">Alertes stock</div><div class="value"><?= count($stockAlertes) ?></div></div>
</div>

<div class="grid-2">
  <div class="card">
    <h3 class="mt-0">🏆 Plats les plus vendus</h3>
    <?php if (!$topPlats): ?><p class="text-muted">Aucune vente pour le moment.</p><?php else: ?>
    <table><thead><tr><th>Plat</th><th class="text-right">Qté</th><th class="text-right">CA</th></tr></thead><tbody>
    <?php foreach ($topPlats as $p): ?>
      <tr><td><?= e($p['name']) ?></td><td class="text-right"><?= (int)$p['q'] ?></td><td class="text-right"><?= money($p['ca']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 class="mt-0">🪑 État des tables</h3>
    <div class="legend">
      <span><span class="dot" style="background:#22c55e"></span>Libres : <b><?= $tables['libre']??0 ?></b></span>
      <span><span class="dot" style="background:#ef4444"></span>Occupées : <b><?= $tables['occupee']??0 ?></b></span>
      <span><span class="dot" style="background:#f59e0b"></span>Réservées : <b><?= $tables['reservee']??0 ?></b></span>
      <span><span class="dot" style="background:#94a3b8"></span>À nettoyer : <b><?= $tables['nettoyer']??0 ?></b></span>
    </div>
    <a href="plan-salle.php" class="btn btn-ghost mt-2">Voir le plan de salle →</a>
  </div>
</div>

<div class="card mt-2">
  <h3 class="mt-0">⚠️ Alertes de stock</h3>
  <?php if (!$stockAlertes): ?><p class="text-muted">Aucune alerte. Tous les ingrédients sont au-dessus du seuil.</p><?php else: ?>
  <table><thead><tr><th>Ingrédient</th><th class="text-right">Stock</th><th class="text-right">Seuil</th><th>Statut</th></tr></thead><tbody>
  <?php foreach ($stockAlertes as $s): ?>
    <tr><td><?= e($s['name']) ?></td>
    <td class="text-right"><?= $s['stock'] ?> <?= e($s['unit']) ?></td>
    <td class="text-right"><?= $s['threshold'] ?> <?= e($s['unit']) ?></td>
    <td><span class="badge badge-occupee">Stock bas</span></td></tr>
  <?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
