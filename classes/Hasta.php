<?php
class Hasta {
    private $conn;
    private $table_name = "hastalar";

    public $id;
    public $tc;
    public $sifre;
    public $ad_soyad;
    public $telefon;
    public $adres;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. KAYIT OLMA
    public function kayitOl($tc, $adsoyad, $telefon, $adres, $sifre) {
        try {
            $sql = "CALL sp_HastaKayit(:tc, :ad, :tel, :adr, :sif)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tc', $tc);
            $stmt->bindParam(':ad', $adsoyad);
            $stmt->bindParam(':tel', $telefon);
            $stmt->bindParam(':adr', $adres);
            $stmt->bindParam(':sif', $sifre);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['Sonuc' => 0, 'Mesaj' => 'Sistem Hatası: ' . $e->getMessage()];
        }
    }

    // 2. GİRİŞ YAPMA
    public function girisYap() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE TCNo = :tc AND Sifre = :sifre LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        
        $this->tc = htmlspecialchars(strip_tags($this->tc));
        $this->sifre = htmlspecialchars(strip_tags($this->sifre));

        $stmt->bindParam(':tc', $this->tc);
        $stmt->bindParam(':sifre', $this->sifre);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['HastaID'];
            $this->ad_soyad = $row['AdSoyad'];
            $this->telefon = $row['Telefon'];
            $this->adres = $row['Adres'];
            return true;
        }
        return false;
    }

    // 3. BİLGİLERİ GETİR
    public function bilgileriGetir($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE HastaID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. BİLGİLERİ GÜNCELLE
    public function bilgileriGuncelle($id, $telefon, $adres) {
        $query = "UPDATE " . $this->table_name . " SET Telefon = ?, Adres = ? WHERE HastaID = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$telefon, $adres, $id]);
    }

    // 5. ŞİFRE DEĞİŞTİR (YENİ EKLENDİ)
    public function sifreDegistir($id, $eskiSifre, $yeniSifre) {
        // A. Eski şifreyi kontrol et
        $query = "SELECT Sifre FROM " . $this->table_name . " WHERE HastaID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $kayit = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($kayit && $kayit['Sifre'] === $eskiSifre) {
            // B. Şifreyi Güncelle
            $update = "UPDATE " . $this->table_name . " SET Sifre = ? WHERE HastaID = ?";
            $stmtUpdate = $this->conn->prepare($update);
            return $stmtUpdate->execute([$yeniSifre, $id]);
        }
        
        return false; // Eski şifre yanlış
    }
}
?>