<?php
session_start(); 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: ../index.php");
    exit();
}
if ($_SESSION['user_type'] === 'administrador') {
    header("Location: ../profile/admin/admin.php");
    exit();
} else {
    header("Location: ../profile/user/user.php");
    exit();
}
