<?php
session_start();

// Informasi koneksi database
$host = "localhost"; // Ganti dengan host database Anda
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$database = "db_perumahan"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Ambil total unit dari tb_unit
$sql_total_unit = "SELECT COUNT(*) AS total FROM tb_unit";
$result_total_unit = $conn->query($sql_total_unit);
$row_total_unit = $result_total_unit->fetch_assoc();
$total_rumah_unit = $row_total_unit['total'];

// Ambil jumlah rumah terjual dari tb_pembelian (asumsi ada kolom 'status_pembelian')
$sql_terjual = "SELECT COUNT(*) AS terjual FROM tb_pembelian WHERE status_pembelian = 'terjual'";
$result_terjual = $conn->query($sql_terjual);
$row_terjual = $result_terjual->fetch_assoc();
$rumah_terjual = $row_terjual['terjual'];

// Ambil jumlah rumah terbooking dari tb_pembelian (asumsi ada kolom 'status_pembelian')
$sql_terbooking = "SELECT COUNT(*) AS terbooking FROM tb_pembelian WHERE status_pembelian = 'terbooking'";
$result_terbooking = $conn->query($sql_terbooking);
$row_terbooking = $result_terbooking->fetch_assoc();
$rumah_terbooking = $row_terbooking['terbooking'];

// Ambil semua data status properti dari tb_pembelian
$sql_status_properti = "SELECT type_rumah, blok_rumah, status_pembelian, tanggal_pembelian
                        FROM tb_pembelian
                        ORDER BY tanggal_pembelian DESC
                        LIMIT 5";
