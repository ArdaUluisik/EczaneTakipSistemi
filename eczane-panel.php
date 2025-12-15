<?php
session_start();

// --- 1. OOP Sınıflarını Dahil Et ---
require_once 'classes/Database.php';
require_once 'classes/Stok.php';

// Güvenlik
if (!isset($_SESSION['personel_id'])) {
    header("Location: login.php");
    exit;
}

// --- 2. Sınıfları Başlat ---
$db = Database::getInstance()->getConnection();
$stokYonetim = new Stok($db);

$eczaneID = $_SESSION['eczane_id'];
$eczaneAdi = $_SESSION['eczane_adi'];
$personelAdi = $_SESSION['personel_adi'];
$mesaj = "";

// --- İŞLEM 1: SIFIRDAN YENİ İLAÇ VE STOK EKLEME ---
if (isset($_POST['yeni_ilac_kaydet'])) {
    // Varsayılan olarak Beyaz ata
    $recete = isset($_POST['recete_turu']) ? $_POST['recete_turu'] : 'Beyaz';

    $sonuc = $stokYonetim->yeniIlacVeStokEkle(
        $eczaneID, 
        $_POST['ilac_adi'], 
        $recete, 
        $_POST['adet'], 
        $_POST['fiyat']
    );

    if ($sonuc == "basarili") {
        $mesaj = "<div class='alert success'><i class='fa-solid fa-check-circle'></i> İlaç sisteme ve stoğa başarıyla eklendi.</div>";
    } elseif ($sonuc == "var") {
        $mesaj = "<div class='alert error'><i class='fa-solid fa-triangle-exclamation'></i> Bu ilaç zaten stoğunuzda var!</div>";
    } else {
        $mesaj = "<div class='alert error'>Bir hata oluştu (" . $sonuc . ")</div>";
    }
}

// --- İŞLEM 2: STOK GÜNCELLEME ---
if (isset($_POST['stok_guncelle'])) {
    $sonuc = $stokYonetim->guncelle($_POST['stok_id'], $eczaneID, $_POST['adet'], $_POST['fiyat']);
    if($sonuc) $mesaj = "<div class='alert success'><i class='fa-solid fa-check-circle'></i> Güncellendi.</div>";
    else $mesaj = "<div class='alert error'>Hata oluştu.</div>";
}

// --- İŞLEM 3: SİLME ---
if (isset($_GET['sil'])) {
    $stokYonetim->sil($_GET['sil'], $eczaneID);
    header("Location: eczane-panel.php");
    exit;
}

