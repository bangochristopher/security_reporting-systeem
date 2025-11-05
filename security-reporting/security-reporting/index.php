<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Reporting System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; text-align: center; }
        h2 { font-size: 28px; font-weight: 600; margin-bottom: 30px; }
        .btn { display: block; width: 100%; padding: 15px; margin: 20px 0; background: #0073e6; color: #fff; border: none; border-radius: 4px; font-size: 18px; cursor: pointer; text-decoration: none; }
        .btn.admin { background: #AC3039; }
        .logo { width: 120px; margin-bottom: 20px; }
    </style>
    <link rel="stylesheet" href="public/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    
</head>
<body>
    <div class="container">
        <img src="public/images/university-logo.png" alt="University Logo" class="logo">
        <h2>Welcome to Security Reporting</h2>
        <a href="public/admin-login.php" class="btn admin">Admin Login</a>
        <a href="public/student-login.php" class="btn">Student Login</a>
        <a href="public/police-login.php" class="btn">ZRP Login</a>
    </div>
</body>
</html>
  