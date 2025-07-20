<?php
session_start();

// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Inisialisasi variabel pesan hapus
$hapus_pesan = "";
if (isset($_GET['hapus'])) {
    if ($_GET['hapus'] == 'sukses') {
        $hapus_pesan = "<div style='color: green; margin-bottom: 10px;'>Data pembelian berhasil dihapus.</div>";
    } elseif ($_GET['hapus'] == 'gagal') {
        $hapus_pesan = "<div style='color: red; margin-bottom: 10px;'>Terjadi kesalahan saat menghapus data pembelian: " . htmlspecialchars($_GET['error']) . "</div>";
    } elseif ($_GET['hapus'] == 'invalid') {
        $hapus_pesan = "<div style='color: orange; margin-bottom: 10px;'>ID pembelian tidak valid.</div>";
    }
}

// Proses pencarian
$search_term = "";
$sql_where = "";
if (isset($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $sql_where = "WHERE nama_pembeli LIKE '%$search_term%'
                    OR no_ktp LIKE '%$search_term%'
                    OR alamat LIKE '%$search_term%' /* ADDED: Pencarian berdasarkan Alamat */
                    OR telepon LIKE '%$search_term%'
                    OR type_rumah LIKE '%$search_term%'
                    OR blok_rumah LIKE '%$search_term%'
                    OR status_pembelian LIKE '%$search_term%'";
}

// Query untuk mengambil data pembelian
// Menambahkan kolom 'alamat' ke dalam SELECT
$sql = "SELECT id_pembelian, tanggal_pembelian, nama_pembeli, no_ktp, alamat, telepon, type_rumah, blok_rumah, status_pembelian
        FROM tb_pembelian
        $sql_where
        ORDER BY tanggal_pembelian DESC"; // Mengurutkan berdasarkan tanggal terbaru
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembelian</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            display: flex;
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            flex: 1;
            min-height: 100vh;
        }

        .sidebar {
            background-color: #fff;
            color: #117c6b;
            width: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .sidebar-header .logo {
            height: 80px;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav ul li a {
            display: flex;
            align-items: center;
            color: #117c6b;
            text-decoration: none;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .sidebar-nav ul li a:hover {
            background-color: #e0f2f1;
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
            background-color: transparent;
            color: #117c6b;
        }

        .sidebar-nav ul li.logout a:hover {
            background-color: #e0f2f1;
        }

        .sidebar-nav ul li a.active {
            background-color: #117c6b;
            color: white;
            font-weight: 600;
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            padding-left: 20px; /* Indentasi untuk submenu */
            display: none; /* Sembunyikan submenu secara default */
        }

        .submenu li a {
            font-size: 14px;
            padding: 10px 15px;
            margin-bottom: 10px;
            display: block;
            color: #117c6b;
            text-decoration: none;
        }

        .submenu li a:hover {
            background-color: #e0f2f1;
        }

        .main-content {
            flex: 1;
            background-color: #f4f6f8;
            display: flex;
            flex-direction: column;
        }

        .main-header {
            background-color: #fff;
            color: #117c6b;
            padding: 20px;
            border-bottom: 2px solid #e0f2f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .main-header h2 {
            margin: 0;
            font-size: 1.8em;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .admin-info {
            display: flex;
            align-items: center;
        }

        .admin-info i {
            margin-right: 10px;
            font-size: 1.2em;
            color: #117c6b;
        }

        .admin-info span {
            font-weight: 500;
            color: #333;
            font-family: 'Poppins', sans-serif;
        }

        .content-area {
            padding: 20px;
            flex: 1;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        /* Styles untuk Laporan Pembelian */
        .report-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .report-title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }

        .report-options {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            justify-content: space-between; /* Mengatur jarak antar elemen di dalamnya */
        }

        .search-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-section input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .add-button {
            background-color: #117c6b; /* Warna hijau yang konsisten */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
            text-decoration: none; /* Tambahkan properti ini */
        }

        .add-button:hover {
            background-color: #0d6658;
        }

        .add-button i {
            margin-right: 5px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; /* Kurangi margin atas tabel */
            border: 1px solid #e0f2f1;
        }

        .report-table th, .report-table td {
            border: 1px solid #e0f2f1;
            padding: 8px 10px; /* Perkecil padding sel */
            text-align: left;
        }

        .report-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #333;
            text-align: center;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .action-buttons {
            white-space: nowrap; /* Mencegah tombol turun ke baris baru */
        }

        .action-buttons a {
            display: inline-block;
            margin-right: 3px; /* Kurangi margin kanan antar tombol */
            padding: 5px 8px; /* Perkecil padding tombol */
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8em; /* Perkecil ukuran font */
        }

        .detail-button {
            background-color: #2196F3;
            color: white;
        }

        .edit-button {
            background-color: #4CAF50;
            color: white;
        }

        .delete-button {
            background-color: #f44336;
            color: white;
        }

        .detail-button:hover {
            background-color: #0b7dda;
        }

        .edit-button:hover {
            background-color: #388E3C;
        }

        .delete-button:hover {
            background-color: #d32f2f;
        }

        .action-buttons i {
            margin-right: 2px; /* Perkecil margin ikon */
            font-size: 0.9em; /* Perkecil ukuran ikon */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            .sidebar-header {
                min-width: auto;
                margin-bottom: 10px;
            }
            .sidebar-nav ul {
                display: flex;
            }
            .sidebar-nav ul li {
                margin-right: 10px;
            }
            .sidebar-nav ul li a {
                padding: 8px 12px;
                font-size: 0.9em;
            }
            .main-content {
                flex-direction: column;
            }
            .report-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px; /* Mengurangi jarak antar elemen saat responsif */
            }
            .search-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .report-table {
                overflow-x: auto;
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header" style="justify-content: center;">
                <img src="gambar/Logo_Green.png" alt="Logo Green" class="logo">
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="Dashboard_admin.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="has-submenu">
                        <a href="#" onclick="toggleSubmenu(event)">
                            <i class="fas fa-home"></i>
                            <span>Data Rumah</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="kategori_rumah.php">Kategori Rumah</a></li>
                            <li><a href="unit_rumah.php">Unit Rumah</a></li>
                        </ul>
                    </li>
                    <li>
                      <a href="kotak_masuk.php">
                            <i class="fas fa-comments"></i>
                            <span>Kotak Masuk</span>
                        </a>
                    </li>
                    <li>
                      <a href="data_pembelian.php" class="active">
                            <i class="fas fa-file-invoice"></i>
                            <span>Data Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_pembayaran.php">
                            <i class="fas fa-money-bill-alt"></i>
                            <span>Data Pembayaran</span>
                        </a>
                    </li>
                    <li class="logout">
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <h2>Data Pembelian</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span>
                </div>
            </header>
            <div class="content-area">
                <div class="report-container">
                    <h3 class="report-title">Data Pembelian</h3>
                    <?php echo $hapus_pesan; ?>
                    <div class="report-options">
                        <div class="search-section">
                            <form method="get" action="">
                                <input type="text" id="search-laporan" name="search" placeholder="search....." value="<?php echo htmlspecialchars($search_term); ?>">
                            </form>
                        </div>
                        <button class="add-button" onclick="window.location.href='tambah_pembelian.php';">
                            <i class="fas fa-plus"></i> Tambah Pembelian
                        </button>
                    </div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Pemesan</th>
                                <th>No KTP</th>
                                <th>Alamat</th> <th>No. Telepon</th>
                                <th>Type</th>
                                <th>Blok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
                                    // Format tanggal
                                    $tanggal_pembelian = date('d/m/Y', strtotime($row["tanggal_pembelian"]));

                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($tanggal_pembelian) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["nama_pembeli"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["no_ktp"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["alamat"]) . "</td>"; /* NEW COLUMN DATA */
                                    echo "<td>" . htmlspecialchars($row["telepon"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["type_rumah"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["blok_rumah"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["status_pembelian"]) . "</td>";
                                    echo "<td class='action-buttons'>";
                                    echo "<a href='edit_pembelian.php?id=" . htmlspecialchars($row["id_pembelian"]) . "' class='edit-button'><i class='fas fa-edit'></i> Edit</a>";
                                    echo "<a href='detail_pembelian.php?id=" . htmlspecialchars($row["id_pembelian"]) . "' class='detail-button'><i class='fas fa-eye'></i> Detail</a>";
                                    echo "<a href='hapus_pembelian.php?id=" . htmlspecialchars($row["id_pembelian"]) . "' class='delete-button' onclick=\"return confirm('Apakah Anda yakin ingin menghapus data ini?')\"><i class='fas fa-trash'></i> Hapus</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                // Mengubah colspan karena jumlah kolom bertambah
                                echo "<tr><td colspan='10'>Tidak ada data pembelian.</td></tr>"; // Updated colspan from 9 to 10
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        function toggleSubmenu(event) {
            event.preventDefault(); // Mencegah perilaku default link
            const submenu = event.target.nextElementSibling; // Ambil submenu yang sesuai
            if (submenu.style.display === "block") {
                submenu.style.display = "none"; // Sembunyikan submenu
            } else {
                submenu.style.display = "block"; // Tampilkan submenu
            }
        }

        // Menutup submenu jika mengklik di luar
        window.onclick = function(event) {
            const submenu = document.querySelector('.submenu');
            // Cek apakah ada submenu dan klik bukan pada area submenu atau link yang memicu submenu
            if (submenu && !event.target.closest('.has-submenu') && !event.target.closest('.submenu')) {
                submenu.style.display = 'none'; // Tutup submenu jika klik di luar
            }
        }

        // Opsional: Untuk memastikan submenu tetap terbuka jika halaman dimuat ulang dan sedang aktif
        document.addEventListener('DOMContentLoaded', function() {
            const activeLink = document.querySelector('.sidebar-nav ul li a.active');
            if (activeLink && activeLink.closest('.has-submenu')) {
                const submenu = activeLink.closest('.has-submenu').querySelector('.submenu');
                if (submenu) {
                    submenu.style.display = 'block';
                }
            }
        });
    </script>
</body>
</html>

<?php
// Menutup koneksi database
$conn->close();
?>