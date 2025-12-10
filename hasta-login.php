<?php
// DOSYA ADI: hasta-login.php
session_start();

// GÜVENLİK: Eğer zaten giriş yapılmışsa, direkt ana sayfaya at
if (isset($_SESSION['hasta_id'])) {
    header("Location: index.php");
    exit;
}

require 'db.php'; // Veritabanı bağlantısı

$hataMesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc = $_POST['tcno'];
    $sifre = $_POST['sifre'];

    // SQL Sorgusu: TC ve Şifre eşleşiyor mu?
    // Not: Şifreler hash'li değil, düz metin (Okul projesi standardı)
    $sql = "SELECT * FROM hastalar WHERE TCNo = :tc AND Sifre = :sifre";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['tc' => $tc, 'sifre' => $sifre]);
        $hasta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($hasta) {
            // Giriş Başarılı: Oturum aç
            $_SESSION['hasta_id'] = $hasta['HastaID'];
            $_SESSION['ad_soyad'] = $hasta['AdSoyad'];
            
            // Hastayı ana sayfaya yönlendir
            header("Location: index.php"); 
            exit;
        } else {
            $hataMesaji = "TC Kimlik No veya Şifre hatalı!";
        }
    } catch (PDOException $e) {
        $hataMesaji = "Sistem hatası: " . $e->getMessage();
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