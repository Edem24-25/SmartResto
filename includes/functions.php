<?php
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function money($n) { return number_format((float)$n, 0, ',', ' ') . ' ' . CURRENCY; }

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) redirect('/public/login.php');
}

function require_role(array $roles) {
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        die('Accès refusé — rôle insuffisant.');
    }
}

function role_label($r) {
    return [
        'gerant' => 'Gérant',
        'serveur' => 'Serveur',
        'cuisinier' => 'Cuisinier',
        'caissier' => 'Caissier',
    ][$r] ?? $r;
}

function table_status_label($s) {
    return ['libre'=>'Libre','occupee'=>'Occupée','reservee'=>'Réservée','nettoyer'=>'À nettoyer'][$s] ?? $s;
}

function order_status_label($s) {
    return [
        'brouillon'=>'Brouillon','envoyee'=>'Envoyée','en_preparation'=>'En préparation',
        'pret'=>'Prête','servie'=>'Servie','encaissee'=>'Encaissée','annulee'=>'Annulée'
    ][$s] ?? $s;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_check() {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '_')) {
        http_response_code(400); die('Jeton CSRF invalide.');
    }
}

function flash($key, $msg = null) {
    if ($msg === null) {
        $v = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $v;
    }
    $_SESSION['flash'][$key] = $msg;
}
