<?php
class Stok {
    private $conn;
    private $table_name = "eczanestok";

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- 1. YENİ: SADECE STOK EKLEME (İlaç Ekleme Yok!) ---
    // Eczacı listeden seçtiği ilacın ID'sini gönderir.
    public function stokEkle($eczaneID, $ilacID, $adet, $fiyat) {
        try {
            // A. Bu ilaç zaten bu eczanenin stoğunda var mı?
            $checkStok = $this->conn->prepare("SELECT StokID FROM " . $this->table_name . " WHERE EczaneID = ? AND IlacID = ?");
            $checkStok->execute([$eczaneID, $ilacID]);

            if ($checkStok->rowCount() > 0) {
                // İlaç zaten ekli, tekrar eklenemez (Güncelleme yapılmalı)
                return "var"; 
            }

            // B. Yoksa Eczane Stoğuna Ekle
            $query = "INSERT INTO " . $this->table_name . " (EczaneID, IlacID, Adet, Fiyat) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            if($stmt->execute([$eczaneID, $ilacID, $adet, $fiyat])) {
                return "basarili";
            } else {
                return "basarisiz";
            }

        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    // --- 2. YENİ: GLOBAL İLAÇ LİSTESİNİ GETİR (Dropdown İçin) ---
    public function tumIlaclariGetir() {
        // İlaçları alfabetik sıraya göre getiriyoruz
        $query = "SELECT * FROM ilaclar ORDER BY IlacAdi ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- 3. MEVCUT LİSTELEME ---
    public function listele($eczaneID) {
        $query = "SELECT es.*, i.IlacAdi, i.ResimYolu, i.Barkod, i.ReceteTuru
                  FROM " . $this->table_name . " es 
                  JOIN ilaclar i ON es.IlacID = i.IlacID 
                  WHERE es.EczaneID = ? 
                  ORDER BY es.StokID DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$eczaneID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- 4. SİLME ---
    public function sil($stokID, $eczaneID) {
        $query = "DELETE FROM " . $this->table_name . " WHERE StokID = ? AND EczaneID = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$stokID, $eczaneID]);
    }

    // --- 5. GÜNCELLEME ---
    public function guncelle($stokID, $eczaneID, $adet, $fiyat) {
        $query = "UPDATE " . $this->table_name . " SET Adet = ?, Fiyat = ? WHERE StokID = ? AND EczaneID = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$adet, $fiyat, $stokID, $eczaneID]);
    }

    // --- 6. TEKİL DETAY ---
    public function stokDetayGetir($stokID, $eczaneID) {
        $query = "SELECT es.*, i.IlacAdi 
                  FROM " . $this->table_name . " es 
                  JOIN ilaclar i ON es.IlacID = i.IlacID 
                  WHERE es.StokID = ? AND es.EczaneID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$stokID, $eczaneID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>