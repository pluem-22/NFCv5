# NFC API Documentation

## Overview
API endpoints สำหรับจัดการระบบ NFC Card และ Wallet

## Endpoints

### 1. Card Registration
**POST** `/api/card_register.php`
ลงทะเบียนบัตร NFC ใหม่

**Request Body:**
```json
{
    "uid": "04AABBCCDD",
    "user_id": 1,
    "nickname": "My Card"
}
```

**Response:**
```json
{
    "success": true,
    "msg": "Card registered successfully"
}
```

### 2. Get Balance
**POST** `/api/get_balance.php`
ดูยอดคงเหลือในบัตร

**Request Body:**
```json
{
    "uid": "04AABBCCDD"
}
```

**Response:**
```json
{
    "success": true,
    "balance": 150.50,
    "user_id": 1,
    "username": "john_doe",
    "card_uid": "04AABBCCDD"
}
```

### 3. Top-up
**POST** `/api/wallet_topup.php`
เติมเงินเข้าบัตร

**Request Body:**
```json
{
    "uid": "04AABBCCDD",
    "amount": 100.50
}
```

**Response:**
```json
{
    "allow": true,
    "newBlock4": "ABCD1234EFGH5678",
    "msg": "OK",
    "txn_id": "TXN123456789"
}
```

### 4. Buy/Spend
**POST** `/api/wallet_authorize.php`
ซื้อสินค้าหรือใช้จ่าย

**Request Body:**
```json
{
    "uid": "04AABBCCDD",
    "amount": 50.25
}
```

**Response:**
```json
{
    "allow": true,
    "newBlock4": "ABCD1234EFGH5678",
    "msg": "OK",
    "txn_id": "TXN123456789"
}
```

### 5. Confirm Transaction
**POST** `/api/wallet_confirm.php`
ยืนยันธุรกรรม

**Request Body:**
```json
{
    "uid": "04AABBCCDD",
    "applied": "ABCD1234EFGH5678IJKL9012MNOP3456"
}
```

**Response:**
```json
{
    "ok": true,
    "msg": "confirmed"
}
```

## Error Responses

### Bad Input (400)
```json
{
    "success": false,
    "msg": "bad input"
}
```

### Card Not Found (404)
```json
{
    "success": false,
    "msg": "Card not found or inactive"
}
```

### Insufficient Balance
```json
{
    "allow": false,
    "msg": "insufficient balance"
}
```

### Database Error (500)
```json
{
    "success": false,
    "msg": "db error: [error message]"
}
```

## Usage Example

### JavaScript
```javascript
const nfcAPI = new NFCAPIClient();

// ดูยอดคงเหลือ
const balance = await nfcAPI.getBalance('04AABBCCDD');
console.log('Balance:', balance.data.balance);

// เติมเงิน
const topup = await nfcAPI.topup('04AABBCCDD', 100);
console.log('Topup result:', topup.data);

// ซื้อสินค้า
const buy = await nfcAPI.buy('04AABBCCDD', 50);
console.log('Buy result:', buy.data);
```

### PHP
```php
$data = ['uid' => '04AABBCCDD', 'amount' => 100];
$response = file_get_contents('http://localhost/api/wallet_topup.php', false, 
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ])
);
$result = json_decode($response, true);
```

## Testing

ใช้ไฟล์ `test_api.php` เพื่อทดสอบ API endpoints:

```bash
php api/test_api.php
```

หรือเปิดในเบราว์เซอร์:
```
http://localhost/projactnfc/api/test_api.php
```
