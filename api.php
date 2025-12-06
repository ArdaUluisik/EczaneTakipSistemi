<?php
// api.php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type == 'get_cities') {
    // Tüm İlleri Getir
    $stmt = $pdo->query("SELECT IlID, IlAdi FROM Iller ORDER BY IlAdi ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} elseif ($type == 'get_districts' && isset($_GET['il_id'])) {
    // Seçilen İlin İlçelerini Getir
    $stmt = $pdo->prepare("SELECT IlceID, IlceAdi FROM Ilceler WHERE IlID = :il_id ORDER BY IlceAdi ASC");
    $stmt->execute(['il_id' => $_GET['il_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>