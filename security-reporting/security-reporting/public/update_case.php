<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!is_logged_in()) {
    header('Location: admin-login.php');
    exit;
}

$case_id = $_GET['id'] ?? null;
$message = '';
$error = '';

if (!$case_id) {
    header('Location: dashboard.php');
    exit;
}

// Get case details
$stmt = $pdo->prepare('SELECT * FROM cases WHERE id = ?');
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $escalate_to_police = isset($_POST['escalate_to_police']) ? true : false;
    $assigned_police_id = isset($_POST['assigned_police_id']) && $_POST['assigned_police_id'] !== '' ? (int)$_POST['assigned_police_id'] : null;
    $message_to_police = trim($_POST['message_to_police'] ?? '');
    
    if ($status) {
        try {
            // Update basic fields
            $assignedPoliceId = $case['assigned_police_id'];
            if ($escalate_to_police) {
                $status = 'Escalated';
            }
            if ($assigned_police_id) {
                $assignedPoliceId = $assigned_police_id;
            }
            $stmt = $pdo->prepare('UPDATE cases SET status = ?, notes = ?, handled_by = ?, handled_role = ?, assigned_police_id = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$status, $notes, $_SESSION['staff_id'], $_SESSION['staff_role'] ?? 'admin', $assignedPoliceId, $case_id]);
            
            // If escalating or sending message, create a message entry from admin to police
            if (($escalate_to_police || $message_to_police !== '') && ($message_to_police !== '')) {
                $stmtMsg = $pdo->prepare('INSERT INTO case_messages (case_id, sender_type, sender_id, message) VALUES (?, "admin", ?, ?)');
                $stmtMsg->execute([$case_id, $_SESSION['staff_id'], $message_to_police]);
            }
            $message = 'Case updated successfully!';
            
            // Refresh case data
            $stmt = $pdo->prepare('SELECT * FROM cases WHERE id = ?');
            $stmt->execute([$case_id]);
            $case = $stmt->fetch();
        } catch (Exception $e) {
            $error = 'Error updating case: ' . $e->getMessage();
        }
    } else {
        $error = 'Please select a status.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Case</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        h2 { text-align: center; }
        .case-info { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        select, textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
        textarea { height: 100px; resize: vertical; }
        button { margin-top: 20px; padding: 10px 20px; background: #0073e6; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .message { margin-top: 15px; color: green; text-align: center; }
        .error { margin-top: 15px; color: red; text-align: center; }
        .back-link { margin-top: 20px; text-align: center; }
        .back-link a { color: #0073e6; text-decoration: none; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Update Case #<?= $case['id'] ?></h2>
        
        <div class="case-info">
            <strong>Student:</strong> <?= htmlspecialchars($case['student_name']) ?> (<?= htmlspecialchars($case['student_number'] ?? 'N/A') ?>)<br>
            <strong>Email:</strong> <?= htmlspecialchars($case['student_email']) ?><br>
            <strong>Title:</strong> <?= htmlspecialchars($case['case_title']) ?><br>
            <strong>Description:</strong> <?= nl2br(htmlspecialchars($case['case_description'])) ?><br>
            <strong>Current Status:</strong> <?= htmlspecialchars($case['status']) ?><br>
            <strong>Reported:</strong> <?= $case['created_at'] ?>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="status">Update Status</label>
            <select id="status" name="status" required>
                <option value="">Select Status</option>
                <option value="Open" <?= $case['status'] === 'Open' ? 'selected' : '' ?>>Open</option>
                <option value="Investigating" <?= $case['status'] === 'Investigating' ? 'selected' : '' ?>>Investigating</option>
                <option value="Escalated" <?= $case['status'] === 'Escalated' ? 'selected' : '' ?>>Escalated</option>
                <option value="Resolved" <?= $case['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="Closed" <?= $case['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
            </select>

            <label for="notes">Staff Notes</label>
            <textarea id="notes" name="notes" placeholder="Add any notes about this case..."><?= htmlspecialchars($case['notes'] ?? '') ?></textarea>

            <div style="margin-top:15px;">
                <label><input type="checkbox" name="escalate_to_police" <?= $case['status']==='Escalated' ? 'checked' : '' ?>> Escalate to ZRP Police</label>
            </div>
            <?php
            // Load available police list
            $policeOptions = [];
            try {
                $ps = $pdo->query('SELECT id, name FROM police_staff ORDER BY name');
                $policeOptions = $ps->fetchAll();
            } catch (Exception $e) {}
            ?>
            <label for="assigned_police_id">Assign to Police Officer</label>
            <select id="assigned_police_id" name="assigned_police_id">
                <option value="">-- Optional --</option>
                <?php foreach ($policeOptions as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= isset($case['assigned_police_id']) && (int)$case['assigned_police_id'] === (int)$p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="message_to_police">Message to Police</label>
            <textarea id="message_to_police" name="message_to_police" placeholder="Provide context for police..."></textarea>

            <?php
            // Load existing messages
            try {
                $stmtMsgs = $pdo->prepare('SELECT m.*, s.name AS sender_name FROM case_messages m LEFT JOIN staff s ON s.id = m.sender_id WHERE m.case_id = ? ORDER BY m.created_at ASC');
                $stmtMsgs->execute([$case_id]);
                $msgs = $stmtMsgs->fetchAll();
            } catch (Exception $e) {
                $msgs = [];
            }
            ?>
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

            <button type="submit">Update Case</button>
        </form>

        <div class="back-link">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 