<?php
require_once __DIR__ . '/config.php';
function get_pdo(): PDO {
  static $conn=null;
  if(!$conn){
    $conn=new PDO(DB_DSN,DB_USER,DB_PASS,[
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
    ]);
  }
  return $conn;
}