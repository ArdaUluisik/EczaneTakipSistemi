<?php
// DOSYA: index.php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Arama.php';

// Hataları gizle
error_reporting(0);

// --- 2. Sınıfları Başlat ---
$db = Database::getInstance()->getConnection();
$aramaMotoru = new Arama($db);

// SEPET SAYISINI HESAPLA
$sepetSayisi = 0;
if (isset($_SESSION['sepet'])) {
    $sepetSayisi = array_sum($_SESSION['sepet']); 
}

// 1. HAFIZA: URL'den gelen arama verilerini al
$gelenSehir = isset($_GET['sehir']) ? $_GET['sehir'] : "";
$gelenIlce  = isset($_GET['ilce'])  ? $_GET['ilce']  : "";
$gelenIlac  = isset($_GET['q'])     ? trim($_GET['q']) : "";

// --- SEPETE EKLEME İŞLEMİ ---
if (isset($_POST['sepete_ekle'])) {
    $eklenecekID = $_POST['ilac_id'];
    if (!isset($_SESSION['sepet'])) { $_SESSION['sepet'] = []; }

    if (isset($_SESSION['sepet'][$eklenecekID])) { $_SESSION['sepet'][$eklenecekID]++; } 
    else { $_SESSION['sepet'][$eklenecekID] = 1; }
    
    // Sayfayı yenile
    $queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    header("Location: " . $_SERVER['PHP_SELF'] . $queryString);
    exit;
}

