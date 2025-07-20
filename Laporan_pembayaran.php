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

// Proses pencarian (menggunakan kolom yang sama dengan data_pembayaran)
$search_term_pembayaran = "";
$sql_where_pembayaran = "";
// Gunakan prepared statements untuk keamanan yang lebih baik
$params = [];
$types = "";

if (isset($_GET['search_pembayaran']) && !empty($_GET['search_pembayaran'])) {
    $search_term_pembayaran = $_GET['search_pembayaran']; // Raw search term for input value
    $search_term_pembayaran_like = "%" . $search_term_pembayaran . "%";
    // Pastikan kolom di WHERE sesuai dengan SELECT query Anda dan ada di tabel yang di-JOIN
    $sql_where_pembayaran = "WHERE tp.tanggal_pembayaran LIKE ?
                             OR tp.nama_pembeli LIKE ?
                             OR tr.type_rumah LIKE ?
                             OR tu.nama_blok LIKE ?
                             OR tp.jenis_transaksi LIKE ?
                             OR tp.jumlah_pembayaran LIKE ?";
    
    $params = [
        $search_term_pembayaran_like,
        $search_term_pembayaran_like,
        $search_term_pembayaran_like,
        $search_term_pembayaran_like,
        $search_term_pembayaran_like,
        $search_term_pembayaran_like
    ];
    $types = "ssssss"; // 6 parameter string
}

// Query untuk mengambil data pembayaran dengan JOIN ke tb_pembelian, tb_rumah, dan tb_unit
$sql_pembayaran = "SELECT
                         tp.id_pembayaran,
                         tp.tanggal_pembayaran,
                         tp.nama_pembeli,
                         tr.type_rumah,
                         tu.nama_blok,
                         tp.jenis_transaksi,
                         tp.jumlah_pembayaran,
                         tp.bukti_pembayaran
                    FROM
                         tb_pembayaran tp
                    JOIN
                         tb_pembelian tpe ON tp.id_pembelian = tpe.id_pembelian
                    JOIN
                         tb_rumah tr ON tpe.id_rumah = tr.id_rumah
                    JOIN
                         tb_unit tu ON tpe.id_unit = tu.id_unit
                    $sql_where_pembayaran
                    ORDER BY tp.tanggal_pembayaran DESC";

$stmt_pembayaran = $conn->prepare($sql_pembayaran);

if ($stmt_pembayaran) {
    if (!empty($params)) {
        // Gunakan call_user_func_array untuk bind_param dengan array dinamis
        $stmt_pembayaran->bind_param($types, ...$params);
    }
    $stmt_pembayaran->execute();
    $result_pembayaran = $stmt_pembayaran->get_result();
} else {
    die("Error preparing statement: " . $conn->error);
}

// Menghitung Total Pembayaran
$total_pembayaran = 0;
$data_pembayaran = []; // Simpan data ke array agar bisa dihitung totalnya
if ($result_pembayaran && $result_pembayaran->num_rows > 0) {
    while ($row_pembayaran = $result_pembayaran->fetch_assoc()) {
        $data_pembayaran[] = $row_pembayaran;
        $total_pembayaran += $row_pembayaran['jumlah_pembayaran']; // Menghitung total saat data diambil
    }
}

