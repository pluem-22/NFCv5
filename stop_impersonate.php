<?php
// stop_impersonate.php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db_config.php';

if (!isset($_SESSION['impersonator_admin_id'], $_SESSION['impersonation_log_id'])) {
    header('Location: index.php'); exit;
}

// ปิด log
$log_id = intval($_SESSION['impersonation_log_id']);
$st=$conn->prepare("UPDATE admin_impersonation_logs SET stopped_at = NOW() WHERE id=?");
$st->execute([$log_id]);

// คืนตัวตนเป็นแอดมิน
$admin_id = intval($_SESSION['impersonator_admin_id']);
unset($_SESSION['impersonator_admin_id'], $_SESSION['impersonation_log_id']);

$s=$conn->prepare("SELECT id, username, role, name FROM users WHERE id=? LIMIT 1");
$s->execute([$admin_id]);
$user = $s->fetch(PDO::FETCH_ASSOC);
if ($user) {
    $_SESSION['user_id']=$user['id']; 
    $_SESSION['username']=$user['username']; 
    $_SESSION['role']=$user['role']; 
    $_SESSION['name']=$user['name'];
}

header('Location: index.php');