$result_status_properti = $conn->query($sql_status_properti);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pimpinan Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8; /* Latar belakang abu-abu muda */
            display: flex;
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            flex: 1;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: #fff; /* Latar belakang putih seperti navbar */
            color: #117c6b; /* Warna teks hijau utama */
            width: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Efek shadow seperti navbar */
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center; /* Agar logo di tengah */
            margin-bottom: 30px;
        }

        .sidebar-header .logo {
            height: 80px; /* Ukuran logo sama seperti di home */
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav ul li a {
            display: flex;
            align-items: center;
            color: #117c6b; /* Warna teks link hijau */
            text-decoration: none;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .sidebar-nav ul li a:hover {
            background-color: #e0f2f1; /* Warna hover hijau muda */
        }

        .sidebar-nav ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav ul li.logout {
            margin-top: auto;
        }

        .sidebar-nav ul li.logout a {
            background-color: transparent; /* Menghilangkan latar belakang hijau */
            color: #117c6b; /* Warna teks tetap hijau */
        }

        .sidebar-nav ul li.logout a:hover {
            background-color: #e0f2f1; /* Warna hover tetap hijau muda */
        }

        .sidebar-nav ul li a.active {
            background-color: #117c6b;
            color: white;
            font-weight: 600;
        }

        /* Submenu Styles */
        .sidebar-nav ul li.has-submenu {
            position: relative;
        }

        .sidebar-nav ul li.has-submenu .arrow {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }

        .sidebar-nav ul li.has-submenu.active .arrow {
            transform: translateY(-50%) rotate(180deg); /* Putar panah saat aktif */
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: #f9f9f9; /* Warna latar belakang sub-menu */
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: none; /* Sembunyikan secara default */
            padding-left: 20px; /* Beri indentasi */
            margin-top: 5px;
        }

        .sidebar-nav ul li.has-submenu.active .submenu {
            display: block; /* Tampilkan sub-menu saat menu utama aktif */
        }

        .submenu li a {
            display: block;
            color: #333;
            text-decoration: none;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 400;
            transition: background-color 0.3s ease;
        }

        .submenu li a:hover {
            background-color: #e0f2f1;
        }

        .submenu li a.sub-active {
            background-color: #117c6b;
            color: white;
            font-weight: 600;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            background-color: #f4f6f8; /* Latar belakang abu-abu muda */
            display: flex;
            flex-direction: column;
        }

        .main-header {
            background-color: #fff; /* Latar belakang putih seperti navbar */
            color: #117c6b; /* Warna teks hijau utama */
            padding: 20px;
            border-bottom: 2px solid #e0f2f1; /* Border bawah hijau muda */
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Efek shadow seperti navbar */
        }

        .main-header h2 {
            margin: 0;
            font-size: 1.8em;
            font-family: 'Montserrat', sans-serif; /* Font Montserrat */
            font-weight: 600;
        }

        .admin-info {
            display: flex;
            align-items: center;
        }

        .admin-info i {
            margin-right: 10px;
            font-size: 1.2em;
            color: #117c6b; /* Warna ikon hijau */
        }

        .admin-info span {
            font-weight: 500;
            color: #333; /* Warna teks abu-abu gelap */
            font-family: 'Poppins', sans-serif; /* Font Poppins */
        }

        .content-area {
            padding: 20px;
            flex: 1;
            font-family: 'Poppins', sans-serif; /* Font Poppins untuk konten */
            color: #333;
            display: flex;
            flex-direction: column;
            gap: 20px; /* Jarak antar bagian */
        }

        /* Dashboard Overview */
        .dashboard-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .overview-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #333;
        }

        .overview-card i {
            font-size: 2em;
            margin-bottom: 10px;
            color: #117c6b; /* Warna ikon hijau */
        }

        .overview-card h3 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 1.2em;
            font-weight: 600;
        }

        .overview-card .value {
            font-size: 1.5em;
            font-weight: bold;
            color: #117c6b;
        }

        /* Recent Activity */
        .recent-activity {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .recent-activity h2 {
            font-size: 1.5em;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }

        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f4f6f8;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activity-list li:last-child {
            border-bottom: none;
        }

        .activity-icon {
            color: #117c6b;
            font-size: 1em;
            width: 20px;
            text-align: center;
        }

        .activity-time {
            font-size: 0.8em;
            color: #777;
            margin-left: auto;
        }

        .activity-list li a {
            color: #117c6b;
            text-decoration: none;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h2 {
            font-size: 1.5em;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap; /* Agar tombol wrap jika layar kecil */
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background-color: #117c6b;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .action-button:hover {
            background-color: #0e6655;
        }

        .action-button i {
            font-size: 1em;
        }
        .property-status {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .property-status h2 {
            font-size: 1.5em;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
     <div id="notification-container" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px 20px; border-radius: 5px; z-index: 1000; display: none;">
        <span id="notification-message"></span>
    </div>
    <div class="admin-container">
    <aside class="sidebar">
            <div class="sidebar-header" style="justify-content: center;">
                <img src="gambar/Logo_Green.png" alt="Logo Green" class="logo">
            </div>
           <nav class="sidebar-nav">
                <ul>
                     <li>
                        <a href="Dashboard_pimpinan.php" class="active">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan_pembelian.php">
                            <i class="fas fa-file-invoice"></i>
                            <span>Laporan Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan_pembayaran.php">
                            <i class="fas fa-money-bill-alt"></i>
                            <span>Laporan Pembayaran</span>
                        </a>
                    </li>
                    <li class="logout">
                        <a href="logout_pimpinan.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <h2>Dashboard</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Pimpinan</span>
                </div>
            </header>
            <div class="content-area">
                <div class="dashboard-overview">
                    <div class="overview-card">
                    <i class="fas fa-home"></i>
                        <h3>Total Rumah</h3>
                        <span class="value"><?php echo $total_rumah_unit; ?></span>
                    </div>
                    <div class="overview-card">
                        <i class="fas fa-check-circle"></i>
                        <h3>Rumah Terjual</h3>
                        <span class="value"><?php echo $rumah_terjual; ?></span>
                    </div>
                    <div class="overview-card">
                        <i class="fas fa-calendar-check"></i>
                        <h3>Rumah Terbooking</h3>
                        <span class="value"><?php echo $rumah_terbooking; ?></span>
                    </div>
                </div>
                <div class="property-status">
    <h2>Status Properti Terbaru</h2>
    <ul class="activity-list">
        <?php
        if ($result_status_properti->num_rows > 0) {
            while ($row = $result_status_properti->fetch_assoc()) {
                $status = strtolower($row['status_pembelian']);
                $status_color = '';
                if ($status == 'terjual') {
                    $status_color = 'red';
                } elseif ($status == 'terbooking') {
                    $status_color = 'green';
                } else {
                    $status_color = 'orange'; // Default jika status lain atau tersedia
                }
                echo '<li>';
                echo '<span class="activity-icon"><i class="fas fa-circle" style="color: ' . $status_color . ';"></i></span>';
                echo 'Rumah Tipe ' . htmlspecialchars($row['type_rumah']) . ' - Blok ' . htmlspecialchars($row['blok_rumah']) . ': <span style="font-weight: bold; color: ' . $status_color . ';">' . ucfirst($status) . '</span>';
                echo '<span class="activity-time">' . date('d/m/Y', strtotime($row['tanggal_pembelian'])) . '</span>';
                echo '</li>';
            }
        } else {
            echo '<li>Tidak ada status properti.</li>';
        }
        ?>
    </ul>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationContainer = document.getElementById('notification-container');
        const notificationMessage = document.getElementById('notification-message');

        // Ambil data notifikasi dari session (jika ada)
        const notificationData = <?php echo isset($_SESSION['notification']) ? json_encode($_SESSION['notification']) : 'null'; ?>;

        if (notificationData && notificationData.type === 'success') {
            notificationMessage.textContent = notificationData.message;
            notificationContainer.style.display = 'block';

            // Sembunyikan notifikasi setelah beberapa detik (misalnya 3 detik)
            setTimeout(function() {
                notificationContainer.style.display = 'none';
            }, 1000);

            // Hapus session notifikasi agar tidak muncul lagi
            <?php unset($_SESSION['notification']); ?>
        }
    });
</script>
</body>
</html>
<?php
$conn->close();
?>