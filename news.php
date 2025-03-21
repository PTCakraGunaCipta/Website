<?php
include 'config.php';

$query = "SELECT foto, judul, keterangan FROM tb_cakra_gn ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!-- HTML -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PT Cakra Guna Cipta - Berita</title>
  <link rel="icon" href="./asset/favicon/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="./css/news.css">
</head>
<body>
  <nav id="navbar">
    <div class="nav-wrapper">
      <div class="logo">
        <a href="./news.php" onclick="window.location.reload()" return false>
          <img src="./asset/logo/logo.webp" alt="Company Logo" class="logo-img"/>
        </a>
      </div>
      <div class="nav-container">
          <a href="./index.php" onclick="window.location.reload()" return false class="nav-link">Kembali</a>
      </div>
      <div class="menu-bar">
          ☰
      </div>
    </div>
  </nav>

  <div class="nav-container2">
    <a href="./index.php" onclick="window.location.reload()" return false class="nav-link">Kembali</a>
  </div>

    <section class="news-all" id="news-all">
      <h1 class="title">BERITA & KEGIATAN</h1>
      <div class="news-container" id="newsContainer">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="news-card">
            <div class="news-image">
                <img src="./asset/data-foto/<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
            </div>
            <div class="news-content">
                <h2><?php echo htmlspecialchars($row['judul']); ?></h2>
                <hr class="underline">
                <p><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </section>
  
  <footer>
    <p>&copy; 2025 PT Cakra Guna Cipta. Semua Hak Dilindungi.</p>
  </footer>
</body>
</html>

