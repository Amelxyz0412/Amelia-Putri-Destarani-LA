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

$pesan_error = "";
$otomatis_id = generateIDPembayaran($conn); // Generate ID otomatis saat halaman pertama kali diakses

// Fungsi untuk menghasilkan ID pembayaran otomatis
function generateIDPembayaran($conn) {
    $sql_max_id = "SELECT MAX(SUBSTR(id_pembayaran, 4)) AS max_id FROM tb_pembayaran";
    $result_max_id = $conn->query($sql_max_id);
    $max_id = $result_max_id->fetch_assoc()['max_id'];

    return $max_id ? "PYM" . sprintf("%03d", intval($max_id) + 1) : "PYM001";
}

// Proses form saat disubmit
if (isset($_POST['submit'])) {
    $id_pembayaran = $conn->real_escape_string($_POST['id_pembayaran']);
    $id_pembelian = $conn->real_escape_string($_POST['id_pembelian']);
    $tanggal_pembayaran = $conn->real_escape_string($_POST['tanggal_pembayaran']);
    $jumlah_pembayaran = $conn->real_escape_string($_POST['jumlah_pembayaran']);
    $jenis_transaksi = 'uang DP'; // Hardcoded as per your existing logic for now

    // Bukti Pembayaran
    $bukti_pembayaran_path = null; // Initialize to null
    $target_dir = "uploads/bukti_pembayaran/"; // Directory to save uploads
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
    }

    // Validasi input
    if (empty($id_pembelian) || empty($tanggal_pembayaran) || empty($jumlah_pembayaran)) {
        $pesan_error = "<div style='color: red; margin-bottom: 10px;'>Kolom ID Pembelian, Tanggal Pembayaran, dan Jumlah Pembayaran wajib diisi.</div>";
    } else {
        // Handle file upload for bukti_pembayaran
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == UPLOAD_ERR_OK) {
            $file_name = basename($_FILES["bukti_pembayaran"]["name"]);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $target_file = $target_dir . uniqid() . '.' . $file_type; // Use uniqid for unique file names

            // Allow certain file formats
            $allowed_types = array("jpg", "png", "jpeg", "gif", "pdf");
            if (!in_array($file_type, $allowed_types)) {
                $pesan_error = "<div style='color: red; margin-bottom: 10px;'>Maaf, hanya file JPG, JPEG, PNG, GIF & PDF yang diperbolehkan untuk bukti pembayaran.</div>";
            } elseif ($_FILES["bukti_pembayaran"]["size"] > 5000000) { // 5MB max
                $pesan_error = "<div style='color: red; margin-bottom: 10px;'>Maaf, ukuran file bukti pembayaran terlalu besar (maks 5MB).</div>";
            } else {
                if (move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
                    $bukti_pembayaran_path = $conn->real_escape_string($target_file);
                } else {
                    $pesan_error = "<div style='color: red; margin-bottom: 10px;'>Terjadi kesalahan saat mengupload bukti pembayaran.</div>";
                }
            }
        }

        if (empty($pesan_error)) { // Proceed only if no file upload errors
            // Mulai transaksi untuk memastikan konsistensi data
            $conn->begin_transaction();

            try {
                // 1. Ambil nama_pembeli, id_rumah, dan id_unit dari tb_pembelian berdasarkan id_pembelian
                $sql_get_pembelian_details = "SELECT p.nama_pembeli, p.id_rumah, p.id_unit, r.type_rumah, u.nama_blok
                                              FROM tb_pembelian p
                                              JOIN tb_rumah r ON p.id_rumah = r.id_rumah
                                              JOIN tb_unit u ON p.id_unit = u.id_unit
                                              WHERE p.id_pembelian = '$id_pembelian'";
                $result_pembelian_details = $conn->query($sql_get_pembelian_details);

                if ($result_pembelian_details->num_rows > 0) {
                    $row_pembelian_details = $result_pembelian_details->fetch_assoc();
                    $nama_pembeli_terkait = $row_pembelian_details['nama_pembeli'];
                    $type_rumah_terkait = $row_pembelian_details['type_rumah'];
                    $nama_blok_terkait = $row_pembelian_details['nama_blok'];
                    $id_unit_terkait = $row_pembelian_details['id_unit'];

                    // Simpan data pembayaran ke tb_pembayaran
                    $sql_insert_pembayaran = "INSERT INTO tb_pembayaran (id_pembayaran, id_pembelian, tanggal_pembayaran, jumlah_pembayaran, nama_pembeli, status_pembelian, jenis_transaksi, bukti_pembayaran)
                                              VALUES ('$id_pembayaran', '$id_pembelian', '$tanggal_pembayaran', '$jumlah_pembayaran', '$nama_pembeli_terkait', 'terjual', '$jenis_transaksi', ";
                    $sql_insert_pembayaran .= $bukti_pembayaran_path ? "'$bukti_pembayaran_path')" : "NULL)";


                    if (!$conn->query($sql_insert_pembayaran)) {
                        throw new Exception("Terjadi kesalahan saat menambahkan data pembayaran: " . $conn->error);
                    }

                    // Update status unit di tb_unit menjadi 'terjual'
                    $sql_update_unit_status = "UPDATE tb_unit SET status = 'terjual' WHERE id_unit = '$id_unit_terkait'";
                    if (!$conn->query($sql_update_unit_status)) {
                        throw new Exception("Terjadi kesalahan saat mengubah status unit: " . $conn->error);
                    }

                    // Update status_pembelian di tb_pembelian menjadi 'Terjual'
                    $sql_update_pembelian_status = "UPDATE tb_pembelian SET status_pembelian = 'Terjual' WHERE id_pembelian = '$id_pembelian'";
                    if (!$conn->query($sql_update_pembelian_status)) {
                        throw new Exception("Terjadi kesalahan saat mengubah status pembelian: " . $conn->error);
                    }

                } else {
                    throw new Exception("ID Pembelian tidak ditemukan atau data terkait tidak lengkap.");
                }

                // Commit transaksi jika semua query berhasil
                $conn->commit();
                $_SESSION['pesan'] = "<div style='color: green; margin-bottom: 10px;'>Data pembayaran berhasil ditambahkan dan status unit serta pembelian diperbarui.</div>";
                header("Location: data_pembayaran.php"); // Redirect ke halaman laporan
                exit();

            } catch (Exception $e) {
                // Rollback transaksi jika terjadi kesalahan
                $conn->rollback();
                $pesan_error = "<div style='color: red; margin-bottom: 10px;'>" . $e->getMessage() . "</div>";
                // Optionally, delete the uploaded file if transaction failed
                if ($bukti_pembayaran_path && file_exists($bukti_pembayaran_path)) {
                    unlink($bukti_pembayaran_path);
                }
            }
        }
    }
}