// 2. ARAMA: Eğer tüm veriler varsa sonuçları çek
$sonuclar = [];
if ($gelenIlce != "" && $gelenIlac != "") {
    $sonuclar = $aramaMotoru->ilacAra($gelenIlce, $gelenIlac);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Ecza | İlaç Sorgulama</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <header class="hero-section">
        <div class="hero-content">
            <h1>İlacınız Hangi Eczanede?</h1>
            <p>Konumunuzu seçin, aradığınız ilacın en yakın hangi eczanede olduğunu hemen bulun.</p>

            <form action="index.php" method="GET" class="search-wrapper" style="display: flex; align-items: center; justify-content: space-between; padding: 8px; background: white; border-radius: 50px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                
                <select name="sehir" id="sehirKutusu" onchange="ilceleriGetir()" required
                        style="border: none; background: transparent; padding: 15px; font-size: 16px; cursor: pointer; flex: 1; outline: none;">
                    <option value="">Şehir Seçiniz</option>
                    <?php
                    $iller = $aramaMotoru->illeriGetir();
                    foreach ($iller as $il) {
                        $secili = ($il['IlID'] == $gelenSehir) ? 'selected' : '';
                        echo '<option value="'.$il['IlID'].'" '.$secili.'>'.$il['IlAdi'].'</option>';
                    }
                    ?>
                </select>

                <div style="width: 1px; height: 30px; background-color: #ddd;"></div>

                <select name="ilce" id="ilceKutusu" required
                        style="border: none; background: transparent; padding: 15px; font-size: 16px; cursor: pointer; flex: 1; outline: none;">
                    <option value="">Önce Şehir Seçiniz</option>
                </select>

                <div style="width: 1px; height: 30px; background-color: #ddd;"></div>

                <input type="text" name="q" placeholder="İlaç adı giriniz..." value="<?php echo htmlspecialchars($gelenIlac); ?>" required
                       style="border: none; background: transparent; padding: 15px; font-size: 16px; flex: 2; outline: none; color: #333;">
                
                <button type="submit"
                        style="background-color: #e63946; color: white; border: none; border-radius: 50px; padding: 12px 35px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3); margin-left: 10px;">
                    <i class="fa-solid fa-search" style="margin-right: 8px;"></i> ARA
                </button>

            </form>
        </div>
    </header>

    <section class="results-container" style="padding: 40px; max-width: 1200px; margin: 0 auto;">
        
        <?php if (!empty($sonuclar)): ?>
            
            <h3 style="margin-bottom: 20px;">"<?php echo htmlspecialchars($gelenIlac); ?>" için sonuçlar:</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                <?php foreach ($sonuclar as $row): ?>
                    
                    <?php 
                        // --- YENİ RENK SİSTEMİ ---
                        $renkKod = '#95a5a6'; $yazi = 'Beyaz Reçete';
                        if (isset($row['ReceteTuru'])) {
                            if ($row['ReceteTuru'] == 'Kirmizi') { $renkKod = '#e74c3c'; $yazi = 'Kırmızı Reçete'; } 
                            elseif ($row['ReceteTuru'] == 'Turuncu') { $renkKod = '#f39c12'; $yazi = 'Turuncu Reçete'; } 
                            elseif ($row['ReceteTuru'] == 'Yesil') { $renkKod = '#2ecc71'; $yazi = 'Yeşil Reçete'; }
                            elseif ($row['ReceteTuru'] == 'Mor') { $renkKod = '#9b59b6'; $yazi = 'Mor Reçete'; }
                        }
                    ?>

                    <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <h4 style="color: #e63946; margin: 0 0 10px 0; font-size: 1.2rem;"><?php echo $row['EczaneAdi']; ?></h4>
                            <span style="background-color: <?php echo $renkKod; ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                <?php echo $yazi; ?>
                            </span>
                        </div>

                        <p style="color: #555; margin-bottom: 5px;"><i class="fa-solid fa-map-pin" style="color:#e63946; width:20px;"></i> <?php echo $row['Adres']; ?></p>
                        <p style="color: #555; margin-bottom: 15px;"><i class="fa-solid fa-phone" style="color:#e63946; width:20px;"></i> <?php echo $row['Telefon']; ?></p>
                        
                        <hr style="margin: 15px 0; border:0; border-top:1px solid #eee;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 600; color: #2a9d8f; display:block; font-size:13px;">
                                    <i class="fa-solid fa-box"></i> Stok: <?php echo $row['Adet']; ?>
                                </span>
                                <span style="font-weight: 700; color: #2c3e50; font-size: 18px;">
                                    <?php echo number_format($row['Fiyat'], 2, ',', '.'); ?> <small>₺</small>
                                </span>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="ilac_id" value="<?php echo isset($row['IlacID']) ? $row['IlacID'] : ''; ?>">
                                <button type="submit" name="sepete_ekle" 
                                        style="background-color: #27ae60; color: white; border: none; padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: 0.2s;">
                                    <i class="fa-solid fa-cart-plus"></i> Sepete Ekle
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($gelenIlac != ""): ?>
            <div style="text-align: center; padding: 50px; color: #7f8c8d;">
                <i class="fa-solid fa-box-open" style="font-size: 50px; margin-bottom: 15px;"></i>
                <p>Aradığınız kriterlere uygun ilaç bulunamadı veya stoklarda yok.</p>
            </div>
        <?php endif; ?>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var seciliSehir = "<?php echo $gelenSehir; ?>";
            var seciliIlce  = "<?php echo $gelenIlce; ?>";
            
            if (seciliSehir !== "") {
                ilceleriGetir(seciliIlce);
            }
        });

        function ilceleriGetir(seciliIlceID = null) {
            var sehirID = document.getElementById('sehirKutusu').value;
            var ilceKutusu = document.getElementById('ilceKutusu');

            if (sehirID === "") {
                ilceKutusu.innerHTML = '<option value="">Önce Şehir Seçiniz</option>';
                return;
            }

            fetch('api.php?type=get_districts&il_id=' + sehirID)
                .then(response => response.json())
                .then(data => {
                    var html = '<option value="">İlçe Seçiniz</option>';
                    data.forEach(function(item) {
                        var isSelected = (seciliIlceID == item.IlceID) ? 'selected' : '';
                        html += '<option value="' + item.IlceID + '" ' + isSelected + '>' + item.IlceAdi + '</option>';
                    });
                    ilceKutusu.innerHTML = html;
                })
                .catch(error => console.error('Hata:', error));
        }
    </script>

</body>
</html>