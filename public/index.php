<?php
require_once __DIR__ . '/../config/config.php';
$stmt = $pdo->query('SELECT c.name AS cat, d.name, d.description, d.price FROM dishes d JOIN categories c ON c.id=d.category_id WHERE d.available=1 ORDER BY c.display_order, d.name LIMIT 8');
$dishes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SmartResto — La gestion moderne des restaurants</title>
<meta name="description" content="SmartResto : plateforme complète de gestion de restaurant à Cotonou. Salle, cuisine, facturation, stock et pilotage réunis.">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head><body class="is-public">
<nav class="public-nav"><div class="container">
  <a href="index.php" style="font-weight:700;font-size:1.2rem;color:var(--text)"><span style="color:var(--primary)">◆</span> SmartResto</a>
  <div>
    <a href="index.php" class="nav-link">Accueil</a>
    <a href="menu.php" class="nav-link">Menu</a>
    <a href="contact.php" class="nav-link">Contact</a>
    <a href="login.php" class="btn btn-sm">Espace pro</a>
  </div>
</div></nav>

<section class="hero"><div class="container hero-grid">
  <div class="hero-copy">
    <h1>Le restaurant, <span style="color:var(--primary)">simplifié.</span></h1>
    <p>SmartResto pilote l'ensemble de votre activité : salle, cuisine, facturation, stocks et rapports — depuis une seule application pensée pour les restaurateurs de Cotonou.</p>
    <div class="cta">
      <a href="login.php" class="btn">Accéder à mon espace →</a>
      <a href="menu.php" class="btn btn-ghost">Voir notre carte</a>
    </div>
  </div>
  <div class="hero-media">
    <img src="<?= BASE_URL ?>/assets/img/restaurant-hero.png" alt="Ambiance d'un restaurant moderne">
  </div>
</div></section>

<section class="features"><div class="container">
  <h2 class="section-title">Une plateforme, tous vos besoins</h2>
  <p class="section-sub">De la prise de commande à l'encaissement Mobile Money, SmartResto couvre chaque étape du service.</p>
  <div class="features-grid">
    <div class="feature"><span class="icon">🪑</span><h3>Plan de salle temps réel</h3><p>Visualisez l'état de chaque table (libre, occupée, réservée) et changez le statut en un clic.</p></div>
    <div class="feature"><span class="icon">🔥</span><h3>Écran cuisine (KDS)</h3><p>Les commandes arrivent en cuisine sans papier, avec minuteur et alerte de retard.</p></div>
    <div class="feature"><span class="icon">💳</span><h3>Encaissement Mobile Money</h3><p>MTN, Moov, Celtiis, espèces ou carte — tout est intégré à la facture.</p></div>
    <div class="feature"><span class="icon">📦</span><h3>Gestion des stocks</h3><p>Suivi par ingrédient, seuils d'alerte, entrées fournisseurs.</p></div>
    <div class="feature"><span class="icon">📊</span><h3>Tableau de bord</h3><p>Chiffre d'affaires du jour, plats les plus vendus, alertes en un coup d'œil.</p></div>
    <div class="feature"><span class="icon">👥</span><h3>Personnel & rôles</h3><p>Gérant, serveur, cuisinier, caissier — chacun voit ce qui le concerne.</p></div>
  </div>
</div></section>

<section class="menu-preview"><div class="container">
  <h2 class="section-title">Un aperçu de notre carte</h2>
  <p class="section-sub">Cuisine locale et internationale, servie chaque jour au cœur de Cotonou.</p>
  <div class="menu-grid">
    <?php foreach ($dishes as $d): ?>
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
      } elseif (strpos($slug, 'riz') !== false || strpos($slug, 'poulet') !== false) {
        $image = 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80';
      }
    ?>
    <div class="dish-card">
      <img class="dish-image" src="<?= e($image) ?>" alt="Photo de <?= e($d['name']) ?>">
      <div class="content">
        <div class="flex-between"><h4><?= e($d['name']) ?></h4><span class="price"><?= money($d['price']) ?></span></div>
        <small class="text-muted"><?= e($d['cat']) ?></small>
        <?php if ($d['description']): ?><p><?= e($d['description']) ?></p><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-2"><a href="menu.php" class="btn btn-ghost">Voir toute la carte</a></div>
</div></section>

<footer class="public-footer"><div class="container">
  <p>© <?= date('Y') ?> SmartResto — Cotonou, Bénin · <a href="contact.php">Contact</a> · <a href="login.php">Connexion</a></p>
</div></footer>
</body></html>
