<?php
// PHP MOTORU (Değişiklik Yok)
require 'db.php'; 
error_reporting(0);

$gelenSehir = isset($_GET['sehir_id']) ? $_GET['sehir_id'] : "";
$gelenIlce  = isset($_GET['ilce_id'])  ? $_GET['ilce_id']  : "";
$gelenIlac  = isset($_GET['ilac_adi']) ? $_GET['ilac_adi'] : "";

$sonuclar = [];

if ($gelenIlac != "" && $gelenIlce != "") {
    try {
        $sql = "CALL sp_IlacBul(:ilac, :ilce)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['ilac' => $gelenIlac, 'ilce' => $gelenIlce]);
        $sonuclar = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $e) { }
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

    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand" style="display: flex; align-items: center;">
                <img src="assets/img/logo.png" alt="Logo" style="height: 35px; margin-right: 10px;" onerror="this.style.display='none'">
                e-Ecza
            </div>
            <div class="nav-right">
                <a href="#" class="nav-link">Nöbetçi Eczaneler</a>
                <a href="hasta-login.php" class="nav-link">Hasta / Üye Girişi</a>
                <a href="login.php" class="btn-staff-login">Personel Girişi</a>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="hero-content">
            <h1>İlacınız Hangi Eczanede?</h1>
            <p>Konumunuzu seçin, aradığınız ilacın en yakın hangi eczanede olduğunu hemen bulun.</p>

            <div class="search-wrapper" style="display: flex; align-items: center; justify-content: space-between; padding: 8px; background: white; border-radius: 50px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                
                <select id="sehirKutusu" onchange="sehirDegistir()" 
                        style="border: none; background: transparent; padding: 15px; font-size: 16px; cursor: pointer; flex: 1; outline: none;">
                    <option value="" disabled <?php echo ($gelenSehir == "") ? 'selected' : ''; ?>>Şehir Seçiniz</option>
                    <?php
                    try {
                        $iller = $pdo->query("SELECT * FROM Iller ORDER BY IlAdi ASC")->fetchAll();
                        foreach ($iller as $il) {
                            $secili = ($il['IlID'] == $gelenSehir) ? 'selected' : '';
                            echo '<option value="'.$il['IlID'].'" '.$secili.'>'.$il['IlAdi'].'</option>';
                        }
                    } catch (Exception $e) { }
                    ?>
                </select>

                <div style="width: 1px; height: 30px; background-color: #ddd;"></div>

                <select id="ilceKutusu" 
                        style="border: none; background: transparent; padding: 15px; font-size: 16px; cursor: pointer; flex: 1; outline: none;">
                    <option value="" disabled <?php echo ($gelenIlce == "") ? 'selected' : ''; ?>>İlçe Seçiniz</option>
                    <?php
                    if ($gelenSehir != "") {
                        try {
                            $stmt = $pdo->prepare("SELECT * FROM Ilceler WHERE IlID = :id ORDER BY IlceAdi ASC");
                            $stmt->execute(['id' => $gelenSehir]);
                            $ilceler = $stmt->fetchAll();
                            foreach ($ilceler as $ilce) {
                                $secili = ($ilce['IlceID'] == $gelenIlce) ? 'selected' : '';
                                echo '<option value="'.$ilce['IlceID'].'" '.$secili.'>'.$ilce['IlceAdi'].'</option>';
                            }
                        } catch (Exception $e) {}
                    }
                    ?>
                </select>

                <div style="width: 1px; height: 30px; background-color: #ddd;"></div>

                <input type="text" id="ilacKutusu" placeholder="İlaç adı giriniz..." value="<?php echo htmlspecialchars($gelenIlac); ?>"
                       style="border: none; background: transparent; padding: 15px; font-size: 16px; flex: 2; outline: none; color: #333;">
                
                <button type="button" onclick="aramaYap()" 
                        style="background-color: #e63946; color: white; border: none; border-radius: 50px; padding: 12px 35px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3); margin-left: 10px;">
                    <i class="fa-solid fa-location-dot" style="margin-right: 8px;"></i>
                    En Yakın İlacımı Bul
                </button>

            </div>
        </div>
    </header>

    <section class="results-container" style="padding: 40px; max-width: 1200px; margin: 0 auto;">
        <?php if (!empty($sonuclar)): ?>
            <h3 style="margin-bottom: 20px;">"<?php echo htmlspecialchars($gelenIlac); ?>" için sonuçlar:</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                <?php foreach ($sonuclar as $row): ?>
                    <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <h4 style="color: #e63946; margin-bottom: 10px;"><?php echo $row['EczaneAdi']; ?></h4>
                        <p style="color: #555;"><i class="fa-solid fa-map-pin"></i> <?php echo $row['Adres']; ?></p>
                        <p style="color: #555;"><i class="fa-solid fa-phone"></i> <?php echo $row['Telefon']; ?></p>
                        <hr style="margin: 15px 0; border:0; border-top:1px solid #eee;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; color: #2a9d8f;">
                                <i class="fa-solid fa-box"></i> Stok: <?php echo $row['Adet']; ?>
                            </span>
                            <span style="background: #2a9d8f; color: white; padding: 5px 15px; border-radius: 20px;">
                                <?php echo $row['Fiyat']; ?> ₺
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($gelenIlac != ""): ?>
            <div style="text-align: center; padding: 50px; color: #7f8c8d;">
                <i class="fa-solid fa-box-open" style="font-size: 50px; margin-bottom: 15px;"></i>
                <p>Aradığınız kriterlere uygun ilaç bulunamadı.</p>
            </div>
        <?php endif; ?>
    </section>

    <script>
        function sehirDegistir() {
            var id = document.getElementById('sehirKutusu').value;
            window.location.href = "index.php?sehir_id=" + id;
        }

        function aramaYap() {
            var sehir = document.getElementById('sehirKutusu').value;
            var ilce  = document.getElementById('ilceKutusu').value;
            var ilac  = document.getElementById('ilacKutusu').value;

            if (sehir == "" || ilce == "" || ilac == "") {
                alert("Lütfen şehir, ilçe ve ilaç adını giriniz!");
                return;
            }
            window.location.href = "index.php?sehir_id=" + sehir + "&ilce_id=" + ilce + "&ilac_adi=" + ilac;
        }
    </script>
    
    <script src="assets/js/search.js"></script>
</body>
</html>