<?php
session_start(); 

// --- GÜVENLİK DUVARI ---
// Eğer giriş yapan kişi PERSONEL ise, bu sayfayı göremez!
if (isset($_SESSION['personel_id'])) {
    // Onu kendi çöplüğüne (Yönetim Paneline) geri gönder
    header("Location: eczane-panel.php");
    exit;
}
// -----------------------

require 'db.php';

try {
    $sql = "SELECT 
                i.IlacID, 
                i.IlacAdi, 
                i.Aciklama, 
                i.ResimYolu, 
                MIN(es.Fiyat) as SatisFiyati
            FROM ilaclar i
            JOIN eczanestok es ON i.IlacID = es.IlacID
            WHERE es.Adet > 0
            GROUP BY i.IlacID
            ORDER BY i.IlacID DESC";

    $stmt = $pdo->query($sql);
    $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $hata = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Ecza | Sağlık Market</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/products.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <header class="store-header">
        <div class="container" style="max-width:1200px; margin:0 auto; padding:0 20px;">
            <h1>Sağlık Market</h1>
            <p>Eczanelerdeki en uygun fiyatlı ürünlere tek tıkla ulaşın.</p>
        </div>
    </header>

    <section class="products-container">
        
        <?php if (isset($hata)): ?>
            <div style="grid-column: 1/-1; text-align:center; padding:40px; border-radius:15px; background:#fff5f5; border:1px solid #feb2b2;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:30px; color:#c53030; margin-bottom:15px;"></i>
                <h3 style="color:#c53030;">Veritabanı Bağlantı Hatası</h3>
                <p><?php echo $hata; ?></p>
            </div>

        <?php elseif (empty($urunler)): ?>
            <div class="db-waiting-state" style="grid-column: 1/-1; text-align:center; padding:60px; border-radius:15px; background:white; box-shadow:0 5px 15px rgba(0,0,0,0.03);">
                <i class="fa-solid fa-box-open" style="font-size:50px; color:#e0e0e0; margin-bottom:20px;"></i>
                <h3 style="color:#555;">Henüz Ürün Yok</h3>
                <p style="color:#999;">Şu an stoklarımızda ilaç bulunmamaktadır. Lütfen daha sonra tekrar deneyin.</p>
            </div>

        <?php else: ?>
            <?php foreach ($urunler as $urun): ?>
                
                <div class="product-card">
                    <div class="product-image">
                        <?php $resim = !empty($urun['ResimYolu']) ? $urun['ResimYolu'] : 'assets/img/logo.png'; ?>
                        <img src="<?php echo htmlspecialchars($resim); ?>" alt="<?php echo htmlspecialchars($urun['IlacAdi']); ?>">
                        
                        <span class="badge">
                            <i class="fa-solid fa-check"></i> Stokta Var
                        </span>
                    </div>

                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($urun['IlacAdi']); ?></h3>
                        
                        <p class="category">
                            <?php 
                            echo mb_strimwidth(htmlspecialchars($urun['Aciklama']), 0, 40, "..."); 
                            ?>
                        </p>
                        
                        <div class="price">
                            <?php echo number_format($urun['SatisFiyati'], 2, ',', '.') . ' ₺'; ?>
                        </div>
                    </div>
                    
                    <div class="product-actions">
                        <button class="btn-action btn-courier">
                            <i class="fa-solid fa-motorcycle"></i> Hemen Sipariş Ver
                        </button>
                        <button class="btn-action btn-prepay">
                            <i class="fa-regular fa-credit-card"></i> Ön Ödeme Yap
                        </button>
                        
                        <button class="btn-action btn-find" onclick="window.location.href='index.php?ilac_adi=<?php echo urlencode($urun['IlacAdi']); ?>'">
                            <i class="fa-solid fa-location-dot"></i> Haritada Gör
                        </button>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>

    </section>

</body>
</html>