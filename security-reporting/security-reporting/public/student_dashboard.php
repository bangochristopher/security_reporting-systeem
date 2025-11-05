<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
    header('Location: student-login.php');
    exit;
}


$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$message = '';

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ? LIMIT 1');
$stmt->execute([$student_id]);
$student = $stmt->fetch();
$student_email = $student['email'] ?? '';
$student_number = $student['student_number'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_title = trim($_POST['case_title'] ?? '');
    $case_description = trim($_POST['case_description'] ?? '');
    if ($case_title && $case_description) {
        try {
            $stmt = $pdo->prepare('INSERT INTO cases (student_name, student_number, student_email, case_title, case_description) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $student_name,
                $student_number,
                $student_email,
                $case_title,
                $case_description
            ]);
            $message = 'Case submitted successfully!';
        } catch (Exception $e) {
            $message = 'Error submitting case: ' . $e->getMessage();
        }
    } else {
        $message = 'Please fill in all fields.';
    }
}

$stmt = $pdo->prepare('SELECT * FROM cases WHERE student_number = ? ORDER BY created_at DESC');
$stmt->execute([$student_number]);
$cases = $stmt->fetchAll();

// Load messages for student's cases (admin-origin messages summarized per case)
$caseIds = array_map(function($c){ return $c['id']; }, $cases);
$messagesByCase = [];
if ($caseIds) {
    $placeholders = implode(',', array_fill(0, count($caseIds), '?'));
    $stmtMsgs = $pdo->prepare("SELECT case_id, message, created_at FROM case_messages WHERE case_id IN ($placeholders) AND sender_type IN ('admin','police') ORDER BY created_at DESC");
    $stmtMsgs->execute($caseIds);
    while ($row = $stmtMsgs->fetch()) {
        if (!isset($messagesByCase[$row['case_id']])) { $messagesByCase[$row['case_id']] = []; }
        $messagesByCase[$row['case_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        h2 { text-align: center; }
        form { margin-bottom: 40px; }
        label { display: block; margin-top: 15px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
        button { margin-top: 20px; width: 100%; padding: 10px; background: #AC3039; color: #fff; border: none; border-radius: 4px; font-size: 16px; }
        .message { margin-top: 15px; color: green; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #AC3039; color: #fff; }
    </style>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
            <a href="student_logout.php" style="background: #AC3039; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: bold;">Logout</a>
        </div>
        <form method="post">
            <h3>Submit a New Case</h3>
            <label for="case_title">Case Title</label>
            <input type="text" name="case_title" id="case_title" required>
            <label for="case_description">Case Description</label>
            <textarea name="case_description" id="case_description" rows="4" required></textarea>
            <button type="submit">Submit Case</button>
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
        </form>
        <h3>Your Submitted Cases</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Latest Update</th>
            </tr>
            <?php foreach ($cases as $case): ?>
                <tr>
                    <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                    <td><?php echo htmlspecialchars($case['case_description']); ?></td>
                    <td><?php echo htmlspecialchars($case['status']); ?></td>
                    <td><?php echo htmlspecialchars($case['updated_at']); ?></td>
                    <td>
                        <?php 
                        $latest = $messagesByCase[$case['id']][0]['message'] ?? '';
                        echo $latest ? htmlspecialchars(mb_strimwidth($latest, 0, 60, '...')) : '-';
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
