<?php
session_start();
if (isset($_SESSION['student_logged_in'])) {
    session_unset();
    session_destroy();
}
header('Location: ../index.php');
exit;
