// ENTER tuşu desteği (Kullanıcı deneyimi için kalsın)
document.getElementById("searchInput").addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        stokSorgula();
    }
});

function stokSorgula() {
    const kelime = document.getElementById('searchInput').value.trim();

    if (kelime.length < 2) {
        alert("Lütfen geçerli bir ilaç ismi giriniz.");
        return;
    }

    // --- BURASI VERİTABANI ENTEGRASYONUNU BEKLİYOR ---
    // İleride buraya Backend API isteği (fetch veya axios) yazacağız.
    // Şimdilik sadece konsola not düşüyoruz.
    
    console.log("Aranan Kelime:", kelime);
    alert("Veritabanı bağlantısı henüz yapılmadığı için sorgulama aktif değildir.");
    
    // Eski sahte veri kodlarını sildik.
}