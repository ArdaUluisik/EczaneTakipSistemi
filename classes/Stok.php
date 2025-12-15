<?php
class Stok {
    private $conn;
    private $table_name = "eczanestok";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. İLAÇ VE STOK EKLEME (Transaction ile)
    public function yeniIlacVeStokEkle($eczaneID, $ilacAdi, $receteTuru, $adet, $fiyat) {
        try {
            $this->conn->beginTransaction();

            // A. İlaç Var mı Kontrol Et
            $check = $this->conn->prepare("SELECT IlacID FROM ilaclar WHERE IlacAdi = ?");
            $check->execute([$ilacAdi]);
            $ilac = $check->fetch(PDO::FETCH_ASSOC);

            if ($ilac) {
                $ilacID = $ilac['IlacID'];
            } else {
                // B. Yoksa Yeni İlaç Ekle
                $insIlac = $this->conn->prepare("INSERT INTO ilaclar (IlacAdi, ReceteTuru) VALUES (?, ?)");
                $insIlac->execute([$ilacAdi, $receteTuru]);
                $ilacID = $this->conn->lastInsertId();
            }

            // C. Eczane Stoğunda Var mı?
            $checkStok = $this->conn->prepare("SELECT StokID FROM eczanestok WHERE EczaneID = ? AND IlacID = ?");
            $checkStok->execute([$eczaneID, $ilacID]);

            if ($checkStok->rowCount() > 0) {
                $this->conn->rollBack();
                return "var"; 
            }

            // D. Stoğa Ekle
            $insStok = $this->conn->prepare("INSERT INTO eczanestok (EczaneID, IlacID, Adet, Fiyat) VALUES (?, ?, ?, ?)");
            $insStok->execute([$eczaneID, $ilacID, $adet, $fiyat]);

            $this->conn->commit();
            return "basarili";

        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    // 2. LİSTELEME
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

    // 3. SİLME
    public function sil($stokID, $eczaneID) {
        $query = "DELETE FROM " . $this->table_name . " WHERE StokID = ? AND EczaneID = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$stokID, $eczaneID]);
    }

    // 4. GÜNCELLEME
    public function guncelle($stokID, $eczaneID, $adet, $fiyat) {
        $query = "UPDATE " . $this->table_name . " SET Adet = ?, Fiyat = ? WHERE StokID = ? AND EczaneID = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$adet, $fiyat, $stokID, $eczaneID]);
    }

    // --- YENİ EKLENEN: TEKİL STOK DETAYI GETİR ---
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