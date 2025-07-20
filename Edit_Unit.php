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

// Ambil data type rumah untuk combo box
$type_rumah_options = [];
$type_sql = "SELECT id_rumah, type_rumah FROM tb_rumah ORDER BY type_rumah ASC";
$type_result = $conn->query($type_sql);
if ($type_result && $type_result->num_rows > 0) {
    while ($row = $type_result->fetch_assoc()) {
        $type_rumah_options[] = $row;
    }
}

// Ambil data unit yang akan diedit
$id_unit = $_GET['id_unit'] ?? '';
$unit_data = null;

if ($id_unit) {
    $unit_sql = "SELECT u.id_unit, u.nama_blok, u.status, u.id_rumah FROM tb_unit u WHERE u.id_unit = ?";
    $stmt = $conn->prepare($unit_sql);
    $stmt->bind_param("i", $id_unit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $unit_data = $result->fetch_assoc();
    }
    $stmt->close();
}

$success_redirect = false;
$error_message = "";

// Proses form jika tombol submit ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $id_rumah = $_POST['id_rumah'] ?? '';
    $nama_blok = $_POST['nama_blok'] ?? '';
    $status = $_POST['status'] ?? '';

    // Validasi sederhana
    if (empty($id_rumah) || empty($nama_blok) || empty($status)) {
        $error_message = "<div class='error-message'>Semua field harus diisi.</div>";
    } else {
        // Update data ke tb_unit
        $sql = "UPDATE tb_unit SET id_rumah = ?, nama_blok = ?, status = ? WHERE id_unit = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $id_rumah, $nama_blok, $status, $id_unit);

        if ($stmt->execute()) {
            $success_redirect = true;
        } else {
            $error_message = "<div class='error-message'>Terjadi kesalahan saat mengupdate data unit: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}

$conn->close();

if ($success_redirect) {
    header("Location: unit_rumah.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Unit Rumah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
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
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        .form-group select {
            font-size: 16px;
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
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
    </style>
    <script>
        function validateForm() {
            const idRumah = document.getElementById('id_rumah').value;
            const namaBlok = document.getElementById('nama_blok').value.trim();
            const status = document.getElementById('status').value;

            if (idRumah === '') {
                alert('Tipe Rumah harus dipilih.');
                return false;
            }
            if (namaBlok === '') {
                alert('Nama Blok harus diisi.');
                return false;
            }
            if (status === '') {
                alert('Status harus dipilih.');
                return false;
            }
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
                        <a href="data_rumah.php" class="active">
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
                <h2>Edit Unit Rumah</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span>
                </div>
            </header>
            <div class="content-area">
                <div class="form-container">
                    <h2>Edit Unit Rumah</h2>
                    <?php
                    if (!empty($error_message)) {
                        echo $error_message;
                    }
                    ?>
                    <form method="post" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="id_rumah">Type Rumah:</label>
                            <select name="id_rumah" id="id_rumah" required>
                                <option value="">-- Pilih Type Rumah --</option>
                                <?php foreach ($type_rumah_options as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option['id_rumah']); ?>"
                                        <?php if ($unit_data && $unit_data['id_rumah'] == $option['id_rumah']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($option['type_rumah']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nama_blok">Nama Blok:</label>
                            <input type="text" name="nama_blok" id="nama_blok"
                                value="<?php echo $unit_data ? htmlspecialchars($unit_data['nama_blok']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select name="status" id="status" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="tersedia" <?php if ($unit_data && $unit_data['status'] == 'tersedia') echo 'selected'; ?>>Tersedia</option>
                                <option value="terjual" <?php if ($unit_data && $unit_data['status'] == 'terjual') echo 'selected'; ?>>Terjual</option>
                                <option value="terbooking" <?php if ($unit_data && $unit_data['status'] == 'terbooking') echo 'selected'; ?>>Terbooking</option>
                            </select>
                        </div>

                        <a href="unit_rumah.php" class="back-button">Batal</a>
                        <button type="submit">Simpan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
