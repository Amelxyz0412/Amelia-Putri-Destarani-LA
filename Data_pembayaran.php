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
$hapus_pesan_pembayaran = "";
if (isset($_GET['hapus_pembayaran'])) {
    if ($_GET['hapus_pembayaran'] == 'sukses') {
        $hapus_pesan_pembayaran = "<div style='color: green; margin-bottom: 10px;'>Data pembayaran berhasil dihapus.</div>";
    } elseif ($_GET['hapus_pembayaran'] == 'gagal') {
        $hapus_pesan_pembayaran = "<div style='color: red; margin-bottom: 10px;'>Terjadi kesalahan saat menghapus data pembayaran: " . htmlspecialchars($_GET['error']) . "</div>";
    } elseif ($_GET['hapus_pembayaran'] == 'invalid') {
        $hapus_pesan_pembayaran = "<div style='color: orange; margin-bottom: 10px;'>ID pembayaran tidak valid.</div>";
    }
}

// Proses pencarian (diperbarui untuk menyertakan kolom baru)
$search_term_pembayaran = "";
$sql_where_pembayaran = "";
if (isset($_GET['search_pembayaran'])) {
    $search_term_pembayaran = $conn->real_escape_string($_GET['search_pembayaran']);
    $sql_where_pembayaran = "WHERE tp.tanggal_pembayaran LIKE '%$search_term_pembayaran%'
                             OR tp.nama_pembeli LIKE '%$search_term_pembayaran%'
                             OR tr.type_rumah LIKE '%$search_term_pembayaran%'
                             OR tu.nama_blok LIKE '%$search_term_pembayaran%'
                             OR tp.jenis_transaksi LIKE '%$search_term_pembayaran%'
                             OR tp.jumlah_pembayaran LIKE '%$search_term_pembayaran%'";
}

// Query untuk mengambil data pembayaran dengan JOIN ke tb_pembelian, tb_rumah, dan tb_unit
// **PENTING: Menambahkan tp.id_pembayaran di SELECT**
$sql_pembayaran = "SELECT
                        tp.id_pembayaran, -- <<< TAMBAH INI
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
                    ORDER BY tp.tanggal_pembayaran DESC"; // Urutkan berdasarkan tanggal terbaru
$result_pembayaran = $conn->query($sql_pembayaran);

// Menghitung Total Pembayaran
$total_pembayaran = 0;
$data_pembayaran = []; // Simpan data ke array agar bisa dihitung totalnya
if ($result_pembayaran->num_rows > 0) {
    while ($row_pembayaran = $result_pembayaran->fetch_assoc()) {
        $data_pembayaran[] = $row_pembayaran;
        $total_pembayaran += $row_pembayaran['jumlah_pembayaran']; // Menghitung total saat data diambil
    }
}

// Menghitung jumlah kolom yang sebenarnya di header tabel (untuk colspan)
// Tanggal, Nama Pembeli, Type, Blok, Transaksi, Jumlah, Bukti Pembayaran, Aksi (8 kolom)
$total_kolom_tabel = 9; // <<< UBAH DARI 7 MENJADI 8

// Query untuk mendapatkan daftar ID Pembelian yang statusnya 'Terbooking' dan belum dibayar
$sql_pembelian_options = "
    SELECT id_pembelian
    FROM tb_pembelian
    WHERE status_pembelian = 'Terbooking'
    AND id_pembelian NOT IN (SELECT id_pembelian FROM tb_pembayaran)
