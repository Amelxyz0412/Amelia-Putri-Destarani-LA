<?php
session_start();

// Konfigurasi koneksi database
$host = "localhost";
$username = "root"; // Ganti dengan username database Anda jika berbeda
$password = "";     // Ganti dengan password database Anda jika berbeda
$database = "db_perumahan"; // Ganti dengan nama database Anda jika berbeda

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Inisialisasi variabel filter
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

// Bangun query SQL dasar
$sql = "SELECT
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
            tb_unit tu ON tpe.id_unit = tu.id_unit";

$where_clauses = [];
$params = [];
$param_types = '';

// Tambahkan kondisi WHERE jika filter bulan dan tahun dipilih
if (!empty($filter_bulan) && !empty($filter_tahun)) {
    $where_clauses[] = "MONTH(tp.tanggal_pembayaran) = ?";
    $where_clauses[] = "YEAR(tp.tanggal_pembayaran) = ?";
    $params[] = $filter_bulan;
    $params[] = $filter_tahun;
    $param_types .= 'ii'; // 'i' for integer (bulan, tahun)
} elseif (!empty($filter_tahun)) {
    // Hanya filter tahun
    $where_clauses[] = "YEAR(tp.tanggal_pembayaran) = ?";
    $params[] = $filter_tahun;
    $param_types .= 'i';
} elseif (!empty($filter_bulan)) {
    // Hanya filter bulan (jarang digunakan sendiri tanpa tahun, tapi bisa diimplementasikan)
    // Untuk tujuan laporan bulanan, biasanya dikombinasikan dengan tahun.
    // Jika Anda ingin mengizinkan filter bulan tanpa tahun, pertimbangkan dampaknya.
    // Untuk saat ini, saya akan mengabaikan filter bulan jika tahun tidak dipilih.
    // Anda bisa menambahkan logika lebih lanjut di sini jika diperlukan.
}


if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY tp.tanggal_pembayaran DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$total_pembayaran = 0; // Inisialisasi variabel untuk total pembayaran
$data_pembayaran = []; // Array untuk menyimpan data agar bisa diiterasi ulang untuk perhitungan total

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_pembayaran[] = $row; // Simpan setiap baris data
        $total_pembayaran += $row['jumlah_pembayaran']; // Tambahkan jumlah pembayaran ke total
    }
}

$conn->close();

