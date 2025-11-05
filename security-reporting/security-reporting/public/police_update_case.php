<?php
session_start();
require_once __DIR__ . '/../includes/police_auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!police_is_logged_in()) {
    header('Location: police-login.php');
    exit;
}

$case_id = $_GET['id'] ?? null;
if (!$case_id) {
    header('Location: police_dashboard.php');
    exit;
}

// Fetch case
$stmt = $pdo->prepare('SELECT * FROM cases WHERE id = ?');
$stmt->execute([$case_id]);
$case = $stmt->fetch();
if (!$case) {
    header('Location: police_dashboard.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? '');
    $police_notes = trim($_POST['police_notes'] ?? '');
    $message_to_admin = trim($_POST['message_to_admin'] ?? '');

    if ($status) {
        try {
            $stmt = $pdo->prepare('UPDATE cases SET status = ?, notes = ?, handled_by = ?, handled_role = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$status, $police_notes, $_SESSION['police_id'], 'police', $case_id]);

            if ($message_to_admin !== '') {
                $stmtMsg = $pdo->prepare('INSERT INTO case_messages (case_id, sender_type, sender_id, message) VALUES (?, "police", ?, ?)');
                $stmtMsg->execute([$case_id, $_SESSION['police_id'], $message_to_admin]);
            }

            $message = 'Case updated.';

            $stmt = $pdo->prepare('SELECT * FROM cases WHERE id = ?');
            $stmt->execute([$case_id]);
            $case = $stmt->fetch();
        } catch (Exception $e) {
            $error = 'Error updating: ' . $e->getMessage();
        }
    } else {
        $error = 'Status is required.';
    }
}

// Load messages
$stmtMsgs = $pdo->prepare('SELECT m.*, s.name AS sender_name FROM case_messages m LEFT JOIN staff s ON s.id = m.sender_id WHERE m.case_id = ? ORDER BY m.created_at ASC');
$stmtMsgs->execute([$case_id]);
$msgs = $stmtMsgs->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Police Update Case</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        select, textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
        textarea { height: 100px; resize: vertical; }
        .message { margin-top: 15px; color: green; text-align: center; }
        .error { margin-top: 15px; color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Case #<?= $case['id'] ?></h2>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="case-info" style="background:#f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <strong>Student:</strong> <?= htmlspecialchars($case['student_name']) ?> (<?= htmlspecialchars($case['student_number'] ?? 'N/A') ?>)<br>
            <strong>Email:</strong> <?= htmlspecialchars($case['student_email']) ?><br>
            <strong>Title:</strong> <?= htmlspecialchars($case['case_title']) ?><br>
            <strong>Description:</strong> <?= nl2br(htmlspecialchars($case['case_description'])) ?><br>
            <strong>Current Status:</strong> <?= htmlspecialchars($case['status']) ?><br>
            <strong>Reported:</strong> <?= $case['created_at'] ?>
        </div>

        <form method="post">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Investigating" <?= $case['status'] === 'Investigating' ? 'selected' : '' ?>>Investigating</option>
                <option value="Resolved" <?= $case['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="Closed" <?= $case['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
            </select>

            <label for="police_notes">Police Notes</label>
            <textarea id="police_notes" name="police_notes" placeholder="Add your investigation notes..."></textarea>

            <label for="message_to_admin">Message to Admin</label>
            <textarea id="message_to_admin" name="message_to_admin" placeholder="Provide feedback for Admin..."></textarea>

            <button type="submit">Save</button>
            <a href="police_dashboard.php" style="margin-left:10px;">Cancel</a>
        </form>

        <div style="margin-top:20px; background:#f9f9f9; padding:10px; border-radius:4px;">
            <strong>Admin–Police Messages</strong>
            <div>
                <?php if (!$msgs): ?>
                    <div style="color:#666;">No messages yet.</div>
                <?php else: ?>
                    <?php foreach ($msgs as $m): ?>
                        <div style="margin:8px 0;">
                            <em>[<?= htmlspecialchars($m['created_at']) ?>]</em>
                            <strong><?= htmlspecialchars(ucfirst($m['sender_type'])) ?><?= $m['sender_name'] ? ' ('.htmlspecialchars($m['sender_name']).')' : '' ?>:</strong>
                            <?= nl2br(htmlspecialchars($m['message'])) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>


