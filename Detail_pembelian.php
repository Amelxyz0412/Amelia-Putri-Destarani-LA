<?php
session_start();

// Konfigurasi koneksi database (sesuaikan dengan informasi database Anda)
$host = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = "";       // Ganti dengan password database Anda
$database = "db_perumahan"; // Ganti dengan nama database Anda

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Inisialisasi pesan sukses/error
$pesan_notifikasi = "";
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_notifikasi = "<div class='notification-message notification-success'>".$_SESSION['pesan_sukses']."</div>";
    unset($_SESSION['pesan_sukses']);
} elseif (isset($_SESSION['pesan_error'])) {
    $pesan_notifikasi = "<div class='notification-message notification-error'>".$_SESSION['pesan_error']."</div>";
    unset($_SESSION['pesan_error']);
}

$pembelian_data = null;
$transaksi_data = [];

if (isset($_GET['id'])) {
    $id_pembelian = $conn->real_escape_string($_GET['id']);

    // --- PROSES SUBMIT TRANSAKSI BARU ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_transaksi'])) {
        $jenis_transaksi = $_POST['jenis_transaksi'];
        $tanggal_transaksi_input = $_POST['tanggal_transaksi']; // Ambil tanggal dari form
        $keterangan = $conn->real_escape_string($_POST['keterangan']);
        $jumlah_transaksi = 0; // Ini akan diisi berdasarkan jenis_transaksi
        $new_status_pembelian = '';
        $new_status_unit = '';
        $kwitansi_path = null;

        // Validasi input tanggal
        if (empty($tanggal_transaksi_input)) {
            $_SESSION['pesan_error'] = "Tanggal transaksi harus diisi.";
            header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
            exit();
        }

        // Penanganan upload file kwitansi
        if (isset($_FILES['kwitansi']) && $_FILES['kwitansi']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/kwitansi/"; // Pastikan folder ini ada dan writable
            $file_extension = pathinfo($_FILES['kwitansi']['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('kwitansi_') . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            // Buat folder jika belum ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['kwitansi']['tmp_name'], $target_file)) {
                $kwitansi_path = $target_file;
            } else {
                $_SESSION['pesan_error'] = "Gagal mengunggah file kwitansi.";
                header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
                exit();
            }
        }

        // Dapatkan type_rumah dan blok_rumah dari tb_pembelian untuk update tb_unit
        $stmt_get_unit_info = $conn->prepare("SELECT type_rumah, blok_rumah, status_pembelian FROM tb_pembelian WHERE id_pembelian = ?");
        if (!$stmt_get_unit_info) {
            $_SESSION['pesan_error'] = "Gagal menyiapkan query info unit: " . $conn->error;
            header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
            exit();
        }
        $stmt_get_unit_info->bind_param("s", $id_pembelian);
        $stmt_get_unit_info->execute();
        $result_unit_info = $stmt_get_unit_info->get_result();
        $unit_info = $result_unit_info->fetch_assoc();
        $stmt_get_unit_info->close();

        if (!$unit_info) {
            $_SESSION['pesan_error'] = "Gagal: Informasi unit terkait pembelian tidak ditemukan.";
            header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
            exit();
        }

        $type_rumah_pembelian = $unit_info['type_rumah'];
        $blok_rumah_pembelian = $unit_info['blok_rumah'];
        $current_status_pembelian = $unit_info['status_pembelian'];

        // Tentukan jumlah dan status berdasarkan jenis transaksi
        if ($jenis_transaksi == 'Uang Booking') {
            $jumlah_transaksi = 1000000;
            $new_status_pembelian = 'Terbooking';
            $new_status_unit = 'Terbooking';

            // Cek jika sudah terbooking atau gagal booking, tidak bisa booking lagi
            if ($current_status_pembelian == 'Terbooking') {
                $_SESSION['pesan_error'] = "Pembelian sudah berstatus 'Terbooking'. Tidak bisa menambah 'Uang Booking' lagi.";
                header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
                exit();
            }
            if ($current_status_pembelian == 'Gagal Booking') {
                $_SESSION['pesan_error'] = "Pembelian sudah berstatus 'Gagal Booking'. Tidak bisa menambah 'Uang Booking'.";
                header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
                exit();
            }

        } elseif ($jenis_transaksi == 'Return Uang Booking') {
            $jumlah_transaksi = 950000;
            $new_status_pembelian = 'Gagal Booking';
            $new_status_unit = 'Tersedia';

            // Cek jika statusnya bukan 'Terbooking', tidak bisa melakukan return
            if ($current_status_pembelian != 'Terbooking') {
                $_SESSION['pesan_error'] = "Pembelian belum berstatus 'Terbooking'. Tidak bisa melakukan 'Return Uang Booking'.";
                header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
                exit();
            }
        } else {
            $_SESSION['pesan_error'] = "Jenis transaksi tidak valid.";
            header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
            exit();
        }

        // Mulai transaksi database
        $conn->begin_transaction();

        try {
            // 1. Insert ke tb_transaksi_pembelian (dengan tanggal dan kwitansi)
            $stmt_insert = $conn->prepare("INSERT INTO tb_transaksi_pembelian (id_pembelian, tanggal_transaksi, jenis_transaksi, jumlah_transaksi, keterangan, kwitansi) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt_insert) {
                throw new mysqli_sql_exception("Prepare failed for tb_transaksi_pembelian: (" . $conn->errno . ") " . $conn->error);
            }
            // Perhatikan tipe parameter 's' untuk $kwitansi_path (string)
           $stmt_insert->bind_param("sssdss", $id_pembelian, $tanggal_transaksi_input, $jenis_transaksi, $jumlah_transaksi, $keterangan, $kwitansi_path);
            $stmt_insert->execute();
            if ($stmt_insert->affected_rows === 0) {
                throw new mysqli_sql_exception("Gagal menambahkan transaksi ke tb_transaksi_pembelian.");
            }
            $stmt_insert->close();

            // 2. Update status_pembelian di tb_pembelian
            $stmt_update_pembelian = $conn->prepare("UPDATE tb_pembelian SET status_pembelian = ? WHERE id_pembelian = ?");
            if (!$stmt_update_pembelian) {
                throw new mysqli_sql_exception("Prepare failed for tb_pembelian update: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt_update_pembelian->bind_param("ss", $new_status_pembelian, $id_pembelian);
            $stmt_update_pembelian->execute();
            if ($stmt_update_pembelian->affected_rows === 0) {
                error_log("Peringatan: Status pembelian di tb_pembelian tidak berubah untuk ID: " . $id_pembelian . ". Status mungkin sudah '" . $new_status_pembelian . "'.");
            }
            $stmt_update_pembelian->close();

            // 3. Update status di tb_unit
            $stmt_get_id_rumah = $conn->prepare("SELECT id_rumah FROM tb_rumah WHERE type_rumah = ?");
            if (!$stmt_get_id_rumah) {
                throw new mysqli_sql_exception("Prepare failed for getting id_rumah from tb_rumah: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt_get_id_rumah->bind_param("s", $type_rumah_pembelian);
            $stmt_get_id_rumah->execute();
            $result_id_rumah = $stmt_get_id_rumah->get_result();
            $row_id_rumah = $result_id_rumah->fetch_assoc();
            $stmt_get_id_rumah->close();

            $id_rumah_target = null;
            if ($row_id_rumah) {
                $id_rumah_target = $row_id_rumah['id_rumah'];
            } else {
                throw new mysqli_sql_exception("Gagal menemukan ID Rumah di tb_rumah untuk type: " . $type_rumah_pembelian);
            }

            $expected_current_unit_status = '';
            if ($jenis_transaksi == 'Uang Booking') {
                $expected_current_unit_status = 'Tersedia'; // Jika booking, unit harusnya tersedia
            } elseif ($jenis_transaksi == 'Return Uang Booking') {
                $expected_current_unit_status = 'Terbooking'; // Jika return, unit harusnya terbooking
            }

            // Cari unit dengan status yang diharapkan
            $stmt_find_unit_id = $conn->prepare("SELECT id_unit FROM tb_unit WHERE id_rumah = ? AND nama_blok = ? AND status = ? LIMIT 1");
            if (!$stmt_find_unit_id) {
                throw new mysqli_sql_exception("Prepare failed for finding unit ID in tb_unit: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt_find_unit_id->bind_param("iss", $id_rumah_target, $blok_rumah_pembelian, $expected_current_unit_status);
            $stmt_find_unit_id->execute();
            $result_find_unit_id = $stmt_find_unit_id->get_result();
            $unit_to_update = $result_find_unit_id->fetch_assoc();
            $stmt_find_unit_id->close();

            if ($unit_to_update) {
                $id_unit_yang_akan_diupdate = $unit_to_update['id_unit'];

                $stmt_update_unit = $conn->prepare("UPDATE tb_unit SET status = ? WHERE id_unit = ?");
                if (!$stmt_update_unit) {
                    throw new mysqli_sql_exception("Prepare failed for updating unit status in tb_unit: (" . $conn->errno . ") " . $conn->error);
                }
                $stmt_update_unit->bind_param("si", $new_status_unit, $id_unit_yang_akan_diupdate);
                $stmt_update_unit->execute();

                if ($stmt_update_unit->affected_rows === 0) {
                    error_log("Peringatan: Tidak ada baris yang diperbarui di tb_unit untuk ID Unit: " . $id_unit_yang_akan_diupdate . ". Status mungkin sudah '" . $new_status_unit . "' atau unit tidak ditemukan dengan status diharapkan sebelumnya.");
                }
                $stmt_update_unit->close();
            } else {
                throw new mysqli_sql_exception("Tidak dapat menemukan unit yang sesuai untuk diperbarui (ID Rumah: " . $id_rumah_target . ", Blok: " . $blok_rumah_pembelian . ", Status diharapkan: " . $expected_current_unit_status . "). Status unit mungkin sudah berubah atau data tidak cocok.");
            }

            // Commit transaksi jika semua berhasil
            $conn->commit();
            $_SESSION['pesan_sukses'] = "Transaksi berhasil ditambahkan dan status unit diperbarui!";
            header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
            exit();

        } catch (mysqli_sql_exception $exception) {
            // Rollback jika ada kesalahan
            $conn->rollback();
            $_SESSION['pesan_error'] = "Gagal menambahkan transaksi: " . $exception->getMessage();
            error_log("SQL Exception in detail_pembelian.php: " . $exception->getMessage() . " on line " . $exception->getLine());
            header("Location: detail_pembelian.php?id=" . urlencode($id_pembelian));
            exit();
        }
    }

    // --- AMBIL DETAIL PEMBELIAN ---
    $sql_pembelian = "SELECT id_pembelian, tanggal_pembelian, nama_pembeli, no_ktp, telepon, type_rumah, blok_rumah, status_pembelian
                      FROM tb_pembelian
                      WHERE id_pembelian = ?";
    $stmt_pembelian = $conn->prepare($sql_pembelian);
    $stmt_pembelian->bind_param("s", $id_pembelian);
    $stmt_pembelian->execute();
    $result_pembelian = $stmt_pembelian->get_result();

    if ($result_pembelian->num_rows == 1) {
        $pembelian_data = $result_pembelian->fetch_assoc();
    } else {
        $_SESSION['pesan_error'] = "Data pembelian dengan ID '{$id_pembelian}' tidak ditemukan.";
        header("Location: data_pembelian.php");
        exit();
    }
    $stmt_pembelian->close();

    // --- AMBIL DATA TRANSAKSI TERKAIT (dengan kolom kwitansi) ---
    $sql_transaksi = "SELECT id_transaksi_pembelian, tanggal_transaksi, jenis_transaksi, jumlah_transaksi, keterangan, kwitansi
                      FROM tb_transaksi_pembelian
                      WHERE id_pembelian = ?
                      ORDER BY tanggal_transaksi DESC";
    $stmt_transaksi = $conn->prepare($sql_transaksi);
    $stmt_transaksi->bind_param("s", $id_pembelian);
    $stmt_transaksi->execute();
    $result_transaksi = $stmt_transaksi->get_result();

    while ($row_transaksi = $result_transaksi->fetch_assoc()) {
        $transaksi_data[] = $row_transaksi;
    }
    $stmt_transaksi->close();

} else {
    $_SESSION['pesan_error'] = "ID pembelian tidak valid.";
    header("Location: data_pembelian.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembelian - <?php echo htmlspecialchars($pembelian_data['id_pembelian'] ?? 'Tidak Ditemukan'); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Variabel Warna (disesuaikan dengan tema Anda) */
        :root {
            --primary-green: #117c6b; /* Hijau yang diminta: #117c6b */
            --light-green: #149c88; /* Sedikit lebih terang untuk hover */
            --dark-green: #0e6a5b; /* Sedikit lebih gelap */
            --background-light: #f4f7f6; /* Latar belakang body, sedikit keabu-abuan */
            --background-card: #fff; /* Latar belakang card/kontainer utama */
            --background-info: #fdfdfd; /* Latar belakang info detail */
            --text-dark: #333; /* Warna teks umum yang lebih gelap */
            --text-medium: #555;
            --border-light: #e0e0e0; /* Border ringan */
            --table-header-bg: #f8f8f8; /* Latar belakang header tabel */
            --table-row-even-bg: #fcfcfc; /* Latar belakang baris genap tabel */
            --shadow-light: rgba(0, 0, 0, 0.08);
            --shadow-medium: rgba(0, 0, 0, 0.15);
        }

        /* General Body and Container Styles */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-light);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            justify-content: flex-start;
            align-items: center;
            padding: 30px 0;
            box-sizing: border-box;
        }

        .main-container {
            width: 90%;
            max-width: 1000px;
            background-color: var(--background-card);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px var(--shadow-light);
            box-sizing: border-box;
        }

        /* Header Section */
        .header-section {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-light);
        }

        .header-section h2 {
            font-size: 2.5em;
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        /* Detail Info Section */
        .detail-info {
            background-color: var(--background-info);
            padding: 25px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            margin-bottom: 20px; /* Diperbarui untuk memberi ruang tombol kembali */
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); /* Bayangan lebih ringan */
        }

        .detail-info p {
            margin-bottom: 10px;
            line-height: 1.6;
            color: var(--text-medium);
            font-size: 1.05em;
            display: flex;
            align-items: center;
        }

        .detail-info strong {
            font-weight: 600;
            color: var(--primary-green); /* Warna hijau untuk label */
            width: 200px;
            display: inline-block;
            flex-shrink: 0;
        }

        /* Tombol "Kembali" */
        .btn-back-container {
            margin-bottom: 30px; /* Jarak antara tombol kembali dan tabel */
            display: flex; /* Untuk menempatkan tombol di kiri */
            justify-content: flex-start; /* Mengatur posisi ke kiri */
        }

        .btn-back {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #6c757d; /* Warna abu-abu yang lebih netral */
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Transaksi Section */
        .transaksi-section {
            margin-top: 40px; /* Jarak dari tombol di atasnya (tidak lagi dari button group) */
            padding-top: 30px; /* Padding atas untuk pemisah */
            border-top: 2px solid var(--border-light);
        }

        .transaksi-header {
            display: flex;
            justify-content: space-between; /* Untuk meletakkan judul dan tombol di ujung */
            align-items: center;
            margin-bottom: 25px;
        }

        .transaksi-header h3 {
            font-size: 2em;
            color: var(--text-dark);
            font-weight: 700;
            margin: 0;
        }

        /* Tombol Tambah Transaksi (di dalam transaksi-header) */
        .btn-add {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: var(--primary-green); /* Warna hijau yang diminta */
            color: white;
        }

        .btn-add:hover:not([disabled]) { /* Hanya hover jika tidak disabled */
            background-color: var(--light-green);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(17, 124, 107, 0.3); /* Bayangan hijau dari warna baru */
        }

        .btn-add[disabled] {
            background-color: #cccccc; /* Warna abu-abu untuk disabled */
            cursor: not-allowed;
            box-shadow: none;
            opacity: 0.7;
        }

        .transaksi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .transaksi-table th, .transaksi-table td {
            padding: 12px 15px;
            text-align: left;
            font-size: 0.9em;
            border-bottom: 1px solid #f0f0f0;
        }

        .transaksi-table th {
            background-color: var(--table-header-bg);
            font-weight: 700;
            color: var(--text-dark);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .transaksi-table tbody tr:last-child td {
            border-bottom: none;
        }

        .transaksi-table tbody tr:nth-child(even) {
            background-color: var(--table-row-even-bg);
        }

        .transaksi-table tbody tr:hover {
            background-color: #eef8ee; /* Sedikit highlight hijau saat hover */
        }

        .transaksi-table td:nth-child(1) { text-align: center; } /* No */
        .transaksi-table td:nth-child(4) { text-align: right; } /* Jumlah */
        .transaksi-table td:nth-child(6) { text-align: center; } /* Kwitansi */

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background-color: var(--background-card);
            padding: 35px;
            border-radius: 12px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 10px 30px var(--shadow-medium);
            position: relative;
            animation: slideIn 0.3s ease-out;
        }

        .close-button {
            color: #888;
            font-size: 32px;
            font-weight: bold;
            position: absolute;
            top: 15px;
            right: 20px;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .close-button:hover,
        .close-button:focus {
            color: #333;
        }

        .modal-content h3 {
            margin-top: 0;
            color: var(--primary-green); /* Warna hijau untuk judul modal */
            font-size: 2em;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
            font-size: 0.95em;
        }

        .form-group select,
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-group input[type="file"] {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .form-group select:focus,
        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="file"]:focus,
        .form-group textarea:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(17, 124, 107, 0.2); /* Bayangan hijau dari warna baru saat fokus */
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 90px;
        }

        .form-actions {
            text-align: right;
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-actions button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .form-actions .btn-submit {
            background-color: var(--primary-green);
            color: white;
        }

        .form-actions .btn-submit:hover {
            background-color: var(--light-green);
            transform: translateY(-1px);
        }

        .form-actions .btn-cancel {
            background-color: #dc3545; /* Tetap merah untuk cancel */
            color: white;
        }

        .form-actions .btn-cancel:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* Notifikasi */
        .notification-message {
            padding: 12px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            font-size: 1.05em;
            animation: fadeIn 0.5s ease-out;
        }
        .notification-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .notification-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            font-size: 0.9em; /* Sedikit lebih kecil untuk error */
        }

        /* Kwitansi Link */
        .kwitansi-link {
            display: inline-block;
            margin-top: 5px;
            color: var(--primary-green); /* Gunakan warna hijau utama */
            text-decoration: none;
            font-weight: 500;
        }
        .kwitansi-link:hover {
            text-decoration: underline;
        }

        /* Kwitansi Preview in Modal */
        #kwitansi_preview {
            margin-top: 15px;
            border: 1px dashed #ccc;
            padding: 10px;
            min-height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        #kwitansi_preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        #kwitansi_preview p {
            color: #888;
            font-style: italic;
            margin: 0;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-section">
            <h2>Detail Pembelian</h2>
        </div>

        <?php if (!empty($pesan_notifikasi)) echo $pesan_notifikasi; ?>

        <?php if ($pembelian_data): ?>
            <div class="detail-info">
                <p><strong>ID Pembelian:</strong> <?php echo htmlspecialchars($pembelian_data["id_pembelian"]); ?></p>
                <p><strong>Tanggal Pembelian:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($pembelian_data["tanggal_pembelian"]))); ?></p>
                <p><strong>Nama Pembeli:</strong> <?php echo htmlspecialchars($pembelian_data["nama_pembeli"]); ?></p>
                <p><strong>No KTP:</strong> <?php echo htmlspecialchars($pembelian_data["no_ktp"]); ?></p>
                <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($pembelian_data["telepon"]); ?></p>
                <p><strong>Type Rumah:</strong> <?php echo htmlspecialchars($pembelian_data["type_rumah"]); ?></p>
                <p><strong>Blok Rumah:</strong> <?php echo htmlspecialchars($pembelian_data["blok_rumah"]); ?></p>
                <p><strong>Status Pembelian:</strong> <?php echo htmlspecialchars($pembelian_data["status_pembelian"]); ?></p>
            </div>

            <div class="btn-back-container">
                <a href="data_pembelian.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <div class="transaksi-section">
                <div class="transaksi-header">
                    <h3>Riwayat Transaksi</h3>
                    <?php
                    // Pastikan $pembelian_data sudah ada sebelum mengaksesnya
                    $isButtonDisabled = '';
                    $buttonTitle = '';
                    $buttonText = '<i class="fas fa-plus"></i> Tambah Transaksi';

                    if ($pembelian_data && $pembelian_data['status_pembelian'] == 'Gagal Booking') {
                        $isButtonDisabled = 'disabled';
                        $buttonTitle = 'Tidak dapat menambah transaksi karena status pembelian Gagal Booking.';
                        $buttonText = 'Tidak Dapat Menambah Transaksi (Gagal Booking)';
                    }
                    ?>
                    <button id="addTransaksiBtn" class="btn btn-add" <?php echo $isButtonDisabled; ?> title="<?php echo $buttonTitle; ?>">
                        <?php echo $buttonText; ?>
                    </button>
                </div>
                
                <table class="transaksi-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Transaksi</th>
                            <th>Jenis Transaksi</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Kwitansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transaksi_data)): ?>
                            <?php $no_transaksi = 1; ?>
                            <?php foreach ($transaksi_data as $transaksi): ?>
                                <tr>
                                    <td><?php echo $no_transaksi++; ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($transaksi["tanggal_transaksi"]))); ?></td>
                                    <td><?php echo htmlspecialchars($transaksi["jenis_transaksi"]); ?></td>
                                    <td><?php echo 'Rp ' . number_format($transaksi["jumlah_transaksi"], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($transaksi["keterangan"]); ?></td>
                                    <td>
                                        <?php if (!empty($transaksi["kwitansi"])): ?>
                                            <a href="<?php echo htmlspecialchars($transaksi["kwitansi"]); ?>" target="_blank" class="kwitansi-link">Lihat Kwitansi <i class="fas fa-external-link-alt"></i></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Belum ada riwayat transaksi untuk pembelian ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: red;">Data pembelian tidak ditemukan atau ID tidak valid.</p>
        <?php endif; ?>
    </div>

    <div id="transaksiModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <h3>Tambah Transaksi Baru</h3>
            <form action="detail_pembelian.php?id=<?php echo urlencode($id_pembelian ?? ''); ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tanggal_transaksi">Tanggal Transaksi:</label>
                    <input type="date" id="tanggal_transaksi" name="tanggal_transaksi" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="jenis_transaksi">Jenis Transaksi:</label>
                    <select id="jenis_transaksi" name="jenis_transaksi" required>
                        <option value="">-- Pilih Jenis Transaksi --</option>
                        <option value="Uang Booking">Uang Booking</option>
                        <option value="Return Uang Booking">Return Uang Booking</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jumlah_transaksi_display">Jumlah (Otomatis):</label>
                    <input type="text" id="jumlah_transaksi_display" value="" readonly>
                    <input type="hidden" id="jumlah_transaksi" name="jumlah_transaksi">
                </div>
                <div class="form-group">
                    <label for="keterangan">Keterangan (Opsional):</label>
                    <textarea id="keterangan" name="keterangan" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="kwitansi">Foto Kwitansi (Opsional):</label>
                    <input type="file" id="kwitansi" name="kwitansi" accept="image/*">
                    <div id="kwitansi_preview" style="margin-top: 15px; text-align: center;">
                        <p>Tidak ada gambar yang dipilih.</p>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" name="submit_transaksi" class="btn-submit">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("transaksiModal");

        // Get the button that opens the modal
        var btn = document.getElementById("addTransaksiBtn");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close-button")[0];

        // Get the cancel button inside the modal
        var cancelBtn = document.querySelector(".modal-content .btn-cancel");

        // Get the kwitansi input and preview div
        const kwitansiInput = document.getElementById('kwitansi');
        const kwitansiPreview = document.getElementById('kwitansi_preview');

        // When the user clicks the button, open the modal
        // This event listener will only fire if the button is not disabled
        if (btn) {
            btn.onclick = function() {
                if (!this.disabled) { // Check if the button is not disabled
                    modal.style.display = "flex";
                }
            }
        }

        // Close modal function
        function closeModal() {
            modal.style.display = "none";
            // Reset form fields
            document.getElementById('tanggal_transaksi').value = '<?php echo date('Y-m-d'); ?>'; // Reset to current date
            document.getElementById('jenis_transaksi').value = '';
            document.getElementById('jumlah_transaksi_display').value = '';
            document.getElementById('jumlah_transaksi').value = '';
            document.getElementById('keterangan').value = '';
            document.getElementById('kwitansi').value = ''; // Reset file input
            kwitansiPreview.innerHTML = '<p>Tidak ada gambar yang dipilih.</p>'; // Clear image preview
        }

        // When the user clicks on <span> (x), close the modal
        if (span) {
            span.onclick = function() {
                closeModal();
            }
        }

        // When the user clicks on the cancel button, close the modal
        if (cancelBtn) {
            cancelBtn.onclick = function() {
                closeModal();
            }
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // Auto-fill jumlah_transaksi based on jenis_transaksi
        document.getElementById('jenis_transaksi').addEventListener('change', function() {
            const jenis = this.value;
            let jumlah = 0;
            if (jenis === 'Uang Booking') {
                jumlah = 1000000;
            } else if (jenis === 'Return Uang Booking') {
                jumlah = 950000;
            }
            document.getElementById('jumlah_transaksi').value = jumlah; // Set hidden input value
            document.getElementById('jumlah_transaksi_display').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(jumlah);
        });

        // Kwitansi Preview Functionality
        kwitansiInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.type.startsWith('image/')) { // Basic validation for image type
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        kwitansiPreview.innerHTML = `<img src="${e.target.result}" alt="Pratinjau Kwitansi">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    kwitansiPreview.innerHTML = '<p style="color: red;">File yang dipilih bukan gambar.</p>';
                    kwitansiInput.value = ''; // Clear the file input if it's not an image
                }
            } else {
                kwitansiPreview.innerHTML = '<p>Tidak ada gambar yang dipilih.</p>';
            }
        });

        // Initialize display amount on page load if a type is pre-selected (unlikely for new transaction)
        document.addEventListener('DOMContentLoaded', function() {
            const jenisTransaksiSelect = document.getElementById('jenis_transaksi');
            // Trigger change event if there's a pre-selected value
            if (jenisTransaksiSelect.value) {
                jenisTransaksiSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>