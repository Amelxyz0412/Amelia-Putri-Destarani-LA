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
    die("<div class='error-message'>Koneksi database gagal: " . $conn->connect_error . "</div>");
}

$success_redirect = false;
$error_message = "";

// Proses form jika tombol submit ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $type_rumah = $_POST['type_rumah'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $spesifikasi_detail = $_POST['spesifikasi_detail'] ?? '';
    $harga_rumah = $_POST['harga_rumah'] ?? '';

    // Penanganan Upload Foto Rumah
    $foto_rumah = '';
    if (isset($_FILES['foto_rumah']) && $_FILES['foto_rumah']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['foto_rumah']['name'];
        $tmp_file = $_FILES['foto_rumah']['tmp_name'];
        $direktori_upload = "uploads/";
        if (!is_dir($direktori_upload)) {
            mkdir($direktori_upload, 0755, true);
        }
        $nama_file_baru = uniqid() . "_" . $nama_file;
        $path_file = $direktori_upload . $nama_file_baru;
        if (move_uploaded_file($tmp_file, $path_file)) {
            $foto_rumah = $path_file;
        } else {
            $error_message = "<div class='error-message'>Gagal mengunggah foto rumah.</div>";
        }
    } else {
        $error_message = "<div class='error-message'>Harap unggah foto rumah.</div>";
    }

    // Penanganan Upload Site Plan (opsional)
    $site_plan = '';
    if (isset($_FILES['site_plan']) && $_FILES['site_plan']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['site_plan']['name'];
        $tmp_file = $_FILES['site_plan']['tmp_name'];
        $direktori_upload = "uploads/";
        $nama_file_baru = uniqid() . "_" . $nama_file;
        $path_file = $direktori_upload . $nama_file_baru;
        if (move_uploaded_file($tmp_file, $path_file)) {
            $site_plan = $path_file;
        } else {
            $error_message .= "<div class='error-message'>Gagal mengunggah site plan.</div>";
        }
    }

    // Penanganan Upload Floor Plan (opsional)
    $floor_plan = '';
    if (isset($_FILES['floor_plan']) && $_FILES['floor_plan']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['floor_plan']['name'];
        $tmp_file = $_FILES['floor_plan']['tmp_name'];
        $direktori_upload = "uploads/";
        $nama_file_baru = uniqid() . "_" . $nama_file;
        $path_file = $direktori_upload . $nama_file_baru;
        if (move_uploaded_file($tmp_file, $path_file)) {
            $floor_plan = $path_file;
        } else {
            $error_message .= "<div class='error-message'>Gagal mengunggah floor plan.</div>";
        }
    }

    // Validasi Data (contoh sederhana)
    if (empty($type_rumah) || empty($deskripsi) || empty($harga_rumah) || !is_numeric($harga_rumah) || !isset($_FILES['foto_rumah']) || $_FILES['foto_rumah']['error'] !== UPLOAD_ERR_OK || $error_message !== "") {
        // Pesan error akan ditampilkan di bagian HTML
    } else {
        // Buat Query INSERT dengan Prepared Statement
       // Ubah Query INSERT Anda menjadi seperti ini:
    $sql = "INSERT INTO tb_rumah (type_rumah, deskripsi, spesifikasi_detail, harga_rumah, foto_rumah, site_plan, floor_plan)
        VALUES (?, ?, ?, ?, ?, ?, ?)"; // Sesuaikan jumlah tanda tanya menjadi 7

        $stmt = $conn->prepare($sql);
        // Ubah bind_param menjadi seperti ini:
// type_rumah (s), deskripsi (s), spesifikasi_detail (s), harga_rumah (d), foto_rumah (s), site_plan (s), floor_plan (s)
$stmt->bind_param("sssdsss", $type_rumah, $deskripsi, $spesifikasi_detail, $harga_rumah, $foto_rumah, $site_plan, $floor_plan);

        // Eksekusi Query
        if ($stmt->execute()) {
            $success_redirect = true;
        } else {
            $error_message .= "<div class='error-message'>Terjadi kesalahan saat menambahkan data rumah: " . $stmt->error . "</div>";
        }

        // Tutup Statement
        $stmt->close();
    }
}

// Tutup Koneksi
$conn->close();

