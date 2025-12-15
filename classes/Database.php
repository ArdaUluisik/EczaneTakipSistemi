<?php
class Database {
    // SENİN ESKİ db.php AYARLARIN
    private $host = "localhost";
    private $db_name = "EczaneDB";  // Senin veritabanı adın
    private $username = "root";
    private $password = "5270";     // Senin şifren
    public $conn;

    // Singleton örneği
    private static $instance = null;

    // Bağlantıyı başlatan kurucu metod
    private function __construct() {
        try {
            // PDO bağlantı cümlesi
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            
            // Hata modunu aç
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Varsayılan fetch modunu ayarla (Senin eski dosyan gibi)
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            die("Veritabanı bağlantı hatası: " . $exception->getMessage());
        }
    }

    // Singleton: Tek bir bağlantı oluşturup onu döndürür
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Bağlantı nesnesini dışarıya verir
    public function getConnection() {
        return $this->conn;
    }
}
?>