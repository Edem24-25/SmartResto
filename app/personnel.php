<?php
require_once __DIR__ . '/../config/config.php';
require_role(['gerant']);
$pageTitle = 'Personnel';

$act = $_POST['action'] ?? '';
if ($act === 'create') {
    csrf_check();
    $pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (full_name,email,password_hash,role,phone,active) VALUES (?,?,?,?,?,1)')
        ->execute([trim($_POST['full_name']), trim($_POST['email']), $pw, $_POST['role'], trim($_POST['phone'])]);
    redirect('/app/personnel.php');
}
if ($act === 'toggle') { csrf_check(); $pdo->prepare('UPDATE users SET active = 1 - active WHERE id=?')->execute([(int)$_POST['id']]); redirect('/app/personnel.php'); }
if ($act === 'delete') { csrf_check(); $pdo->prepare('DELETE FROM users WHERE id=? AND id<>?')->execute([(int)$_POST['id'], current_user()['id']]); redirect('/app/personnel.php'); }

$users = $pdo->query('SELECT * FROM users ORDER BY role, full_name')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>👥 Personnel & rôles</h1></div>

<div class="card mb-2">
  <h3 class="mt-0">Créer un compte employé</h3>
  <form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="create">
    <div class="row">
      <div class="col field"><label>Nom complet</label><input class="input" name="full_name" required></div>
      <div class="col field"><label>Email</label><input class="input" type="email" name="email" required></div>
    </div>
    <div class="row">
      <div class="col field"><label>Téléphone</label><input class="input" name="phone"></div>
      <div class="col field"><label>Rôle</label>
        <select class="input" name="role" required>
          <option value="serveur">Serveur</option>
          <option value="cuisinier">Cuisinier</option>
          <option value="caissier">Caissier</option>
          <option value="gerant">Gérant</option>
        </select></div>
      <div class="col field"><label>Mot de passe temporaire</label><input class="input" type="text" name="password" required minlength="6"></div>
    </div>
    <button class="btn">Créer le compte</button>
  </form>
</div>

<div class="card">
  <h3 class="mt-0">Employés (<?= count($users) ?>)</h3>
  <table><thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Statut</th><th></th></tr></thead><tbody>
  <?php foreach ($users as $u): ?>
    <tr><td><b><?= e($u['full_name']) ?></b></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td>
    <td><span class="badge badge-envoyee"><?= e(role_label($u['role'])) ?></span></td>
    <td><?php if($u['active']): ?><span class="badge badge-libre">Actif</span><?php else: ?><span class="badge badge-nettoyer">Inactif</span><?php endif; ?></td>
    <td class="flex">
      <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $u['id'] ?>">
        <button class="btn btn-sm btn-ghost"><?= $u['active']?'Désactiver':'Activer' ?></button></form>
      <?php if ($u['id']!==current_user()['id']): ?>
      <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $u['id'] ?>">
        <button class="btn btn-sm btn-danger" data-confirm="Supprimer ce compte ?">Suppr</button></form>
      <?php endif; ?>
    </td></tr>
  <?php endforeach; ?>
  </tbody></table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