";
$result_pembelian_options = $conn->query($sql_pembelian_options);
$options_pembelian = [];
if ($result_pembelian_options->num_rows > 0) {
    while ($row = $result_pembelian_options->fetch_assoc()) {
        $options_pembelian[] = $row['id_pembelian'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penjualan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* (SEMUA STYLE CSS SEBELUMNYA TETAP SAMA) */
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
            background-color: #fff; /* Latar belakang putih */
            color: #117c6b; /* Warna teks hijau utama */
            width: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Efek bayangan */
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center; /* Agar logo di tengah */
            margin-bottom: 30px;
        }

        .sidebar-header .logo {
            height: 80px; /* Ukuran logo */
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
            margin-bottom: 20px; /* Jarak antar item */
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .sidebar-nav ul li a:hover {
            background-color: #e0f2f1; /* Warna hover */
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
            background-color: transparent; /* Menghilangkan latar belakang */
            color: #117c6b; /* Warna teks tetap hijau */
        }

        .sidebar-nav ul li.logout a:hover {
            background-color: #e0f2f1; /* Warna hover tetap hijau muda */
        }

        .sidebar-nav ul li a.active {
            background-color: #117c6b; /* Warna aktif */
            color: white;
            font-weight: 600;
        }

        /* Submenu styles */
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

        .has-submenu.active .submenu {
            display: block; /* Tampilkan submenu saat menu utama aktif */
        }

        .main-content {
            flex: 1;
            background-color: #f4f6f8; /* Latar belakang abu-abu muda */
            display: flex;
            flex-direction: column;
        }

        .main-header {
            background-color: #fff; /* Latar belakang putih */
            color: #117c6b; /* Warna teks hijau utama */
            padding: 20px;
            border-bottom: 2px solid #e0f2f1; /* Border bawah hijau muda */
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Efek bayangan */
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
            display: flex; /* Enable flex layout for content area */
            flex-direction: column; /* Stack elements vertically */
        }

        .report-container {
            background-color: #fff; /* Latar belakang putih untuk laporan */
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Efek bayangan */
        }

        .report-title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid #e0f2f1; /* Border bawah */
            padding-bottom: 10px;
        }

        .report-options {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            justify-content: space-between; /* Untuk menyebar item */
        }

        .report-options input[type="text"] { /* Input teks untuk pencarian */
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
            text-decoration: none; /* Pastikan tidak ada underline */
        }

        .add-button:hover {
            background-color: #0d6658; /* Warna hover */
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e0f2f1;
        }

        .report-table th,
        .report-table td { /* Terapkan border ke th dan td */
            border: 1px solid #e0f2f1; /* Border sel */
            padding: 8px 10px; /* Padding lebih ringkas */
            text-align: left;
            font-size: 0.9em; /* Ukuran font data diperkecil */
            vertical-align: middle; /* Align konten di tengah secara vertikal */
        }

        .report-table th {
            background-color: #f9f9f9; /* Latar belakang header tabel */
            font-weight: 600;
            color: #333;
            text-align: center; /* Teks header di tengah */
        }
         .report-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Style untuk total pembayaran di dalam tfoot */
        .report-table tfoot td {
            background-color: #f9f9f9; /* Latar belakang abu-abu muda */
            font-weight: bold;
            color: #117c6b; /* Warna teks hijau utama */
            border-top: 2px solid #117c6b; /* Garis pemisah dari tbody */
        }

        /* Gaya untuk tombol aksi */
        .action-buttons {
            white-space: nowrap; /* Mencegah tombol turun ke baris baru */
            text-align: center; /* Pusatkan tombol dalam sel */
        }

        .action-buttons a {
            display: inline-block;
            margin: 0 3px; /* Kurangi margin kanan antar tombol */
            padding: 5px 8px; /* Perkecil padding tombol */
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8em; /* Perkecil ukuran font */
        }

        .detail-button {
            background-color: #2196F3; /* Biru */
            color: white;
        }

        .edit-button {
            background-color: #4CAF50; /* Hijau */
            color: white;
        }

        .delete-button {
            background-color: #f44336; /* Merah */
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
                height: auto; /* Allow sidebar to collapse vertically */
            }
            .sidebar-header {
                min-width: auto;
                margin-bottom: 10px;
            }
            .sidebar-nav ul {
                display: flex;
                flex-wrap: nowrap; /* Prevent wrapping in horizontal mode */
            }
            .sidebar-nav ul li {
                margin-right: 10px;
                flex-shrink: 0; /* Prevent items from shrinking too much */
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
                width: 200px; /* Lebar submenu saat responsif */
                left: 0;
                top: 100%; /* Di bawah parent */
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
                        <a href="data_pembelian.php">
                            <i class="fas fa-file-invoice"></i>
                            <span>Data Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_pembayaran.php" class="active">
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
                <h2>Data Pembayaran</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span>
                </div>
            </header>
            <div class="content-area">
                <div class="report-container">
                    <h3 class="report-title">Data Pembayaran</h3>
                    <?php echo $hapus_pesan_pembayaran; // Tampilkan pesan hapus di sini ?>
                    <div class="report-options">
                        <form method="get" action="" class="search-section">
                            <input type="text" id="search_pembayaran" name="search_pembayaran" placeholder="search....." value="<?php echo htmlspecialchars($search_term_pembayaran); ?>">
                        </form>
                        <button class="add-button" onclick="window.location.href='tambah_pembayaran.php';"><i class="fas fa-plus"></i> Tambah Pembayaran</button>
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
                            $no = 1; // Ini akan memulai nomor urut dari 1
                            if (!empty($data_pembayaran)) {
                                foreach ($data_pembayaran as $row_pembayaran) {
                                    $tanggal_pembayaran_formatted = date('d/m/Y', strtotime($row_pembayaran["tanggal_pembayaran"]));
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($tanggal_pembayaran_formatted) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["nama_pembeli"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["type_rumah"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["nama_blok"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row_pembayaran["jenis_transaksi"]) . "</td>";
                                   echo "<td style='text-align: right; white-space: nowrap;'>Rp " . number_format($row_pembayaran["jumlah_pembayaran"], 0, ',', '.') . "</td>";
                                    echo "<td style='text-align: center;'>"; // Pusatkan konten sel bukti pembayaran
                                    if (!empty($row_pembayaran["bukti_pembayaran"])) {
                                        echo "<a href='" . htmlspecialchars($row_pembayaran["bukti_pembayaran"]) . "' target='_blank'>";
                                        echo "<img src='" . htmlspecialchars($row_pembayaran["bukti_pembayaran"]) . "' alt='Bukti Pembayaran' style='max-width: 80px; max-height: 80px; object-fit: cover;'>"; // Ukuran lebih kecil
                                        echo "</a>";
                                        // Anda bisa menambahkan tooltip atau teks alternatif di sini jika perlu
                                    } else {
                                        echo "Tidak ada bukti";
                                    }
                                    echo "</td>";
                                    echo "<td class='action-buttons'>"; // <<< TAMBAH KOLOM AKSI
                                    echo "<a href='edit_pembayaran.php?id=" . htmlspecialchars($row_pembayaran["id_pembayaran"]) . "' class='edit-button'><i class='fas fa-edit'></i> Edit</a>";
                                    echo "<a href='hapus_pembayaran.php?id=" . htmlspecialchars($row_pembayaran["id_pembayaran"]) . "' class='delete-button' onclick=\"return confirm('Apakah Anda yakin ingin menghapus data ini?')\"><i class='fas fa-trash'></i> Hapus</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='" . $total_kolom_tabel . "'>Tidak ada data pembayaran.</td></tr>";
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
        function toggleSubmenu(event) {
            event.preventDefault(); // Mencegah perilaku default link
            const parentLi = event.target.closest('li.has-submenu');
            if (parentLi) {
                parentLi.classList.toggle('active');
            }
        }

        // Menutup submenu jika mengklik di luar
        window.onclick = function(event) {
            const hasSubmenuItems = document.querySelectorAll('.has-submenu');
            hasSubmenuItems.forEach(item => {
                if (!item.contains(event.target)) {
                    item.classList.remove('active');
                }
            });
        }

        // Opsional: Untuk memastikan submenu tetap terbuka jika halaman dimuat ulang dan sedang aktif
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const currentPage = currentPath.substring(currentPath.lastIndexOf('/') + 1);

            // Cek apakah halaman saat ini adalah salah satu sub-halaman dari "Data Rumah"
            const dataRumahSubPages = ['kategori_rumah.php', 'unit_rumah.php'];
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