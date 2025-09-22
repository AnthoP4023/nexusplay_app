<?php
session_start();

require_once __DIR__ . '/functions_panel/fun_auth_panel.php';

logoutPanelAdmin();

header('Location: dashboard.php');
exit();
?>