<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant','serveur','caissier']);
$pageTitle = 'Plan de salle';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['change_status'])) {
    csrf_check();
    $pdo->prepare('UPDATE tables_resto SET status=? WHERE id=?')
        ->execute([$_POST['status'], (int)$_POST['table_id']]);
    redirect('/app/plan-salle.php');
}

$tables = $pdo->query('SELECT * FROM tables_resto ORDER BY number')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Plan de salle</h1>
  <a href="commandes.php?new=1" class="btn">+ Nouvelle commande</a>
</div>

<div class="legend">
  <span><span class="dot" style="background:#22c55e"></span>Libre</span>
  <span><span class="dot" style="background:#ef4444"></span>Occupée</span>
  <span><span class="dot" style="background:#f59e0b"></span>Réservée</span>
  <span><span class="dot" style="background:#94a3b8"></span>À nettoyer</span>
  <span class="text-muted">Cliquez sur une table pour changer son état.</span>
</div>

<div class="floor" data-autorefresh="15">
  <?php foreach ($tables as $t): ?>
    <div class="table-btn <?= e($t['status']) ?>" style="left:<?= (int)$t['pos_x'] ?>px;top:<?= (int)$t['pos_y'] ?>px"
      onclick="document.getElementById('form-<?= $t['id'] ?>').style.display='block'">
      <div class="num"><?= e($t['number']) ?></div>
      <div class="cap"><?= (int)$t['capacity'] ?> pl.</div>
      <div style="font-size:.7rem;margin-top:6px"><?= e(table_status_label($t['status'])) ?></div>
    </div>
    <div id="form-<?= $t['id'] ?>" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:100;align-items:center;justify-content:center" onclick="if(event.target===this) this.style.display='none'">
      <div class="card" style="min-width:320px" onclick="event.stopPropagation()">
        <h3 class="mt-0">Table <?= e($t['number']) ?></h3>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="change_status" value="1">
          <input type="hidden" name="table_id" value="<?= $t['id'] ?>">
          <div class="field"><label>Nouvel état</label>
            <select name="status" class="input">
              <?php foreach (['libre','occupee','reservee','nettoyer'] as $s): ?>
                <option value="<?= $s ?>" <?= $s===$t['status']?'selected':'' ?>><?= e(table_status_label($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="flex">
            <button class="btn">Enregistrer</button>
            <a class="btn btn-ghost" href="commandes.php?table=<?= $t['id'] ?>">Prendre commande</a>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
