<?php require_once __DIR__ . '/../config/config.php';
$sent = false;
if ($_SERVER['REQUEST_METHOD']==='POST') { $sent = true; }
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact — SmartResto</title><link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css"></head><body class="is-public">
<nav class="public-nav"><div class="container">
  <a href="index.php" style="font-weight:700;font-size:1.2rem;color:var(--text)"><span style="color:var(--primary)">◆</span> SmartResto</a>
  <div><a href="index.php" class="nav-link">Accueil</a><a href="menu.php" class="nav-link">Menu</a><a href="contact.php" class="nav-link">Contact</a><a href="login.php" class="btn btn-sm">Espace pro</a></div>
</div></nav>
<section style="padding:60px 0"><div class="container" style="max-width:900px">
  <h1 class="section-title">Nous contacter</h1>
  <div class="grid-2 mt-2">
    <div class="card">
      <h3>Informations</h3>
      <p>📍 Boulevard de la Marina, Cotonou, Bénin</p>
      <p>📞 +229 97 00 00 01</p>
      <p>✉️ contact@smartresto.bj</p>
      <p>🕐 Lun-Dim, 11h-23h</p>
    </div>
    <div class="card">
      <h3>Envoyez-nous un message</h3>
      <?php if ($sent): ?><div class="alert alert-success">Merci, votre message a bien été envoyé.</div><?php endif; ?>
      <form method="post">
        <div class="field"><label>Nom</label><input class="input" name="name" required></div>
        <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
        <div class="field"><label>Message</label><textarea class="input" name="msg" rows="4" required></textarea></div>
        <button class="btn btn-block">Envoyer</button>
      </form>
    </div>
  </div>
</div></section>
<footer class="public-footer"><div class="container"><p>© <?= date('Y') ?> SmartResto — Cotonou, Bénin</p></div></footer>
</body></html>
