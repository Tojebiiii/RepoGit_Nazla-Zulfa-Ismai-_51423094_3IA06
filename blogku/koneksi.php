<?php
// Pengaturan Koneksi Database
$host = "localhost";
$user = "root";     // Default XAMPP
$pass = "";         // Default XAMPP (kosong)
$db_name = "diary_blog";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db_name);

// Mengecek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>