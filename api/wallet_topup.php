<?php
require_once __DIR__.'/db.php'; 
require_once __DIR__.'/functions.php';

$d = read_json(); 
$uid = $d['uid'] ?? ''; 
$amt = $d['amount'] ?? null;

if($uid === '' || !is_numeric($amt) || ($amt = (float)$amt) <= 0) {
    json_out(['allow' => false, 'msg' => 'bad input'], 400);
}

$pdo = get_pdo(); 

// ค้นหาบัตรและผู้ใช้
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
    json_out(['allow' => false, 'msg' => 'UID not found or inactive']);
}

$new = (float)$row['balance'] + $amt;
$pdo->beginTransaction();

try {
    // สร้าง transaction ID
    $txnId = 'TXN' . strtoupper(base_convert(time(), 10, 36)) . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    
    // บันทึกธุรกรรม topup
    $stmt = $pdo->prepare('INSERT INTO transactions (transaction_id, amount, status, customer_name, type, is_paid, is_confirmed, user_id) VALUES (?, ?, "completed", ?, "topup", 1, 1, ?)');
    $stmt->execute([$txnId, $amt, $row['username'], $row['user_id']]);
    
    $pdo->commit();
    json_out(['allow' => true, 'newBlock4' => block4_from_balance((int)$new), 'msg' => 'OK', 'txn_id' => $txnId]);
} catch(Throwable $e) { 
    $pdo->rollBack(); 
    json_out(['allow' => false, 'msg' => 'db error: ' . $e->getMessage()], 500); 
}