<?php
require_once __DIR__.'/db.php'; 
require_once __DIR__.'/functions.php';

$d = read_json(); 
$uid = $d['uid'] ?? ''; 
$user_id = $d['user_id'] ?? null;
$nickname = $d['nickname'] ?? '';

if($uid === '' || !is_numeric($user_id) || ($user_id = (int)$user_id) <= 0) {
    json_out(['success' => false, 'msg' => 'bad input'], 400);
}

$pdo = get_pdo(); 

try {
    $pdo->beginTransaction();
    
    // ตรวจสอบว่า UID ถูกใช้แล้วหรือไม่
    $stmt = $pdo->prepare('SELECT id FROM nfc_cards WHERE card_uid = ?');
    $stmt->execute([$uid]);
    if($stmt->fetch()) {
        $pdo->rollBack();
        json_out(['success' => false, 'msg' => 'UID already exists']);
    }
    
    // ตรวจสอบว่าผู้ใช้มีบัตรแล้วหรือไม่
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM nfc_cards WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    if($count > 0) {
        $pdo->rollBack();
        json_out(['success' => false, 'msg' => 'User already has a card']);
    }
    
    // เพิ่มบัตรใหม่
    $stmt = $pdo->prepare('INSERT INTO nfc_cards (user_id, card_uid, nickname, is_active, created_at) VALUES (?, ?, ?, 1, NOW())');
    $stmt->execute([$user_id, $uid, $nickname]);
    
    $pdo->commit();
    json_out(['success' => true, 'msg' => 'Card registered successfully']);
    
} catch(Throwable $e) {
    $pdo->rollBack();
    json_out(['success' => false, 'msg' => 'db error: ' . $e->getMessage()], 500);
}
