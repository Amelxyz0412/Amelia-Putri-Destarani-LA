<?php
session_start();

// Konfigurasi koneksi database
$host = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = "";     // Ganti dengan password database Anda
$database = "db_perumahan"; // Ganti dengan nama database Anda

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Inisialisasi variabel filter
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

// Bangun query SQL dasar
$sql = "SELECT id_pembelian, tanggal_pembelian, nama_pembeli, no_ktp, alamat, telepon, type_rumah, blok_rumah, status_pembelian
        FROM tb_pembelian";

$where_clauses = [];
$params = [];
$param_types = '';

// Tambahkan kondisi WHERE jika filter bulan dan tahun dipilih
if (!empty($filter_bulan) && !empty($filter_tahun)) {
    $where_clauses[] = "MONTH(tanggal_pembelian) = ?";
    $where_clauses[] = "YEAR(tanggal_pembelian) = ?";
    $params[] = $filter_bulan;
    $params[] = $filter_tahun;
    $param_types .= 'ii'; // 'i' for integer (bulan, tahun)
} elseif (!empty($filter_tahun)) {
    // Hanya filter tahun
    $where_clauses[] = "YEAR(tanggal_pembelian) = ?";
    $params[] = $filter_tahun;
    $param_types .= 'i';
}
// Tidak ada elseif untuk filter bulan saja, karena biasanya laporan bulanan memerlukan tahun.

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY tanggal_pembelian DESC"; // Mengurutkan berdasarkan tanggal terbaru

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .container {
            max-width: 900px; /* Diperlebar sedikit agar lebih sesuai dengan kolom */
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
            vertical-align: top; /* Pastikan konten tidak terpotong */
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-align: center; /* Pusatkan teks header */
        }
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #777;
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
            Laporan Data Pembelian
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
                    // Misalnya, dari 5 tahun ke belakang sampai 10 tahun ke depan
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
                    <th>Nama Pemesan</th>
                    <th>No KTP</th>
                    <th>Alamat</th>
                    <th>No. Telepon</th>
                    <th>Type Rumah</th>
                    <th>Blok Rumah</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        // Format tanggal
                        $tanggal_pembelian = date('d/m/Y', strtotime($row["tanggal_pembelian"]));

                        echo '<tr>';
                        echo '<td class="text-center">' . $no++ . '</td>';
                        echo '<td>' . htmlspecialchars($tanggal_pembelian) . '</td>';
                        echo '<td>' . htmlspecialchars($row["nama_pembeli"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["no_ktp"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["alamat"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["telepon"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["type_rumah"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["blok_rumah"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["status_pembelian"]) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="9" class="text-center">Tidak ada data pembelian yang ditemukan untuk filter ini.</td></tr>';
                }
                ?>
            </tbody>
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