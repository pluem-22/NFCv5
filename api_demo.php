<?php
// api_demo.php - ตัวอย่างการใช้งาน API
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db_config.php';

$userId = intval($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';

$flash = ['ok' => [], 'err' => []];

// ฟังก์ชันเรียกใช้ API
function callAPI($endpoint, $data) {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/' . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
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

// จัดการ POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_or_die($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';

    if ($action === 'register_card') {
        $uid = strtoupper(trim($_POST['card_uid'] ?? ''));
        $nickname = trim($_POST['nickname'] ?? '');
        
        $result = callAPI('card_register.php', [
            'uid' => $uid,
            'user_id' => $userId,
            'nickname' => $nickname
        ]);
        
        if ($result['response']['success']) {
            $flash['ok'][] = "ลงทะเบียนบัตรสำเร็จ";
        } else {
            $flash['err'][] = "ลงทะเบียนล้มเหลว: " . $result['response']['msg'];
        }
    }
    elseif ($action === 'topup') {
        $uid = strtoupper(trim($_POST['card_uid'] ?? ''));
        $amount = floatval($_POST['amount'] ?? 0);
        
        $result = callAPI('wallet_topup.php', [
            'uid' => $uid,
            'amount' => $amount
        ]);
        
        if ($result['response']['allow']) {
            $flash['ok'][] = "เติมเงินสำเร็จ: " . number_format($amount, 2) . " บาท";
        } else {
            $flash['err'][] = "เติมเงินล้มเหลว: " . $result['response']['msg'];
        }
    }
    elseif ($action === 'buy') {
        $uid = strtoupper(trim($_POST['card_uid'] ?? ''));
        $amount = floatval($_POST['amount'] ?? 0);
        
        $result = callAPI('wallet_authorize.php', [
            'uid' => $uid,
            'amount' => $amount
        ]);
        
        if ($result['response']['allow']) {
            $flash['ok'][] = "ซื้อสำเร็จ: " . number_format($amount, 2) . " บาท";
        } else {
            $flash['err'][] = "ซื้อล้มเหลว: " . $result['response']['msg'];
        }
    }
}

// ดึงข้อมูลบัตร
$cards = [];
$stmt = $conn->prepare("SELECT card_uid, nickname, is_active, created_at FROM nfc_cards WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cards[] = $row;
}

$csrf = csrf_token();
?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>API Demo - NFC System</title>
    <link rel="stylesheet" href="style.css">
    <script src="api_client.js"></script>
    <style>
        .card { border-radius: 16px; padding: 16px; margin-bottom: 16px; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
        .grid { display: grid; gap: 12px; }
        .grid-2 { grid-template-columns: 1fr; }
        @media(min-width:900px) { .grid-2 { grid-template-columns: 1fr 1fr; } }
        .btn-primary { background: #3b82f6; color: #fff; border: 0; padding: 10px 16px; cursor: pointer; border-radius: 10px; }
        .btn-secondary { background: #6b7280; color: #fff; border: 0; padding: 10px 16px; cursor: pointer; border-radius: 10px; text-decoration: none; display: inline-block; }
        .ok { color: #2e7d32; }
        .err { color: #c62828; }
        .muted { color: #666; }
        .balance { font-weight: 600; font-size: 1.2em; }
    </style>
</head>
<body>
<div class="container" style="max-width: 1100px; margin: 24px auto;">
    <h2>API Demo - NFC System</h2>
    
    <div style="margin: 8px 0 16px 0;">
        <a href="index.php" class="btn-secondary">← กลับหน้าแรก</a>
    </div>

    <?php if($flash['ok'] || $flash['err']): ?>
        <div class="card">
            <?php foreach($flash['ok'] as $m): ?><div class="ok">• <?= htmlspecialchars($m) ?></div><?php endforeach; ?>
            <?php foreach($flash['err'] as $m): ?><div class="err">• <?= htmlspecialchars($m) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-2">
        <div class="card">
            <h3>ลงทะเบียนบัตร NFC</h3>
            <form method="post" class="grid">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="register_card">
                <label>Card UID (hex)</label>
                <input type="text" name="card_uid" placeholder="เช่น 04AABBCCDD" required>
                <label>ชื่อเล่นบัตร</label>
                <input type="text" name="nickname" placeholder="เช่น โทรศัพท์, บัตรนักเรียน">
                <button class="btn-primary" type="submit">ลงทะเบียนบัตร</button>
            </form>
        </div>

        <div class="card">
            <h3>บัตรของฉัน</h3>
            <?php if(!$cards): ?>
                <div class="muted">ยังไม่มีบัตร</div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 10px; border-bottom: 1px solid #ddd;">UID</th>
                            <th style="padding: 10px; border-bottom: 1px solid #ddd;">ชื่อเล่น</th>
                            <th style="padding: 10px; border-bottom: 1px solid #ddd;">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cards as $c): ?>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;"><code><?= htmlspecialchars($c['card_uid']) ?></code></td>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?= htmlspecialchars($c['nickname'] ?? '') ?></td>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>เติมเงิน (API)</h3>
            <form method="post" class="grid">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="topup">
                <label>เลือกบัตร</label>
                <select name="card_uid" required>
                    <option value="">— เลือก —</option>
                    <?php foreach($cards as $c): if($c['is_active']): ?>
                        <option value="<?= htmlspecialchars($c['card_uid']) ?>"><?= htmlspecialchars($c['nickname'] ? ($c['nickname'] . ' - ' . $c['card_uid']) : $c['card_uid']) ?></option>
                    <?php endif; endforeach; ?>
                </select>
                <label>จำนวนเงิน (บาท)</label>
                <input type="number" name="amount" min="1" step="0.01" placeholder="เช่น 50.00" required>
                <button class="btn-primary" type="submit">เติมเงิน</button>
            </form>
        </div>

        <div class="card">
            <h3>ซื้อสินค้า (API)</h3>
            <form method="post" class="grid">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="buy">
                <label>เลือกบัตร</label>
                <select name="card_uid" required>
                    <option value="">— เลือก —</option>
                    <?php foreach($cards as $c): if($c['is_active']): ?>
                        <option value="<?= htmlspecialchars($c['card_uid']) ?>"><?= htmlspecialchars($c['nickname'] ? ($c['nickname'] . ' - ' . $c['card_uid']) : $c['card_uid']) ?></option>
                    <?php endif; endforeach; ?>
                </select>
                <label>จำนวนเงิน (บาท)</label>
                <input type="number" name="amount" min="1" step="0.01" placeholder="เช่น 25.50" required>
                <button class="btn-primary" type="submit">ซื้อสินค้า</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h3>ทดสอบ API ด้วย JavaScript</h3>
        <button class="btn-primary" onclick="testAPI()">ทดสอบ API</button>
        <div id="api-result" style="margin-top: 16px; padding: 16px; background: #f5f5f5; border-radius: 8px; display: none;">
            <h4>ผลลัพธ์:</h4>
            <pre id="api-output"></pre>
        </div>
    </div>
</div>

<script>
async function testAPI() {
    const resultDiv = document.getElementById('api-result');
    const outputDiv = document.getElementById('api-output');
    
    resultDiv.style.display = 'block';
    outputDiv.textContent = 'กำลังทดสอบ...';
    
    try {
        // ทดสอบดูยอดคงเหลือ
        const balance = await nfcAPI.getBalance('04AABBCCDD');
        console.log('Balance result:', balance);
        
        let output = '=== API Test Results ===\n\n';
        output += '1. Get Balance:\n';
        output += JSON.stringify(balance, null, 2) + '\n\n';
        
        // ทดสอบเติมเงิน
        const topup = await nfcAPI.topup('04AABBCCDD', 100);
        output += '2. Top-up:\n';
        output += JSON.stringify(topup, null, 2) + '\n\n';
        
        // ทดสอบซื้อสินค้า
        const buy = await nfcAPI.buy('04AABBCCDD', 50);
        output += '3. Buy:\n';
        output += JSON.stringify(buy, null, 2) + '\n\n';
        
        outputDiv.textContent = output;
        
    } catch (error) {
        outputDiv.textContent = 'Error: ' + error.message;
    }
}
</script>
</body>
</html>
