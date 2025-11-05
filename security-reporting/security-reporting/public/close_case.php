<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!is_logged_in()) {
    header('Location: admin-login.php');
    exit;
}

$case_id = $_GET['id'] ?? null;

if ($case_id) {
    try {
        $stmt = $pdo->prepare('UPDATE cases SET status = "Closed", handled_by = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$_SESSION['staff_id'], $case_id]);
    } catch (Exception $e) {
    }
}

header('Location: dashboard.php');
exit; 