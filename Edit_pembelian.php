<?php
session_start();

// Koneksi ke Database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$pesan = "";
$data_pembelian = null;
$old_id_unit_from_db_get_request = ''; // ID unit saat data pertama kali dimuat (GET request)
$old_status_unit_from_db_get_request = ''; // Status unit saat data pertama kali dimuat (GET request)
$old_status_pembelian_from_db_get_request = ''; // Status pembelian saat data pertama kali dimuat (GET request)
$is_failed_booking = false; // Flag untuk menandakan status "Gagal Booking"

// Fungsi untuk menghasilkan ID awal
function generateInitialPembelianID($type_rumah_text, $blok_rumah) {
    $type_prefix = strtoupper(substr($type_rumah_text, 0, 1));
    return $type_prefix . strtoupper($blok_rumah);
}

// Fungsi untuk menghasilkan ID dengan timestamp jika ada duplikasi
function generateUniquePembelianID($initial_id, $conn) {
    $counter = 1;
    $new_id = $initial_id;
    while (true) {
        $check_sql = "SELECT id_pembelian FROM tb_pembelian WHERE id_pembelian = ?";
        $stmt_check = $conn->prepare($check_sql);
        if (!$stmt_check) {
            return $initial_id . '-' . date("YmdHis"); // Fallback
        }
        $stmt_check->bind_param("s", $new_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        if ($check_result->num_rows == 0) {
            $stmt_check->close();
            return $new_id;
        }
        $stmt_check->close();
        // Append a timestamp and counter to ensure uniqueness
        $new_id = $initial_id . "-" . date("YmdHis") . "-" . $counter++;
    }
}


// --- Ambil data pembelian yang akan diedit (INITIAL LOAD atau setelah redirect POST) ---
$id_pembelian_param = $_GET['id_pembelian'] ?? $_GET['id'] ?? null;

if ($id_pembelian_param) {
    $id_pembelian_to_load = $id_pembelian_param;

    // Gabungkan query untuk mengambil data pembelian DAN status unitnya
    $stmt_get_data = $conn->prepare("SELECT p.*, u.status AS status_unit_pembelian FROM tb_pembelian p JOIN tb_unit u ON p.id_unit = u.id_unit WHERE p.id_pembelian = ?");
    if (!$stmt_get_data) {
        $pesan = "<div class='error-message'>Error preparing statement (get_data_pembelian): " . $conn->error . "</div>";
    } else {
        $stmt_get_data->bind_param("s", $id_pembelian_to_load);
        $stmt_get_data->execute();
        $result_data = $stmt_get_data->get_result();
        if ($result_data->num_rows > 0) {
            $data_pembelian = $result_data->fetch_assoc();
            // Simpan status unit lama untuk validasi di JS dan POST
            $old_id_unit_from_db_get_request = $data_pembelian['id_unit']; // Simpan ID unit lama
            $old_status_unit_from_db_get_request = $data_pembelian['status_unit_pembelian'];
            // Simpan juga status pembelian lama untuk keperluan logika lain di POST
            $old_status_pembelian_from_db_get_request = $data_pembelian['status_pembelian'];

            // Set flag is_failed_booking
            if (strtolower($old_status_pembelian_from_db_get_request) === 'gagal booking') {
                $is_failed_booking = true;
                $pesan = "<div class='warning-message'>Data pembelian ini berstatus 'Gagal Booking' dan tidak dapat diedit.</div>";
            }

        } else {
            $pesan = "<div class='error-message'>Data pembelian tidak ditemukan.</div>";
            $data_pembelian = null;
        }
        $stmt_get_data->close();
    }
} else {
    $pesan = "<div class='error-message'>ID Pembelian tidak diberikan. Silakan pilih data pembelian yang ingin diedit dari halaman <a href='data_pembelian.php'>Data Pemesanan</a>.</div>";
}

// --- Proses form jika disubmit (Update Data) ---
// Tambahkan kondisi agar form tidak diproses jika is_failed_booking adalah true
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit'])) {
    $old_id_pembelian = $_POST['old_id_pembelian'] ?? '';
    $old_id_unit_posted = $_POST['old_id_unit'] ?? ''; // ID unit yang terikat dengan pembelian INI, dari hidden field

    // Fetch original data again to get the most current state from DB
    // This is crucial because $data_pembelian from GET might be outdated if form validation fails and user resubmits.
    $original_pembelian_data = null;
    $original_unit_status = '';
    $original_pembelian_status = '';

    $stmt_get_original_data = $conn->prepare("SELECT p.*, u.status AS status_unit_pembelian FROM tb_pembelian p JOIN tb_unit u ON p.id_unit = u.id_unit WHERE p.id_pembelian = ?");
    if ($stmt_get_original_data) {
        $stmt_get_original_data->bind_param("s", $old_id_pembelian);
        $stmt_get_original_data->execute();
        $result_original_data = $stmt_get_original_data->get_result();
        if ($result_original_data->num_rows > 0) {
            $original_pembelian_data = $result_original_data->fetch_assoc();
            $original_unit_status = $original_pembelian_data['status_unit_pembelian'];
            $original_pembelian_status = $original_pembelian_data['status_pembelian'];
        }
        $stmt_get_original_data->close();
    }

    if (!$original_pembelian_data) {
        $_SESSION['pesan'] = "<div class='error-message'>Data pembelian asli tidak ditemukan. Tidak dapat melakukan update.</div>";
        header("Location: data_pembelian.php"); // Redirect back
        exit();
    }

    // Pastikan bahwa jika data_pembelian memiliki status 'Gagal Booking', proses POST dihentikan.
    if (strtolower($original_pembelian_status) === 'gagal booking') {
        $_SESSION['pesan'] = "<div class='error-message'>Data pembelian berstatus 'Gagal Booking' tidak dapat diedit.</div>";
        header("Location: " . $_SERVER["PHP_SELF"] . "?id_pembelian=" . urlencode($old_id_pembelian));
        exit();
    }


    // Ambil data dari form
    $tanggal_pembelian = $_POST['tanggal_pembelian'] ?? '';
    $nama_pembeli = $_POST['nama_pembeli'] ?? '';
    $no_ktp = $_POST['no_ktp'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $id_rumah_selected = $_POST['id_rumah'] ?? '';
    $id_unit_selected = $_POST['id_unit'] ?? '';

    // If Type and Blok were disabled (due to original_unit_status being 'Terjual'),
    // use the original values from $original_pembelian_data.
    // This prevents manipulation if JS fields were disabled.
    if (strtolower($original_unit_status) === 'terjual') {
        $id_rumah_selected = $original_pembelian_data['id_rumah'];
        $id_unit_selected = $original_pembelian_data['id_unit'];
    }

    // Basic form field validation
    if (empty($tanggal_pembelian) || empty($nama_pembeli) || empty($no_ktp) || empty($telepon) || empty($alamat) || empty($id_rumah_selected) || empty($id_unit_selected)) {
        $pesan = "<div class='error-message'>Semua field harus diisi.</div>";
    } else {
        // --- Ambil Type Rumah dan Blok Rumah baru ---
        $blok_rumah = '';
        $type_rumah_text = '';
        $status_unit_newly_selected_from_db = ''; // Status unit dari unit yang dipilih di form (bisa Tersedia, Terbooking, Terjual)

        // Get type_rumah_text
        $stmt_get_type = $conn->prepare("SELECT type_rumah FROM tb_rumah WHERE id_rumah = ?");
        if (!$stmt_get_type) { $pesan = "<div class='error-message'>Error preparing statement (get_type): " . $conn->error . "</div>"; }
        else {
            $stmt_get_type->bind_param("i", $id_rumah_selected);
            $stmt_get_type->execute();
            $result_get_type = $stmt_get_type->get_result();
            if ($row_type = $result_get_type->fetch_assoc()) {
                $type_rumah_text = $row_type['type_rumah'];
            } else {
                $pesan = "<div class='error-message'>Type Rumah tidak ditemukan.</div>";
            }
            $stmt_get_type->close();
        }

        // Get nama_blok and status for the newly selected unit
        if (empty($pesan)) {
            $stmt_get_blok = $conn->prepare("SELECT nama_blok, status FROM tb_unit WHERE id_unit = ?");
            if (!$stmt_get_blok) { $pesan = "<div class='error-message'>Error preparing statement (get_blok): " . $conn->error . "</div>"; }
            else {
                $stmt_get_blok->bind_param("i", $id_unit_selected);
                $stmt_get_blok->execute();
                $result_get_blok = $stmt_get_blok->get_result();
                if ($row_blok = $result_get_blok->fetch_assoc()) {
                    $blok_rumah = $row_blok['nama_blok'];
                    $status_unit_newly_selected_from_db = $row_blok['status'];
                } else {
                    $pesan = "<div class='error-message'>Blok Rumah tidak ditemukan.</div>";
                }
                $stmt_get_blok->close();
            }
        }

        if (empty($type_rumah_text) || empty($blok_rumah)) {
            if (empty($pesan)) $pesan = "<div class='error-message'>Type Rumah atau Blok Rumah tidak valid.</div>";
        }


        // --- VALIDASI LOGIKA BISNIS ---
        // Jika status unit asli adalah 'Terjual', maka Type/Blok tidak boleh diubah sama sekali.
        if (strtolower($original_unit_status) === 'terjual') {
            if ($id_rumah_selected != $original_pembelian_data['id_rumah'] || $id_unit_selected != $original_pembelian_data['id_unit']) {
                $pesan = "<div class='error-message'>Type Rumah dan Blok Rumah tidak dapat diubah jika status unit adalah 'Terjual'.</div>";
            }
        }

        // Jika unit diubah (id_unit_selected BUKAN original_pembelian_data['id_unit'])
        if (empty($pesan) && $id_unit_selected != $original_pembelian_data['id_unit']) {
            // Unit baru harus 'Tersedia'
            if (strtolower($status_unit_newly_selected_from_db) !== 'tersedia') {
                $pesan = "<div class='error-message'>Blok Rumah yang dipilih ('" . htmlspecialchars($blok_rumah) . "') tidak tersedia. Statusnya adalah '" . htmlspecialchars($status_unit_newly_selected_from_db) . "'. Silakan pilih unit 'Tersedia'.</div>";
            }
        }


        // Lanjutkan hanya jika tidak ada pesan error
        if (empty($pesan)) {
            // --- Penentuan ID Pembelian Baru ---
            $new_id_pembelian = generateInitialPembelianID($type_rumah_text, $blok_rumah);
            if ($new_id_pembelian !== $old_id_pembelian) { // Hanya generate baru jika prefix berubah
                $new_id_pembelian = generateUniquePembelianID($new_id_pembelian, $conn);
                $pesan .= "<div class='warning-message'>ID pembelian baru dihasilkan: " . htmlspecialchars($new_id_pembelian) . ".</div>";
            } else {
                $new_id_pembelian = $old_id_pembelian; // Gunakan ID lama jika tidak ada perubahan type/blok
            }

            // --- Penentuan status_pembelian_new dan status_unit_update ---
            $status_pembelian_new = '';
            $desired_status_for_selected_unit = ''; // Status yang ingin kita atur untuk unit yang dipilih di tb_unit

            // Logika untuk menentukan status pembelian baru dan status unit yang diinginkan
            if ($id_unit_selected != $original_pembelian_data['id_unit']) {
                // KASUS 1: Unit diubah
                // Unit lama dikembalikan ke 'Tersedia' (sudah di bawah, di dalam try-catch)
                // Unit baru menjadi 'Terbooking'
                $status_pembelian_new = 'Terbooking';
                $desired_status_for_selected_unit = 'Terbooking';
            } else {
                // KASUS 2: Unit tidak diubah
                // Status pembelian dan status unit harus tetap seperti aslinya
                $status_pembelian_new = $original_pembelian_status;
                $desired_status_for_selected_unit = $original_unit_status;
            }

            // Pastikan jika status pembelian asli 'Kosong' atau 'Batal', dan unit tidak diubah,
            // maka status pembelian baru menjadi 'Terbooking' dan unit menjadi 'Terbooking'.
            // Ini untuk re-aktivasi pembelian yang sebelumnya dibatalkan/kosong.
            if (strtolower($original_pembelian_status) === 'kosong' || strtolower($original_pembelian_status) === 'batal') {
                 // Jika unitnya adalah unit yang sama dan statusnya masih Tersedia, kita bisa ubah jadi Terbooking
                 if (strtolower($status_unit_newly_selected_from_db) === 'tersedia' && $id_unit_selected == $original_pembelian_data['id_unit']) {
                    $status_pembelian_new = 'Terbooking';
                    $desired_status_for_selected_unit = 'Terbooking';
                 }
            }


            // Mulai transaksi database
            $conn->begin_transaction();

            try {
                // Query untuk update data pembelian
                $stmt_update_pembelian = $conn->prepare("UPDATE tb_pembelian SET
                                                            id_pembelian = ?,
                                                            tanggal_pembelian = ?,
                                                            nama_pembeli = ?,
                                                            no_ktp = ?,
                                                            telepon = ?,
                                                            alamat = ?,
                                                            id_rumah = ?,
                                                            id_unit = ?,
                                                            type_rumah = ?,
                                                            blok_rumah = ?,
                                                            status_pembelian = ?
                                                            WHERE id_pembelian = ?");

                if (!$stmt_update_pembelian) {
                    throw new Exception("Error preparing statement (update_pembelian): " . $conn->error);
                }
                $stmt_update_pembelian->bind_param("ssssssiissss",
                    $new_id_pembelian,
                    $tanggal_pembelian,
                    $nama_pembeli,
                    $no_ktp,
                    $telepon,
                    $alamat,
                    $id_rumah_selected,
                    $id_unit_selected,
                    $type_rumah_text,
                    $blok_rumah,
                    $status_pembelian_new,
                    $old_id_pembelian // Parameter WHERE
                );

                $stmt_update_pembelian->execute();
                $stmt_update_pembelian->close();


                // --- Logika Update Status di tb_unit ---

                // KONDISI 1: Unit lama perlu dikembalikan ke 'Tersedia' jika unit berubah
                // ATAU jika status pembelian lama adalah 'Gagal Booking', 'Kosong', atau 'Batal'
                // dan unit lama bukan yang TERJUAL, kita kembalikan statusnya ke 'Tersedia'.
                // Jika unit lama 'Terjual' (status unitnya), biarkan saja.
                // Jika unit tidak berubah, dan statusnya bukan Terjual, dan pembelian tidak di-reaktivasi, tidak perlu update unit lama.
                if ($old_id_unit_posted != $id_unit_selected) {
                    // Unit diubah, kembalikan unit lama ke 'Tersedia'
                    // Kecuali jika unit lama itu sendiri sudah 'Terjual' (ini skenario tidak mungkin jika validasi awal bekerja)
                    if (strtolower($original_unit_status) !== 'terjual') { // Pastikan unit lama bukan 'Terjual'
                        $stmt_reset_old_unit = $conn->prepare("UPDATE tb_unit SET status = 'Tersedia' WHERE id_unit = ?");
                        if (!$stmt_reset_old_unit) {
                            throw new Exception("Error preparing statement (reset_old_unit): " . $conn->error);
                        }
                        $stmt_reset_old_unit->bind_param("i", $old_id_unit_posted);
                        $stmt_reset_old_unit->execute();
                        $stmt_reset_old_unit->close();
                    }
                }

                // KONDISI 2: Unit yang baru dipilih perlu di-update statusnya
                // Hanya update jika status aktual berbeda dari status yang diinginkan
                // DAN unit baru ini bukan unit yang statusnya 'Terjual'
                // PENTING: Ambil status TERKINI dari DB untuk unit yang baru dipilih sebelum update
                $current_actual_status_selected_unit = '';
                $stmt_get_current_actual_status = $conn->prepare("SELECT status FROM tb_unit WHERE id_unit = ?");
                if (!$stmt_get_current_actual_status) {
                    throw new Exception("Error preparing statement (get_current_actual_status): " . $conn->error);
                }
                $stmt_get_current_actual_status->bind_param("i", $id_unit_selected);
                $stmt_get_current_actual_status->execute();
                $result_current_actual_status = $stmt_get_current_actual_status->get_result();
                if ($row_current_actual_status = $result_current_actual_status->fetch_assoc()) {
                    $current_actual_status_selected_unit = $row_current_actual_status['status'];
                }
                $stmt_get_current_actual_status->close();

                // Hanya update jika status aktual berbeda dari status yang diinginkan
                // Dan unit yang dipilih saat ini BUKAN 'Terjual' (karena Terjual tidak boleh diubah melalui form ini)
                if (strtolower($current_actual_status_selected_unit) !== strtolower($desired_status_for_selected_unit) && strtolower($current_actual_status_selected_unit) !== 'terjual') {
                     $stmt_update_new_unit = $conn->prepare("UPDATE tb_unit SET status = ? WHERE id_unit = ?");
                     if (!$stmt_update_new_unit) {
                         throw new Exception("Error preparing statement (update_new_unit_status): " . $conn->error);
                     }
                     $stmt_update_new_unit->bind_param("si", $desired_status_for_selected_unit, $id_unit_selected);
                     $stmt_update_new_unit->execute();
                     $stmt_update_new_unit->close();
                }


                // --- Sinkronisasi dengan tb_pembayaran ---
                // 1. Update id_pembelian di tb_pembayaran jika id_pembelian berubah
                if ($old_id_pembelian !== $new_id_pembelian) {
                    $stmt_update_pembayaran_id = $conn->prepare("UPDATE tb_pembayaran SET id_pembelian = ? WHERE id_pembelian = ?");
                    if (!$stmt_update_pembayaran_id) {
                        throw new Exception("Error preparing statement (update_pembayaran_id): " . $conn->error);
                    }
                    $stmt_update_pembayaran_id->bind_param("ss", $new_id_pembelian, $old_id_pembelian);
                    $stmt_update_pembayaran_id->execute();
                    $stmt_update_pembayaran_id->close();
                }

                // 2. Update nama_pembeli di tb_pembayaran
                $stmt_update_pembayaran_nama = $conn->prepare("UPDATE tb_pembayaran SET nama_pembeli = ? WHERE id_pembelian = ?");
                if (!$stmt_update_pembayaran_nama) {
                    throw new Exception("Error preparing statement (update_pembayaran_nama): " . $conn->error);
                }
                $stmt_update_pembayaran_nama->bind_param("ss", $nama_pembeli, $new_id_pembelian); // Use new_id_pembelian
                $stmt_update_pembayaran_nama->execute();
                $stmt_update_pembayaran_nama->close();


                $conn->commit(); // Commit transaksi jika semua berhasil
                $_SESSION['pesan'] = "<div class='success-message'>Data pembelian berhasil diperbarui dengan ID: " . htmlspecialchars($new_id_pembelian) . ".</div>";
                header("Location: data_pembelian.php"); // Redirect ke halaman daftar
                exit();

            } catch (Exception $e) {
                $conn->rollback(); // Rollback jika ada kesalahan
                $_SESSION['pesan'] = "<div class='error-message'>Error saat menyimpan data: " . $e->getMessage() . "</div>";
                // Kembali ke halaman ini untuk menampilkan error, sertakan ID pembelian agar form tetap terisi
                header("Location: " . $_SERVER["PHP_SELF"] . "?id_pembelian=" . urlencode($old_id_pembelian));
                exit();
            }
        }
    }
}


// Query untuk mengambil data type rumah dari tb_rumah
$sql_type_rumah = "SELECT id_rumah, type_rumah FROM tb_rumah ORDER BY type_rumah ASC";
$result_type_rumah = $conn->query($sql_type_rumah);

// Tangani pesan dari session setelah redirect
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']); // Hapus pesan setelah ditampilkan
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Pembelian</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ... (CSS Anda tetap sama) ... */
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

        .form-scroll-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow-y: auto; /* Enable vertical scrolling */
            max-height: 70vh; /* Set a maximum height for the form container */
        }

        .form-scroll-container h2 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }

        .form-group input[type="date"],
        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
            box-sizing: border-box;
        }
        /* Style for read-only/disabled fields */
        .form-group input[readonly],
        .form-group select[disabled],
        .form-group textarea[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .form-group textarea {
            resize: vertical;
        }

        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="currentColor" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position-x: 95%;
            background-position-y: center;
            padding-right: 25px;
        }

        .button-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .submit-button, .back-button {
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            margin-top: 10px;
            line-height: 1;
            box-sizing: border-box;
            display: inline-block;
            height: 38px;
            vertical-align: middle;
            font-family: 'Segoe UI', sans-serif;
            min-width: 100px;
            text-align: center;
        }

        .submit-button {
            background-color: #117c6b;
            color: white;
            border: 1px solid transparent;
        }

        .submit-button:hover {
            background-color: #0d6658;
        }

        /* Style for disabled submit button */
        .submit-button[disabled] {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .back-button {
            background-color: #f9f9f9;
            color: #333;
            border: 1px solid #ccc;
        }

        .back-button:hover {
            background-color: #e0e0e0;
        }

        /* Pesan error dan sukses */
        .error-message {
            color: red;
            background-color: #ffe0e0;
            border: 1px solid red;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .success-message {
            color: green;
            background-color: #e0ffe0;
            border: 1px solid green;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .warning-message {
            color: orange;
            background-color: #fffbe0;
            border: 1px solid orange;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 10px;
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
                    <li><a href="Dashboard_admin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="data_rumah.php"><i class="fas fa-home"></i> <span>Data Rumah</span></a></li>
                    <li><a href="kotak_masuk.php"><i class="fas fa-comments"></i> <span>Kotak Masuk</span></a></li>
                    <li><a href="data_pembelian.php" class="active"><i class="fas fa-file-invoice"></i> <span>Data Pembelian</span></a></li>
                    <li><a href="data_pembayaran.php"><i class="fas fa-money-bill-alt"></i> <span>Data Pembayaran</span></a></li>
                    <li class="logout"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <h2>Edit Data Pembelian</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span>
                </div>
            </header>
            <div class="content-area">
                <div class="form-scroll-container">
                    <h2>Form Edit Data Pembelian</h2>
                    <?php
                    // Tampilkan pesan jika ada
                    if (!empty($pesan)) {
                        if (strpos($pesan, 'berhasil diperbarui') !== false) {
                            echo "<div class='success-message'>" . strip_tags($pesan) . "</div>";
                        } elseif (strpos($pesan, 'gagal') !== false || strpos($pesan, 'Error') !== false || strpos($pesan, 'tidak valid') !== false) {
                            echo "<div class='error-message'>" . strip_tags($pesan) . "</div>";
                        } elseif (strpos($pesan, 'sudah ada') !== false || strpos($pesan, 'dihasilkan') !== false || strpos($pesan, 'Gagal Booking') !== false) {
                            echo "<div class='warning-message'>" . strip_tags($pesan) . "</div>";
                        } else {
                            echo $pesan;
                        }
                    }

                    // Tampilkan form hanya jika data pembelian ditemukan
                    if ($data_pembelian) :
                    ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id_pembelian=' . urlencode($data_pembelian['id_pembelian']); ?>">
                        <input type="hidden" name="old_id_pembelian" value="<?php echo htmlspecialchars($data_pembelian['id_pembelian']); ?>">
                        <input type="hidden" name="old_id_unit" value="<?php echo htmlspecialchars($data_pembelian['id_unit']); ?>">
                        <input type="hidden" name="old_status_unit_pembelian" value="<?php echo htmlspecialchars($old_status_unit_from_db_get_request); ?>">
                        <input type="hidden" name="old_status_pembelian_posted" value="<?php echo htmlspecialchars($old_status_pembelian_from_db_get_request); ?>">


                        <div class="form-group">
                            <label for="tanggal_pembelian">Tanggal Pembelian:</label>
                           <input type="date" id="tanggal_pembelian" name="tanggal_pembelian" value="<?php echo htmlspecialchars($data_pembelian['tanggal_pembelian'] ?? date('Y-m-d')); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="nama_pembeli">Nama Pembeli:</label>
                            <input type="text" id="nama_pembeli" name="nama_pembeli" value="<?php echo htmlspecialchars($data_pembelian['nama_pembeli']); ?>" <?php echo $is_failed_booking ? 'readonly disabled' : ''; ?> required>
                        </div>
                        <div class="form-group">
                            <label for="no_ktp">No. KTP:</label>
                            <input type="text" id="no_ktp" name="no_ktp" value="<?php echo htmlspecialchars($data_pembelian['no_ktp']); ?>" <?php echo $is_failed_booking ? 'readonly disabled' : ''; ?> required>
                        </div>
                        <div class="form-group">
                            <label for="telepon">Telepon:</label>
                            <input type="tel" id="telepon" name="telepon" value="<?php echo htmlspecialchars($data_pembelian['telepon']); ?>" <?php echo $is_failed_booking ? 'readonly disabled' : ''; ?> required>
                        </div>
                        <div class="form-group">
                            <label for="alamat">Alamat:</label>
                            <textarea id="alamat" name="alamat" rows="3" <?php echo $is_failed_booking ? 'readonly disabled' : ''; ?> required><?php echo htmlspecialchars($data_pembelian['alamat']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="id_rumah">Type Rumah:</label>
                            <select id="id_rumah" name="id_rumah" <?php echo (strtolower($old_status_unit_from_db_get_request) === 'terjual' || $is_failed_booking) ? 'disabled' : ''; ?> required>
                                <option value="">Pilih Type Rumah</option>
                                <?php
                                if ($result_type_rumah && $result_type_rumah->num_rows > 0) {
                                    while ($row = $result_type_rumah->fetch_assoc()) {
                                        $selected = ($row['id_rumah'] == $data_pembelian['id_rumah']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($row['id_rumah']) . "' " . $selected . ">" . htmlspecialchars($row['type_rumah']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_unit">Blok Rumah:</label>
                            <select id="id_unit" name="id_unit" <?php echo (strtolower($old_status_unit_from_db_get_request) === 'terjual' || $is_failed_booking) ? 'disabled' : ''; ?> required>
                                <option value="">Pilih Blok Rumah</option>
                                <?php
                                // Initial load of the current unit for the dropdown
                                if ($data_pembelian['id_rumah'] && $data_pembelian['id_unit']) {
                                    $stmt_initial_unit = $conn->prepare("SELECT id_unit, nama_blok, status FROM tb_unit WHERE id_unit = ? AND id_rumah = ?");
                                    if ($stmt_initial_unit) {
                                        $stmt_initial_unit->bind_param("ii", $data_pembelian['id_unit'], $data_pembelian['id_rumah']);
                                        $stmt_initial_unit->execute();
                                        $result_initial_unit = $stmt_initial_unit->get_result();
                                        if ($row_initial_unit = $result_initial_unit->fetch_assoc()) {
                                            $selected_status_text = ' (' . htmlspecialchars($row_initial_unit['status']) . ')';
                                            $disabled_attr = '';
                                            // Make sure the originally selected unit is *not* disabled if its status is 'Terjual'
                                            // or 'Terbooking', as it's the current one.
                                            // Other units that are 'Terbooking' or 'Terjual' will be disabled by JS.
                                            echo "<option value='" . htmlspecialchars($row_initial_unit['id_unit']) . "' selected>" . htmlspecialchars($row_initial_unit['nama_blok']) . $selected_status_text . "</option>";
                                        }
                                        $stmt_initial_unit->close();
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="button-group">
                            <button type="submit" name="submit_edit" class="submit-button" <?php echo $is_failed_booking ? 'disabled' : ''; ?>>Update Data Pembelian</button>
                            <a href="data_pembelian.php" class="back-button">Batal</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var idRumahSelect = document.getElementById('id_rumah');
        var idUnitSelect = document.getElementById('id_unit');
        // oldIdUnit diambil dari hidden field yang mencerminkan ID unit pembelian SAAT INI
        var oldIdUnit = document.querySelector('input[name="old_id_unit"]').value;
        // oldStatusUnitFromDb_get_request adalah status unit asli dari DB saat pertama kali GET request
        var oldStatusUnitFromDb_get_request = document.querySelector('input[name="old_status_unit_pembelian"]').value.toLowerCase();
        // oldStatusPembelianFromDb_get_request adalah status pembelian asli dari DB saat pertama kali GET request
        var oldStatusPembelianFromDb_get_request = document.querySelector('input[name="old_status_pembelian_posted"]').value.toLowerCase();

        // Check if the form should be disabled due to 'Gagal Booking' status
        var shouldDisableFormCompletely = (oldStatusPembelianFromDb_get_request === 'gagal booking');
        // Check if Type Rumah and Blok Rumah should be disabled due to 'Terjual' status
        var shouldDisableUnitSelection = (oldStatusUnitFromDb_get_request === 'terjual');

        // Apply global form disable if 'Gagal Booking'
        if (shouldDisableFormCompletely) {
            var formElements = document.querySelectorAll('.form-group input, .form-group select, .form-group textarea');
            formElements.forEach(function(element) {
                element.setAttribute('readonly', 'true');
                element.setAttribute('disabled', 'true');
                element.style.backgroundColor = '#e9ecef';
                element.style.cursor = 'not-allowed';
            });
            document.querySelector('button[name="submit_edit"]').setAttribute('disabled', 'true');
        }

        // Apply specific disable for unit selection if unit is 'Terjual'
        if (shouldDisableUnitSelection && !shouldDisableFormCompletely) { // Only apply if not already completely disabled
            idRumahSelect.setAttribute('disabled', 'true');
            idUnitSelect.setAttribute('disabled', 'true');
            // Add style for visual feedback
            idRumahSelect.style.backgroundColor = '#e9ecef';
            idRumahSelect.style.cursor = 'not-allowed';
            idUnitSelect.style.backgroundColor = '#e9ecef';
            idUnitSelect.style.cursor = 'not-allowed';
        }


        idRumahSelect.addEventListener('change', function() {
            var idRumah = this.value;
            idUnitSelect.innerHTML = '<option value="">Memuat Blok Rumah...</option>';

            // Do not fetch units if the form is completely disabled or unit selection is disabled
            if (shouldDisableFormCompletely || shouldDisableUnitSelection) {
                // Restore the original unit option if the form is disabled and user somehow triggers change
                if (oldIdUnit && idRumah === '<?php echo htmlspecialchars($data_pembelian['id_rumah']); ?>') {
                     // Add logic to re-add the current unit if it was present
                     var currentUnitText = '<?php echo htmlspecialchars($data_pembelian['blok_rumah'] . ' (' . $old_status_unit_from_db_get_request . ')'); ?>';
                     idUnitSelect.innerHTML = '<option value="' + oldIdUnit + '" selected>' + currentUnitText + '</option>';
                } else {
                    idUnitSelect.innerHTML = '<option value="">Pilih Blok Rumah</option>';
                }
                return;
            }

            if (idRumah) {
                fetch('get_blok.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_rumah=' + idRumah
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    idUnitSelect.innerHTML = '<option value="">Pilih Blok Rumah</option>';
                    if (data.blok && data.blok.length > 0) {
                        data.blok.forEach(function(unit) {
                            var option = document.createElement('option');
                            option.value = unit.id_unit;
                            option.textContent = unit.nama_blok;

                            // Logika untuk menonaktifkan opsi:
                            // 1. Jika unit BUKAN 'Tersedia'
                            // 2. DAN unit tersebut BUKAN unit yang sedang terhubung dengan pembelian ini (oldIdUnit)
                            //    (Artinya, unit yang 'Terbooking' atau 'Terjual' DAN BUKAN milik pembelian ini akan disabled)
                            // Note: oldIdUnit adalah unit yang sedang terikat dengan pembelian INI saat ini.
                            if (unit.status.toLowerCase() !== 'tersedia' && unit.id_unit != oldIdUnit) {
                                option.disabled = true;
                                option.textContent += ' (' + unit.status + ')';
                            } else if (unit.id_unit == oldIdUnit) {
                                // Jika ini adalah unit yang sedang terhubung, pastikan terpilih
                                // Dan tampilkan status asli unit lama
                                option.selected = true;
                                option.textContent += ' (' + unit.status + ')';
                            }
                            idUnitSelect.appendChild(option);
                        });
                    } else {
                        idUnitSelect.innerHTML = '<option value="">Tidak ada Blok Rumah tersedia</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching units:', error);
                    idUnitSelect.innerHTML = '<option value="">Gagal memuat Blok Rumah</option>';
                });
            } else {
                idUnitSelect.innerHTML = '<option value="">Pilih Blok Rumah</option>';
            }
        });

        // Trigger change event on load if a type is already selected (for existing data)
        // Ini akan memastikan dropdown unit terisi dengan benar saat halaman pertama kali dimuat
        // dan hanya jika form TIDAK sepenuhnya disabled.
        if (idRumahSelect.value && !shouldDisableFormCompletely) {
            var event = new Event('change');
            idRumahSelect.dispatchEvent(event);
        }
    });
    </script>
</body>
</html>