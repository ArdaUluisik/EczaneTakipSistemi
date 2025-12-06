<?php
session_start();
require 'db.php'; 

$hataMesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc = $_POST['tcno'];
    $sifre = $_POST['sifre'];

    // Procedure Kullanımı
    $sql = "CALL sp_PersonelGiris(:tc, :sifre)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['tc' => $tc, 'sifre' => $sifre]);
        $personel = $stmt->fetch();

        if ($personel) {
            $_SESSION['personel_id'] = $personel['PersonelID'];
            $_SESSION['ad_soyad'] = $personel['AdSoyad'];
            
            // Başarılıysa panele gönder (Panel sayfası henüz yoksa index'e atar)
            header("Location: index.php"); 
            exit;
        } else {
            $hataMesaji = "TC Kimlik Numaranız veya Şifreniz hatalı.";
        }
    } catch (PDOException $e) {
        $hataMesaji = "Bir hata oluştu.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Ecza | Personel Girişi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <nav class="login-nav">
        <a href="index.php" class="nav-back-link">
            <i class="fa-solid fa-arrow-left"></i> İlaç Sorgulamaya Dön
        </a>
    </nav>

    <div class="login-wrapper">
        <div class="login-container">
            <div class="card-top-line"></div>

            <div class="login-header">
                <img src="assets/img/logo.png" alt="e-Ecza Logo" class="brand-logo" onerror="this.style.display='none'">
                <h2>Personel Girişi</h2>
                <p>Lütfen yetkili bilgilerinizi giriniz.</p>

                <?php if(!empty($hataMesaji)): ?>
                    <div style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-top: 10px; font-size: 0.9rem; text-align: center;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $hataMesaji; ?>
                    </div>
                <?php endif; ?>
            </div>

            <form id="loginForm" method="POST" action="">
                
                <div class="input-group">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" name="tcno" id="tcno" placeholder="TC Kimlik Numaranız" maxlength="11" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="sifre" id="password" placeholder="Şifreniz" required>
                </div>

                <div class="form-footer">
                    <a href="#" class="forgot-pass">Şifremi Unuttum?</a>
                </div>

                <button type="submit" class="btn-login">Sisteme Giriş Yap</button>
            </form>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>