// Hitung jumlah kolom yang sebenarnya di header tabel (untuk colspan)
// No, Tanggal, Nama Pembeli, Type, Blok, Transaksi, Jumlah, Bukti Pembayaran (8 kolom)
$total_kolom_tabel = 8;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            -webkit-print-color-adjust: exact; /* Penting untuk mencetak warna latar belakang */
            print-color-adjust: exact;
        }
        .container {
            max-width: 900px; /* Lebarkan sedikit container untuk menampung lebih banyak kolom */
            margin: 0 auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #117c6b;
            padding-bottom: 15px;
            overflow: hidden;
            display: flex; /* Menggunakan flexbox untuk header */
            align-items: center; /* Pusatkan item secara vertikal */
            justify-content: center; /* Pusatkan item secara horizontal */
            gap: 20px; /* Jarak antara logo dan teks */
        }
        .header .logo {
            width: 80px;
            height: auto; /* Agar aspek rasio tetap terjaga */
        }
        .header-text {
            text-align: left;
            flex-grow: 1; /* Agar teks mengisi sisa ruang */
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #117c6b;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
        }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
            color: #555;
            padding-top: 20px;
        }
        .filter-form {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        .filter-form label {
            margin-right: 10px;
            font-weight: bold;
            color: #333;
        }
        .filter-form select, .filter-form button {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 10px;
        }
        .filter-form button {
            background-color: #117c6b;
            color: white;
            cursor: pointer;
            border: none;
        }
        .filter-form button:hover {
            background-color: #0e6355;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            vertical-align: top; /* Agar konten sel tidak terpotong */
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-align: center; /* Pusatkan teks header */
        }
        /* Alternating row colors for better readability in print */
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9; /* Slightly different shade for even rows in print */
        }
        .text-right {
            text-align: right;
        }
        .text-center { /* New class for centered text */
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #777;
        }

        tfoot td {
            background-color: #f2f2f2; /* Menggunakan warna abu-abu muda yang konsisten */
            font-weight: bold;
            color: #333; /* Warna teks lebih gelap */
            border-top: 1px solid #ddd;
        }
        tfoot td:first-child { /* Adjust colspan for the first cell in tfoot */
            text-align: right;
        }

        /* Style for image in table */
        .bukti-pembayaran-img {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            display: block; /* Agar gambar tidak ada ruang di bawahnya */
            margin: 0 auto; /* Pusatkan gambar */
        }

        /* Hide elements that should not be printed */
        @media print {
            .no-print, .filter-form {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="gambar/Logo_Green.png" alt="Logo Perusahaan" class="logo">
            <div class="header-text">
                <h1>PT. BINTANG AGUNG PROPERTY</h1>
                <p>Jl. Bypass Soekarno Hatta - Terminal Km12, Kecamatan Alang-Alang Lebar,<br>Kota Palembang, Sumatera Selatan, 30151</p>
                <p>Telepon: (0711) 5645669 | Email: info@greenresortcity.com</p>
            </div>
        </div>

        <div class="report-title">
            Laporan Data Pembayaran
            <?php
            // Menampilkan bulan dan tahun yang difilter jika ada
            if (!empty($filter_bulan) && !empty($filter_tahun)) {
                $nama_bulan = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ];
                echo " Bulan " . $nama_bulan[$filter_bulan] . " Tahun " . $filter_tahun;
            } elseif (!empty($filter_tahun)) {
                echo " Tahun " . $filter_tahun;
            }
            ?>
        </div>

        <div class="filter-form no-print">
            <form action="" method="get">
                <label for="bulan">Bulan:</label>
                <select name="bulan" id="bulan">
                    <option value="">-- Semua Bulan --</option>
                    <?php
                    $nama_bulan_arr = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    foreach ($nama_bulan_arr as $num => $name) {
                        $selected = ($filter_bulan == $num) ? 'selected' : '';
                        echo '<option value="' . $num . '" ' . $selected . '>' . $name . '</option>';
                    }
                    ?>
                </select>

                <label for="tahun">Tahun:</label>
                <select name="tahun" id="tahun">
                    <option value="">-- Semua Tahun --</option>
                    <?php
                    $tahun_sekarang = date('Y');
                    for ($tahun = $tahun_sekarang; $tahun >= 2000; $tahun--) { // Sesuaikan rentang tahun sesuai kebutuhan
                        $selected = ($filter_tahun == $tahun) ? 'selected' : '';
                        echo '<option value="' . $tahun . '" ' . $selected . '>' . $tahun . '</option>';
                    }
                    ?>
                </select>

                <button type="submit">Filter Laporan</button>
            </form>
        </div>

        <table>
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
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($data_pembayaran)) {
                    $no = 1;
                    foreach ($data_pembayaran as $row) {
                        $tanggal_pembayaran_formatted = date('d/m/Y', strtotime($row["tanggal_pembayaran"]));
                        echo '<tr>';
                        echo '<td class="text-center">' . $no++ . '</td>'; // Center No. column
                        echo '<td>' . htmlspecialchars($tanggal_pembayaran_formatted) . '</td>';
                        echo '<td>' . htmlspecialchars($row["nama_pembeli"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["type_rumah"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["nama_blok"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["jenis_transaksi"]) . '</td>';
                        echo '<td class="text-right">' . number_format($row["jumlah_pembayaran"], 0, ',', '.') . '</td>';
                        echo '<td class="text-center">'; // Center content of Bukti Pembayaran column
                        if (!empty($row["bukti_pembayaran"])) {
                            echo '<img src="' . htmlspecialchars($row["bukti_pembayaran"]) . '" alt="Bukti Pembayaran" class="bukti-pembayaran-img">';
                        } else {
                            echo "Tidak ada bukti";
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="' . $total_kolom_tabel . '">Tidak ada data pembayaran yang ditemukan untuk filter ini.</td></tr>';
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">Total Pembayaran:</td>
                    <td class="text-right"><?php echo number_format($total_pembayaran, 0, ',', '.') . '</td>';
                    echo '<td></td>'; // Empty cell for Bukti Pembayaran column
                ?>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            Dicetak pada: <?php echo date('d-m-Y H:i:s'); ?>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #117c6b; color: white; border: none; border-radius: 5px; cursor: pointer;">Cetak Laporan (Save as PDF)</button>
        </div>
    </div>
</body>
</html>