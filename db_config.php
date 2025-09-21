<?php
try {
    $host = 'localhost';  # หรือ '127.0.0.1' ถ้า localhost ไม่ทำงาน
    $dbname = 'if0_39302480_nano_db';  # ชื่อฐานข้อมูลจริง (เช่น 'nfcvvv33')
    $user = 'root';  # เช่น 'root' หรือ user ที่สร้าง
    $pass = '';  # รหัสผ่าน
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();  # เพิ่มเพื่อ debug
}
?>