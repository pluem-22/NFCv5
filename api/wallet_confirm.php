<?php
require_once __DIR__.'/db.php'; 
require_once __DIR__.'/functions.php';

$d = read_json(); 
$uid = $d['uid'] ?? ''; 
$ap = $d['applied'] ?? '';

if($uid === '' || $ap === '' || !is_hex32($ap)) {
    json_out(['ok' => false, 'msg' => 'bad input'], 400);
}

$pdo = get_pdo(); 

try {
    // บันทึกการยืนยันในตาราง transactions หรือ logs
    $stmt = $pdo->prepare('INSERT INTO transaction_logs (card_uid, action, applied_hash, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$uid, 'confirm', strtoupper($ap)]);
    
    json_out(['ok' => true, 'msg' => 'confirmed']);
} catch(Throwable $e) {
    json_out(['ok' => false, 'msg' => 'db error: ' . $e->getMessage()], 500);
}