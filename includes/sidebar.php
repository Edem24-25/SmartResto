<?php $u = current_user(); $role = $u['role'] ?? ''; $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
  <nav>
    <a href="<?= BASE_URL ?>/app/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">📊 Tableau de bord</a>
    <?php if (in_array($role, ['gerant','serveur','caissier'])): ?>
    <a href="<?= BASE_URL ?>/app/plan-salle.php" class="<?= $current==='plan-salle.php'?'active':'' ?>">🪑 Plan de salle</a>
    <a href="<?= BASE_URL ?>/app/commandes.php" class="<?= $current==='commandes.php'?'active':'' ?>">📝 Commandes</a>
    <?php endif; ?>
    <?php if (in_array($role, ['gerant','cuisinier'])): ?>
    <a href="<?= BASE_URL ?>/app/cuisine.php" class="<?= $current==='cuisine.php'?'active':'' ?>">🔥 Cuisine (KDS)</a>
    <?php endif; ?>
    <?php if (in_array($role, ['gerant','caissier'])): ?>
    <a href="<?= BASE_URL ?>/app/facturation.php" class="<?= $current==='facturation.php'?'active':'' ?>">💳 Facturation</a>
    <?php endif; ?>
    <?php if ($role === 'gerant'): ?>
    <div class="sidebar-sep">Gestion</div>
    <a href="<?= BASE_URL ?>/app/menu-gestion.php" class="<?= $current==='menu-gestion.php'?'active':'' ?>">🍽️ Menu</a>
    <a href="<?= BASE_URL ?>/app/stocks.php" class="<?= $current==='stocks.php'?'active':'' ?>">📦 Stocks</a>
    <a href="<?= BASE_URL ?>/app/personnel.php" class="<?= $current==='personnel.php'?'active':'' ?>">👥 Personnel</a>
    <a href="<?= BASE_URL ?>/app/rapports.php" class="<?= $current==='rapports.php'?'active':'' ?>">📈 Rapports</a>
    <?php endif; ?>
  </nav>
</aside>
