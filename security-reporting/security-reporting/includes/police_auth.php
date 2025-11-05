<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function police_is_logged_in() {
    return isset($_SESSION['police_logged_in']) && $_SESSION['police_logged_in'] === true;
}

function police_login($email, $password) {
    require __DIR__ . '/db.php';
    $stmt = $pdo->prepare('SELECT * FROM police_staff WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $police = $stmt->fetch();
    if ($police) {
        if (password_verify($password, $police['password']) || $password === $police['password']) {
            $_SESSION['police_logged_in'] = true;
            $_SESSION['police_name'] = $police['name'];
            $_SESSION['police_id'] = $police['id'];
            return true;
        }
    }
    return false;
}

function police_logout() {
    unset($_SESSION['police_logged_in'], $_SESSION['police_name'], $_SESSION['police_id']);
}


