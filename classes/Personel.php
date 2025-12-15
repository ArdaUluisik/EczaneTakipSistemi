<?php
class Personel {
    private $conn;
    private $table_name = "personel";

    // Tablo sütunlarına karşılık gelen özellikler
    public $id;          // PersonelID
    public $eczane_id;   // EczaneID
    public $tc;          // TCNo
    public $sifre;       // Sifre
    public $ad_soyad;    // AdSoyad
    public $rol;         // Rol (Eczaci, Admin vb.)
    
    // Veritabanından JOIN ile gelecek ek özellik
    public $eczane_adi;

    // 1. Bağlantıyı al
    public function __construct($db) {
        $this->conn = $db;
    }

    // 2. Giriş Yap Fonksiyonu
    public function girisYap() {
        // SQL Sorgusu: 
        // Personel tablosunu (p) Eczaneler tablosu (e) ile birleştiriyoruz.
        // Böylece personelin adını alırken, eczanesinin adını da öğreniyoruz.
        $query = "SELECT p.*, e.EczaneAdi 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN eczaneler e ON p.EczaneID = e.EczaneID 
                  WHERE p.TCNo = :tc AND p.Sifre = :sifre 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        // Güvenlik temizliği (HTML taglerini ve boşlukları temizle)
        $this->tc = htmlspecialchars(strip_tags($this->tc));
        $this->sifre = htmlspecialchars(strip_tags($this->sifre));

        // Parametreleri bağla (:tc ve :sifre yerine gerçek değerleri koy)
        $stmt->bindParam(':tc', $this->tc);
        $stmt->bindParam(':sifre', $this->sifre);

        // Sorguyu çalıştır
        $stmt->execute();

        // Eğer veritabanında böyle biri varsa
        if ($stmt->rowCount() > 0) {
            // Veriyi satır olarak çek
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Sınıfın özelliklerini doldur (Veritabanı sütun isimlerine DİKKAT ET)
            $this->id = $row['PersonelID'];
            $this->eczane_id = $row['EczaneID'];
            $this->tc = $row['TCNo'];
            $this->ad_soyad = $row['AdSoyad'];
            $this->rol = $row['Rol']; // Rol bilgisini de aldık
            
            // Eğer personel bir eczaneye bağlıysa adını al, yoksa (örn: sistem yöneticisi) boş geç
            $this->eczane_adi = isset($row['EczaneAdi']) ? $row['EczaneAdi'] : "Merkez Yönetim";
            
            return true; // Giriş başarılı
        }
        
        return false; // Giriş başarısız
    }
}
?>