// Menghitung jumlah kolom yang sebenarnya di header tabel
// No, Tanggal, Nama Pembeli, Type, Blok, Transaksi, Jumlah, Bukti Pembayaran, Aksi (9 kolom)
$total_kolom_tabel = 9;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran</title>
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

        /* Sidebar Styles */
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

        /* Submenu styles (jika diperlukan di tampilan pimpinan) */
        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            padding-left: 20px;
            display: none;
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

        .has-submenu.active .submenu {
            display: block;
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
            display: flex;
            flex-direction: column;
        }

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
            gap: 15px; /* Reduced gap for a tighter look */
            margin-bottom: 20px;
            /* Removed justify-content: space-between; */
        }

        .search-section { /* Added to group search and export */
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-section input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 200px; /* Adjust width as needed */
        }

        .export-button {
            background-color: #117c6b;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: flex; /* Make it a flex container to align icon and text */
            align-items: center; /* Center icon and text vertically */
        }

        .export-button:hover {
            background-color: #0d6658;
        }

        .export-button i {
            margin-right: 5px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e0f2f1;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #e0f2f1;
            padding: 8px 10px;
            text-align: left;
            font-size: 0.9em;
            vertical-align: middle;
        }

        .report-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #333;
            text-align: center;
        }

        /* Alternating row colors */
        .report-table tbody tr:nth-child(odd) {
            background-color: #ffffff; /* White background for odd rows */
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f2f2f2; /* Light gray background for even rows */
        }

        .report-table tfoot td {
            background-color: #f9f9f9;
            font-weight: bold;
            color: #117c6b;
            border-top: 2px solid #117c6b;
        }

        /* Gaya untuk tombol detail yang baru dipisah */
        .detail-button {
            display: inline-flex; /* Use flex to align icon and text */
            align-items: center; /* Vertically center icon and text */
            padding: 6px 10px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85em;
            transition: background-color 0.3s ease;
            margin-top: 5px; /* Add a little margin if it's below an image */
        }

        .detail-button:hover {
            background-color: #0d6658;
        }

        .detail-button i {
            margin-right: 5px;
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
                height: auto;
            }
            .sidebar-header {
                min-width: auto;
                margin-bottom: 10px;
            }
            .sidebar-nav ul {
                display: flex;
                flex-wrap: nowrap;
            }
            .sidebar-nav ul li {
                margin-right: 10px;
                flex-shrink: 0;
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
                gap: 10px;
            }
            .search-section { /* Added to group search and export */
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .report-table {
                overflow-x: auto;
                display: block;
            }
            /* Adjustments for submenu on small screens */
            .submenu {
                position: absolute;
                background-color: #fff;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                z-index: 10;
                width: 200px;
                left: 0;
                top: 100%;
            }
            .has-submenu {
                position: relative;
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
                        <a href="Dashboard_pimpinan.php">
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
                        <a href="laporan_pembayaran.php" class="active">
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
                <h2>Laporan Pembayaran</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Pimpinan</span>
                </div>
            </header>
            <div class="content-area">
                <div class="report-container">
                    <h3 class="report-title">Laporan Pembayaran</h3>
                    <div class="report-options">
                        <div class="search-section"> 
                            <form method="get" action="" style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" id="search_pembayaran" name="search_pembayaran" placeholder="Cari data..." value="<?php echo htmlspecialchars($_GET['search_pembayaran'] ?? ''); ?>">
                                <button type="submit" style="display:none;"></button> 
                            </form>
                            <a href="export_pembayaran.php" class="export-button"><i class="fas fa-download"></i> Export </a>
                        </div>
                    </div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>No</th> 
                                <th>Tanggal</th>
                                <th>Nama Pembeli</th>
                                <th>Type</th>
                                <th>Blok</th>
                                <th>Transaksi</th>
                                <th>Jumlah</th>
                                <th>Bukti Pembayaran</th>
                                <th>Aksi</th> </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($data_pembayaran)) {
                                $no = 1; // Initialize row counter
                                foreach ($data_pembayaran as $row_pembayaran) {
                                    $tanggal_pembayaran_formatted = date('d/m/Y', strtotime($row_pembayaran["tanggal_pembayaran"]));
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>"; // Display and increment row number
                                    echo "<td>" . htmlspecialchars($tanggal_pembayaran_formatted) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["nama_pembeli"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["type_rumah"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["nama_blok"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["jenis_transaksi"]) . "</td>";
                                   echo "<td style='text-align: right; white-space: nowrap;'>Rp ". number_format($row_pembayaran["jumlah_pembayaran"], 0, ',', '.') . "</td>";
                                    echo "<td style='text-align: center;'>";
                                    if (!empty($row_pembayaran["bukti_pembayaran"])) {
                                        echo "<a href='" . htmlspecialchars($row_pembayaran["bukti_pembayaran"]) . "' target='_blank'>";
                                        echo "<img src='" . htmlspecialchars($row_pembayaran["bukti_pembayaran"]) . "' alt='Bukti Pembayaran' style='max-width: 80px; max-height: 80px; object-fit: cover;'>";
                                        echo "</a>";
                                    } else {
                                        echo "Tidak ada bukti";
                                    }
                                    echo "</td>";
                                    echo "<td style='text-align: center;'>"; // Kolom Aksi terpisah
                                    echo "<a href='detail_laporan_pembayaran.php?id=" . htmlspecialchars($row_pembayaran["id_pembayaran"]) . "' class='detail-button'><i class='fas fa-eye'></i> Detail</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                // Update colspan to match the new number of columns (9)
                                echo "<tr><td colspan='" . $total_kolom_tabel . "'>Tidak ada data pembayaran yang ditemukan.</td></tr>";
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right;">Total Pembayaran:</td>
                              <td style="text-align: right; white-space: nowrap;"><?php echo "Rp " . number_format($total_pembayaran, 0, ',', '.'); ?></td>
                                <td colspan="2"></td> </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Script untuk sidebar submenu (jika diperlukan)
        function toggleSubmenu(event) {
            event.preventDefault();
            const parentLi = event.target.closest('li.has-submenu');
            if (parentLi) {
                parentLi.classList.toggle('active');
            }
        }

        // Script untuk menjaga submenu tetap terbuka saat halaman dimuat ulang
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const currentPage = currentPath.substring(currentPath.lastIndexOf('/') + 1);

            // Cek apakah halaman saat ini adalah salah satu sub-halaman dari "Data Rumah"
            const dataRumahSubPages = ['kategori_rumah.php', 'unit_rumah.php']; // Ini mungkin tidak relevan untuk pimpinan
            if (dataRumahSubPages.includes(currentPage)) {
                const dataRumahMenuItem = document.querySelector('.sidebar-nav ul li.has-submenu');
                if (dataRumahMenuItem) {
                    dataRumahMenuItem.classList.add('active');
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