<?php
session_start();
require 'db.php';

// Güvenlik: Sadece personel girebilir
if (!isset($_SESSION['personel_id'])) {
    header("Location: login.php");
    exit;
}

$eczaneID = $_SESSION['eczane_id'];
$eczaneAdi = $_SESSION['eczane_adi'];
$personelAdi = $_SESSION['personel_adi'];
$mesaj = "";

// --- İŞLEM 1: YENİ İLAÇ EKLEME ---
if (isset($_POST['yeni_ilac_ekle'])) {
    $ilacID = $_POST['ilac_id'];
    $adet = $_POST['adet'];
    $fiyat = $_POST['fiyat'];

    $kontrol = $pdo->prepare("SELECT * FROM eczanestok WHERE EczaneID = ? AND IlacID = ?");
    $kontrol->execute([$eczaneID, $ilacID]);

    if ($kontrol->rowCount() > 0) {
        $mesaj = "<div class='alert error'><i class='fa-solid fa-triangle-exclamation'></i> Bu ilaç zaten stoğunuzda var! Lütfen listeden düzenleyin.</div>";
    } else {
        $sql = "INSERT INTO eczanestok (EczaneID, IlacID, Adet, Fiyat) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $sonuc = $stmt->execute([$eczaneID, $ilacID, $adet, $fiyat]);
        if($sonuc) $mesaj = "<div class='alert success'><i class='fa-solid fa-check-circle'></i> İlaç başarıyla stoğa eklendi.</div>";
    }
}

// --- İŞLEM 2: STOK GÜNCELLEME ---
if (isset($_POST['stok_guncelle'])) {
    $stokID = $_POST['stok_id'];
    $adet = $_POST['adet'];
    $fiyat = $_POST['fiyat'];

    $sql = "UPDATE eczanestok SET Adet = ?, Fiyat = ? WHERE StokID = ? AND EczaneID = ?";
    $stmt = $pdo->prepare($sql);
    $sonuc = $stmt->execute([$adet, $fiyat, $stokID, $eczaneID]);

    if($sonuc) $mesaj = "<div class='alert success'><i class='fa-solid fa-check-circle'></i> Stok bilgisi güncellendi.</div>";
    else $mesaj = "<div class='alert error'>Güncelleme hatası.</div>";
}

// --- İŞLEM 3: SİLME ---
if (isset($_GET['sil'])) {
    $stokID = $_GET['sil'];
    $sil = $pdo->prepare("DELETE FROM eczanestok WHERE StokID = ? AND EczaneID = ?");
    $sil->execute([$stokID, $eczaneID]);
    header("Location: eczane-panel.php");
    exit;
}

