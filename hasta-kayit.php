<?php
require 'db.php'; // Veritabanı bağlantısı

$mesaj = "";
$mesajTuru = ""; // success veya error

if ($_POST) {
    // Formdan gelen verileri al
    $tc = $_POST['tc'];
    $adsoyad = $_POST['adsoyad'];
    $telefon = $_POST['telefon'];
    $adres = $_POST['adres'];
    $sifre = $_POST['sifre'];

    // 1. Basit Doğrulama
    if (empty($tc) || empty($adsoyad) || empty($sifre)) {
        $mesaj = "Lütfen zorunlu alanları (TC, Ad Soyad, Şifre) doldurunuz.";
        $mesajTuru = "error";
    } else {
        try {
            // 2. Aynı TCNo var mı kontrol et
            $kontrol = $pdo->prepare("SELECT * FROM hastalar WHERE TCNo = ?");
            $kontrol->execute([$tc]);
            
            if ($kontrol->rowCount() > 0) {
                $mesaj = "Bu TC Kimlik numarası ile zaten kayıtlı bir hasta var.";
                $mesajTuru = "error";
            } else {
                // 3. Kayıt İşlemi
                $sql = "INSERT INTO hastalar (TCNo, AdSoyad, Telefon, Adres, Sifre) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $sonuc = $stmt->execute([$tc, $adsoyad, $telefon, $adres, $sifre]);

                if ($sonuc) {
                    $mesaj = "Kayıt başarıyla oluşturuldu! Giriş sayfasına yönlendiriliyorsunuz...";
                    $mesajTuru = "success";
                    // 2 saniye sonra giriş sayfasına at
                    header("refresh:2;url=hasta-login.php"); 
                } else {
                    $mesaj = "Kayıt sırasında bir hata oluştu.";
                    $mesajTuru = "error";
                }
            }
        } catch (PDOException $e) {
            $mesaj = "Veritabanı Hatası: " . $e->getMessage();
            $mesajTuru = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Ecza | Hasta Kayıt</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <nav class="login-nav">
        <div style="display: flex; gap: 20px;">
            <a href="index.php" class="nav-back-link">
                <i class="fa-solid fa-house"></i> Ana Sayfa
            </a>
            
            <a href="hasta-login.php" class="nav-back-link">
                <i class="fa-solid fa-arrow-left"></i> Giriş Ekranına Dön
            </a>
        </div>
    </nav>

    <div class="login-wrapper" style="padding: 40px 0;">
        <div class="login-container">
            <div class="card-top-line"></div>

            <div class="login-header">
                <img src="assets/img/logo.png" alt="e-Ecza Logo" class="brand-logo" onerror="this.style.display='none'">
                <h2>Aramıza Katılın</h2>
                <p>İlaç takibi ve kolay sipariş için hemen hesap oluşturun.</p>
            </div>

            <?php if ($mesaj != ""): ?>
                <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-size: 14px; font-weight: 500;
                    <?php echo ($mesajTuru == 'success') ? 'background:#d4edda; color:#155724;' : 'background:#f8d7da; color:#721c24;'; ?>">
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div class="input-group">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" name="tc" placeholder="TC Kimlik Numaranız" maxlength="11" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="adsoyad" placeholder="Adınız ve Soyadınız" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" name="telefon" placeholder="Telefon Numaranız (5XX...)">
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <input type="text" name="adres" placeholder="Adresiniz">
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="sifre" placeholder="Şifre Belirleyin" required>
                </div>

                <button type="submit" class="btn-login" style="background-color: #2ecc71;">
                    <i class="fa-solid fa-user-plus"></i> Kaydı Tamamla
                </button>
            </form>

            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                <p style="margin: 0; font-size: 14px; color: #7f8c8d;">Zaten hesabınız var mı?</p>
                <a href="hasta-login.php" style="color: var(--ana-renk); font-weight: 600; text-decoration: none; display: inline-block; margin-top: 5px;">
                    Giriş Yap
                </a>
            </div>
        </div>
    </div>

</body>
</html>