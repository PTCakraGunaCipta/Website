<?php
require 'config.php'; // Panggil file koneksi database

header("Content-Type: application/json"); // Set response ke JSON
header("Access-Control-Allow-Origin: *"); // Izinkan akses dari mana saja
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Fungsi untuk membersihkan input


// Ambil metode HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Ambil semua berita
        $sql = "SELECT * FROM tb_cakra_gn";
        $result = $conn->query($sql);
        $news = [];

        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }

        echo json_encode(["success" => true, "data" => $news]);
        break;

        case 'POST':
          // Ambil data input
          $judul = sanitizeInput($_POST['judul']);
          $keterangan = sanitizeInput($_POST['keterangan']);
      
          // Inisialisasi variabel foto sebagai NULL
          $file_name = NULL;
      
          // Proses upload foto (jika ada)
          if (!empty($_FILES['foto']['name'])) {
              $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
              $file_name = time() . "." . $ext;
              $file_path = "asset/data-foto/" . $file_name;
              move_uploaded_file($_FILES['foto']['tmp_name'], $file_path);
          }
      
          // Query INSERT dengan handling NULL untuk foto
          $sql = "INSERT INTO tb_cakra_gn (foto, judul, keterangan, created_at) 
                  VALUES (" . ($file_name ? "'$file_name'" : "NULL") . ", '$judul', '$keterangan', NOW())";
      
          if ($conn->query($sql)) {
              echo json_encode(["success" => true, "message" => "Berita berhasil ditambahkan"]);
          } else {
              echo json_encode(["success" => false, "message" => "Gagal menambahkan berita"]);
          }
          break;
      

    case 'PUT':
        // Baca input dari request body
        $input = json_decode(file_get_contents("php://input"), true);
    
        // Validasi apakah data yang dibutuhkan tersedia
        if (!isset($input['id']) || !isset($input['judul']) || !isset($input['keterangan'])) {
            echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
            exit;
        }
    
        // Ambil data dari request
        $id = sanitizeInput($input['id']);
        $judul = sanitizeInput($input['judul']);
        $keterangan = sanitizeInput($input['keterangan']);
    
        // Proses update data berita
        $sql = "UPDATE tb_cakra_gn SET judul = '$judul', keterangan = '$keterangan', updated_at = NOW() WHERE id = $id";
    
        if ($conn->query($sql)) {
            echo json_encode(["success" => true, "message" => "Berita berhasil diperbarui"]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal memperbarui berita"]);
        }
        break;
      

        case 'DELETE':
          // Ambil data JSON dari request body
          $data = json_decode(file_get_contents("php://input"), true);
      
          // Pastikan 'id' ada di data yang dikirim
          if (!isset($data['id']) || empty($data['id'])) {
              echo json_encode(["success" => false, "message" => "ID tidak ditemukan"]);
              exit;
          }
      
          $id = sanitizeInput($data['id']);
      
          // Hapus data berdasarkan ID
          $sql = "DELETE FROM tb_cakra_gn WHERE id = '$id'";
      
          if ($conn->query($sql)) {
              echo json_encode(["success" => true, "message" => "Berita berhasil dihapus"]);
          } else {
              echo json_encode(["success" => false, "message" => "Gagal menghapus berita"]);
          }
          break;
      
}

$conn->close();
?>
