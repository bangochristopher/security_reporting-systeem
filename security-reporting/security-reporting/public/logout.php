<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/police_auth.php';
// Log out both possible sessions
logout();
police_logout();
header('Location: ../index.php');
exit; 