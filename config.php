<?php
// Konfigurasi Database
$host = "localhost";  // Ganti jika server database bukan lokal
$user = "root";       // Sesuaikan dengan username database Anda
$pass = "123456";           // Jika ada password database, isi di sini
$dbname = "db_cakra"; // Nama database

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter ke UTF-8
$conn->set_charset("utf8");

// Fungsi untuk membersihkan input (mencegah SQL Injection)
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

?>
