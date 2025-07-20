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
$old_status_unit_from_db = ''; // Variabel untuk menyimpan status unit lama dari DB (saat GET request)
$old_status_pembelian_from_db = ''; // Variabel untuk menyimpan status pembelian lama dari DB (saat GET request)
$is_editable = true; // New variable to control form editability
$is_customer_info_editable = false; // New variable to control customer info editability

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
            $old_status_unit_from_db = $data_pembelian['status_unit_pembelian'];
            // Simpan juga status pembelian lama untuk keperluan logika lain di POST
            $old_status_pembelian_from_db = $data_pembelian['status_pembelian'];

            // Set is_editable based on status_pembelian or status_unit_pembelian
            if (strtolower($old_status_pembelian_from_db) === 'gagal booking') {
                $is_editable = false;
                $pesan = "<div class='warning-message'>Data ini tidak dapat diubah karena statusnya '" . htmlspecialchars($old_status_pembelian_from_db) . "'.</div>";
            } elseif (strtolower($old_status_unit_from_db) === 'terjual') {
                $is_editable = true; // By default, everything is not editable for 'terjual'
                $is_customer_info_editable = true; // EXCEPT customer information
                $pesan = "<div class='warning-message'>Unit ini sudah 'Terjual'. Hanya informasi pembeli yang dapat diubah.</div>";
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

    // NEW LOGIC: Prevent saving if purchase status is 'Gagal Booking'
    if (strtolower($original_pembelian_status) === 'gagal booking') {
        $_SESSION['pesan'] = "<div class='error-message'>Update dibatalkan: Data ini tidak dapat diubah karena statusnya '" . htmlspecialchars($original_pembelian_status) . "'.</div>";
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

    // If unit is 'Terjual', only allow customer info fields to be updated.
    // Revert other fields to their original values from the database.
    if (strtolower($original_unit_status) === 'terjual') {
        $tanggal_pembelian = $original_pembelian_data['tanggal_pembelian'];
        $id_rumah_selected = $original_pembelian_data['id_rumah'];
        $id_unit_selected = $original_pembelian_data['id_unit'];
    }

    // Basic form field validation for customer info if editable, or all if not 'terjual'
    if (empty($nama_pembeli) || empty($no_ktp) || empty($telepon) || empty($alamat)) {
        $pesan = "<div style='color: red; margin-bottom: 10px;'>Nama, No. KTP, Telepon, dan Alamat harus diisi.</div>";
    }

    if (strtolower($original_unit_status) !== 'terjual') { // Apply these validations only if unit is NOT 'terjual'
        if (empty($tanggal_pembelian) || empty($id_rumah_selected) || empty($id_unit_selected)) {
            $pesan = "<div style='color: red; margin-bottom: 10px;'>Tanggal Pembelian, Type Rumah, dan Blok Rumah harus diisi.</div>";
        }
    }


    if (empty($pesan)) {
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
                $pesan = "<div style='color: red; margin-bottom: 10px;'>Type Rumah tidak ditemukan.</div>";
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
                    $pesan = "<div style='color: red; margin-bottom: 10px;'>Blok Rumah tidak ditemukan.</div>";
                }
                $stmt_get_blok->close();
            }
        }

        if (empty($type_rumah_text) || empty($blok_rumah)) {
            if (empty($pesan)) $pesan = "<div style='color: red; margin-bottom: 10px;'>Type Rumah atau Blok Rumah tidak valid.</div>";
        }


        // --- VALIDASI LOGIKA BISNIS ---
        // If unit changed and is not 'terjual', new unit must be 'Tersedia'
        if (empty($pesan) && $id_unit_selected != $original_pembelian_data['id_unit'] && strtolower($original_unit_status) !== 'terjual') {
            if (strtolower($status_unit_newly_selected_from_db) !== 'tersedia') {
                $pesan = "<div class='error-message'>Blok Rumah yang dipilih ('" . htmlspecialchars($blok_rumah) . "') tidak tersedia. Statusnya adalah '" . htmlspecialchars($status_unit_newly_selected_from_db) . "'. Silakan pilih unit 'Tersedia'.</div>";
            }
        }


        // Lanjutkan hanya jika tidak ada pesan error
        if (empty($pesan)) {
            // --- Penentuan ID Pembelian Baru ---
            $new_id_pembelian = generateInitialPembelianID($type_rumah_text, $blok_rumah);
            if ($new_id_pembelian !== $old_id_pembelian) {
                // Only generate a new unique ID if the type/blok combination actually changed AND the unit wasn't 'terjual'
                if (strtolower($original_unit_status) !== 'terjual') {
                    $new_id_pembelian = generateUniquePembelianID($new_id_pembelian, $conn);
                    $pesan .= "<div class='warning-message'>ID pembelian baru dihasilkan: " . htmlspecialchars($new_id_pembelian) . ".</div>";
                } else {
                    // If unit is 'Terjual', ID should never change
                    $new_id_pembelian = $old_id_pembelian;
                }
            } else {
                $new_id_pembelian = $old_id_pembelian; // Gunakan ID lama jika tidak ada perubahan type/blok
            }

            // --- Penentuan status_pembelian_new dan status_unit_update ---
            $status_pembelian_new = $original_pembelian_status; // Default to original status
            $desired_status_for_selected_unit = $original_unit_status; // Default to original unit status

            // If the unit was changed or the original purchase status was 'Kosong' / 'Batal'
            if ($id_unit_selected != $original_pembelian_data['id_unit'] || strtolower($original_pembelian_status) === 'kosong' || strtolower($original_pembelian_status) === 'batal') {
                 // But only if the unit was NOT 'terjual' initially.
                if (strtolower($original_unit_status) !== 'terjual') {
                    $status_pembelian_new = 'Terbooking';
                    $desired_status_for_selected_unit = 'Terbooking';
                }
            }
            // If the original unit was 'terjual', maintain 'Lunas' status for purchase and 'Terjual' for unit
            if (strtolower($original_unit_status) === 'terjual') {
                $status_pembelian_new = 'Lunas';
                $desired_status_for_selected_unit = 'Terjual';
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
                // Only update unit status if the original unit was NOT 'Terjual'
                if (strtolower($original_unit_status) !== 'terjual') {
                    // KONDISI 1: Unit lama perlu dikembalikan ke 'Tersedia' jika unit berubah
                    if ($old_id_unit_posted != $id_unit_selected) {
                        // Fetch the *current* status of the old unit from the database
                        $current_status_old_unit = '';
                        $stmt_get_old_unit_status = $conn->prepare("SELECT status FROM tb_unit WHERE id_unit = ?");
                        if ($stmt_get_old_unit_status) {
                            $stmt_get_old_unit_status->bind_param("i", $old_id_unit_posted);
                            $stmt_get_old_unit_status->execute();
                            $result_old_unit_status = $stmt_get_old_unit_status->get_result();
                            if ($row_old_unit_status = $result_old_unit_status->fetch_assoc()) {
                                $current_status_old_unit = $row_old_unit_status['status'];
                            }
                            $stmt_get_old_unit_status->close();
                        }

                        // Only reset old unit to 'Tersedia' if its current status is 'Terbooking'
                        if (strtolower($current_status_old_unit) === 'terbooking') {
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

                    // Only update if the actual status is different from the desired status
                    // AND the selected unit's current status is NOT 'Terjual'
                    if (strtolower($current_actual_status_selected_unit) !== strtolower($desired_status_for_selected_unit) && strtolower($current_actual_status_selected_unit) !== 'terjual') {
                        $stmt_update_new_unit = $conn->prepare("UPDATE tb_unit SET status = ? WHERE id_unit = ?");
                        if (!$stmt_update_new_unit) {
                            throw new Exception("Error preparing statement (update_new_unit_status): " . $conn->error);
                        }
                        $stmt_update_new_unit->bind_param("si", $desired_status_for_selected_unit, $id_unit_selected);
                        $stmt_update_new_unit->execute();
                        $stmt_update_new_unit->close();
                    }
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
            background-color: #fff; /* Default background */
        }

        .form-group input[type="date"]:disabled,
        .form-group input[type="text"]:disabled,
        .form-group input[type="tel"]:disabled,
        .form-group textarea:disabled,
        .form-group select:disabled {
            background-color: #e9ecef; /* Lighter background for disabled fields */
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

        .submit-button:hover:not(:disabled) { /* Add :not(:disabled) for hover effect */
            background-color: #0d6658;
        }

        .submit-button:disabled { /* Style for disabled submit button */
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
            border-radius: 4px;
            padding: 8px 12px; /* Added padding */
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
                    <span>Admin</span>
                </div>
            </header>

            <section class="content-area">
                <div class="form-scroll-container">
                    <?php if (!empty($pesan)) : ?>
                        <?php echo $pesan; ?>
                    <?php endif; ?>

                    <?php if ($data_pembelian) : ?>
                        <form action="" method="POST">
                            <input type="hidden" name="old_id_pembelian" value="<?php echo htmlspecialchars($data_pembelian['id_pembelian']); ?>">
                            <input type="hidden" name="old_id_unit" value="<?php echo htmlspecialchars($data_pembelian['id_unit']); ?>">

                            <div class="form-group">
                                <label for="tanggal_pembelian">Tanggal Pembelian:</label>
                                <input type="date" id="tanggal_pembelian" name="tanggal_pembelian" value="<?php echo htmlspecialchars($data_pembelian['tanggal_pembelian']); ?>" <?php echo (!$is_editable && !$is_customer_info_editable) ? 'disabled' : ''; ?>>
                            </div>

                            <div class="form-group">
                                <label for="nama_pembeli">Nama Pembeli:</label>
                                <input type="text" id="nama_pembeli" name="nama_pembeli" value="<?php echo htmlspecialchars($data_pembelian['nama_pembeli']); ?>" <?php echo (!$is_editable && !$is_customer_info_editable) ? 'disabled' : ''; ?>>
                            </div>

                            <div class="form-group">
                                <label for="no_ktp">No. KTP:</label>
                                <input type="text" id="no_ktp" name="no_ktp" value="<?php echo htmlspecialchars($data_pembelian['no_ktp']); ?>" <?php echo (!$is_editable && !$is_customer_info_editable) ? 'disabled' : ''; ?>>
                            </div>

                            <div class="form-group">
                                <label for="telepon">No. Telepon:</label>
                                <input type="tel" id="telepon" name="telepon" value="<?php echo htmlspecialchars($data_pembelian['telepon']); ?>" <?php echo (!$is_editable && !$is_customer_info_editable) ? 'disabled' : ''; ?>>
                            </div>

                            <div class="form-group">
                                <label for="alamat">Alamat:</label>
                                <textarea id="alamat" name="alamat" rows="4" <?php echo (!$is_editable && !$is_customer_info_editable) ? 'disabled' : ''; ?>><?php echo htmlspecialchars($data_pembelian['alamat']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="id_rumah">Type Rumah:</label>
                                <select id="id_rumah" name="id_rumah" <?php echo (!$is_editable) ? 'disabled' : ''; ?>>
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
                                <select id="id_unit" name="id_unit" <?php echo (!$is_editable) ? 'disabled' : ''; ?>>
                                    <option value="">Pilih Blok Rumah</option>
                                    <?php
                                    // Populate Blok Rumah based on initial Type Rumah
                                    if ($data_pembelian['id_rumah']) {
                                        $stmt_blok = $conn->prepare("SELECT id_unit, nama_blok, status FROM tb_unit WHERE id_rumah = ? ORDER BY nama_blok ASC");
                                        if ($stmt_blok) {
                                            $stmt_blok->bind_param("i", $data_pembelian['id_rumah']);
                                            $stmt_blok->execute();
                                            $result_blok = $stmt_blok->get_result();
                                            while ($row_blok = $result_blok->fetch_assoc()) {
                                                $status_display = '';
                                                if (strtolower($row_blok['status']) === 'terbooking') {
                                                    $status_display = ' (Terbooking)';
                                                } elseif (strtolower($row_blok['status']) === 'terjual') {
                                                    $status_display = ' (Terjual)';
                                                }

                                                $selected = ($row_blok['id_unit'] == $data_pembelian['id_unit']) ? 'selected' : '';
                                                // Disable option if not selected AND not available (unless it's the currently selected one)
                                                $disabled_option = '';
                                                if (strtolower($row_blok['status']) !== 'tersedia' && $row_blok['id_unit'] != $data_pembelian['id_unit']) {
                                                    $disabled_option = 'disabled';
                                                }
                                                echo "<option value='" . htmlspecialchars($row_blok['id_unit']) . "' " . $selected . " " . $disabled_option . ">" . htmlspecialchars($row_blok['nama_blok']) . $status_display . "</option>";
                                            }
                                            $stmt_blok->close();
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status_pembelian">Status Pembelian:</label>
                                <input type="text" id="status_pembelian" name="status_pembelian" value="<?php echo htmlspecialchars($data_pembelian['status_pembelian']); ?>" disabled>
                            </div>

                            <div class="button-group">
                                <button type="submit" name="submit_edit" class="submit-button">Simpan Perubahan</button>
                                <a href="data_pembelian.php" class="back-button">Batal</a>
                            </div>
                        </form>
                    <?php else : ?>
                        <p>Tidak ada data pembelian yang ditemukan atau ID tidak valid.</p>
                        <div class="button-group">
                            <a href="data_pembelian.php" class="back-button">Kembali ke Data Pembelian</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var idRumahSelect = document.getElementById('id_rumah');
            var idUnitSelect = document.getElementById('id_unit');
            var currentUnitId = <?php echo json_encode($data_pembelian['id_unit'] ?? null); ?>;
            var isEditableGlobal = <?php echo json_encode($is_editable); ?>;
            var isCustomerInfoEditable = <?php echo json_encode($is_customer_info_editable); ?>;
            var submitButton = document.querySelector('.submit-button');

            // Initial state for fields based on $is_editable and $is_customer_info_editable
            document.getElementById('tanggal_pembelian').disabled = !isEditableGlobal;
            document.getElementById('nama_pembeli').disabled = !(isEditableGlobal || isCustomerInfoEditable);
            document.getElementById('no_ktp').disabled = !(isEditableGlobal || isCustomerInfoEditable);
            document.getElementById('telepon').disabled = !(isEditableGlobal || isCustomerInfoEditable);
            document.getElementById('alamat').disabled = !(isEditableGlobal || isCustomerInfoEditable);
            document.getElementById('id_rumah').disabled = !isEditableGlobal;
            document.getElementById('id_unit').disabled = !isEditableGlobal;

            // Disable submit button if nothing is editable
            if (!isEditableGlobal && !isCustomerInfoEditable) {
                submitButton.disabled = true;
            }

            idRumahSelect.addEventListener('change', function() {
                var selectedIdRumah = this.value;
                idUnitSelect.innerHTML = '<option value="">Memuat Blok Rumah...</option>'; // Reset and show loading

                if (selectedIdRumah) {
                    fetch('get_blok_rumah.php?id_rumah=' + selectedIdRumah)
                        .then(response => response.json())
                        .then(data => {
                            idUnitSelect.innerHTML = '<option value="">Pilih Blok Rumah</option>'; // Reset options
                            data.forEach(function(unit) {
                                var option = document.createElement('option');
                                option.value = unit.id_unit;
                                option.textContent = unit.nama_blok;
                                if (unit.status.toLowerCase() === 'terbooking') {
                                    option.textContent += ' (Terbooking)';
                                } else if (unit.status.toLowerCase() === 'terjual') {
                                    option.textContent += ' (Terjual)';
                                }

                                // If the unit is not available AND not the currently selected unit for this purchase, disable it.
                                if (unit.status.toLowerCase() !== 'tersedia' && unit.id_unit != currentUnitId) {
                                    option.disabled = true;
                                }

                                idUnitSelect.appendChild(option);
                            });
                            // If the original unit is still in the list and it belongs to the selected id_rumah,
                            // ensure it's selected and not disabled if its status is 'terbooked' or 'terjual'
                            // when changing the house type (if allowed).
                            if (currentUnitId && selectedIdRumah == <?php echo json_encode($data_pembelian['id_rumah'] ?? null); ?>) {
                                var currentOption = idUnitSelect.querySelector('option[value="' + currentUnitId + '"]');
                                if (currentOption) {
                                    currentOption.selected = true;
                                    currentOption.disabled = false; // Ensure current selected unit is not disabled
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching blok rumah:', error);
                            idUnitSelect.innerHTML = '<option value="">Gagal memuat blok rumah</option>';
                        });
                } else {
                    idUnitSelect.innerHTML = '<option value="">Pilih Blok Rumah</option>'; // Clear if no type selected
                }
            });

            // Re-select the correct unit if the house type is reloaded or the page is refreshed after an error
            // (Only if a data_pembelian exists and a unit was previously selected)
            <?php if ($data_pembelian && $data_pembelian['id_unit']) : ?>
                var initialIdRumah = <?php echo json_encode($data_pembelian['id_rumah']); ?>;
                var initialIdUnit = <?php echo json_encode($data_pembelian['id_unit']); ?>;

                // Function to set selected unit
                function setInitialUnit() {
                    if (idUnitSelect.options.length > 1) { // Check if options are populated (beyond default)
                        var unitOption = idUnitSelect.querySelector('option[value="' + initialIdUnit + '"]');
                        if (unitOption) {
                            unitOption.selected = true;
                            unitOption.disabled = false; // Ensure the original selected unit is not disabled even if its status is not 'Tersedia'
                        }
                    } else {
                        // If options are not yet loaded, wait and try again (e.g., if page loads slowly)
                        setTimeout(setInitialUnit, 100);
                    }
                }
                setInitialUnit();
            <?php endif; ?>
        });
    </script>
</body>
</html>