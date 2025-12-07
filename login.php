<?php
session_start();
require 'db.php'; 

$hataMesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc = $_POST['tcno'];
    $sifre = $_POST['sifre'];

    // 1. GÜNCELLEME: Personel ve Eczane tablolarını birleştiren sorgu
    // Bu sayede personelin hangi eczanede çalıştığını öğreniyoruz.
    $sql = "SELECT p.*, e.EczaneAdi 
            FROM personel p 
            JOIN eczaneler e ON p.EczaneID = e.EczaneID 
            WHERE p.TCNo = :tc AND p.Sifre = :sifre";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['tc' => $tc, 'sifre' => $sifre]);
        $personel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($personel) {
            // 2. GÜNCELLEME: Panele lazım olan bilgileri oturuma kaydet
            $_SESSION['personel_id'] = $personel['PersonelID'];
            $_SESSION['personel_adi'] = $personel['AdSoyad']; // Panelde "Hoşgeldin Ahmet" demek için
            $_SESSION['eczane_id'] = $personel['EczaneID'];     // Panelde hangi ilaçları göstereceğimizi bilmek için
            $_SESSION['eczane_adi'] = $personel['EczaneAdi'];   // Panel başlığı için
            
            // 3. GÜNCELLEME: Doğrudan yönetim paneline yönlendir
            header("Location: eczane-panel.php"); 
            exit;
        } else {
            $hataMesaji = "TC Kimlik Numaranız veya Şifreniz hatalı.";
        }
    } catch (PDOException $e) {
        $hataMesaji = "Bir hata oluştu: " . $e->getMessage();
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
            <div class="card-top-line" style="background: #e63946;"></div>

            <div class="login-header">
                <img src="assets/img/logo.png" alt="e-Ecza Logo" class="brand-logo" onerror="this.style.display='none'">
                <h2>Personel Girişi</h2>
                <p>Eczane yönetim paneline erişmek için giriş yapın.</p>

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

                <button type="submit" class="btn-login" style="background-color: #e63946;">Yönetim Paneline Gir</button>
            </form>
        </div>
    </div>

</body>
</html>