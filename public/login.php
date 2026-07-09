<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
if (current_user()) redirect('/app/dashboard.php');
$error = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (attempt_login($pdo, $email, $pass)) redirect('/app/dashboard.php');
    $error = "Identifiants invalides.";
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Connexion — SmartResto</title><link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css"></head><body class="is-public">
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">◆</div>
    <h1>SmartResto</h1>
    <p class="text-center text-muted mt-0">Connectez-vous à votre espace de gestion</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="field"><label>Email</label><input class="input" type="email" name="email" required autofocus></div>
      <div class="field"><label>Mot de passe</label><input class="input" type="password" name="password" required></div>
      <button class="btn btn-block">Se connecter</button>
    </form>
    <div class="demo-creds">
      <b>Comptes de démonstration :</b><br>
      Gérant : admin@smartresto.bj / admin123<br>
      Serveur : serveur@smartresto.bj / serveur123<br>
      Cuisinier : cuisine@smartresto.bj / cuisine123<br>
      Caissier : caisse@smartresto.bj / caisse123
    </div>
    <p class="text-center mt-2"><a href="index.php" class="text-muted">← Retour au site</a></p>
  </div>
</div>
</body></html>
