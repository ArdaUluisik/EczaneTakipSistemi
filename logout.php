<?php
session_start(); // Oturumu başlat
session_destroy(); // Oturumu yok et (Çıkış yap)
header("Location: index.php"); // Ana sayfaya gönder
exit;
?>