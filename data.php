<?php
include 'config.php'; // Panggil file koneksi database

// Inisialisasi variabel notifikasi
$successMessage = "";
$errorMessage = "";

// Handle Upload Foto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Cek apakah ada file yang diunggah
    if (!empty($_FILES['imageFile']['name'])) {
        $fileExtension = pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION);

        // Validasi format file (hanya .webp)
        if (strtolower($fileExtension) !== 'webp') {
            $errorMessage = "Hanya file dengan format .webp yang diperbolehkan.";
        } else {
            $imageName = time() . '-' . $_FILES['imageFile']['name'];
            $imagePath = './asset/product/' . $imageName;
            
            // Pindahkan file jika berhasil diupload
            if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $imagePath)) {
                // Insert data ke database
                $stmt = $conn->prepare("INSERT INTO tb_image_product (foto_produk, created_at) VALUES (?, NOW())");
                $stmt->bind_param("s", $imageName);
                
                if ($stmt->execute()) {
                    $successMessage = "Foto produk berhasil ditambahkan!";
                } else {
                    $errorMessage = "Gagal menyimpan data ke database.";
                }

                $stmt->close();
            } else {
                $errorMessage = "Gagal mengunggah foto.";
            }
        }
    } else {
        $errorMessage = "Silakan pilih file foto.";
    }
}

// Handle Delete Foto
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Ambil nama file foto dari database
    $stmt = $conn->prepare("SELECT foto_produk FROM tb_image_product WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($foto_produk);
    $stmt->fetch();
    $stmt->close();

    // Hapus file dari folder
    if ($foto_produk && file_exists("./asset/product/{$foto_produk}")) {
        unlink("./asset/product/{$foto_produk}");
    }

    // Hapus data dari database
    $stmt = $conn->prepare("DELETE FROM tb_image_product WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $successMessage = "Foto produk berhasil dihapus!";
    } else {
        $errorMessage = "Gagal menghapus foto produk.";
    }
    $stmt->close();
}

// Ambil data foto dari database
$query = "SELECT id, foto_produk FROM tb_image_product ORDER BY created_at DESC";
$result = $conn->query($query);
$photos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT Cakra Guna Cipta - Admin</title>
    <link rel="icon" href="/asset/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/design.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .admin-panel {
            align-items: center;
            text-align: center;
            background: #fff;
            padding: 50px 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            max-width: 1200px;
            width: 90%;
            margin: 100px auto;
        }
        input[type="file"] {
            margin: 20px 0;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
        .float-window {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            display: none;
            z-index: 1000;
        }
        .float-window.success {
            border-left: 5px solid green;
        }
        .float-window.error {
            border-left: 5px solid red;
        }
        .float-window .close-btn {
            float: right;
            cursor: pointer;
            font-weight: bold;
        }
        .photo-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        .photo-item {
            position: relative;
            width: 200px;
            height: 200px;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .photo-item:hover img {
            transform: scale(1.1);
        }
        .delete-icon {
            position: absolute;
            width: 35px;
            height: 35px;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            padding: 5px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .delete-icon:hover {
            background-color: rgba(255, 0, 0, 1);
        }
    </style>
</head>
<body>
  <nav id="navbar">
        <div class="nav-wrapper">
            <div class="logo">
                <a href="./data.php" onclick="window.location.reload()" return false>
                    <img src="./asset/logo/logo.webp" alt="Company Logo" class="logo-img"/>
                </a>
            </div>
            <div class="nav-container">
                <a href="./admin.php" onclick="window.location.reload()" return false class="nav-link">Kembali</a>
            </div>
            <div class="menu-bar" id="menu-bar">
                ☰
            </div>
        </div>
    </nav>

    <div class="nav-container2">
      <a href="./admin.php" onclick="window.location.reload()" return false class="nav-link">Kembali</a>
    </div>

    <div class="admin-panel">
        <h1>Input - Foto Product</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="imageFile" accept=".webp" required><br>
            <button type="submit" name="submit">Tambahkan Foto Product</button>
        </form>

        <!-- Tampilkan foto yang tersimpan -->
        <div class="photo-container">
            <?php if (!empty($photos)): ?>
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <img src="./asset/product/<?php echo htmlspecialchars($photo['foto_produk']); ?>" alt="Foto Produk">
                        <div class="delete-icon" onclick="confirmDelete(<?php echo $photo['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Tidak ada foto produk yang tersimpan.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Float Window Notifikasi -->
    <div id="floatWindow" class="float-window">
        <span class="close-btn" onclick="hideFloatWindow()">✖</span>
        <p id="floatMessage"></p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const menuBar = document.getElementById("menu-bar");
            const navContainer = document.querySelector('.nav-container2');

            menuBar.addEventListener('click', () => {
                if (navContainer.style.right === "0px") {
                    navContainer.style.right = "-250px";
                } else {
                    navContainer.style.right = "0px";
                }
            });

            // Tutup menu saat klik di luar sidebar
            document.addEventListener("click", function (event) {
                if (!menuBar.contains(event.target) && !navContainer.contains(event.target)) {
                    navContainer.style.right = "-250px";
                }
            });
        });
        // Tampilkan notifikasi
        function showFloatWindow(message, type) {
            var floatWindow = document.getElementById('floatWindow');
            var floatMessage = document.getElementById('floatMessage');
            floatMessage.innerText = message;
            floatWindow.className = "float-window " + type;
            floatWindow.style.display = "block";

            // Tutup otomatis setelah 3 detik
            setTimeout(function() {
                hideFloatWindow();
            }, 3000);
        }

        function hideFloatWindow() {
            document.getElementById('floatWindow').style.display = "none";
        }

        // Konfirmasi hapus foto
        function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus foto ini?")) {
                window.location.href = "?delete=" + id;
            }
        }

        // Cek apakah ada notifikasi dari PHP
        <?php if (!empty($successMessage)): ?>
            showFloatWindow("<?php echo $successMessage; ?>", "success");
        <?php elseif (!empty($errorMessage)): ?>
            showFloatWindow("<?php echo $errorMessage; ?>", "error");
        <?php endif; ?>
    </script>
</body>
</html>