// Query untuk mendapatkan daftar ID Pembelian yang statusnya 'Terbooking' dan belum dibayar
$sql_pembelian_options = "
    SELECT p.id_pembelian
    FROM tb_pembelian p
    LEFT JOIN tb_pembayaran py ON p.id_pembelian = py.id_pembelian
    WHERE p.status_pembelian = 'Terbooking' AND py.id_pembayaran IS NULL;
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
    <title>Tambah Pembayaran</title>
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

        .form-scroll-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow-y: auto;
            max-height: 70vh;
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

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .form-group input[type="file"] {
            padding: 8px; /* Slightly less padding for file input */
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f8f8f8;
        }

        .form-group textarea {
            resize: vertical;
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

        .back-button {
            background-color: #f9f9f9;
            color: #333;
            border: 1px solid #ccc;
        }

        .back-button:hover {
            background-color: #e0e0e0;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .success-message {
            color: green;
            margin-top: 10px;
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
                    <li>
                        <a href="data_rumah.php">
                            <i class="fas fa-home"></i>
                            <span>Data Rumah</span>
                        </a>
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
                <h2>Tambah Data Pembayaran</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani </span>
                </div>
            </header>
            <div class="content-area">
                <div class="form-scroll-container">
                    <h2>Tambah Data Pembayaran</h2>
                    <?php echo $pesan_error; ?>
                    <?php
                    if (isset($_SESSION['pesan'])) {
                        echo $_SESSION['pesan'];
                        unset($_SESSION['pesan']);
                    }
                    ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="id_pembayaran">ID Pembayaran</label>
                            <input type="text" id="id_pembayaran" name="id_pembayaran" value="<?php echo htmlspecialchars($otomatis_id); ?>" readonly>
                            <small>ID akan terisi otomatis.</small>
                        </div>

                        <div class="form-group">
                            <label for="id_pembelian">ID Pembelian</label>
                            <select id="id_pembelian" name="id_pembelian" required>
                                <option value="">Pilih ID Pembelian</option>
                                <?php foreach ($options_pembelian as $id): ?>
                                    <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($id); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nama_pembeli">Nama Pembeli</label>
                            <input type="text" id="nama_pembeli" name="nama_pembeli" readonly placeholder="Akan terisi otomatis">
                        </div>

                        <div class="form-group">
                            <label for="type_rumah">Type Rumah</label>
                            <input type="text" id="type_rumah" name="type_rumah" readonly placeholder="Akan terisi otomatis">
                        </div>

                        <div class="form-group">
                            <label for="nama_blok">Nama Blok</label>
                            <input type="text" id="nama_blok" name="nama_blok" readonly placeholder="Akan terisi otomatis">
                        </div>

                        <div class="form-group" style="display: none;"> <label for="jenis_transaksi">Jenis Transaksi</label>
                            <input type="text" id="jenis_transaksi" name="jenis_transaksi" value="uang DP" readonly>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_pembayaran">Tanggal Pembayaran</label>
                            <input type="date" id="tanggal_pembayaran" name="tanggal_pembayaran" required>
                        </div>

                        <div class="form-group">
                            <label for="jumlah_pembayaran">Jumlah Pembayaran</label>
                            <input type="number" id="jumlah_pembayaran" name="jumlah_pembayaran" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="bukti_pembayaran">Bukti Pembayaran (JPG, PNG, PDF maks 5MB)</label>
                            <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" accept=".jpg, .jpeg, .png, .gif, .pdf">
                            <small>Opsional: Unggah bukti pembayaran.</small>
                        </div>

                        <div class="button-group">
                            <a href="data_pembayaran.php" class="back-button">Batal</a>
                            <button type="submit" class="submit-button" name="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle ID Pembelian change to populate Nama Pembeli, Type Rumah, and Nama Blok
        const idPembelianSelect = document.getElementById('id_pembelian');
        const namaPembeliInput = document.getElementById('nama_pembeli');
        const typeRumahInput = document.getElementById('type_rumah');
        const namaBlokInput = document.getElementById('nama_blok');

        idPembelianSelect.addEventListener('change', function() {
            const selectedIdPembelian = this.value;

            if (selectedIdPembelian) {
                // Make an AJAX request to fetch details for the selected ID Pembelian
                fetch('get_pembelian_details.php?id_pembelian=' + selectedIdPembelian)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            namaPembeliInput.value = data.nama_pembeli;
                            typeRumahInput.value = data.type_rumah;
                            namaBlokInput.value = data.nama_blok;
                        } else {
                            namaPembeliInput.value = '';
                            typeRumahInput.value = '';
                            namaBlokInput.value = '';
                            alert('Gagal mengambil detail pembelian: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        namaPembeliInput.value = '';
                        typeRumahInput.value = '';
                        namaBlokInput.value = '';
                        alert('Terjadi kesalahan saat berkomunikasi dengan server.');
                    });
            } else {
                // Clear fields if no ID Pembelian is selected
                namaPembeliInput.value = '';
                typeRumahInput.value = '';
                namaBlokInput.value = '';
            }
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>