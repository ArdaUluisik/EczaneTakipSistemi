<?php
class Arama {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. İlleri Getir
    public function illeriGetir() {
        $query = "SELECT * FROM iller ORDER BY IlAdi ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. İlçeleri Getir
    public function ilceleriGetir($ilID) {
        $query = "SELECT * FROM ilceler WHERE IlID = ? ORDER BY IlceAdi ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ilID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. İlaç Arama (Stok Sorgusu)
    public function ilacAra($ilceID, $ilacAdi) {
        $query = "SELECT 
                    e.EczaneAdi, e.Adres, e.Telefon, 
                    es.Adet, es.Fiyat, 
                    i.IlacID, i.ReceteTuru, i.IlacAdi
                  FROM eczanestok es
                  JOIN eczaneler e ON es.EczaneID = e.EczaneID
                  JOIN ilaclar i ON es.IlacID = i.IlacID
                  WHERE e.IlceID = :ilce 
                    AND i.IlacAdi LIKE :ilac 
                    AND es.Adet > 0
                  ORDER BY es.Fiyat ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            'ilce' => $ilceID,
            'ilac' => '%' . $ilacAdi . '%'
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- YENİ EKLENEN FONKSİYON: NÖBETÇİ ECZANELER ---
    public function nobetciEczaneleriGetir($sehirID, $ilceID) {
        $bugun = date('Y-m-d');
        
        $sql = "SELECT 
                    e.EczaneID, e.EczaneAdi, e.Adres, e.Telefon, e.Enlem, e.Boylam,
                    i.IlAdi, ilc.IlceAdi, 
                    nc.Aciklama as NobetNotu
                FROM NobetCizelgesi nc
                JOIN Eczaneler e ON nc.EczaneID = e.EczaneID
                JOIN Ilceler ilc ON e.IlceID = ilc.IlceID  
                JOIN Iller i ON ilc.IlID = i.IlID          
                WHERE nc.NobetTarihi = :bugun";

        // Dinamik filtreleme (Eğer şehir veya ilçe seçildiyse SQL'e ekle)
        $params = [':bugun' => $bugun];

        if (!empty($sehirID)) {
            $sql .= " AND i.IlID = :sehir";
            $params[':sehir'] = $sehirID;
        }
        if (!empty($ilceID)) {
            $sql .= " AND e.IlceID = :ilce";
            $params[':ilce'] = $ilceID;
        }

        $sql .= " ORDER BY e.EczaneAdi ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>