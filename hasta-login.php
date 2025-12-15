<?php
// DOSYA ADI: hasta-login.php
session_start();

// 1. OOP Sınıflarını Dahil Et
require_once 'classes/Database.php';
require_once 'classes/Hasta.php';

// GÜVENLİK: Eğer zaten giriş yapılmışsa, direkt ana sayfaya at
if (isset($_SESSION['hasta_id'])) {
    header("Location: index.php");
    exit;
}

$hataMesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Sınıfları Başlat
    $db = Database::getInstance()->getConnection();
    $hasta = new Hasta($db);

    // Formdan gelen verileri sınıfa ver
    // Not: Formdaki input name="tcno", Sınıftaki özellik $tc
    $hasta->tc = $_POST['tcno'];
    $hasta->sifre = $_POST['sifre'];

    // 3. Giriş Yapmayı Dene (Sınıfın içindeki girisYap fonksiyonu çalışır)
    if ($hasta->girisYap()) {
        
        // Giriş Başarılı: Oturum aç
        // Sınıf, giriş başarılıysa kendi içindeki id ve ad_soyad özelliklerini doldurur.
        $_SESSION['hasta_id'] = $hasta->id;
        $_SESSION['ad_soyad'] = $hasta->ad_soyad;
        
        // Hastayı ana sayfaya yönlendir
        header("Location: index.php"); 
        exit;

    } else {
        $hataMesaji = "TC Kimlik No veya Şifre hatalı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Ecza | Hasta Girişi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <nav class="login-nav">
        <a href="index.php" class="nav-back-link">
            <i class="fa-solid fa-arrow-left"></i> Ana Sayfaya Dön
        </a>
    </nav>

    <div class="login-wrapper">
        <div class="login-container">
            <div class="card-top-line"></div>

            <div class="login-header">
                <img src="assets/img/logo.png" alt="e-Ecza Logo" class="brand-logo" onerror="this.style.display='none'">
                
                <div style="margin-bottom: 10px;">
                    <span style="background-color: #e6f7ff; color: #0077b6; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid #b3e0ff;">
                        <i class="fa-solid fa-motorcycle"></i> Eve Teslimat & Kurye
                    </span>
                </div>

                <h2>Hasta Girişi</h2>
                <p>Reçete takibi ve siparişleriniz için giriş yapın.</p>

                <?php if(!empty($hataMesaji)): ?>
                    <div style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-top: 15px; font-size: 14px; text-align: center;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $hataMesaji; ?>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="">
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="tcno" placeholder="TC Kimlik Numaranız" maxlength="11" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-key"></i>
                    <input type="password" name="sifre" placeholder="Şifreniz" required>
                </div>

                <div class="form-footer">
                    <a href="#" class="forgot-pass">Şifremi Unuttum?</a>
                </div>

                <button type="submit" class="btn-login">Giriş Yap</button>
            </form>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <p style="margin: 0; font-size: 14px; color: #7f8c8d;">Hesabınız yok mu?</p>
                <a href="hasta-kayit.php" style="color: var(--ana-renk); font-weight: 600; text-decoration: none; display: inline-block; margin-top: 5px;">
                    Hasta Kaydı Oluştur
                </a>
            </div>
        </div>
    </div>

</body>
</html>