<?php
require_once __DIR__ . '/../config/config.php';

function attempt_login(PDO $pdo, string $email, string $password): bool {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if (!$u) return false;

    // Support des mots de passe seed non-hashés lors de la démo
    $ok = password_verify($password, $u['password_hash']);
    if (!$ok) {
        // Fallback démo : mapping en clair -> reset hash
        $demo = [
            'admin@smartresto.bj' => 'admin123',
            'serveur@smartresto.bj' => 'serveur123',
            'cuisine@smartresto.bj' => 'cuisine123',
            'caisse@smartresto.bj' => 'caisse123',
        ];
        if (isset($demo[$email]) && $demo[$email] === $password) {
            $new = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$new, $u['id']]);
            $ok = true;
        }
    }
    if (!$ok) return false;

    $_SESSION['user'] = [
        'id' => (int)$u['id'],
        'full_name' => $u['full_name'],
        'email' => $u['email'],
        'role' => $u['role'],
    ];
    return true;
}

function logout() {
    $_SESSION = [];
    session_destroy();
}
