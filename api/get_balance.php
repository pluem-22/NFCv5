<?php
require_once __DIR__.'/db.php'; 
require_once __DIR__.'/functions.php';

$d = read_json(); 
$uid = $d['uid'] ?? '';

if($uid === '') {
    json_out(['success' => false, 'msg' => 'bad input'], 400);
}

$pdo = get_pdo(); 

try {
    // คำนวณยอดคงเหลือจาก transactions
    $stmt = $pdo->prepare('
        SELECT nc.card_uid, u.id as user_id, u.username,
               COALESCE(SUM(CASE 
                   WHEN t.type = "topup" THEN t.amount 
                   WHEN t.type = "buy" THEN -t.amount 
                   ELSE 0 
               END), 0) as balance
        FROM nfc_cards nc 
        LEFT JOIN users u ON nc.user_id = u.id 
        LEFT JOIN transactions t ON u.id = t.user_id AND t.is_confirmed = 1
        WHERE nc.card_uid = ? AND nc.is_active = 1
        GROUP BY nc.card_uid, u.id, u.username
    ');
    $stmt->execute([$uid]); 
    $row = $stmt->fetch();

    if(!$row) {
        json_out(['success' => false, 'msg' => 'Card not found or inactive']);
    }

    json_out([
        'success' => true, 
        'balance' => (float)$row['balance'],
        'user_id' => $row['user_id'],
        'username' => $row['username'],
        'card_uid' => $row['card_uid']
    ]);
    
} catch(Throwable $e) {
    json_out(['success' => false, 'msg' => 'db error: ' . $e->getMessage()], 500);
}
