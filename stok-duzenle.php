<?php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Stok.php';

// Güvenlik
if (!isset($_SESSION['personel_id']) || !isset($_GET['id'])) {
    header("Location: eczane-panel.php");
    exit;
}

// --- 2. Sınıfları Başlat ---
$db = Database::getInstance()->getConnection();
$stokYonetim = new Stok($db);

$stokID = $_GET['id'];
$eczaneID = $_SESSION['eczane_id'];
$mesaj = "";

// GÜNCELLEME İŞLEMİ (OOP)
if ($_POST) {
    $adet = $_POST['adet'];
    $fiyat = $_POST['fiyat'];

    // Sınıfın güncelleme fonksiyonunu kullan
    $sonuc = $stokYonetim->guncelle($stokID, $eczaneID, $adet, $fiyat);

    if ($sonuc) {
        header("Location: eczane-panel.php"); // Başarılıysa panele geri dön
        exit;
    } else {
        $mesaj = "Hata oluştu.";
    }
}

// MEVCUT VERİYİ ÇEK (OOP)
// Formun içini doldurmak için tekil veriyi sınıftan çekiyoruz
$veri = $stokYonetim->stokDetayGetir($stokID, $eczaneID);

if (!$veri) { echo "Stok bulunamadı."; exit; }
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Stok Düzenle</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <style>
        body { background: #f4f6f8; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: 'Poppins'; }
        .edit-box { background: white; padding: 40px; border-radius: 15px; width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-control { width: 100%; padding: 12px; margin: 10px 0 20px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-save { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #777; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

    <div class="edit-box">
        <h2 style="margin-top:0; color:#333;">Düzenle: <?php echo htmlspecialchars($veri['IlacAdi']); ?></h2>
        
        <?php if($mesaj): ?>
            <p style="color:red; text-align:center;"><?php echo $mesaj; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label style="font-size:13px; font-weight:600; color:#666;">Stok Adedi</label>
            <input type="number" name="adet" class="form-control" value="<?php echo $veri['Adet']; ?>" required>

            <label style="font-size:13px; font-weight:600; color:#666;">Satış Fiyatı (₺)</label>
            <input type="number" name="fiyat" class="form-control" value="<?php echo $veri['Fiyat']; ?>" step="0.01" required>

            <button type="submit" class="btn-save">Güncelle</button>
            <a href="eczane-panel.php" class="btn-cancel">İptal</a>
        </form>
    </div>

</body>
</html>