// --- VERİLERİ ÇEK ---
$stoklar = $pdo->prepare("
    SELECT es.*, i.IlacAdi, i.ResimYolu, i.Barkod
    FROM eczanestok es 
    JOIN ilaclar i ON es.IlacID = i.IlacID 
    WHERE es.EczaneID = ?
    ORDER BY es.StokID DESC
");
$stoklar->execute([$eczaneID]);
$stokListesi = $stoklar->fetchAll();

$tumIlaclar = $pdo->query("SELECT * FROM ilaclar ORDER BY IlacAdi ASC")->fetchAll();
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
        
        /* HEADER KART */
        .panel-header {
            background: white; padding: 25px 35px; border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 30px;
            display: flex; justify-content: space-between; align-items: center; border: 1px solid #f0f0f0;
        }
        
        /* İÇERİK KARTI */
        .card { 
            background: white; padding: 30px; border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; 
        }
        
        /* TABLO TASARIMI */
        table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: 15px; }
        th { text-align: left; padding: 15px 20px; color: #95a5a6; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        td { background: #fff; padding: 15px 20px; vertical-align: middle; border-top: 1px solid #f9f9f9; border-bottom: 1px solid #f9f9f9; transition: all 0.2s; }
        tr:hover td { background-color: #fafafa; }
        
        tr td:first-child { border-left: 1px solid #f9f9f9; border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        tr td:last-child { border-right: 1px solid #f9f9f9; border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
        
        /* BUTONLAR */
        .btn-action {
            width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;
            color: white; margin-right: 8px; transition: 0.2s; text-decoration: none; border:none; cursor: pointer; font-size: 14px;
        }
        .btn-edit { background-color: #eef2ff; color: #4361ee; } 
        .btn-edit:hover { background-color: #4361ee; color: white; }
        
        .btn-delete { background-color: #fff1f2; color: #e11d48; } 
        .btn-delete:hover { background-color: #e11d48; color: white; }
        
        .btn-add {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white; padding: 12px 25px; border-radius: 50px; border: none;
            font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 10px;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3); transition: transform 0.2s;
        }
        .btn-add:hover { transform: translateY(-2px); }

        /* MODAL (POP-UP) */
        .modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); align-items: center; justify-content: center;
        }
        .modal-content {
            background-color: white; padding: 40px; border-radius: 20px; width: 450px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2); position: relative; animation: slideIn 0.3s ease;
        }
        @keyframes slideIn { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        
        .close-btn { position: absolute; top: 20px; right: 25px; font-size: 28px; cursor: pointer; color: #ddd; transition: 0.2s; }
        .close-btn:hover { color: #333; }
        
        /* MESAJ KUTULARI */
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .form-control { 
            width: 100%; padding: 14px; border: 1px solid #e0e0e0; border-radius: 10px; margin-top: 8px; margin-bottom: 20px; 
            font-family: 'Poppins'; font-size: 14px; transition: 0.2s; background: #fafafa;
        }
        .form-control:focus { border-color: #2ecc71; background: white; outline: none; }
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
                <i class="fa-solid fa-plus-circle"></i> Yeni İlaç Ekle
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
                    <p style="font-size:14px;">"Yeni İlaç Ekle" butonu ile başlayabilirsiniz.</p>
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
                                    <img src="<?php echo !empty($stok['ResimYolu']) ? $stok['ResimYolu'] : 'assets/img/logo.png'; ?>" 
                                         style="width:50px; height:50px; object-fit:contain; border-radius:10px; border:1px solid #f0f0f0; padding:2px;">
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#2c3e50; font-size:15px;"><?php echo $stok['IlacAdi']; ?></div>
                                    <div style="font-size:12px; color:#95a5a6;"><?php echo $stok['Barkod'] ?? 'Barkodsuz'; ?></div>
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
                                    <button 
                                        onclick="openEditModal(this)" 
                                        data-id="<?php echo $stok['StokID']; ?>"
                                        data-ad="<?php echo $stok['IlacAdi']; ?>"
                                        data-adet="<?php echo $stok['Adet']; ?>"
                                        data-fiyat="<?php echo $stok['Fiyat']; ?>"
                                        class="btn-action btn-edit" title="Düzenle">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <a href="?sil=<?php echo $stok['StokID']; ?>" class="btn-action btn-delete" title="Sil" onclick="return confirm('Bu ilacı stoktan silmek istediğinize emin misiniz?')">
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
                <div style="background:#e8f5e9; width:60px; height:60px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#27ae60; font-size:24px; margin-bottom:10px;">
                    <i class="fa-solid fa-pills"></i>
                </div>
                <h3 style="margin:0; color:#333;">Yeni İlaç Ekle</h3>
            </div>
            
            <form method="POST">
                <label style="font-size:13px; font-weight:600; color:#555; margin-left:5px;">İlaç Seçiniz</label>
                <select name="ilac_id" class="form-control" required>
                    <option value="">Listeden Seçiniz...</option>
                    <?php foreach($tumIlaclar as $ilac): ?>
                        <option value="<?php echo $ilac['IlacID']; ?>"><?php echo $ilac['IlacAdi']; ?></option>
                    <?php endforeach; ?>
                </select>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#555; margin-left:5px;">Stok Adedi</label>
                        <input type="number" name="adet" class="form-control" placeholder="0" required min="0">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#555; margin-left:5px;">Birim Fiyat (₺)</label>
                        <input type="number" name="fiyat" class="form-control" placeholder="0.00" step="0.01" required min="0">
                    </div>
                </div>

                <button type="submit" name="yeni_ilac_ekle" class="btn-add" style="width:100%; justify-content:center; margin-top:10px;">
                    Kaydet ve Ekle
                </button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            <div style="text-align:center; margin-bottom:25px;">
                <div style="background:#eef2ff; width:60px; height:60px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#4361ee; font-size:24px; margin-bottom:10px;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <h3 style="margin:0; color:#333;">Stok Düzenle</h3>
            </div>
            
            <form method="POST">
                <input type="hidden" name="stok_id" id="edit_stok_id">

                <label style="font-size:13px; font-weight:600; color:#555; margin-left:5px;">İlaç Adı</label>
                <input type="text" id="edit_ilac_adi" class="form-control" readonly style="background:#f0f0f0; color:#777; cursor:not-allowed;">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#555; margin-left:5px;">Yeni Stok</label>
                        <input type="number" name="adet" id="edit_adet" class="form-control" required min="0">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#555; margin-left:5px;">Yeni Fiyat (₺)</label>
                        <input type="number" name="fiyat" id="edit_fiyat" class="form-control" step="0.01" required min="0">
                    </div>
                </div>

                <button type="submit" name="stok_guncelle" class="btn-add" style="width:100%; justify-content:center; margin-top:10px; background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%); box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);">
                    Güncelle
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openEditModal(btn) {
            var id = btn.getAttribute('data-id');
            var ad = btn.getAttribute('data-ad');
            var adet = btn.getAttribute('data-adet');
            var fiyat = btn.getAttribute('data-fiyat');

            document.getElementById('edit_stok_id').value = id;
            document.getElementById('edit_ilac_adi').value = ad;
            document.getElementById('edit_adet').value = adet;
            document.getElementById('edit_fiyat').value = fiyat;

            openModal('editModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
    </script>

</body>
</html>