<?php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Arama.php';

// Hataları gizle
error_reporting(0);
setlocale(LC_TIME, 'tr_TR.UTF-8', 'tr_TR', 'tr', 'turkish');

// --- 2. Sınıfları Başlat ---
$db = Database::getInstance()->getConnection();
$aramaMotoru = new Arama($db);

// Filtre Değerlerini Al
$sehirID = isset($_GET['sehir']) ? $_GET['sehir'] : '';
$ilceID  = isset($_GET['ilce'])  ? $_GET['ilce']  : '';
$aramaYapildi = isset($_GET['btn_ara']); // Butona basıldı mı?

$eczaneler = [];

// SADECE BUTONA BASILDIYSA SORGULA (Sınıf Üzerinden)
if ($aramaYapildi) {
    // Eski SQL kodları yerine tek satırlık sınıf çağrısı:
    $eczaneler = $aramaMotoru->nobetciEczaneleriGetir($sehirID, $ilceID);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>e-Ecza | Nöbetçi Eczaneler</title>
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css">
    
    <style>
        /* SADECE BU SAYFAYA ÖZEL STİLLER (Navbar stili index.css'den geliyor) */
        
        .duty-header {
            background: linear-gradient(135deg, #d62828 0%, #c0392b 100%);
            color: white; padding: 60px 20px 90px;
            text-align: center; border-radius: 0 0 50% 50% / 30px; margin-bottom: 50px;
        }
        
        .filter-bar {
            background: white; padding: 10px 20px; border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); max-width: 800px;
            margin: -80px auto 40px; display: flex; gap: 15px; position: relative; z-index: 10;
        }

        .filter-select { flex: 1; padding: 15px; border: none; font-size: 16px; outline: none; cursor: pointer; background: transparent; color: #333; }
        
        .btn-filter {
            background: #2c3e50; color: white; border: none; padding: 12px 35px;
            border-radius: 50px; cursor: pointer; font-weight: 600; transition: 0.2s;
        }
        .btn-filter:hover { background: #34495e; transform: scale(1.05); }

        .pharmacy-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px; max-width: 1200px; margin: 0 auto 60px; padding: 0 20px;
        }

        .pharmacy-card {
            background: white; border-radius: 16px; border: 1px solid #f0f0f0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative;
        }
        .pharmacy-card:hover { transform: translateY(-7px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }

        .status-pulse {
            position: absolute; top: 25px; right: 25px;
            background: #ffebee; color: #d62828; padding: 6px 12px;
            border-radius: 20px; font-size: 11px; font-weight: 700;
            display: flex; align-items: center; gap: 8px; border: 1px solid #ffcdd2;
        }
        .pulse-dot { width: 8px; height: 8px; background: #d62828; border-radius: 50%; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.5); } 100% { opacity: 1; transform: scale(1); } }

        .pharmacy-name { font-size: 1.4rem; color: #2c3e50; margin-bottom: 5px; font-weight: 700; }
        .pharmacy-location { color: #7f8c8d; font-size: 0.95rem; margin-bottom: 15px; }
        .action-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn-call { flex: 1; background: #eef2ff; color: #4361ee; text-decoration: none; padding: 12px; border-radius: 12px; text-align: center; font-weight: 600; transition: 0.2s; }
        .btn-call:hover { background: #4361ee; color: white; }
        .btn-map { flex: 1; background: #dcfce7; color: #166534; text-decoration: none; padding: 12px; border-radius: 12px; text-align: center; font-weight: 600; transition: 0.2s; }
        .btn-map:hover { background: #166534; color: white; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <header class="duty-header">
        <div class="container">
            <div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 14px; margin-bottom: 10px;">
                <i class="fa-regular fa-calendar-check"></i> <?php echo date("d.m.Y"); ?>
            </div>
            <h1 style="margin:0;"><i class="fa-solid fa-star-of-life fa-spin" style="font-size: 30px; margin-right: 10px;"></i> Nöbetçi Eczaneler</h1>
            <p style="opacity: 0.9; margin-top: 10px;">Bugün hizmet veren en yakın nöbetçi eczaneyi bulun.</p>
        </div>
    </header>

    <form method="GET" class="filter-bar">
        <select name="sehir" class="filter-select" onchange="this.form.submit()">
            <option value="">Şehir Seçiniz</option>
            <?php
            // SQL yerine Sınıf metodu kullanıyoruz
            $iller = $aramaMotoru->illeriGetir();
            foreach ($iller as $il) {
                $selected = ($il['IlID'] == $sehirID) ? 'selected' : '';
                echo "<option value='".$il['IlID']."' $selected>".$il['IlAdi']."</option>";
            }
            ?>
        </select>

        <select name="ilce" class="filter-select">
            <option value="">İlçe Seçiniz</option>
            <?php
            if ($sehirID) {
                // SQL yerine Sınıf metodu kullanıyoruz
                $ilceler = $aramaMotoru->ilceleriGetir($sehirID);
                foreach ($ilceler as $ilce) {
                    $selected = ($ilce['IlceID'] == $ilceID) ? 'selected' : '';
                    echo "<option value='".$ilce['IlceID']."' $selected>".$ilce['IlceAdi']."</option>";
                }
            } else {
                echo "<option disabled>Önce Şehir Seçiniz</option>";
            }
            ?>
        </select>

        <button type="submit" name="btn_ara" class="btn-filter">
            <i class="fa-solid fa-search"></i> Bul
        </button>
    </form>

    <section class="pharmacy-grid">
        
        <?php if (!$aramaYapildi): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999;">
                <i class="fa-solid fa-magnifying-glass-location" style="font-size: 60px; margin-bottom: 20px; color: #e0e0e0;"></i>
                <h3 style="color:#666;">Lütfen Nöbetçi Eczane Araması Yapınız</h3>
                <p>Şehir ve ilçe seçtikten sonra "Bul" butonuna basınız.</p>
            </div>

        <?php elseif (empty($eczaneler)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999;">
                <i class="fa-solid fa-hospital-user" style="font-size: 60px; margin-bottom: 20px; color: #ddd;"></i>
                <h3 style="color:#555;">Kayıtlı nöbetçi eczane bulunamadı.</h3>
                <p>Bu bölgede bugün için sisteme girilmiş nöbetçi eczane yok.</p>
            </div>

        <?php else: ?>
            <?php foreach ($eczaneler as $eczane): ?>
                <div class="pharmacy-card">
                    <div class="status-pulse">
                        <div class="pulse-dot"></div>
                        AÇIK
                    </div>

                    <div class="pharmacy-name"><?php echo htmlspecialchars($eczane['EczaneAdi']); ?></div>
                    
                    <div class="pharmacy-location">
                        <i class="fa-solid fa-map-pin" style="color:#d62828;"></i> 
                        <?php echo htmlspecialchars($eczane['IlceAdi'] . ' / ' . $eczane['IlAdi']); ?>
                    </div>
                    
                    <div style="margin-bottom: 15px; color: #555; font-size: 14px;">
                        <i class="fa-regular fa-clock" style="color:#f39c12;"></i> 
                        <?php echo !empty($eczane['NobetNotu']) ? $eczane['NobetNotu'] : '09:00 - 09:00 (24 Saat)'; ?>
                    </div>

                    <p style="color: #666; font-size: 14px; margin-bottom: 20px; line-height: 1.5; border-top: 1px solid #f0f0f0; padding-top: 15px;">
                        <?php echo htmlspecialchars($eczane['Adres']); ?>
                    </p>

                    <div class="action-buttons">
                        <a href="tel:<?php echo $eczane['Telefon']; ?>" class="btn-call">
                            <i class="fa-solid fa-phone"></i> Hemen Ara
                        </a>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($eczane['EczaneAdi'] . ' ' . $eczane['IlceAdi']); ?>" target="_blank" class="btn-map">
                            <i class="fa-solid fa-location-arrow"></i> Yol Tarifi
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </section>

</body>
</html>