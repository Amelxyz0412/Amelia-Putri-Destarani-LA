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

// Inisialisasi pesan sukses atau error
$pesan = "";

// Fungsi untuk menghasilkan ID awal
function generateInitialPembelianID($type_rumah_text, $blok_rumah) {
    // Tetap mengambil huruf pertama dari type_rumah_text
    $type_prefix = strtoupper(substr($type_rumah_text, 0, 1));
    return $type_prefix . strtoupper($blok_rumah);
}

// Fungsi untuk menghasilkan ID dengan timestamp jika ada duplikasi
function generateUniquePembelianID($initial_id) {
    $timestamp = date("YmdHis");
    return $initial_id . "-" . $timestamp;
}

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $tanggal_pembelian = $_POST['tanggal_pembelian'] ?? '';
    $nama_pembeli = $_POST['nama_pembeli'] ?? '';
    $no_ktp = $_POST['no_ktp'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    // Ambil ID rumah dan ID unit langsung dari input tersembunyi/value
    $id_rumah_selected = $_POST['id_rumah'] ?? ''; // Ini adalah id_rumah dari tb_rumah
    $id_unit_selected = $_POST['id_unit'] ?? ''; // Ini adalah id_unit dari tb_unit (BARU!)

    // Note: Kita masih perlu type_rumah_text dan blok_rumah untuk generate ID pembelian
    $blok_rumah = ''; // Akan diambil berdasarkan id_unit_selected
    $type_rumah_text = ''; // Akan diambil berdasarkan id_rumah_selected

    // *** PERUBAHAN UTAMA: Status awal pembelian adalah 'Kosong' ***
    // Pastikan tidak ada spasi jika kolom di database tidak memperbolehkan string kosong atau spasi.
    $initial_status_pembelian = 'Kosong'; 

    // Validasi data input dasar
    if (empty($tanggal_pembelian) || empty($nama_pembeli) || empty($no_ktp) || empty($telepon) || empty($alamat) || empty($id_rumah_selected) || empty($id_unit_selected)) {
        $pesan = "<div style='color: red; margin-bottom: 10px;'>Semua field harus diisi.</div>";
    } else {
        // Ambil type_rumah (string) berdasarkan id_rumah_selected dari tb_rumah
        $stmt_get_type = $conn->prepare("SELECT type_rumah FROM tb_rumah WHERE id_rumah = ?");
        // Tambahkan pengecekan jika prepare gagal
        if (!$stmt_get_type) {
            $pesan = "<div class='error-message'>Error preparing statement (get_type): " . $conn->error . "</div>";
        } else {
            $stmt_get_type->bind_param("i", $id_rumah_selected);
            $stmt_get_type->execute();
            $result_get_type = $stmt_get_type->get_result();
            if ($row_type = $result_get_type->fetch_assoc()) {
                $type_rumah_text = $row_type['type_rumah'];
            }
            $stmt_get_type->close();
        }


        // Ambil nama_blok (string) berdasarkan id_unit_selected dari tb_unit
        $stmt_get_blok = $conn->prepare("SELECT nama_blok FROM tb_unit WHERE id_unit = ?");
        // Tambahkan pengecekan jika prepare gagal
        if (!$stmt_get_blok) {
            $pesan = "<div class='error-message'>Error preparing statement (get_blok): " . $conn->error . "</div>";
        } else {
            $stmt_get_blok->bind_param("i", $id_unit_selected);
            $stmt_get_blok->execute();
            $result_get_blok = $stmt_get_blok->get_result();
            if ($row_blok = $result_get_blok->fetch_assoc()) {
                $blok_rumah = $row_blok['nama_blok'];
            }
            $stmt_get_blok->close();
        }

        // Jika type_rumah_text atau blok_rumah tidak ditemukan, berarti ID tidak valid
        if (empty($type_rumah_text) || empty($blok_rumah)) {
            $pesan = "<div style='color: red; margin-bottom: 10px;'>Type Rumah atau Blok Rumah tidak valid.</div>";
        } else {
            // Generate ID awal
            $id_pembelian = generateInitialPembelianID($type_rumah_text, $blok_rumah);
            $is_duplicate = false;

            // Cek apakah ID sudah ada di database (menggunakan prepared statement)
            $check_sql = "SELECT id_pembelian FROM tb_pembelian WHERE id_pembelian = ?";
            $stmt_check = $conn->prepare($check_sql);
            if (!$stmt_check) {
                $pesan = "<div class='error-message'>Error preparing statement (check_id): " . $conn->error . "</div>";
            } else {
                $stmt_check->bind_param("s", $id_pembelian);
                $stmt_check->execute();
                $check_result = $stmt_check->get_result();

                if ($check_result->num_rows > 0) {
                    // ID sudah ada, tambahkan timestamp
                    $id_pembelian = generateUniquePembelianID($id_pembelian);
                    $is_duplicate = true;
                }
                $stmt_check->close();
            }

            // Mulai transaksi database untuk memastikan kedua insert/update berhasil atau tidak sama sekali
            $conn->begin_transaction();

            try {
                // Query untuk menyimpan data pembelian baru
                $stmt_insert = $conn->prepare("INSERT INTO tb_pembelian (id_pembelian, tanggal_pembelian, nama_pembeli, no_ktp, telepon, alamat, id_rumah, id_unit, type_rumah, blok_rumah, status_pembelian)
                                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                // *** PERBAIKAN UTAMA DI SINI: String format "ssssssiisss" ***
                // Jumlah 's' dan 'i' harus sesuai dengan urutan dan tipe data variabel Anda.
                // Ada 11 variabel: 6 string (s), 2 integer (i), 3 string (s).
                // id_pembelian (s)
                // tanggal_pembelian (s)
                // nama_pembeli (s)
                // no_ktp (s)
                // telepon (s)
                // alamat (s) <-- ini yang sebelumnya kurang 's'
                // id_rumah_selected (i)
                // id_unit_selected (i)
                // type_rumah_text (s)
                // blok_rumah (s)
                // initial_status_pembelian (s)
                $stmt_insert->bind_param("ssssssiisss", 
                    $id_pembelian, 
                    $tanggal_pembelian, 
                    $nama_pembeli, 
                    $no_ktp, 
                    $telepon, 
                    $alamat, // Ini sekarang cocok dengan 's' keenam
                    $id_rumah_selected, 
                    $id_unit_selected, 
                    $type_rumah_text, 
                    $blok_rumah, 
                    $initial_status_pembelian
                );
                
                $stmt_insert->execute();
                $stmt_insert->close();

                // *** PENTING: HAPUS BAGIAN INI JIKA ANDA TIDAK INGIN STATUS UNIT LANGSUNG MENJADI 'TERJUAL' ***
                // Jika Anda ingin unit tetap 'Tersedia' saat awal pemesanan dan hanya berubah ketika proses booking/pembayaran,
                // maka bagian update ini harus dihilangkan atau dipindahkan ke logika lain (misalnya, saat pembayaran pertama).
                // Berdasarkan deskripsi Anda, status 'Kosong' di tb_pembelian mengindikasikan bahwa unit belum 'Terjual' secara final.
                /*
                $update_unit_sql = "UPDATE tb_unit SET status = 'Terjual' WHERE id_unit = ?";
                $stmt_update_unit = $conn->prepare($update_unit_sql);
                if (!$stmt_update_unit) {
                    throw new Exception("Error preparing statement (update_unit): " . $conn->error);
                }
                $stmt_update_unit->bind_param("i", $id_unit_selected); // Bind id_unit
                $stmt_update_unit->execute();
                $stmt_update_unit->close();
                */
                // Jika Anda ingin unit tetap 'Tersedia' saat awal pemesanan, pastikan kolom 'status' di tb_unit tidak diubah di sini.
                // Biarkan unit tetap 'Tersedia' sampai ada transaksi booking atau penjualan yang lebih lanjut.

                $conn->commit(); // Commit transaksi jika semua berhasil
                $_SESSION['pesan'] = "<div class='success-message'>Data pembelian berhasil ditambahkan dengan ID: " . htmlspecialchars($id_pembelian) . ".</div>";
                header("Location: data_pembelian.php");
                exit();

            } catch (Exception $e) {
                $conn->rollback(); // Rollback jika ada kesalahan
                $_SESSION['pesan'] = "<div class='error-message'>Error saat menyimpan data: " . $e->getMessage() . "</div>";
                header("Location: " . $_SERVER["PHP_SELF"]); // Kembali ke halaman ini untuk menampilkan error
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
    <title>Tambah Data Pembelian</title>
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
                <h2>Tambah Data Pembelian</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span>
                </div>
            </header>
            <div class="content-area">
                <div class="form-scroll-container">
                    <h2>Form Tambah Data Pembelian</h2>
                    <?php
                    // Tampilkan pesan jika ada
                    if (!empty($pesan)) {
                        if (strpos($pesan, 'berhasil ditambahkan') !== false) {
                            echo "<div class='success-message'>" . strip_tags($pesan) . "</div>";
                        } elseif (strpos($pesan, 'gagal') !== false || strpos($pesan, 'Error') !== false) {
                            echo "<div class='error-message'>" . strip_tags($pesan) . "</div>";
                        } elseif (strpos($pesan, 'Namun') !== false) {
                            echo "<div class='warning-message'>" . strip_tags($pesan) . "</div>";
                        } else {
                            echo $pesan; // Jika pesan tidak memiliki tag div, tampilkan langsung
                        }
                    }
                    ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="tanggal_pembelian">Tanggal Pembelian:</label>
                            <input type="date" id="tanggal_pembelian" name="tanggal_pembelian" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_pembeli">Nama Pembeli:</label>
                            <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Masukkan nama pembeli" required>
                        </div>
                        <div class="form-group">
                            <label for="no_ktp">Nomor KTP:</label>
                            <input type="text" id="no_ktp" name="no_ktp" placeholder="Masukkan Nomor KTP" required>
                        </div>
                        <div class="form-group">
                            <label for="telepon">Nomor Telepon:</label>
                            <input type="tel" id="telepon" name="telepon" placeholder="Masukkan Nomor Telepon" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat">Alamat Lengkap:</label>
                            <textarea id="alamat" name="alamat" placeholder="Masukkan Alamat Lengkap Pembeli" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="id_rumah">Type Rumah:</label>
                            <select id="id_rumah" name="id_rumah" required>
                                <option value="">Pilih Type Rumah</option>
                                <?php
                                if ($result_type_rumah->num_rows > 0) {
                                    while ($row_type_rumah = $result_type_rumah->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row_type_rumah["id_rumah"]) . "'>" . htmlspecialchars($row_type_rumah["type_rumah"]) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nama_blok">Blok Rumah:</label>
                            <select id="nama_blok" name="id_unit" required> <option value="">Pilih Blok Rumah</option>
                            </select>
                        </div>
                        <div class="button-group">
                            <a href="data_pembelian.php" class="back-button">Batal</a>
                            <button type="submit" class="submit-button" name="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.getElementById('id_rumah').addEventListener('change', function() {
            var idRumah = this.value; // Ambil ID rumah yang dipilih
            var blokSelect = document.getElementById('nama_blok'); // Ini sekarang akan menyimpan id_unit sebagai value
            blokSelect.innerHTML = '<option value="">Memuat...</option>'; // Opsi loading

            if (idRumah === "") { // Jika tidak ada id_rumah yang dipilih
                blokSelect.innerHTML = '<option value="">Pilih Blok Rumah</option>';
                return; // Hentikan eksekusi selanjutnya
            }

            // Mengambil data blok dari server melalui AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_blok.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        blokSelect.innerHTML = '<option value="">Pilih Blok Rumah</option>'; // Kosongkan dan tambahkan default

                        if (data.blok && data.blok.length > 0) {
                            data.blok.forEach(function(blok) {
                                var option = document.createElement('option');
                                option.value = blok.id_unit; // GANTI: Simpan id_unit sebagai value
                                option.textContent = blok.nama_blok + ' (' + blok.status + ')'; // Tampilkan nama_blok dan status

                                // Trim spasi dan ubah ke huruf kecil untuk perbandingan yang konsisten
                                if (blok.status.trim().toLowerCase() !== 'tersedia') {
                                    option.disabled = true;
                                    option.style.backgroundColor = '#f0f0f0';
                                    option.style.color = '#888';
                                }
                                blokSelect.appendChild(option);
                            });
                        } else {
                            var option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'Tidak ada blok tersedia untuk tipe rumah ini.';
                            blokSelect.appendChild(option);
                        }

                    } catch (e) {
                        console.error("Error parsing JSON response: ", e);
                        console.error("Response text: ", xhr.responseText);
                        blokSelect.innerHTML = '<option value="">Error memuat data (JSON)</option>';
                    }
                } else {
                    blokSelect.innerHTML = '<option value="">Gagal memuat blok (HTTP Error)</option>';
                    console.error('Terjadi kesalahan saat mengambil data blok: HTTP Status ' + xhr.status);
                }
            };
            xhr.onerror = function() {
                blokSelect.innerHTML = '<option value="">Kesalahan jaringan</option>';
                console.error('Kesalahan jaringan saat mengambil data blok.');
            };
            // Kirim id_rumah ke get_blok.php
            xhr.send('id_rumah=' + encodeURIComponent(idRumah));
        });

        document.addEventListener('DOMContentLoaded', function() {
            var idRumahSelect = document.getElementById('id_rumah');
            if (idRumahSelect.value) {
                idRumahSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

<?php
// Menutup koneksi database setelah digunakan
if (isset($conn)) {
    $conn->close();
}
?>
</body>
</html>