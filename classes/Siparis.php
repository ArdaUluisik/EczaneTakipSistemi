<?php
class Siparis {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- YARDIMCI FONKSİYON: DETAYLI REÇETE DOĞRULA ---
    public function receteDogrula($receteKodu, $hastaID, $sepet) {
        // 1. Önce reçete başlığını ve sahibini kontrol et
        $sqlBaslik = "SELECT r.ReceteID 
                      FROM Receteler r
                      JOIN Hastalar h ON r.TCNo = h.TCNo
                      WHERE r.ReceteKodu = ? AND h.HastaID = ?";
        
        $stmt = $this->conn->prepare($sqlBaslik);
        $stmt->execute([$receteKodu, $hastaID]);
        $recete = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recete) {
            return ["status" => false, "mesaj" => "Reçete numarası geçersiz veya bu hastaya ait değil."];
        }

        $receteID = $recete['ReceteID'];

        // 2. Sepetteki her ilacı kontrol et
        foreach ($sepet as $ilacID => $adet) {
            
            // İlacın reçete türünü öğren
            $sqlTur = "SELECT ReceteTuru, IlacAdi FROM Ilaclar WHERE IlacID = ?";
            $stmtTur = $this->conn->prepare($sqlTur);
            $stmtTur->execute([$ilacID]);
            $ilacBilgi = $stmtTur->fetch(PDO::FETCH_ASSOC);

            // Eğer ilaç "Beyaz" değilse, reçete detayında olmak ZORUNDA
            if ($ilacBilgi['ReceteTuru'] != 'Beyaz') {
                
                $sqlDetay = "SELECT * FROM ReceteDetay WHERE ReceteID = ? AND IlacID = ?";
                $stmtDetay = $this->conn->prepare($sqlDetay);
                $stmtDetay->execute([$receteID, $ilacID]);
                
                if ($stmtDetay->rowCount() == 0) {
                    return [
                        "status" => false, 
                        "mesaj" => "HATA: Sepetinizdeki '{$ilacBilgi['IlacAdi']}' isimli ilaç, girdiğiniz reçetede ($receteKodu) yazmıyor!"
                    ];
                }
            }
        }

        return ["status" => true];
    }

    // --- 1. SİPARİŞ OLUŞTUR (DÜZELTİLDİ: Transaction Hatası Giderildi) ---
    public function siparisOlustur($hastaID, $sepet, $teslimatTuru, $receteNo = null) {
        try {
            // A. Önce Reçete Kontrolü (Transaction başlamadan önce)
            if (!empty($receteNo)) {
                $kontrol = $this->receteDogrula($receteNo, $hastaID, $sepet);
                
                if ($kontrol['status'] === false) {
                    // Hata varsa direkt Exception fırlat, veritabanına hiç dokunma
                    throw new Exception($kontrol['mesaj']);
                }
            }

            // B. Kontroller geçildiyse işlemi başlat
            $this->conn->beginTransaction();

            $sql = "INSERT INTO siparisler (HastaID, ToplamTutar, Durum, ReceteNo) VALUES (?, 0, 'Bekleniyor', ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$hastaID, $receteNo]);
            $siparisID = $this->conn->lastInsertId();

            $toplamTutar = 0;
            $ekstraUcret = ($teslimatTuru == 'kurye') ? 50 : 0;

            foreach ($sepet as $ilacID => $adet) {
                $sqlStok = "SELECT StokID, EczaneID, Fiyat FROM eczanestok WHERE IlacID = ? AND Adet >= ? ORDER BY Fiyat ASC LIMIT 1";
                $stmtStok = $this->conn->prepare($sqlStok);
                $stmtStok->execute([$ilacID, $adet]);
                $stokKaydi = $stmtStok->fetch(PDO::FETCH_ASSOC);

                if ($stokKaydi) {
                    $eczaneID = $stokKaydi['EczaneID'];
                    $birimFiyat = $stokKaydi['Fiyat'];
                    $stokID = $stokKaydi['StokID'];

                    $sqlGuncelle = "UPDATE eczanestok SET Adet = Adet - ? WHERE StokID = ?";
                    $stmtGuncelle = $this->conn->prepare($sqlGuncelle);
                    $stmtGuncelle->execute([$adet, $stokID]);

                    $sqlDetay = "INSERT INTO siparisdetay (SiparisID, IlacID, EczaneID, Adet, BirimFiyat) VALUES (?, ?, ?, ?, ?)";
                    $stmtDetay = $this->conn->prepare($sqlDetay);
                    $stmtDetay->execute([$siparisID, $ilacID, $eczaneID, $adet, $birimFiyat]);

                    $toplamTutar += ($birimFiyat * $adet);
                } else {
                    throw new Exception("Sepetinizdeki bazı ürünler için yeterli stok bulunamadı.");
                }
            }

            $sonTutar = $toplamTutar + $ekstraUcret;
            $sqlTutar = "UPDATE siparisler SET ToplamTutar = ? WHERE SiparisID = ?";
            $stmtTutar = $this->conn->prepare($sqlTutar);
            $stmtTutar->execute([$sonTutar, $siparisID]);

            $this->conn->commit();
            return ['status' => true, 'siparis_id' => $siparisID];

        } catch (Exception $e) {
            // DÜZELTME BURADA: Sadece işlem başladıysa geri al
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['status' => false, 'mesaj' => $e->getMessage()];
        }
    }

    // --- DİĞER FONKSİYONLAR (DEĞİŞMEDİ) ---
    public function hastaSiparisleriniGetir($hastaID) {
        $sql = "SELECT * FROM siparisler WHERE HastaID = ? ORDER BY SiparisTarihi DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hastaID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function siparisDetaylariniGetir($siparisID) {
        $sql = "SELECT d.*, i.IlacAdi, e.EczaneAdi 
                FROM siparisdetay d
                JOIN ilaclar i ON d.IlacID = i.IlacID
                JOIN eczaneler e ON d.EczaneID = e.EczaneID
                WHERE d.SiparisID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$siparisID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function personelBilgisiGetir($personelID) {
        $query = "SELECT EczaneID, AdSoyad FROM personel WHERE PersonelID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$personelID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function durumGuncelle($siparisID, $yeniDurum) {
        $query = "UPDATE siparisler SET Durum = ? WHERE SiparisID = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$yeniDurum, $siparisID]);
    }

    public function eczaneSiparisleriniGetir($eczaneID) {
        $sql = "SELECT 
                    s.SiparisID, s.SiparisTarihi, s.Durum, s.ToplamTutar, s.ReceteNo,
                    h.AdSoyad AS HastaAdi, h.Telefon, h.Adres,
                    d.IlacID, d.Adet, d.BirimFiyat,
                    i.IlacAdi, i.ReceteTuru
                FROM siparisdetay d
                JOIN siparisler s ON d.SiparisID = s.SiparisID
                JOIN hastalar h ON s.HastaID = h.HastaID
                JOIN ilaclar i ON d.IlacID = i.IlacID
                WHERE d.EczaneID = ?
                ORDER BY s.SiparisTarihi DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$eczaneID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>