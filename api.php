<?php
// api.php

// 1. Yeni OOP Sınıflarını Dahil Et
require_once 'classes/Database.php';
require_once 'classes/Arama.php';

// JSON formatında çıktı verileceğini belirt
header('Content-Type: application/json; charset=utf-8');

// 2. Sınıfları Başlat
$db = Database::getInstance()->getConnection();
$aramaMotoru = new Arama($db);

// İsteğin türünü al
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type == 'get_cities') {
    // Arama sınıfındaki fonksiyonu kullan
    $iller = $aramaMotoru->illeriGetir();
    echo json_encode($iller);

} elseif ($type == 'get_districts' && isset($_GET['il_id'])) {
    // Arama sınıfındaki fonksiyonu kullan
    $ilceler = $aramaMotoru->ilceleriGetir($_GET['il_id']);
    echo json_encode($ilceler);
}
?>