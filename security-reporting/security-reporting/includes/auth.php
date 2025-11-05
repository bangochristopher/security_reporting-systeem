<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] === true;
}

function login($email, $password) {
    require __DIR__ . '/db.php';
    $stmt = $pdo->prepare('SELECT * FROM staff WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $staff = $stmt->fetch();
    if ($staff) {
        if (password_verify($password, $staff['password']) || $password === $staff['password']) {
            $_SESSION['staff_logged_in'] = true;
            $_SESSION['staff_name'] = $staff['name'];
            $_SESSION['staff_role'] = $staff['role'];
            $_SESSION['staff_id'] = $staff['id'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
} 