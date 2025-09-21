<?php
// test_api.php - ไฟล์ทดสอบ API endpoints
require_once __DIR__.'/functions.php';

// ฟังก์ชันทดสอบ API
function test_api($url, $data, $method = 'POST') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, $method === 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

echo "<h1>API Test Results</h1>";

// ทดสอบ get_balance
echo "<h2>1. Testing get_balance</h2>";
$result = test_api('http://localhost/projactnfc/api/get_balance.php', ['uid' => '04AABBCCDD']);
echo "<pre>" . print_r($result, true) . "</pre>";

// ทดสอบ card_register
echo "<h2>2. Testing card_register</h2>";
$result = test_api('http://localhost/projactnfc/api/card_register.php', [
    'uid' => '04TEST123',
    'user_id' => 1,
    'nickname' => 'Test Card'
]);
echo "<pre>" . print_r($result, true) . "</pre>";

// ทดสอบ wallet_topup
echo "<h2>3. Testing wallet_topup</h2>";
$result = test_api('http://localhost/projactnfc/api/wallet_topup.php', [
    'uid' => '04TEST123',
    'amount' => 100.50
]);
echo "<pre>" . print_r($result, true) . "</pre>";

// ทดสอบ wallet_authorize
echo "<h2>4. Testing wallet_authorize</h2>";
$result = test_api('http://localhost/projactnfc/api/wallet_authorize.php', [
    'uid' => '04TEST123',
    'amount' => 50.25
]);
echo "<pre>" . print_r($result, true) . "</pre>";

// ทดสอบ wallet_confirm
echo "<h2>5. Testing wallet_confirm</h2>";
$result = test_api('http://localhost/projactnfc/api/wallet_confirm.php', [
    'uid' => '04TEST123',
    'applied' => 'ABCD1234EFGH5678IJKL9012MNOP3456'
]);
echo "<pre>" . print_r($result, true) . "</pre>";

echo "<h2>Test Complete</h2>";
?>
