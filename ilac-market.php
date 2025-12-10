<?php
session_start(); 

// GÜVENLİK: Personel giremez
if (isset($_SESSION['personel_id'])) {
    header("Location: eczane-panel.php");
    exit;
}

require 'db.php';

// SEPET SAYISINI HESAPLA (Navbar için)
$sepetSayisi = 0;
if (isset($_SESSION['sepet'])) {
    $sepetSayisi = array_sum($_SESSION['sepet']);
}

// --- SEPETE EKLEME MANTIĞI ---
if (isset($_POST['sepete_ekle'])) {
    $eklenecekID = $_POST['ilac_id'];
    
    if (!isset($_SESSION['sepet'])) {
        $_SESSION['sepet'] = [];
    }

    if (isset($_SESSION['sepet'][$eklenecekID])) {
        $_SESSION['sepet'][$eklenecekID]++;
    } else {
        $_SESSION['sepet'][$eklenecekID] = 1;
    }
    
    // Sayfayı yenile (Sepet sayısı güncellensin)
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

try {
    // SORGEYİ GÜNCELLEDİK: i.ReceteTuru eklendi
    $sql = "SELECT 
                i.IlacID, 
                i.IlacAdi, 
                i.Aciklama, 
                i.ResimYolu,
                i.ReceteTuru,  
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
            <div style="grid-column: 1/-1; text-align:center; padding:40px; background:#fff5f5; color:#c53030; border-radius:15px;">
                <h3>Veritabanı Hatası</h3> <p><?php echo $hata; ?></p>
            </div>

        <?php elseif (empty($urunler)): ?>
            <div class="db-waiting-state" style="grid-column: 1/-1; text-align:center; padding:60px; background:white; border-radius:15px;">
                <i class="fa-solid fa-box-open" style="font-size:50px; color:#e0e0e0; margin-bottom:20px;"></i>
                <h3 style="color:#555;">Henüz Ürün Yok</h3>
            </div>

        <?php else: ?>
            <?php foreach ($urunler as $urun): ?>
                
                <?php 
                    $renk = '#3498db'; // Mavi (Normal)
                    $etiket = 'Normal Reçete';
                    $ikon = 'fa-file-prescription';

                    if ($urun['ReceteTuru'] == 'Kirmizi') {
                        $renk = '#e74c3c'; 
                        $etiket = 'Kırmızı Reçete';
                        $ikon = 'fa-triangle-exclamation';
                    } elseif ($urun['ReceteTuru'] == 'Sari') {
                        $renk = '#f1c40f'; 
                        $etiket = 'Sarı Reçete';
                        $ikon = 'fa-capsules';
                    } elseif ($urun['ReceteTuru'] == 'Yesil') {
                        $renk = '#2ecc71'; 
                        $etiket = 'Yeşil Reçete';
                        $ikon = 'fa-leaf';
                    }
                ?>

                <div class="product-card">
                    <div class="product-image">
                        <?php $resim = !empty($urun['ResimYolu']) ? $urun['ResimYolu'] : 'assets/img/logo.png'; ?>
                        <img src="<?php echo htmlspecialchars($resim); ?>" alt="<?php echo htmlspecialchars($urun['IlacAdi']); ?>">
                        
                        <span class="badge" style="background-color: <?php echo $renk; ?>; color: white;">
                            <i class="fa-solid <?php echo $ikon; ?>"></i> <?php echo $etiket; ?>
                        </span>
                    </div>

                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($urun['IlacAdi']); ?></h3>
                        
                        <p class="category">
                            <?php echo mb_strimwidth(htmlspecialchars($urun['Aciklama']), 0, 40, "..."); ?>
                        </p>
                        
                        <div class="price">
                            <?php echo number_format($urun['SatisFiyati'], 2, ',', '.') . ' ₺'; ?>
                        </div>
                    </div>
                    
                    <div class="product-actions">
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="ilac_id" value="<?php echo $urun['IlacID']; ?>">
                            <button type="submit" name="sepete_ekle" class="btn-action btn-courier" style="width: 100%;">
                                <i class="fa-solid fa-cart-plus"></i> Sepete Ekle
                            </button>
                        </form>

                        <button class="btn-action btn-find" onclick="window.location.href='index.php?ilac_adi=<?php echo urlencode($urun['IlacAdi']); ?>'">
                            <i class="fa-solid fa-location-dot"></i> Harita
                        </button>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>

    </section>

</body>
</html>