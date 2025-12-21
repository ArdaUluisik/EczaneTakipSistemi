<?php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Market.php';
require_once 'classes/Siparis.php';

// HATA MODUNU KAPATTIK (Normal Mod)
error_reporting(0);

// --- 2. Sınıfları Başlat ---
$db = Database::getInstance()->getConnection();
$market = new Market($db);
$siparisYonetim = new Siparis($db);

// --- SEPET İŞLEMLERİ ---
if (isset($_GET['islem']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $islem = $_GET['islem'];

    if (isset($_SESSION['sepet'][$id])) {
        if ($islem == 'artir') { $_SESSION['sepet'][$id]++; } 
        elseif ($islem == 'azalt') {
            $_SESSION['sepet'][$id]--;
            if ($_SESSION['sepet'][$id] <= 0) { unset($_SESSION['sepet'][$id]); }
        } 
        elseif ($islem == 'sil') { unset($_SESSION['sepet'][$id]); }
    }
    header("Location: sepet.php");
    exit;
}

if (isset($_GET['bosalt'])) {
    unset($_SESSION['sepet']);
    header("Location: sepet.php");
    exit;
}

// --- SİPARİŞİ TAMAMLAMA ---
if (isset($_POST['siparis_onayla'])) {

    // Giriş kontrolü
    if (!isset($_SESSION['hasta_id'])) {
        echo "<script>alert('Lütfen önce giriş yapınız.'); window.location.href='hasta-login.php';</script>";
        exit;
    }
    
    $hastaID = $_SESSION['hasta_id'];
    $teslimat = $_POST['teslimat_turu'];
    // Eğer teslimat eczane ise ödeme türünü zorla 'on_odeme' yap (Güvenlik önlemi)
    $odemeTuru = ($teslimat == 'eczane') ? 'on_odeme' : $_POST['odeme_turu'];
    
    $receteNo = isset($_POST['recete_no']) ? trim($_POST['recete_no']) : null;
    
    // Sınıfı kullanarak siparişi oluştur
    // Not: Siparis sınıfındaki metoduna $odemeTuru parametresi eklemen gerekebilir veya varsayılan davranışı kontrol etmelisin.
    // Şimdilik mevcut yapını bozmadan devam ediyoruz.
    $sonuc = $siparisYonetim->siparisOlustur($hastaID, $_SESSION['sepet'], $teslimat, $receteNo);

    if ($sonuc['status']) {
        unset($_SESSION['sepet']);
        $mesaj = "Siparişiniz Alındı! Sipariş Numaranız: " . $sonuc['siparis_id'];
        if ($teslimat == 'kurye') $mesaj .= "\\nKurye en kısa sürede yola çıkacak.";
        else $mesaj .= "\\nÜrünleriniz adınıza ayrıldı. Eczaneden teslim alabilirsiniz.";
        
        echo "<script>alert('$mesaj'); window.location.href='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('HATA: " . $sonuc['mesaj'] . "'); window.location.href='sepet.php';</script>";
        exit;
    }
}

// --- SEPET LİSTELEME ---
$sepetUrunleri = [];
$toplamTutar = 0;
$ozelReceteVar = false;

if (isset($_SESSION['sepet']) && count($_SESSION['sepet']) > 0) {
    $ids = array_keys($_SESSION['sepet']);
    if(!empty($ids)) {
        $idListesi = implode(',', $ids);
        $dbUrunler = $market->sepetUrunleriniGetir($idListesi);

        foreach ($dbUrunler as $urun) {
            $adet = $_SESSION['sepet'][$urun['IlacID']];
            $araToplam = $urun['BirimFiyat'] * $adet;
            $toplamTutar += $araToplam;

            $urun['Adet'] = $adet;
            $urun['AraToplam'] = $araToplam;
            $sepetUrunleri[] = $urun;

            if ($urun['ReceteTuru'] != 'Beyaz') {
                $ozelReceteVar = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>e-Ecza | Sepetim</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .cart-header { background: linear-gradient(135deg, #d62828 0%, #c0392b 100%); color: white; padding: 60px 20px 90px; text-align: center; border-radius: 0 0 50% 50% / 30px; margin-bottom: 30px; }
        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px; }
        .cart-wrapper { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 40px; margin-top: -70px; position: relative; z-index: 10; margin-bottom: 50px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #95a5a6; font-weight: 600; font-size: 14px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f0; }
        td { padding: 25px 0; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }
        .product-info { display: flex; align-items: center; gap: 20px; }
        .product-img { width: 70px; height: 70px; border-radius: 12px; object-fit: contain; border: 1px solid #eee; padding: 5px; background: #fff; }
        .product-name { font-weight: 700; color: #2c3e50; font-size: 16px; margin-bottom: 5px; }
        .price-text { font-weight: 700; color: #2ecc71; font-size: 17px; }
        .btn-remove { color: #ff6b6b; cursor: pointer; transition: 0.2s; font-size: 18px; }
        .btn-remove:hover { color: #c0392b; transform: scale(1.1); }
        .cart-footer { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 30px; border-top: 2px solid #f0f0f0; }
        .total-price { font-size: 28px; font-weight: 800; color: #2c3e50; }
        .btn-checkout { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 15px 45px; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 16px; border: none; cursor: pointer; box-shadow: 0 5px 20px rgba(46, 204, 113, 0.3); transition: 0.3s; display: inline-flex; align-items: center; gap: 10px; width: 100%; justify-content: center; }
        .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(46, 204, 113, 0.4); }
        .empty-cart { text-align: center; padding: 60px 0; color: #95a5a6; }
        .options-group h4 { margin: 0 0 15px 0; font-size: 16px; color: #2c3e50; font-weight: 700; }
        .radio-option { display: flex; align-items: center; gap: 10px; padding: 15px; border: 1px solid #eee; border-radius: 12px; margin-bottom: 10px; cursor: pointer; transition: 0.2s; background: #fafafa; }
        .radio-option:hover { background: #f0f0f0; border-color: #ddd; }
        .radio-option input[type="radio"] { accent-color: #d62828; transform: scale(1.2); }
        .option-label { font-weight: 600; color: #555; display: flex; justify-content: space-between; width: 100%; }
        .extra-cost { font-size: 12px; background: #e63946; color: white; padding: 2px 8px; border-radius: 10px; }
        .highlight-text { font-size: 12px; color: #27ae60; background: #e8f5e9; padding: 2px 8px; border-radius: 10px; }
        .qty-control { display: inline-flex; align-items: center; justify-content: center; background: #f8f9fa; border: 1px solid #eee; border-radius: 50px; padding: 5px; }
        .btn-qty { width: 25px; height: 25px; border-radius: 50%; border: none; background: white; color: #333; font-weight: bold; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; }
        .btn-qty:hover { background: #e63946; color: white; }
        .qty-val { margin: 0 15px; font-weight: 600; color: #333; min-width: 20px; text-align: center; }
        
        .recete-alert-box { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 10px; margin-bottom: 10px; font-size: 13px; display: flex; align-items: center; gap: 10px; }
        .recete-input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; margin-bottom: 20px; transition: 0.3s; font-family: 'Poppins'; }
        .recete-input:focus { border-color: #d62828; outline: none; }

        /* Pasif Seçenek Stili */
        .option-disabled { opacity: 0.5; pointer-events: none; background: #f0f0f0; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <header class="cart-header">
        <h1 style="margin:0; font-size: 36px; font-weight: 700;"><i class="fa-solid fa-cart-shopping"></i> Sepetim</h1>
        <p style="opacity: 0.9; margin-top: 10px; font-size: 16px;">Siparişlerinizi güvenle tamamlayın.</p>
    </header>

    <div class="content-container">
        <div class="cart-wrapper">
            
            <?php if (empty($sepetUrunleri)): ?>
                <div class="empty-cart">
                    <i class="fa-solid fa-basket-shopping" style="font-size: 80px; margin-bottom: 25px; color: #eee;"></i>
                    <h3 style="color:#555; font-size: 22px;">Sepetiniz henüz boş.</h3>
                    <p style="margin-bottom: 30px;">İhtiyacınız olan ürünleri Sağlık Market'ten ekleyebilirsiniz.</p>
                    <a href="ilac-market.php" class="btn-checkout" style="background: #3498db; box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3); width: auto;">
                        <i class="fa-solid fa-store"></i> Alışverişe Başla
                    </a>
                </div>
            <?php else: ?>

                <form method="POST">
                    <table>
                        <thead>
                            <tr>
                                <th width="45%">Ürün Bilgisi</th>
                                <th width="15%" style="text-align:center;">Adet</th>
                                <th width="15%" style="text-align:right;">Birim Fiyat</th>
                                <th width="15%" style="text-align:right;">Toplam</th>
                                <th width="10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sepetUrunleri as $urun): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="<?php echo htmlspecialchars($urun['ResimYolu'] ?: 'assets/img/logo.png'); ?>" class="product-img">
                                        <div>
                                            <div class="product-name"><?php echo htmlspecialchars($urun['IlacAdi']); ?></div>
                                            <?php 
                                                $receteTuru = isset($urun['ReceteTuru']) ? $urun['ReceteTuru'] : 'Beyaz';
                                                $renk = '#95a5a6'; $yazi = 'Beyaz Reçete';
                                                if($receteTuru == 'Kirmizi') { $renk = '#e74c3c'; $yazi = 'Kırmızı Reçete'; }
                                                elseif($receteTuru == 'Turuncu') { $renk = '#f39c12'; $yazi = 'Turuncu Reçete'; }
                                                elseif($receteTuru == 'Yesil') { $renk = '#2ecc71'; $yazi = 'Yeşil Reçete'; }
                                                elseif($receteTuru == 'Mor') { $renk = '#9b59b6'; $yazi = 'Mor Reçete'; }
                                            ?>
                                            <span style="font-size:11px; font-weight:700; color:white; background:<?php echo $renk; ?>; padding:2px 8px; border-radius:10px; display:inline-block;">
                                                <?php echo $yazi; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <div class="qty-control">
                                        <a href="sepet.php?islem=azalt&id=<?php echo $urun['IlacID']; ?>" class="btn-qty">-</a>
                                        <span class="qty-val"><?php echo $urun['Adet']; ?></span>
                                        <a href="sepet.php?islem=artir&id=<?php echo $urun['IlacID']; ?>" class="btn-qty">+</a>
                                    </div>
                                </td>
                                <td style="text-align:right; color:#7f8c8d;"><?php echo number_format($urun['BirimFiyat'], 2, ',', '.'); ?> ₺</td>
                                <td style="text-align:right;" class="price-text"><?php echo number_format($urun['AraToplam'], 2, ',', '.'); ?> ₺</td>
                                <td style="text-align:right;">
                                    <a href="sepet.php?islem=sil&id=<?php echo $urun['IlacID']; ?>" class="btn-remove" title="Kaldır" onclick="return confirm('Bu ürünü silmek istiyor musunuz?');">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-footer">
                        <div>
                            <div class="options-group" style="margin-bottom: 25px;">
                                <h4><i class="fa-solid fa-truck-fast"></i> Teslimat Yöntemi</h4>
                                <label class="radio-option">
                                    <input type="radio" name="teslimat_turu" value="eczane" checked onchange="secenekleriGuncelle()">
                                    <span class="option-label">Eczaneden Gelip Alacağım<span class="extra-cost" style="background:#2ecc71;">Ücretsiz</span></span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="teslimat_turu" value="kurye" onchange="secenekleriGuncelle()">
                                    <span class="option-label">Adrese Kurye İle Gelsin<span class="extra-cost">+50 TL</span></span>
                                </label>
                            </div>

                            <div class="options-group">
                                <h4><i class="fa-regular fa-credit-card"></i> Ödeme Yöntemi</h4>
                                <label class="radio-option" id="opt-on-odeme">
                                    <input type="radio" name="odeme_turu" value="on_odeme" checked>
                                    <span class="option-label">Online Ön Ödeme (Kredi Kartı)<span class="highlight-text" id="on-odeme-not">Sıra Beklemeden Teslim</span></span>
                                </label>
                                <label class="radio-option" id="opt-kapida">
                                    <input type="radio" name="odeme_turu" value="kapida">
                                    <span class="option-label" id="kapida-text">Kapıda / Eczanede Ödeme</span>
                                </label>
                            </div>
                        </div>
                        
                        <div style="text-align:right; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <a href="sepet.php?bosalt=1" style="color:#e74c3c; text-decoration:none; font-size:14px; display:inline-block; margin-bottom:15px;">
                                    <i class="fa-regular fa-trash-can"></i> Sepeti Temizle
                                </a>
                                <br>
                                <a href="ilac-market.php" style="color:#7f8c8d; text-decoration:none; font-size:14px;">
                                    <i class="fa-solid fa-arrow-left"></i> Alışverişe Dön
                                </a>
                            </div>

                            <div>
                                <?php if($ozelReceteVar): ?>
                                    <div class="recete-alert-box">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        Sepetinizde <b>Kontrollü İlaç</b> bulunmaktadır. Lütfen e-Reçete kodunuzu giriniz.
                                    </div>
                                    <input type="text" name="recete_no" class="recete-input" placeholder="e-Reçete Kodunuzu Girin (Örn: 12AB34)" required>
                                <?php endif; ?>

                                <div style="font-size:14px; color:#95a5a6; margin-bottom:5px;">Ödenecek Toplam Tutar</div>
                                <div class="total-price" id="toplamTutar" data-raw="<?php echo $toplamTutar; ?>">
                                    <?php echo number_format($toplamTutar, 2, ',', '.'); ?> ₺
                                </div>
                                <div id="kuryeBilgisi" style="font-size:12px; color:#e74c3c; display:none; margin-top:5px;">+50 TL Kurye Ücreti Eklendi</div>
                                <br>
                                <button type="submit" name="siparis_onayla" class="btn-checkout">
                                    Siparişi Onayla <i class="fa-solid fa-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function secenekleriGuncelle() {
            var teslimat = document.querySelector('input[name="teslimat_turu"]:checked').value;
            var anaFiyat = parseFloat(document.getElementById('toplamTutar').getAttribute('data-raw'));
            var kuryeUcreti = 50;

            // Ödeme Seçeneklerini Yakala
            var kapidaOdemeInput = document.querySelector('input[value="kapida"]');
            var kapidaOdemeLabel = document.getElementById('opt-kapida');
            var kapidaText = document.getElementById('kapida-text');
            var onOdemeInput = document.querySelector('input[value="on_odeme"]');

            // --- DEĞİŞEN KISIM BURASI ---
            if (teslimat === 'eczane') {
                // Eczaneden alınacaksa Kapıda Ödemeyi KAPAT
                kapidaOdemeInput.disabled = true;
                kapidaOdemeLabel.classList.add('option-disabled'); // Opaklığı düşür
                kapidaText.innerHTML = "Eczanede Ödeme (Sadece Kurye İçin)";
                
                // Eğer kapıda ödeme seçiliyse, zorla Online Ödeme'ye geçir
                if (kapidaOdemeInput.checked) {
                    onOdemeInput.checked = true;
                }

                // Fiyat Hesaplama (Kurye Yok)
                document.getElementById('kuryeBilgisi').style.display = 'none';

            } else {
                // Kurye ise Kapıda Ödemeyi AÇ
                kapidaOdemeInput.disabled = false;
                kapidaOdemeLabel.classList.remove('option-disabled');
                kapidaText.innerHTML = "Kapıda Nakit / Kart ile Ödeme";

                // Fiyat Hesaplama (+50 TL)
                anaFiyat += kuryeUcreti;
                document.getElementById('kuryeBilgisi').style.display = 'block';
            }
            // -----------------------------

            var formatliFiyat = anaFiyat.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
            document.getElementById('toplamTutar').innerText = formatliFiyat;

            var onOdemeNot = document.getElementById('on-odeme-not');
            if (teslimat === 'eczane') {
                onOdemeNot.innerText = 'Ayırtmak İçin Zorunlu';
                onOdemeNot.style.background = '#e8f5e9'; onOdemeNot.style.color = '#27ae60';
            } else {
                onOdemeNot.innerText = 'Kredi Kartı ile';
                onOdemeNot.style.background = '#e3f2fd'; onOdemeNot.style.color = '#1e88e5';
            }
        }

        // Sayfa ilk açıldığında fonksiyonu çalıştır (Varsayılan duruma göre ayarlasın)
        window.onload = secenekleriGuncelle;
    </script>

</body>
</html>