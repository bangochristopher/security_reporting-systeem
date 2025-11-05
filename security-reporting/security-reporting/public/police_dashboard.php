<?php
session_start();
require_once __DIR__ . '/../includes/police_auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!police_is_logged_in()) {
    header('Location: police-login.php');
    exit;
}

$police_id = $_SESSION['police_id'];

// Fetch cases assigned to police or escalated without specific police
$stmt = $pdo->prepare('SELECT c.*, s.name AS handled_name, s.role AS handled_role
                       FROM cases c
                       LEFT JOIN staff s ON c.handled_by = s.id
                       WHERE c.status IN ("Escalated", "Investigating")
                         AND (c.assigned_police_id IS NULL OR c.assigned_police_id = ?)
                       ORDER BY c.updated_at DESC');
$stmt->execute([$police_id]);
$cases = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZRP Police Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ccc;
        }
        .actions a { margin-right: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://mir-s3-cdn-cf.behance.net/projects/404/5c84214687153.Y3JvcCwyNTAwLDE5NTUsMCwyNzI.png" alt="Logo" class="logo">
    </div>
    <div class="container">
        <a href="logout.php" class="logout">Logout</a>
        <h2>ZRP Police Cases</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Assigned</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($cases as $case): ?>
                <tr>
                    <td><?= $case['id'] ?></td>
                    <td><?= htmlspecialchars($case['student_name']) ?> (<?= htmlspecialchars($case['student_number'] ?? 'N/A') ?>)</td>
                    <td><?= htmlspecialchars($case['case_title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($case['case_description'])) ?></td>
                    <td><?= htmlspecialchars($case['status']) ?></td>
                    <td><?= $case['assigned_police_id'] ? ($case['assigned_police_id']==$police_id?'You':'Assigned') : 'Unassigned' ?></td>
                    <td><?= htmlspecialchars($case['updated_at']) ?></td>
                    <td class="actions">
                        <?php if (!$case['assigned_police_id']): ?>
                            <a href="police_take_case.php?id=<?= $case['id'] ?>">Take Case</a>
                        <?php endif; ?>
                        <a href="police_update_case.php?id=<?= $case['id'] ?>">View/Update</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>


