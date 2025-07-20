<?php
session_start();

// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Inisialisasi variabel untuk menghindari error undefined jika query gagal
$id_pembayaran_tampil = '';
$id_pembelian_tampil = '';
$tanggal_pembayaran_tampil = '';
$jumlah_pembayaran_tampil = '';
$jenis_transaksi_tampil = ''; // Variabel baru untuk jenis transaksi
$keterangan_pembayaran_tampil = 'Tidak ada keterangan'; // Default value
$nama_pembeli_tampil = '';
$type_rumah_tampil = '';
$nama_blok_tampil = '';
$bukti_pembayaran_tampil = ''; // Variabel baru untuk bukti pembayaran

// Ambil ID pembayaran dari parameter GET
if (isset($_GET['id'])) {
    $id_pembayaran = $_GET['id'];

    // Query untuk mengambil data pembayaran dan informasi terkait dari tabel lain
    $sql_select = "
        SELECT
            tp.id_pembayaran,
            tp.id_pembelian,
            tp.tanggal_pembayaran,
            tp.jumlah_pembayaran,
            tp.jenis_transaksi,
            tp.bukti_pembayaran,
            tpb.nama_pembeli,
            tr.type_rumah,
            tu.nama_blok
        FROM
            tb_pembayaran tp
        JOIN
            tb_pembelian tpb ON tp.id_pembelian = tpb.id_pembelian
        JOIN
            tb_rumah tr ON tpb.id_rumah = tr.id_rumah
        JOIN
            tb_unit tu ON tpb.id_unit = tu.id_unit
        WHERE
            tp.id_pembayaran = ?
    ";

    // Menggunakan prepared statement untuk keamanan
    $stmt = $conn->prepare($sql_select);
    if ($stmt === false) {
        $_SESSION['pesan_error'] = "<div style='color: red;'>Gagal menyiapkan statement: " . $conn->error . "</div>";
        die("Gagal menyiapkan statement: " . $conn->error); // Untuk debugging
    }

    $stmt->bind_param("s", $id_pembayaran);
    $stmt->execute();
    $result_select = $stmt->get_result();

    if ($result_select->num_rows == 1) {
        $row = $result_select->fetch_assoc();
        $id_pembayaran_tampil = $row['id_pembayaran'];
        $id_pembelian_tampil = $row['id_pembelian'];
        $tanggal_pembayaran_tampil = date('d F Y', strtotime($row['tanggal_pembayaran']));
        $jumlah_pembayaran_tampil = number_format($row['jumlah_pembayaran'], 0, ',', '.');
        $jenis_transaksi_tampil = $row['jenis_transaksi'];
        $keterangan_pembayaran_tampil = !empty($row['keterangan_pembayaran']) ? $row['keterangan_pembayaran'] : 'Tidak ada keterangan';
        $nama_pembeli_tampil = $row['nama_pembeli'];
        $type_rumah_tampil = $row['type_rumah'];
        $nama_blok_tampil = $row['nama_blok'];
        $bukti_pembayaran_tampil = $row['bukti_pembayaran'];
    } else {
        $_SESSION['pesan_error'] = "<div style='color: red;'>Data pembayaran dengan ID '{$id_pembayaran}' tidak ditemukan.</div>";
        die("Data pembayaran dengan ID '{$id_pembayaran}' tidak ditemukan."); // Untuk debugging
    }
    $stmt->close();
} else {
    $_SESSION['pesan_error'] = "<div style='color: red;'>ID pembayaran tidak valid.</div>";
    die("ID pembayaran tidak valid."); // Untuk debugging
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembayaran - <?php echo htmlspecialchars($id_pembayaran_tampil); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .detail-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
        }

        .detail-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.2em;
            color: #117c6b;
            margin-bottom: 30px;
            font-weight: 700;
            border-bottom: 3px solid #e0f2f1;
            padding-bottom: 15px;
            text-align: center;
        }

        .detail-info {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 15px 10px;
            align-items: baseline;
            margin-bottom: 20px;
        }

        .detail-info > div {
            display: contents;
        }

        .detail-info strong {
            font-weight: 600;
            color: #555;
            text-align: right;
            padding-right: 10px;
            white-space: nowrap;
        }

        .detail-info span {
            color: #333;
            word-wrap: break-word;
        }
        
        .detail-info .full-width {
            grid-column: 1 / -1;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            margin-top: 20px;
        }
        
        .detail-info .full-width strong {
            display: block;
            text-align: center;
            margin-bottom: 15px;
            padding-right: 0;
        }

        .bukti-pembayaran-image {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: block;
            margin-left: auto;
            margin-right: auto;
            cursor: zoom-in;
        }

        .no-bukti-text {
            color: #777;
            font-style: italic;
            text-align: center;
            margin-top: 15px;
        }

        .back-button {
            display: block;
            padding: 12px 20px;
            background-color: #117c6b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 30px;
            text-align: center;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #0d6658;
        }

        /* Gaya untuk tombol cetak bukti saja */
        .print-image-button {
            display: block;
            padding: 12px 20px;
            background-color: #2d8cf0; /* Warna biru untuk tombol cetak bukti saja */
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px; /* Beri jarak dari tombol sebelumnya */
            text-align: center;
            font-size: 1em;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
            width: 100%; /* Agar lebarnya sama dengan container */
            display: flex; /* Menggunakan flexbox untuk ikon dan teks */
            justify-content: center; /* Pusatkan konten horizontal */
            align-items: center; /* Pusatkan konten vertikal */
            gap: 8px; /* Jarak antara ikon dan teks */
        }

        .print-image-button:hover {
            background-color: #267bd9; /* Warna biru lebih gelap saat hover */
        }

        /* --- Gaya Khusus untuk Cetak (menggunakan JS) --- */
        /* Pastikan elemen cetak tidak memiliki margin/padding yang mengganggu */
        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important; /* Reset flexbox agar cetak normal */
                background-color: #fff !important; /* Pastikan background putih saat cetak */
            }
            /* Semua yang tidak ingin dicetak akan disembunyikan oleh JS,
               tapi kita tambahkan sedikit keamanan di CSS */
            .detail-container,
            .detail-title,
            .detail-info,
            .back-button,
            .print-image-button,
            .no-bukti-text {
                /* Ini akan di-override oleh JS, tapi bagus untuk jaga-jaga */
                display: none !important;
            }
            /* Pastikan gambar bukti pembayaran terlihat jelas saat dicetak */
            .bukti-pembayaran-image {
                display: block !important;
                max-width: 100% !important;
                height: auto !important;
                margin: 0 auto !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="detail-container">
        <h2 class="detail-title">Detail Pembayaran</h2>
        <div class="detail-info">
            <div>
                <strong>ID Pembayaran:</strong>
                <span><?php echo htmlspecialchars($id_pembayaran_tampil); ?></span>
            </div>
            <div>
                <strong>ID Pembelian:</strong>
                <span><?php echo htmlspecialchars($id_pembelian_tampil); ?></span>
            </div>
            <div>
                <strong>Nama Pembeli:</strong>
                <span><?php echo htmlspecialchars($nama_pembeli_tampil); ?></span>
            </div>
            <div>
                <strong>Tipe Rumah:</strong>
                <span><?php echo htmlspecialchars($type_rumah_tampil); ?></span>
            </div>
            <div>
                <strong>Blok Rumah:</strong>
                <span><?php echo htmlspecialchars($nama_blok_tampil); ?></span>
            </div>
            <div>
                <strong>Tanggal Pembayaran:</strong>
                <span><?php echo htmlspecialchars($tanggal_pembayaran_tampil); ?></span>
            </div>
            <div>
                <strong>Jumlah Pembayaran:</strong>
                <span>Rp <?php echo htmlspecialchars($jumlah_pembayaran_tampil); ?></span>
            </div>
            <div>
                <strong>Jenis Transaksi:</strong>
                <span><?php echo htmlspecialchars($jenis_transaksi_tampil); ?></span>
            </div>
            
            <div class="full-width">
                <strong>Bukti Pembayaran:</strong>
                <?php if (!empty($bukti_pembayaran_tampil)) : ?>
                    <img id="buktiPembayaranImg" src="<?php echo htmlspecialchars($bukti_pembayaran_tampil); ?>" alt="Bukti Pembayaran" class="bukti-pembayaran-image">
                    <br><br>
                    <button onclick="printOnlyImage()" class="print-image-button">
                        <i class="fas fa-print"></i> Cetak Bukti Pembayaran 
                    </button>
                <?php else : ?>
                    <p class="no-bukti-text">Tidak ada bukti pembayaran.</p>
                <?php endif; ?>
            </div>
        </div>
        <a href="laporan_pembayaran.php" class="back-button"><i class="fas fa-arrow-left"></i> Kembali ke laporan</a>
    </div>

    <script>
        function printOnlyImage() {
            var printContent = document.getElementById('buktiPembayaranImg');
            if (!printContent) {
                alert("Gambar bukti pembayaran tidak ditemukan.");
                return;
            }

            var originalBody = document.body.innerHTML; // Simpan konten asli body

            // Ganti isi body dengan gambar yang ingin dicetak
            document.body.innerHTML = '<div style="text-align: center; margin: 20px;">' +
                                      '<img src="' + printContent.src + '" style="max-width: 100%; height: auto; border: none; box-shadow: none;">' +
                                      '</div>';

            window.print(); // Panggil fungsi cetak browser

            // Kembalikan isi body setelah cetak selesai (dengan sedikit delay)
            // Event 'afterprint' lebih disarankan jika didukung penuh
            setTimeout(function() {
                document.body.innerHTML = originalBody;
                // Kadang perlu me-reload skrip jika ada event listener lain yang hilang
                // window.location.reload(); // Hanya jika perlu, ini akan me-refresh halaman
            }, 500); // Delay singkat untuk memastikan dialog cetak muncul dan ditutup
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>