// --- VERİLERİ ÇEK ---
$stokListesi = $stokYonetim->listele($eczaneID);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli | <?php echo htmlspecialchars($eczaneAdi); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .panel-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .panel-header { background: white; padding: 25px 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #f0f0f0; }
        .card { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: 15px; }
        th { text-align: left; padding: 15px 20px; color: #95a5a6; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { background: #fff; padding: 15px 20px; vertical-align: middle; border-top: 1px solid #f9f9f9; border-bottom: 1px solid #f9f9f9; transition: all 0.2s; }
        tr:hover td { background-color: #fafafa; }
        tr td:first-child { border-left: 1px solid #f9f9f9; border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        tr td:last-child { border-right: 1px solid #f9f9f9; border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
        .btn-action { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: white; margin-right: 8px; transition: 0.2s; text-decoration: none; border:none; cursor: pointer; font-size: 14px; }
        .btn-edit { background-color: #eef2ff; color: #4361ee; } .btn-edit:hover { background-color: #4361ee; color: white; }
        .btn-delete { background-color: #fff1f2; color: #e11d48; } .btn-delete:hover { background-color: #e11d48; color: white; }
        .btn-add { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 12px 25px; border-radius: 50px; border: none; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3); transition: transform 0.2s; }
        .btn-add:hover { transform: translateY(-2px); }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); align-items: center; justify-content: center; }
        .modal-content { background-color: white; padding: 40px; border-radius: 20px; width: 500px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); position: relative; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .close-btn { position: absolute; top: 20px; right: 25px; font-size: 28px; cursor: pointer; color: #ddd; transition: 0.2s; }
        .close-btn:hover { color: #333; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .form-control { width: 100%; padding: 14px; border: 1px solid #e0e0e0; border-radius: 10px; margin-top: 8px; margin-bottom: 20px; font-family: 'Poppins'; font-size: 14px; transition: 0.2s; background: #fafafa; }
        .form-control:focus { border-color: #2ecc71; background: white; outline: none; }
        label { font-size:13px; font-weight:600; color:#555; margin-left:5px; display:block; margin-bottom:5px; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="panel-container">
        <div class="panel-header">
            <div style="display:flex; align-items:center; gap:20px;">
                <div style="width:60px; height:60px; background:#fff0f1; border-radius:15px; display:flex; align-items:center; justify-content:center; color:#e63946; font-size:28px;">
                    <i class="fa-solid fa-shop"></i>
                </div>
                <div>
                    <h2 style="margin:0; font-size:22px; color:#2c3e50; font-weight:700;"><?php echo htmlspecialchars($eczaneAdi); ?></h2>
                    <span style="font-size:14px; color:#7f8c8d; font-weight:500;">
                        <i class="fa-solid fa-user-doctor"></i> <?php echo htmlspecialchars($personelAdi); ?> (Yönetici)
                    </span>
                </div>
            </div>
            
            <button onclick="openModal('addModal')" class="btn-add">
                <i class="fa-solid fa-plus-circle"></i> Yeni İlaç Tanımla
            </button>
        </div>

        <?php echo $mesaj; ?>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:15px;">
                <h3 style="margin:0; font-size:18px; color:#333; font-weight:600;">
                    <i class="fa-solid fa-list-check" style="color:#3498db; margin-right:10px;"></i> Mevcut Stok Durumu
                </h3>
                <span style="font-size:13px; color:#999;"><?php echo count($stokListesi); ?> ürün listeleniyor</span>
            </div>
            
            <?php if(empty($stokListesi)): ?>
                <div style="text-align:center; padding:60px; color:#bdc3c7;">
                    <i class="fa-solid fa-box-open" style="font-size:60px; margin-bottom:15px; display:block;"></i>
                    <p style="font-size:16px;">Stoğunuzda henüz hiç ilaç bulunmamaktadır.</p>
                </div>
            <?php else: ?>
                <table style="width:100%">
                    <thead>
                        <tr>
                            <th width="80">Görsel</th>
                            <th>İlaç Adı</th>
                            <th>Stok Durumu</th>
                            <th>Satış Fiyatı</th>
                            <th width="120" style="text-align:right;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stokListesi as $stok): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo (!empty($stok['ResimYolu']) && file_exists($stok['ResimYolu'])) ? $stok['ResimYolu'] : 'assets/img/logo.png'; ?>" 
                                         style="width:50px; height:50px; object-fit:contain; border-radius:10px; border:1px solid #f0f0f0; padding:2px;">
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#2c3e50; font-size:15px;"><?php echo $stok['IlacAdi']; ?></div>
                                    <?php 
                                        $receteTuru = isset($stok['ReceteTuru']) ? $stok['ReceteTuru'] : 'Beyaz';
                                        
                                        // YENİ RENK KODLARI
                                        $renk = '#95a5a6'; $yazi = 'Beyaz Reçete';
                                        if($receteTuru == 'Kirmizi') { $renk = '#e74c3c'; $yazi = 'Kırmızı Reçete'; }
                                        elseif($receteTuru == 'Turuncu') { $renk = '#f39c12'; $yazi = 'Turuncu Reçete'; }
                                        elseif($receteTuru == 'Yesil') { $renk = '#2ecc71'; $yazi = 'Yeşil Reçete'; }
                                        elseif($receteTuru == 'Mor') { $renk = '#9b59b6'; $yazi = 'Mor Reçete'; }
                                    ?>
                                    <span style="font-size:10px; font-weight:700; color:white; background:<?php echo $renk; ?>; padding:2px 8px; border-radius:10px; display:inline-block; margin-top:3px;">
                                        <?php echo $yazi; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($stok['Adet'] < 10): ?>
                                        <span style="background:#fee2e2; color:#991b1b; padding:6px 15px; border-radius:30px; font-weight:600; font-size:12px;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Kritik: <?php echo $stok['Adet']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="background:#dcfce7; color:#166534; padding:6px 15px; border-radius:30px; font-weight:600; font-size:12px;">
                                            <i class="fa-solid fa-check"></i> <?php echo $stok['Adet']; ?> Adet
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-weight:700; color:#2c3e50; font-size:16px;">
                                        <?php echo number_format($stok['Fiyat'], 2, ',', '.'); ?> <small>₺</small>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <button onclick="openEditModal(this)" 
                                            data-id="<?php echo $stok['StokID']; ?>"
                                            data-ad="<?php echo $stok['IlacAdi']; ?>"
                                            data-adet="<?php echo $stok['Adet']; ?>"
                                            data-fiyat="<?php echo $stok['Fiyat']; ?>"
                                            class="btn-action btn-edit" title="Düzenle">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="?sil=<?php echo $stok['StokID']; ?>" class="btn-action btn-delete" title="Sil" onclick="return confirm('Silmek istediğinize emin misiniz?')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
            <div style="text-align:center; margin-bottom:25px;">
                <h3 style="margin:0; color:#333;">Yeni İlaç Kaydı</h3>
            </div>
            
            <form method="POST">
                
                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:15px;">
                    <div>
                        <label>İlaç Adı</label>
                        <input type="text" name="ilac_adi" class="form-control" placeholder="Örn: Parol 500mg" required>
                    </div>
                    <div>
                        <label>Reçete Türü</label>
                        <select name="recete_turu" class="form-control" required>
                            <option value="Beyaz">⚪ Beyaz</option>
                            <option value="Kirmizi">🔴 Kırmızı</option>
                            <option value="Yesil">🟢 Yeşil</option>
                            <option value="Turuncu">🟠 Turuncu</option>
                            <option value="Mor">🟣 Mor</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div><label>Stok Adedi</label><input type="number" name="adet" class="form-control" required min="0"></div>
                    <div><label>Birim Fiyat (₺)</label><input type="number" name="fiyat" class="form-control" step="0.01" required min="0"></div>
                </div>

                <button type="submit" name="yeni_ilac_kaydet" class="btn-add" style="width:100%; justify-content:center;">Kaydet ve Ekle</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            <h3>Stok Düzenle</h3>
            <form method="POST">
                <input type="hidden" name="stok_id" id="edit_stok_id">
                <label>İlaç Adı</label>
                <input type="text" id="edit_ilac_adi" class="form-control" readonly style="background:#f0f0f0;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div><label>Yeni Stok</label><input type="number" name="adet" id="edit_adet" class="form-control" required></div>
                    <div><label>Yeni Fiyat</label><input type="number" name="fiyat" id="edit_fiyat" class="form-control" step="0.01" required></div>
                </div>
                <button type="submit" name="stok_guncelle" class="btn-add" style="width:100%; justify-content:center;">Güncelle</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        function openEditModal(btn) {
            document.getElementById('edit_stok_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_ilac_adi').value = btn.getAttribute('data-ad');
            document.getElementById('edit_adet').value = btn.getAttribute('data-adet');
            document.getElementById('edit_fiyat').value = btn.getAttribute('data-fiyat');
            openModal('editModal');
        }
        window.onclick = function(e) { if (e.target.classList.contains('modal')) e.target.style.display = "none"; }
    </script>

</body>
</html>