<?php
session_start();
require 'db.php';

// 1. GÜVENLİK KONTROLÜ
if (!isset($_SESSION['personel_id'])) {
    header("Location: login.php");
    exit;
}

$personelID = $_SESSION['personel_id'];

// 2. PERSONELİN ECZANE ID'SİNİ BUL
// Oturumda kayıtlı değilse veritabanından çekelim
if (!isset($_SESSION['eczane_id'])) {
    $stmtP = $pdo->prepare("SELECT EczaneID, AdSoyad FROM Personel WHERE PersonelID = ?");
    $stmtP->execute([$personelID]);
    $personelData = $stmtP->fetch(PDO::FETCH_ASSOC);
    $_SESSION['eczane_id'] = $personelData['EczaneID'];
    $_SESSION['personel_adi'] = $personelData['AdSoyad'];
}
$eczaneID = $_SESSION['eczane_id'];

// 3. DURUM GÜNCELLEME İŞLEMİ
if (isset($_POST['durum_guncelle'])) {
    $siparisID = $_POST['siparis_id'];
    $yeniDurum = $_POST['yeni_durum'];

    // Siparişin genel durumunu güncelliyoruz
    $upd = $pdo->prepare("UPDATE Siparisler SET Durum = ? WHERE SiparisID = ?");
    $upd->execute([$yeniDurum, $siparisID]);
    
    // Sayfayı yenile (form tekrar gönderilmesin diye)
    header("Location: personel-siparisler.php");
    exit;
}

// 4. SİPARİŞLERİ ÇEKME (Karmaşık Sorgu)
// Mantık: SiparisDetay tablosundan SADECE benim eczaneme ait olan satırları,
// Siparisler ve Hastalar tablosuyla birleştirerek getir.
$sql = "SELECT 
            s.SiparisID, s.SiparisTarihi, s.Durum, s.ToplamTutar,
            h.AdSoyad AS HastaAdi, h.Telefon, h.Adres,
            d.IlacID, d.Adet, d.BirimFiyat,
            i.IlacAdi, i.ReceteTuru
        FROM SiparisDetay d
        JOIN Siparisler s ON d.SiparisID = s.SiparisID
        JOIN Hastalar h ON s.HastaID = h.HastaID
        JOIN Ilaclar i ON d.IlacID = i.IlacID
        WHERE d.EczaneID = ?
        ORDER BY s.SiparisTarihi DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$eczaneID]);
$satirlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. VERİLERİ GRUPLAMA
// SQL satır satır ilaç döndürür, biz bunları Sipariş bazında gruplamalıyız.
$siparisler = [];
foreach ($satirlar as $row) {
    $id = $row['SiparisID'];
    
    // Eğer bu sipariş dizide yoksa başlık bilgilerini ekle
    if (!isset($siparisler[$id])) {
        $siparisler[$id] = [
            'info' => [
                'Tarih' => $row['SiparisTarihi'],
                'Durum' => $row['Durum'],
                'HastaAdi' => $row['HastaAdi'],
                'Telefon' => $row['Telefon'],
                'Adres' => $row['Adres'],
            ],
            'urunler' => [],
            'toplamTutar' => 0 // Sadece benim eczanemdeki tutar
        ];
    }
    
    // Ürünü listeye ekle
    $siparisler[$id]['urunler'][] = [
        'IlacAdi' => $row['IlacAdi'],
        'Adet' => $row['Adet'],
        'Fiyat' => $row['BirimFiyat'],
        'Recete' => $row['ReceteTuru']
    ];
    
    // Eczane bazlı toplam tutarı hesapla
    $siparisler[$id]['toplamTutar'] += ($row['Adet'] * $row['BirimFiyat']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Eczane Paneli | Gelen Siparişler</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    
    <style>
        body { background-color: #f1f2f6; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        
        .panel-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 15px;
        }
        .panel-header h2 { color: #2c3e50; margin: 0; }
        
        /* Kart Tasarımı */
        .order-card {
            background: white; border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px; overflow: hidden;
            border-left: 5px solid #bdc3c7; /* Varsayılan renk */
        }
        
        /* Durumlara göre kenar renkleri */
        .border-bekleniyor { border-left-color: #f1c40f !important; }
        .border-hazirlaniyor { border-left-color: #3498db !important; }
        .border-tamamlandi { border-left-color: #2ecc71 !important; }
        .border-iptal { border-left-color: #e74c3c !important; }

        .card-header {
            padding: 20px; background: #fdfdfd; border-bottom: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: flex-start;
        }
        
        .customer-info h4 { margin: 0 0 5px 0; color: #2c3e50; font-size: 18px; }
        .customer-info p { margin: 0; color: #7f8c8d; font-size: 14px; }
        .customer-info i { width: 20px; text-align: center; color: #95a5a6; }

        .order-meta { text-align: right; }
        .order-id { font-weight: 700; color: #34495e; font-size: 16px; display: block; margin-bottom: 5px; }
        .order-date { font-size: 13px; color: #95a5a6; }

        .card-body { padding: 20px; }
        
        table.order-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.order-table th { text-align: left; color: #95a5a6; font-size: 13px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table.order-table td { padding: 10px 0; border-bottom: 1px solid #f9f9f9; color: #333; }
        
        .recete-badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; color: white; margin-left: 5px; }

        .card-footer {
            background: #fafafa; padding: 15px 20px;
            display: flex; justify-content: space-between; align-items: center;
            border-top: 1px solid #eee;
        }

        .total-amount { font-size: 18px; font-weight: 700; color: #2c3e50; }
        .total-label { font-size: 13px; color: #7f8c8d; font-weight: 400; }

        /* Form Stili */
        .status-form { display: flex; gap: 10px; align-items: center; }
        .status-select {
            padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd;
            font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-update {
            background: #2c3e50; color: white; border: none; padding: 8px 15px;
            border-radius: 6px; cursor: pointer; font-size: 13px; transition: 0.2s;
        }
        .btn-update:hover { background: #1a252f; }
        
        .empty-box { text-align: center; padding: 50px; color: #bdc3c7; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        
        <div class="panel-header">
            <h2><i class="fa-solid fa-clipboard-list"></i> Gelen Siparişler</h2>
            <span style="font-size: 14px; color: #7f8c8d;">
                <?php echo htmlspecialchars($_SESSION['personel_adi']); ?> - Eczane ID: <?php echo $eczaneID; ?>
            </span>
        </div>

        <?php if (empty($siparisler)): ?>
            <div class="empty-box">
                <i class="fa-solid fa-box-open" style="font-size: 60px; margin-bottom: 20px;"></i>
                <h3>Şu an bekleyen sipariş bulunmuyor.</h3>
            </div>
        <?php else: ?>

            <?php foreach ($siparisler as $siparisID => $data): ?>
                <?php 
                    // Duruma göre renk belirleme
                    $durumKod = strtolower($data['info']['Durum']);
                    if(strpos($durumKod, 'bekle') !== false) $borderClass = 'border-bekleniyor';
                    elseif(strpos($durumKod, 'hazır') !== false) $borderClass = 'border-hazirlaniyor';
                    elseif(strpos($durumKod, 'tamam') !== false) $borderClass = 'border-tamamlandi';
                    elseif(strpos($durumKod, 'iptal') !== false) $borderClass = 'border-iptal';
                    else $borderClass = '';
                ?>
                
                <div class="order-card <?php echo $borderClass; ?>">
                    <div class="card-header">
                        <div class="customer-info">
                            <h4><?php echo htmlspecialchars($data['info']['HastaAdi']); ?></h4>
                            <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($data['info']['Telefon']); ?></p>
                            <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($data['info']['Adres']); ?></p>
                        </div>
                        <div class="order-meta">
                            <span class="order-id">Sipariş #<?php echo $siparisID; ?></span>
                            <span class="order-date"><i class="fa-regular fa-clock"></i> <?php echo date("d.m.Y H:i", strtotime($data['info']['Tarih'])); ?></span>
                            <br>
                            <span style="font-size:12px; font-weight:600; color:#555; background:#eee; padding:2px 8px; border-radius:4px; margin-top:5px; display:inline-block;">
                                <?php echo $data['info']['Durum']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>İlaç Adı</th>
                                    <th>Reçete</th>
                                    <th style="text-align:center;">Adet</th>
                                    <th style="text-align:right;">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['urunler'] as $urun): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($urun['IlacAdi']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $rRenk = '#95a5a6'; 
                                            if($urun['Recete']=='Kirmizi') $rRenk='#e74c3c';
                                            elseif($urun['Recete']=='Yesil') $rRenk='#2ecc71';
                                        ?>
                                        <span class="recete-badge" style="background:<?php echo $rRenk; ?>"><?php echo $urun['Recete']; ?></span>
                                    </td>
                                    <td style="text-align:center; font-weight:bold;"><?php echo $urun['Adet']; ?></td>
                                    <td style="text-align:right;"><?php echo number_format($urun['Fiyat'] * $urun['Adet'], 2); ?> ₺</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <div>
                            <span class="total-label">Eczane Toplamı:</span>
                            <span class="total-amount"><?php echo number_format($data['toplamTutar'], 2); ?> ₺</span>
                        </div>
                        
                        <form method="POST" class="status-form">
                            <input type="hidden" name="siparis_id" value="<?php echo $siparisID; ?>">
                            
                            <select name="yeni_durum" class="status-select">
                                <option value="Bekleniyor" <?php echo ($data['info']['Durum'] == 'Bekleniyor') ? 'selected' : ''; ?>>Bekleniyor</option>
                                <option value="Hazırlanıyor" <?php echo ($data['info']['Durum'] == 'Hazırlanıyor') ? 'selected' : ''; ?>>Hazırlanıyor</option>
                                <option value="Kuryede" <?php echo ($data['info']['Durum'] == 'Kuryede') ? 'selected' : ''; ?>>Kuryede</option>
                                <option value="Tamamlandı" <?php echo ($data['info']['Durum'] == 'Tamamlandı') ? 'selected' : ''; ?>>Tamamlandı</option>
                                <option value="İptal" <?php echo ($data['info']['Durum'] == 'İptal') ? 'selected' : ''; ?>>İptal Et</option>
                            </select>
                            
                            <button type="submit" name="durum_guncelle" class="btn-update">
                                <i class="fa-solid fa-rotate"></i> Güncelle
                            </button>
                        </form>
                    </div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</body>
</html>