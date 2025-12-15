<?php
class Siparis {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- 1. YENİ SİPARİŞ OLUŞTUR ---
    public function siparisOlustur($hastaID, $sepet, $teslimatTuru) {
        try {
            $this->conn->beginTransaction();

            $sql = "INSERT INTO siparisler (HastaID, ToplamTutar, Durum) VALUES (?, 0, 'Bekleniyor')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$hastaID]);
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
                    throw new Exception("Sepetinizdeki bazı ürünler (ID: $ilacID) için yeterli stok bulunamadı.");
                }
            }

            $sonTutar = $toplamTutar + $ekstraUcret;
            $sqlTutar = "UPDATE siparisler SET ToplamTutar = ? WHERE SiparisID = ?";
            $stmtTutar = $this->conn->prepare($sqlTutar);
            $stmtTutar->execute([$sonTutar, $siparisID]);

            $this->conn->commit();
            return ['status' => true, 'siparis_id' => $siparisID];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['status' => false, 'mesaj' => $e->getMessage()];
        }
    }

    // --- 2. HASTA SİPARİŞLERİNİ GETİR (YENİ EKLENDİ) ---
    public function hastaSiparisleriniGetir($hastaID) {
        $sql = "SELECT * FROM siparisler WHERE HastaID = ? ORDER BY SiparisTarihi DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hastaID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- 3. SİPARİŞ DETAYLARINI GETİR (YENİ EKLENDİ) ---
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

    // --- 4. ECZANE İŞLEMLERİ (Panel İçin) ---
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
                    s.SiparisID, s.SiparisTarihi, s.Durum, s.ToplamTutar,
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