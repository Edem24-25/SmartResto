<?php
// Redirection racine
require_once __DIR__ . '/config/config.php';
if (current_user()) redirect('/app/dashboard.php');
redirect('/public/index.php');
