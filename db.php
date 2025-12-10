<?php
// db.php
$host = 'localhost';
$dbname = 'EczaneDB';
$username = 'root';
$password = '5270'; // BURAYA KENDİ MYSQL ŞİFRENİ YAZ! (Yoksa boş bırak: '')

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
