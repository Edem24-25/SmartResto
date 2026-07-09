<?php
require_once __DIR__ . '/../config/config.php';
$cats = $pdo->query('SELECT * FROM categories ORDER BY display_order')->fetchAll();
$byCat = [];
foreach ($pdo->query('SELECT * FROM dishes WHERE available=1 ORDER BY name') as $d) $byCat[$d['category_id']][] = $d;
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Notre carte — SmartResto</title><link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css"></head><body class="is-public">
<nav class="public-nav"><div class="container">
  <a href="index.php" style="font-weight:700;font-size:1.2rem;color:var(--text)"><span style="color:var(--primary)">◆</span> SmartResto</a>
  <div><a href="index.php" class="nav-link">Accueil</a><a href="menu.php" class="nav-link">Menu</a><a href="contact.php" class="nav-link">Contact</a><a href="login.php" class="btn btn-sm">Espace pro</a></div>
</div></nav>
<section style="padding:60px 0"><div class="container">
  <h1 class="section-title">Notre carte</h1>
  <p class="section-sub">Une sélection de saveurs locales et internationales, préparées chaque jour.</p>
  <?php foreach ($cats as $c): if (empty($byCat[$c['id']])) continue; ?>
    <h2 style="color:var(--primary);margin-top:40px"><?= e($c['name']) ?></h2>
    <div class="menu-grid">
      <?php foreach ($byCat[$c['id']] as $d): ?>
        <?php
          $slug = strtolower($d['name']);
          $image = 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80';
          if (strpos($slug, 'beignet') !== false || strpos($slug, 'crevette') !== false) {
            $image = 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'salade') !== false) {
            $image = 'https://images.unsplash.com/photo-1543353071-873f17a7a088?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'poisson') !== false || strpos($slug, 'grill') !== false) {
            $image = 'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'burger') !== false) {
            $image = 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'pâtes') !== false || strpos($slug, 'pasta') !== false) {
            $image = 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'riz') !== false) {
            $image = 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'poulet') !== false) {
            $image = 'https://images.unsplash.com/photo-1505576633757-0ac1084af824?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'sauce') !== false || strpos($slug, 'soupe') !== false) {
            $image = 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80';
          } elseif (strpos($slug, 'dessert') !== false || strpos($slug, 'gâteau') !== false) {
            $image = 'https://images.unsplash.com/photo-1482049016688-2d3e1b311543?auto=format&fit=crop&w=800&q=80';
          } else {
            $image = 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=800&q=80';
          }
        ?>
        <div class="dish-card">
          <img class="dish-image" src="<?= e($image) ?>" alt="Photo de <?= e($d['name']) ?>">
          <div class="content">
            <div class="flex-between"><h4><?= e($d['name']) ?></h4><span class="price"><?= money($d['price']) ?></span></div>
            <?php if ($d['description']): ?><p><?= e($d['description']) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div></section>
<footer class="public-footer"><div class="container"><p>© <?= date('Y') ?> SmartResto — Cotonou, Bénin</p></div></footer>
</body></html>
