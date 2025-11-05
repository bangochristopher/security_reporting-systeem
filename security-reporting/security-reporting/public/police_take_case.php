<?php
session_start();
require_once __DIR__ . '/../includes/police_auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!police_is_logged_in()) {
    header('Location: police-login.php');
    exit;
}

$case_id = $_GET['id'] ?? null;
if ($case_id) {
    try {
        $stmt = $pdo->prepare('UPDATE cases SET assigned_police_id = ?, status = IF(status="Escalated", "Investigating", status), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$_SESSION['police_id'], $case_id]);
    } catch (Exception $e) {
    }
}

header('Location: police_dashboard.php');
exit;


