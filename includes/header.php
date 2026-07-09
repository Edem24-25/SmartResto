<?php require_once __DIR__ . '/../config/config.php'; $u = current_user(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="app <?= $u ? 'is-authed' : 'is-public' ?>">
<?php if ($u): ?>
<header class="topbar">
  <div class="brand"><a href="<?= BASE_URL ?>/app/dashboard.php"><span class="logo">◆</span> SmartResto</a></div>
  <div class="topbar-right">
    <span class="user-chip"><strong><?= e($u['full_name']) ?></strong><small><?= e(role_label($u['role'])) ?></small></span>
    <a class="btn btn-ghost" href="<?= BASE_URL ?>/app/logout.php">Déconnexion</a>
  </div>
</header>
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="main">
<?php endif; ?>
