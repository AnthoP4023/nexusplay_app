<?php
session_start(); 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: /nexusplay/index.php");
    exit();
}
if ($_SESSION['user_type'] === 'administrador') {
    header("Location: /nexusplay/profile/admin/admin.php");
    exit();
} else {
    header("Location: /nexusplay/profile/user/user.php");
    exit();
}