// Redirect setelah pemrosesan form berhasil
if ($success_redirect) {
    header("Location: Kategori_Rumah.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rumah Baru</title>
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
            overflow: hidden; /* Mencegah body ikut scroll */
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
            height: 100vh; /* Membuat sidebar setinggi viewport */
            overflow-y: auto; /* Jika isi sidebar melebihi tinggi viewport, bisa di-scroll */
            position: fixed; /* Membuat sidebar tetap di posisinya saat konten lain di-scroll */
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
            margin-left: 250px; /* Memberikan ruang untuk sidebar yang fixed */
            height: 100vh; /* Membuat main content setinggi viewport */
            overflow-y: auto; /* Membuat main content bisa di-scroll jika isinya melebihi tinggi viewport */
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
            position: sticky; /* Membuat header tetap di atas saat konten di-scroll */
            top: 0;
            z-index: 10; /* Memastikan header berada di atas konten yang di-scroll */
            margin-left: 40px;
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
            justify-content: center;
            align-items: flex-start; /* Agar form dimulai dari atas content area */
            overflow-y: auto; /* Membuat area konten bisa di-scroll jika form terlalu panjang */
        }

        .form-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 800px;
            margin-top: 20px; /* Memberikan sedikit jarak dari header */
            margin-bottom: 20px; /* Memberikan sedikit jarak dari bawah */
            overflow-y: auto; /* Membuat form bisa di scroll */
        }

        .form-container h2 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
            text-align: center;
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

        .form-group input,
        .form-group input::placeholder,
        .form-group textarea {
            font-size: 16px;
        }

        .form-group input,
        .form-group textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 8px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .spesifikasi-group {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .spesifikasi-group h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #333;
            font-size: 1.4em;
        }

        .spesifikasi-item {
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
        }

        .spesifikasi-item label {
            width: 100%;
            margin-bottom: 5px;
        }

        .spesifikasi-item input {
            width: calc(100% - 22px);
        }

        button, .back-button {
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-weight: 500;
        text-decoration: none;
        margin-top: 20px;
        line-height: 1;
        box-sizing: border-box;
        display: inline-block;
        height: 38px;
        vertical-align: middle;
        font-family: 'Segoe UI', sans-serif;
        min-width: 120px; /* Sesuaikan lebar jika perlu */
        text-align: center;
    }

    button {
        background-color: #117c6b;
        color: white;
        border: 1px solid transparent;
        margin-right: 10px; /* Memberikan jarak antara tombol Simpan dan Batal */
    }

    button:hover {
        background-color: #0d6658;
    }

    .back-button {
        background-color: #f9f9f9; /* Warna abu-abu */
        color: #333;
        border: 1px solid #ccc;
    }

    .back-button:hover {
        background-color: #e0e0e0;
    }

        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .success-message {
            color: green;
            font-size: 1em;
            margin-top: 10px;
            text-align: center;
        }
    </style>
    <script>
        function validateForm() {
            const typeRumah = document.getElementById('type_rumah').value.trim();
            const deskripsi = document.getElementById('deskripsi').value.trim();
            const hargaRumah = document.getElementById('harga_rumah').value.trim();
            const fotoRumah = document.getElementById('foto_rumah').value;

            if (typeRumah === '') {
                alert('Tipe Rumah harus diisi.');
                return false;
            }

            if (deskripsi === '') {
                alert('Deskripsi rumah harus diisi.');
                return false;
            }

            if (hargaRumah === '' || isNaN(hargaRumah)) {
                alert('Harga Rumah harus diisi dengan angka.');
                return false;
            }

            if (fotoRumah === '') {
                alert('Foto Rumah harus diunggah.');
                return false;
            }

            // Tambahkan validasi lain sesuai kebutuhan Anda

            return true;
        }
    </script>
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
    <a href="data_rumah.php"class="active">
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
                <h2>Tambah Rumah Baru</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span>
                </div>
            </header>
            <div class="content-area">
                <div class="form-container">
                    <h2>Tambah Rumah Baru</h2>
                    <?php
                    // Tampilkan pesan error jika ada
                    if (!empty($error_message)) {
                        echo $error_message;
                    }
                    ?>
                    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="type_rumah">Type Rumah:</label>
                            <input type="text" name="type_rumah" id="type_rumah" value="<?php echo isset($_POST['type_rumah']) ? htmlspecialchars($_POST['type_rumah']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi:</label>
                            <textarea name="deskripsi" id="deskripsi" placeholder=" "><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
    <label for="spesifikasi_detail">Detail Spesifikasi Rumah (Luas, Pondasi, Dll.):</label>
    <textarea name="spesifikasi_detail" id="spesifikasi_detail" placeholder="    " rows="12"><?php echo isset($_POST['spesifikasi_detail']) ? htmlspecialchars($_POST['spesifikasi_detail']) : ''; ?></textarea>
</div>

                        <div class="form-group">
                            <label for="harga_rumah">Harga Rumah:</label>
                            <input type="text" name="harga_rumah" id="harga_rumah" value="<?php echo isset($_POST['harga_rumah']) ? htmlspecialchars($_POST['harga_rumah']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="foto_rumah">Foto Rumah:</label>
                            <input type="file" name="foto_rumah" id="foto_rumah">
                            <div class="error-message" id="foto_rumah_error"></div>
                        </div>

                        <div class="form-group">
                            <label for="site_plan">Site Plan:</label>
                            <input type="file" name="site_plan" id="site_plan">
                        </div>

                        <div class="form-group">
                            <label for="floor_plan">Floor Plan:</label>
                            <input type="file" name="floor_plan" id="floor_plan">
                        </div>

                        <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
    <a href="kategori_rumah.php" class="back-button">Batal</a>
    <button type="submit">Simpan</button>
</form>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>