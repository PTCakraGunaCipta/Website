<?php
include 'config.php';

// Inisialisasi variabel notifikasi
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $imageName = '';

    // Cek apakah ada file yang diunggah
    if (!empty($_FILES['imageFile']['name'])) {
        $imageName = time() . '-' . $_FILES['imageFile']['name'];
        $imagePath = './asset/data-foto/' . $imageName;
        
        // Pindahkan file jika berhasil diupload
        if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $imagePath)) {
            $successMessage = "Berita berhasil ditambahkan!";
        } else {
            $errorMessage = "Gagal mengunggah foto.";
        }
    }

    // Insert data ke database
    $stmt = $conn->prepare("INSERT INTO tb_cakra_gn (foto, judul, keterangan, created_at, updated_at) VALUES (?, ?, ?, NOW(), NULL)");
    $stmt->bind_param("sss", $imageName, $title, $content);
    
    if ($stmt->execute()) {
        header("Location: ".$_SERVER['PHP_SELF']."?success=1"); // Redirect untuk mencegah resubmit
        exit();
    } else {
        $errorMessage = "Gagal menambahkan berita.";
    }

    $stmt->close();
    $conn->close();
}
// Handle Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $imageName = $_POST['existingImage'];

    if (!empty($_FILES['imageFile']['name'])) {
        $imageName = time() . '-' . $_FILES['imageFile']['name'];
        $imagePath = './asset/data-foto/' . $imageName;
        
        if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $imagePath)) {
            // Hapus gambar lama jika ada
            if (!empty($_POST['existingImage'])) {
                unlink('./asset/data-foto/' . $_POST['existingImage']);
            }
        } else {
            $errorMessage = "Gagal mengunggah foto.";
        }
    }

    $stmt = $conn->prepare("UPDATE tb_cakra_gn SET foto = ?, judul = ?, keterangan = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssi", $imageName, $title, $content, $id);
    
    if ($stmt->execute()) {
        header("Location: ".$_SERVER['PHP_SELF']."?success=1");
        exit();
    } else {
        $errorMessage = "Gagal mengupdate berita.";
    }

    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tb_cakra_gn WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: ".$_SERVER['PHP_SELF']."?success=1");
        exit();
    } else {
        $errorMessage = "Gagal menghapus berita.";
    }

    $stmt->close();
}

