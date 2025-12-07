<?php
// 1. Oturum kontrolü (Hata vermemesi için güvenli başlatma)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hangi sayfadayız? (Aktif linki boyamak için)
$aktif_sayfa = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css">
<link rel="stylesheet" href="assets/css/index.css"> 

<nav class="navbar">
    <div class="nav-container">
        
        <div class="nav-brand" style="display: flex; align-items: center;">
            <img src="assets/img/logo.png" alt="Logo" style="height: 35px; width: auto; margin-right: 10px;">
            <a href="index.php" style="text-decoration:none; color:inherit;">e-Ecza</a>
        </div>

        <div class="nav-right">
            
            <a href="nobetci-eczaneler.php" class="nav-link" 
               style="<?php echo ($aktif_sayfa == 'nobetci-eczaneler.php') ? 'color: #e63946; font-weight:700;' : 'color: #d62828; font-weight: 600;'; ?>">
                <i class="fa-solid fa-star-of-life"></i> Nöbetçi Eczaneler
            </a>

            <?php if (!isset($_SESSION['personel_id'])): ?>
                <a href="ilac-market.php" class="nav-link" 
                   style="<?php echo ($aktif_sayfa == 'ilac-market.php') ? 'color: #e63946; font-weight:700;' : ''; ?>">
                    <i class="fa-solid fa-basket-shopping"></i> Sağlık Market
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['hasta_id'])): ?>
                
                <a href="hesap-ayarlari.php" class="nav-link" 
                   style="<?php echo ($aktif_sayfa == 'hesap-ayarlari.php') ? 'color: #e63946; font-weight:700;' : 'color: #2a9d8f; font-weight: 600;'; ?>">
                    <i class="fa-solid fa-user-circle"></i> 
                    Hoş geldin, <?php echo htmlspecialchars($_SESSION['ad_soyad']); ?>
                </a>

                <a href="logout.php" class="nav-link" style="color: #7f8c8d;">
                    <i class="fa-solid fa-right-from-bracket"></i> Çıkış
                </a>

            <?php elseif (isset($_SESSION['personel_id'])): ?>

                <a href="eczane-panel.php" class="nav-link" 
                   style="<?php echo ($aktif_sayfa == 'eczane-panel.php') ? 'color: #e63946; font-weight:700;' : 'color: #e63946; font-weight: 600;'; ?>">
                    <i class="fa-solid fa-store"></i> Yönetim Paneli
                </a>

                <div class="nav-link" style="color: #333; font-size: 14px; cursor: default;">
                    <i class="fa-solid fa-user-doctor"></i> <?php echo htmlspecialchars($_SESSION['personel_adi']); ?>
                </div>

                <a href="logout.php" class="nav-link" style="color: #7f8c8d;">
                    <i class="fa-solid fa-power-off"></i> Çıkış
                </a>

            <?php else: ?>

                <a href="hasta-kayit.php" class="nav-link" 
                   style="<?php echo ($aktif_sayfa == 'hasta-kayit.php') ? 'color: #e63946; font-weight:700;' : ''; ?>">
                    <i class="fa-solid fa-user-plus"></i> Kayıt Ol
                </a>

                <a href="hasta-login.php" class="nav-link" style="margin-right: 15px; 
                   <?php echo ($aktif_sayfa == 'hasta-login.php') ? 'color: #e63946; font-weight:700;' : ''; ?>">
                    <i class="fa-solid fa-right-to-bracket"></i> Giriş Yap
                </a>

                <a href="login.php" class="btn-staff-login" 
                   style="<?php echo ($aktif_sayfa == 'login.php') ? 'background-color: #d62828;' : ''; ?>">
                    <i class="fa-solid fa-user-lock"></i> Personel
                </a>

            <?php endif; ?>
            
        </div>
    </div>
</nav>