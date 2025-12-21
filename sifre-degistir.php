<?php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Hasta.php';

// Güvenlik
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

if ($_POST) {
    $eskiSifre = $_POST['eski_sifre'];
    $yeniSifre = $_POST['yeni_sifre'];
    $yeniSifreTekrar = $_POST['yeni_sifre_tekrar'];

    // 1. Alanlar boş mu?
    if (empty($eskiSifre) || empty($yeniSifre) || empty($yeniSifreTekrar)) {
        $mesaj = "Lütfen tüm alanları doldurunuz.";
        $mesajTuru = "error";
    } 
    // 2. Yeni şifreler uyuşuyor mu?
    elseif ($yeniSifre !== $yeniSifreTekrar) {
        $mesaj = "Yeni şifreler birbiriyle uyuşmuyor!";
        $mesajTuru = "error";
    }
    // 3. Eski şifre ile yeni şifre aynı mı?
    elseif ($eskiSifre === $yeniSifre) {
        $mesaj = "Yeni şifreniz eski şifrenizle aynı olamaz.";
        $mesajTuru = "error";
    }
    else {
        // 4. Şifre Değiştirme İşlemi (OOP)
        // Sınıfın içindeki fonksiyon hem kontrolü hem de güncellemeyi yapar
        $sonuc = $hastaNesnesi->sifreDegistir($hastaID, $eskiSifre, $yeniSifre);

        if ($sonuc) {
            $mesaj = "Şifreniz başarıyla değiştirildi.";
            $mesajTuru = "success";
        } else {
            $mesaj = "Girdiğiniz ESKİ şifre hatalı!";
            $mesajTuru = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>e-Ecza | Şifre Değiştir</title>
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/login.css">
    
    <style>
        body { background-color: #f8f9fa; display: block !important; }
        .settings-wrapper {
            max-width: 450px; margin: 50px auto; background: white;
            padding: 40px; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); border: 1px solid #eee;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="settings-wrapper">
        
        <a href="hesap-ayarlari.php" style="color:#777; text-decoration:none; font-size:14px; margin-bottom:20px; display:inline-block;">
            <i class="fa-solid fa-arrow-left"></i> Profil Ayarlarına Dön
        </a>

        <div class="login-header" style="margin-bottom: 25px;">
            <h2 style="font-size: 24px; color: #333;">Şifre Değiştir</h2>
            <p style="color: #777;">Güvenliğiniz için şifrenizi güncelleyin.</p>
        </div>

        <?php if ($mesaj != ""): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-size: 14px; font-weight: 500;
                <?php echo ($mesajTuru == 'success') ? 'background:#d4edda; color:#155724;' : 'background:#f8d7da; color:#721c24;'; ?>">
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            
            <label style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 5px; display: block;">Mevcut (Eski) Şifreniz</label>
            <div class="input-group">
                <i class="fa-solid fa-lock-open" style="color: #7f8c8d;"></i>
                <input type="password" name="eski_sifre" placeholder="Eski şifrenizi girin">
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

            <label style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 5px; display: block;">Yeni Şifreniz</label>
            <div class="input-group">
                <i class="fa-solid fa-key" style="color: var(--ana-renk);"></i>
                <input type="password" name="yeni_sifre" placeholder="Yeni şifreniz">
            </div>

            <label style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 5px; display: block;">Yeni Şifre (Tekrar)</label>
            <div class="input-group">
                <i class="fa-solid fa-key" style="color: var(--ana-renk);"></i>
                <input type="password" name="yeni_sifre_tekrar" placeholder="Yeni şifrenizi doğrulayın">
            </div>

            <button type="submit" class="btn-login" style="background-color: #e67e22; margin-top: 15px;">
                <i class="fa-solid fa-check-circle"></i> Şifreyi Güncelle
            </button>
        </form>
    </div>

</body>
</html>