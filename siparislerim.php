<?php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Siparis.php';

// Güvenlik: Sadece giriş yapmış hastalar görebilir
if (!isset($_SESSION['hasta_id'])) {
    header("Location: hasta-login.php");
    exit;
}

// --- 2. Sınıfları Başlat ---
$db = Database::getInstance()->getConnection();
$siparisYonetim = new Siparis($db);

$hastaID = $_SESSION['hasta_id'];

// 3. Ana Siparişleri Çek (OOP)
$siparisler = $siparisYonetim->hastaSiparisleriniGetir($hastaID);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Siparişlerim | e-Ecza</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .page-title {
            color: #2c3e50;
            border-bottom: 2px solid #e63946;
            padding-bottom: 10px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #eee;
        }

        .order-header {
            background: #fdfdfd;
            padding: 15px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-info span {
            display: block;
            font-size: 13px;
            color: #7f8c8d;
        }
        .order-info strong {
            color: #2c3e50;
            font-size: 15px;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-bekleniyor { background: #fff3cd; color: #856404; }
        .status-tamamlandi { background: #d4edda; color: #155724; }
        .status-iptal { background: #f8d7da; color: #721c24; }

        .order-body {
            padding: 20px 25px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f9f9f9;
        }
        .item-row:last-child { border-bottom: none; }
        
        .item-name { font-weight: 500; color: #333; }
        .item-pharmacy { font-size: 12px; color: #95a5a6; }
        .item-price { font-weight: 600; color: #2c3e50; }

        .total-section {
            background: #fafafa;
            padding: 15px 25px;
            text-align: right;
            border-top: 1px solid #eee;
        }
        .total-text { font-size: 14px; color: #7f8c8d; margin-right: 10px; }
        .total-amount { font-size: 20px; font-weight: 700; color: #e63946; }

        .empty-state { text-align: center; padding: 50px; color: #95a5a6; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2 class="page-title"><i class="fa-solid fa-box-open"></i> Sipariş Geçmişim</h2>

        <?php if (empty($siparisler)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-clipboard-list" style="font-size: 60px; margin-bottom: 20px;"></i>
                <h3>Henüz hiç siparişiniz yok.</h3>
                <a href="ilac-market.php" style="color: #e63946;">Alışverişe Başla</a>
            </div>
        <?php else: ?>

            <?php foreach ($siparisler as $siparis): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <span>Sipariş Tarihi</span>
                            <strong><?php echo date("d.m.Y H:i", strtotime($siparis['SiparisTarihi'])); ?></strong>
                        </div>
                        <div class="order-info">
                            <span>Sipariş No</span>
                            <strong>#<?php echo $siparis['SiparisID']; ?></strong>
                        </div>
                        <div>
                            <?php 
                                $durumClass = 'status-bekleniyor';
                                if($siparis['Durum'] == 'Tamamlandı') $durumClass = 'status-tamamlandi';
                                elseif($siparis['Durum'] == 'İptal') $durumClass = 'status-iptal';
                            ?>
                            <span class="status-badge <?php echo $durumClass; ?>">
                                <?php echo htmlspecialchars($siparis['Durum']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-body">
                        <?php
                        // Bu siparişe ait detayları çek (OOP)
                        // Döngü içinde tekrar sınıfa gidiyoruz (N+1 sorunu olsa da, bu basit bir proje için kabul edilebilir)
                        $detaylar = $siparisYonetim->siparisDetaylariniGetir($siparis['SiparisID']);
                        ?>

                        <?php foreach ($detaylar as $detay): ?>
                            <div class="item-row">
                                <div>
                                    <div class="item-name"><?php echo htmlspecialchars($detay['IlacAdi']); ?></div>
                                    <div class="item-pharmacy"><i class="fa-solid fa-store"></i> <?php echo htmlspecialchars($detay['EczaneAdi']); ?></div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-size:13px; color:#777;"><?php echo $detay['Adet']; ?> Adet x <?php echo number_format($detay['BirimFiyat'], 2); ?> ₺</div>
                                    <div class="item-price"><?php echo number_format($detay['Adet'] * $detay['BirimFiyat'], 2); ?> ₺</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="total-section">
                        <span class="total-text">Toplam Tutar:</span>
                        <span class="total-amount"><?php echo number_format($siparis['ToplamTutar'], 2, ',', '.'); ?> ₺</span>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</body>
</html>