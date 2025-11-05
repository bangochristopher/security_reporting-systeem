<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!is_logged_in()) {
    header('Location: admin-login.php');
    exit;
}

$stmt = $pdo->query('SELECT c.*, s.name AS handled_name, s.role AS handled_role, p.name AS assigned_police_name FROM cases c LEFT JOIN staff s ON c.handled_by = s.id LEFT JOIN police_staff p ON c.assigned_police_id = p.id ORDER BY c.created_at DESC');
$cases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Cases Dashboard</title>
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
    </style>
</head>
<body>
    <div class="header">
        <img src="images/lg.png" alt="Logo" class="logo">
    </div>
    <div class="container">
        <a href="logout.php" class="logout">Logout</a>
        <h2>Security Cases</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Student Number</th>
                <th>Student Email</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Assigned Police</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Handled By</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($cases as $case): ?>
                <tr>
                    <td><?= $case['id'] ?></td>
                    <td><?= htmlspecialchars($case['student_name']) ?></td>
                    <td><?= htmlspecialchars($case['student_number'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($case['student_email']) ?></td>
                    <td><?= htmlspecialchars($case['case_title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($case['case_description'])) ?></td>
                    <td>
                        <span style="
                            padding: 4px 8px; 
                            border-radius: 4px; 
                            font-size: 12px; 
                            font-weight: bold;
                            <?php
                            switch($case['status']) {
                                case 'Open': echo 'background: #ffeb3b; color: #000;'; break;
                                case 'Investigating': echo 'background: #2196f3; color: #fff;'; break;
                                case 'Escalated': echo 'background: #ff9800; color: #fff;'; break;
                                case 'Resolved': echo 'background: #4caf50; color: #fff;'; break;
                                case 'Closed': echo 'background: #9e9e9e; color: #fff;'; break;
                                default: echo 'background: #f5f5f5; color: #000;';
                            }
                            ?>
                        ">
                            <?= htmlspecialchars($case['status']) ?>
                        </span>
                    </td>
                    <td><?= $case['notes'] ? htmlspecialchars(substr($case['notes'], 0, 50)) . (strlen($case['notes']) > 50 ? '...' : '') : '-' ?></td>
                    <td><?= $case['assigned_police_name'] ? htmlspecialchars($case['assigned_police_name']) : '-' ?></td>
                    <td><?= $case['created_at'] ?></td>
                    <td><?= $case['updated_at'] ?></td>
                    <td><?= $case['handled_name'] ? htmlspecialchars($case['handled_name']) : '-' ?></td>
                    <td class="actions">
                        <a href="update_case.php?id=<?= $case['id'] ?>">Update</a>
                        <a href="close_case.php?id=<?= $case['id'] ?>" onclick="return confirm('Close this case?');">Close</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html> 