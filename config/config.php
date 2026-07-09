<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'SmartResto');
define('APP_TAGLINE', 'La gestion moderne des restaurants de Cotonou');
define('CURRENCY', 'FCFA');
define('TAX_RATE', 0.00); // v1 : pas de TVA
define('BASE_URL', '/smartresto');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';
