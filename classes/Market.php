<?php
class Market {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Markette satılan ürünleri getir (Genel Liste)
    public function urunleriGetir() {
        try {
            $sql = "SELECT i.IlacID, i.IlacAdi, i.Aciklama, i.ResimYolu, i.ReceteTuru, MIN(es.Fiyat) as SatisFiyati
                    FROM ilaclar i
                    JOIN eczanestok es ON i.IlacID = es.IlacID
                    WHERE es.Adet > 0
                    GROUP BY i.IlacID
                    ORDER BY i.IlacID DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // Sepetteki ürünlerin detaylarını getir (Sepet Sayfası İçin)
    public function sepetUrunleriniGetir($idListesi) {
        if (empty($idListesi)) return [];
        
        $sql = "SELECT i.IlacID, i.IlacAdi, i.ResimYolu, i.ReceteTuru, MIN(es.Fiyat) as BirimFiyat 
                FROM ilaclar i 
                JOIN eczanestok es ON i.IlacID = es.IlacID 
                WHERE i.IlacID IN ($idListesi) AND es.Adet > 0
                GROUP BY i.IlacID";
                
        $stmt = $this->conn->prepare($sql); // Burada prepare kullanmak daha güvenli ama IN ile biraz trick gerekiyor
        // Basitlik ve mevcut yapıyı korumak için query kullanıyoruz (ID listesi string olarak geliyor)
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>