// Get Data ke database
$query = "SELECT id, foto, judul, keterangan FROM tb_cakra_gn ORDER BY created_at DESC";
$result = $conn->query($query);
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
            padding: 50px 300px 50px 300px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            max-width: 1200px;
            max-height: 1000px;
            height: 100%;
            width: 100%;
            margin: 100px auto;
        }
        input{
            max-width: 600px;
            max-height: 50px;
            width: 100%;
            height: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        textarea {
            max-width: 600px;
            width: 100%;
            min-height: 100px; /* Atur tinggi minimal */
            height: auto;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical; /* Memungkinkan resize hanya ke bawah */
            overflow-y: auto; /* Tambahkan scroll bila konten terlalu panjang */
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

        /* News */
        #news5 {
            background-color: #fff;
            padding: 40px;
            text-align: center;
            overflow: hidden;
            position: relative;
        }
        .news5-title {
            color: var(--four-color);
            font-weight: bold;
            margin-bottom: 20px;
        }
        .news5-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
        }
        .news5-item {
            width: 100%;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }
        .news5-content {
            display: flex;
            /* flex-direction: column; */
            background-color: #b8c8b8;
            padding: 40px;
            width: 100%;
            max-width: 1000px;
            align-items: center;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .news5-actions {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .news5-actions i {
            cursor: pointer;
            margin-left: 10px;
        }
        .image5-placeholder img {
            max-width: 100%;
            height: auto;
            border-radius: 10%;
        }
        .hero5-image {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        .image5-placeholder {
            background: gray;
            width: 300px;
            height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 16px;
            border-radius: 10%;
        }
        .image5-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10%;
        }
        .news5-text {
            flex: 2;
            text-align: left;
            font-size: 18px;
            color: #333;
            line-height: 1.8;
            padding-left: 20px;
            max-width: 100%;
            white-space: normal;
        }
        .news5-text h5 {
            font-size: 25px;
        }
        .news5-text p {
            font-size: 20px;
        }
        .float-window.update {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            display: none;
            z-index: 1000;
        }

        @media (max-width: 520px) {
            body{
                margin: 20px auto;
            }
            .admin-panel {
                padding: 50px 30px 50px 30px;
                max-width: 450px;
                max-height: 1000px;
                height: 100%;
                width: 90%;
                margin: 80px auto;
            }
            .admin-panel h1{
                font-size: 20px;
            }

            #news5{
                padding: 20px;
            }
            .news5-title {
                font-size: 20px;
            }
            .news5-content {
                flex-direction: column;
            }
            .image5-placeholder {
                width: 150px;
                height: 150px;
                margin-bottom: 20px;
            }
            .news5-text h5{
                font-size: 15px;
            }
            .news5-text p{
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <nav id="navbar">
        <div class="nav-wrapper">
            <div class="logo">
                <a href="./admin.php" onclick="window.location.reload()" return false>
                    <img src="./asset/logo/logo.webp" alt="Company Logo" class="logo-img"/>
                </a>
            </div>
            <div class="nav-container">
                <a href="./index.php" onclick="window.location.reload()" return false class="nav-link">Kembali</a>
                <a href="./data.php" onclick="window.location.reload()" return false class="nav-link">Gambar Product</a>
            </div>
            <div class="menu-bar" id="menu-bar">
                ☰
            </div>
        </div>
    </nav>

    <div class="nav-container2">
        <a href="./index.php" onclick="window.location.reload()" return false class="nav-link">Kembali</a>
        <a href="./data.php" onclick="window.location.reload()" return false class="nav-link">Gambar Product</a>
    </div>

    <div class="admin-panel">
        <h1>Input - Tambah Berita</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Judul Berita" required><br>
            <textarea name="content" placeholder="Isi Berita" required></textarea><br>
            <input type="file" name="imageFile" accept="image/*"><br>
            <button type="submit" name="submit">Tambahkan Berita</button>
        </form>
    </div>

    <!-- Float Window Notifikasi -->
    <div id="floatWindow" class="float-window">
        <span class="close-btn" onclick="hideFloatWindow()">✖</span>
        <p id="floatMessage"></p>
    </div>

    <section id="news5">
        <h2 class="news5-title">BERITA & KEGIATAN</h2>
        <div class="news5-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="news5-item">
                    <div class="news5-content">
                        <div class="news5-actions">
                            <i class="fas fa-edit" onclick="showUpdateWindow(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['judul']); ?>', '<?php echo htmlspecialchars($row['keterangan']); ?>', '<?php echo htmlspecialchars($row['foto']); ?>')"></i>
                            <i class="fas fa-trash" onclick="confirmDelete(<?php echo $row['id']; ?>)"></i>
                        </div>
                        <div class="hero5-image">
                            <div class="image5-placeholder">
                                <img src="./asset/data-foto/<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                            </div>
                        </div>
                        <div class="news5-text">
                            <h5><?php echo htmlspecialchars($row['judul']); ?></h5>
                            <p><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <!-- Float Window untuk Update -->
    <div id="updateWindow" class="float-window update">
        <span class="close-btn" onclick="hideUpdateWindow()">✖</span>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="updateId">
            <input type="hidden" name="existingImage" id="existingImage">
            <input type="text" name="title" id="updateTitle" placeholder="Judul Berita" required><br>
            <textarea name="content" id="updateContent" placeholder="Isi Berita" required></textarea><br>
            <input type="file" name="imageFile" accept="image/*"><br>
            <button type="submit" name="update">Update Berita</button>
        </form>
    </div>



    <script>
        document.addEventListener("input", function (event) {
            if (event.target.tagName.toLowerCase() === "textarea") {
                event.target.style.height = "auto";
                event.target.style.height = event.target.scrollHeight + "px";
            }
        });
        // Cegah resubmit saat refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Tampilkan notifikasi jika ada parameter success di URL
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

        // Cek apakah ada success message dari URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            showFloatWindow("Berita berhasil ditambahkan!", "success");
        }

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

        function showUpdateWindow(id, title, content, image) {
            document.getElementById('updateId').value = id;
            document.getElementById('updateTitle').value = title;
            document.getElementById('updateContent').value = content;
            document.getElementById('existingImage').value = image;
            document.getElementById('updateWindow').style.display = "block";
        }

        function hideUpdateWindow() {
            document.getElementById('updateWindow').style.display = "none";
        }

        function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus berita ini?")) {
                window.location.href = "?delete=" + id;
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
