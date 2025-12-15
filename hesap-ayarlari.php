<?php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Hasta.php';

// Güvenlik: Giriş yapmayan giremez
if (!isset($_SESSION['hasta_id'])) {
    header("Location: hasta-login.php");
    exit;
}

// --- 2. Sınıfları Başlat ---
$db = Database::getInstance()->getConnection();
$hastaNesnesi = new Hasta($db);

$mesaj = "";
$mesajTuru = "";
$hastaID = $_SESSION['hasta_id'];

// --- GÜNCELLEME İŞLEMİ (OOP) ---
if ($_POST) {
    $telefon = $_POST['telefon'];
    $adres = $_POST['adres'];

    // Sınıfın güncelleme metodunu çağır
    $sonuc = $hastaNesnesi->bilgileriGuncelle($hastaID, $telefon, $adres);

    if ($sonuc) {
        $mesaj = "İletişim bilgileriniz güncellendi.";
        $mesajTuru = "success";
    } else {
        $mesaj = "Güncelleme yapılamadı.";
        $mesajTuru = "error";
    }
}

// --- MEVCUT BİLGİLERİ ÇEK (OOP) ---
$bilgi = $hastaNesnesi->bilgileriGetir($hastaID);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>e-Ecza | Hesap Ayarları</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/login.css">
    
    <style>
        body { background-color: #f8f9fa; display: block !important; }
        .settings-wrapper {
            max-width: 500px; margin: 50px auto; background: white;
            padding: 40px; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); border: 1px solid #eee;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="settings-wrapper">
        <div class="login-header" style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; color: #333;">Profil Bilgileri</h2>
            <p style="color: #777;">İletişim bilgilerinizi güncelleyin.</p>
        </div>

        <?php if ($mesaj != ""): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-size: 14px; font-weight: 500;
                <?php echo ($mesajTuru == 'success') ? 'background:#d4edda; color:#155724;' : 'background:#f8d7da; color:#721c24;'; ?>">
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label style="font-size: 12px; font-weight: 600; color: #777; margin-bottom: 5px; display: block;">TC Kimlik No</label>
            <div class="input-group" style="background-color: #f9f9f9; border: 1px solid #eee;">
                <i class="fa-solid fa-id-card" style="color: #bbb;"></i>
                <input type="text" value="<?php echo htmlspecialchars($bilgi['TCNo']); ?>" readonly style="color: #999; cursor: not-allowed; background: transparent;">
                <i class="fa-solid fa-lock" style="color: #ccc; margin-left: auto; font-size: 12px;"></i>
            </div>

            <label style="font-size: 12px; font-weight: 600; color: #777; margin-bottom: 5px; display: block; margin-top: 15px;">Ad Soyad</label>
            <div class="input-group" style="background-color: #f9f9f9; border: 1px solid #eee;">
                <i class="fa-solid fa-user" style="color: #bbb;"></i>
                <input type="text" value="<?php echo htmlspecialchars($bilgi['AdSoyad']); ?>" readonly style="color: #999; cursor: not-allowed; background: transparent;">
                <i class="fa-solid fa-lock" style="color: #ccc; margin-left: auto; font-size: 12px;"></i>
            </div>

            <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">

            <label style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 5px; display: block;">Telefon Numaranız</label>
            <div class="input-group">
                <i class="fa-solid fa-phone" style="color: var(--ana-renk);"></i>
                <input type="text" name="telefon" value="<?php echo htmlspecialchars($bilgi['Telefon']); ?>" placeholder="Telefon Giriniz">
            </div>

            <label style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 5px; display: block; margin-top: 15px;">Adresiniz</label>
            <div class="input-group">
                <i class="fa-solid fa-map-location-dot" style="color: var(--ana-renk);"></i>
                <input type="text" name="adres" value="<?php echo htmlspecialchars($bilgi['Adres']); ?>" placeholder="Adres Giriniz">
            </div>

            <button type="submit" class="btn-login" style="background-color: #2ecc71; margin-top: 20px;">
                <i class="fa-solid fa-floppy-disk"></i> Bilgileri Güncelle
            </button>

            <a href="sifre-degistir.php" class="btn-login" style="background-color: #34495e; margin-top: 15px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:10px;">
                <i class="fa-solid fa-key"></i> Şifremi Değiştir
            </a>

        </form>
    </div>
</body>
</html>