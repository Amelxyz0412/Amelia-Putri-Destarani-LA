<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rumah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
       body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    min-height: 100vh; /* Pastikan body setidaknya setinggi viewport */
    display: flex;
    flex-direction: column; /* Mengatur body sebagai flex container vertikal */
}

        .container .house-image-container {
            width: 95%;
            max-width: 95%;
            height: 500px;
            margin-top: 50px;
            margin-bottom: 50px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .container .house-image-container img.house-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        h2 {
            color: #117c6b; /* Warna hijau tema */
            font-family: 'Montserrat', sans-serif; /* Font judul diubah menjadi Montserrat */
            margin-bottom: 15px;
        }

        .description {
            color: #555;
            line-height: 1.7; /* Sedikit peningkatan line-height untuk keterbacaan */
            margin-bottom: 25px;
        }

       /* Tambahkan CSS ini di dalam tag <style> */
.unit-grid {
    display: grid;
    grid-template-columns: repeat(10, 1fr); /* 10 kolom */
    gap: 10px;
    margin-top: 20px; /* Ini mungkin menyebabkan jarak yang tidak diinginkan */
}

.unit-box {
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    padding: 15px;
    border-radius: 5px;
    text-align: center;
    font-weight: 600;
    color: #555;
    transition: background-color 0.2s ease;
    text-decoration: none; /* <--- TAMBAHKAN ATAU PASTIKAN INI ADA */
    display: block;
}


.unit-available {
    background-color: white;
}

.unit-sold {
    background-color: red;
    color: white;
}

.unit-booked {
    background-color:#117c6b;
    color: white;
}


        /* Style untuk bagian SITE PLAN & FLOOR PLAN */
        .site-plan-toggle,
        .floor-plan-toggle {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .toggle-header {
            background-color: #f9f9f9;
            color: #333;
            padding: 12px 18px; /* Sedikit peningkatan padding */
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            font-family: 'Montserrat', sans-serif; /* Font header toggle diubah menjadi Montserrat */
            font-weight: 600; /* Sedikit bold */
        }

        .toggle-icon {
            font-size: 1.2em;
            margin-right: 12px; /* Sedikit peningkatan margin */
            color: #117c6b; /* Warna ikon hijau */
        }

        .image-container {
            padding: 15px; /* Sedikit peningkatan padding */
            background-color: #fff;
            text-align: center;
        }

        .toggle-image {
            max-width: 95%; /* Sedikit peningkatan max-width */
            height: auto;
            display: block;
            margin: 10px auto; /* Sedikit penyesuaian margin */
            border-radius: 4px; /* Sedikit border radius pada gambar */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); /* Sedikit shadow pada gambar */
        }

        /* Styling untuk Navbar (Full Width) */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex-wrap: wrap;
            overflow-x: auto;
        }

        .nav-left img {
            height: 80px;
            flex-shrink: 0;
        }

        .nav-center {
            display: flex;
            align-items: center;
            gap: 18px;
            justify-content: center;
            flex-grow: 1;
            flex-wrap: wrap;
        }

        .nav-center a {
            text-decoration: none;
            color: #117c6b;
            font-weight: 500;
            font-size: 14px;
            padding: 6px 8px;
            white-space: nowrap;
            border-right: 1px solid #ccc;
        }

        .nav-center a:last-child {
            border-right: none;
        }

        .nav-center .login-btn {
            background-color: #117c6b;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .nav-center .login-btn:hover {
            background-color: #0e6655;
        }

        .nav-right img {
            height: 50px;
            flex-shrink: 0;
        }

        /* Optional Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-center {
                justify-content: flex-start;
                flex-wrap: wrap;
                gap: 10px;
            }

            .nav-left, .nav-right {
                margin-bottom: 10px;
            }
        }

        /* Styling untuk Header (Full Width) */
        .header-bg {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        .header-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
        }

        .header-content {
            background-color: rgba(0, 0, 0, 0.5);
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .header-title {
            font-size: 2em; /* Sedikit peningkatan font-size */
            margin-bottom: 8px; /* Sedikit penyesuaian margin */
            color: white;
            font-family: 'Montserrat', sans-serif; /* Font judul header diubah menjadi Montserrat */
            font-weight: 700; /* Lebih bold */
        }

        .breadcrumb {
            font-size: 0.9em; /* Sedikit peningkatan font-size */
            color: #eee;
        }

        .breadcrumb a {
            color: #ddd;
            text-decoration: none;
        }

        .breadcrumb span {
            margin: 0 8px; /* Sedikit penyesuaian margin */
        }

        /* Styling untuk Footer */
       footer {
    background-color: #117c6b;
    color: white;
    padding: 15px 20px;
    text-align: center;
    /* Remove margin-top if you want it to directly follow the content,
       or adjust it if flex-grow doesn't fill enough space above it. */
    margin-top: 20px; /* Pertahankan ini jika ada jarak yang diinginkan dari konten di atasnya */
}

        footer .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            grid-template-rows: auto auto;
            align-items: center;
            gap: 5px;
        }

        footer .contact-info {
            text-align: center;
            grid-column: 1 / 4;
        }

        footer .contact-info p {
            font-size: 0.9em;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 3px;
        }

        footer .contact-info .phone-email {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 3px;
        }

        footer .tagline {
            text-align: left;
            grid-row: 2;
            grid-column: 1;
        }

        footer .tagline p {
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 3px;
        }

        footer .social-icons {
            text-align: right;
            grid-row: 2;
            grid-column: 3;
        }

        footer .social-icons div {
            display: flex;
            gap: 20px;
            justify-content: flex-end;
        }

        footer .social-icons a {
            text-decoration: none;
            color: white;
            font-size: 1.2em;
        }

        footer hr {
            border-color: #fff;
            margin: 10px 0;
        }

        footer .copyright {
            text-align: center;
            font-size: 0.8em;
            font-family: 'Poppins', sans-serif;
        }


        /* Style untuk bagian DESCRIPTION dan ADDITIONAL DETAILS (Di dalam container) */
        .detail-section {
            margin-bottom: 20px; /* Sedikit peningkatan margin-bottom */
            padding: 20px; /* Sedikit peningkatan padding */
            border-radius: 6px; /* Sedikit peningkatan border-radius */
        }

        .detail-header {
            display: flex;
            align-items: center;
            gap: 10px; /* Sedikit peningkatan gap */
        }

        .detail-icon {
            color: #117c6b; /* Warna hijau tema untuk ikon */
            font-size: 1.1em; /* Sedikit peningkatan font-size ikon */
        }

        .detail-title {
            color: #333;
            font-family: 'Montserrat', sans-serif; /* Font judul detail diubah menjadi Montserrat */
            font-size: 1.3em; /* Sedikit peningkatan font-size */
            font-weight: 600; /* Sedikit bold */
        }

        .detail-paragraph {
            color: #555;
            line-height: 1.7; /* Sedikit peningkatan line-height */
            margin-bottom: 15px; /* Sedikit peningkatan margin-bottom */
        }

        .facility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Penyesuaian lebar minimum grid */
            gap: 12px; /* Sedikit peningkatan gap */
            margin-top: 15px; /* Sedikit peningkatan margin-top */
        }

        .overflow-x-auto {
            overflow-x: auto;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: none; /* Hapus border keseluruhan tabel */
        }

        .detail-table th,
        .detail-table td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd; /* Pertahankan hanya border bawah */
        }

        .detail-table th {
            background-color: rgba(17, 124, 107, 0.1); /* Warna hijau dengan opasitas rendah */
            font-weight: bold;
            font-family: 'Montserrat', sans-serif;
            color: #333;
            width: 35%; /* Sesuaikan lebar kolom pertama jika perlu */
        }

        /* Hilangkan border bawah pada baris terakhir */
        .detail-table tr:last-child th,
        .detail-table tr:last-child td {
            border-bottom: none;
        }

       
.house-price-row {
    display: flex; /* Aktifkan Flexbox untuk baris harga dan tipe */
    align-items: center; /* Rata tengah secara vertikal */
    justify-content: space-between; /* Atur jarak antara elemen */
    gap: 20px; /* Jarak antara elemen */
    width: 100%; /* Pastikan pengaturan lebar yang diinginkan */
}

.house-price {
    color: #117c6b; /* Warna hijau tema */
    font-size: 2.5em;
    font-weight: bold;
    margin: 0; /* Hapus margin atas */
    font-family: 'Montserrat', sans-serif; /* Tambahkan font yang diinginkan */
    padding: 20px;
}

.house-type {
    color: #117c6b; /* Warna hijau tema */
    font-size: 2.5em;
    font-weight: bold;
    margin: 0; /* Hapus margin atas */
    font-family: 'Montserrat', sans-serif; /* Tambahkan font yang diinginkan */
    white-space: nowrap; /* Mencegah teks tipe pecah baris */
    padding: 20px;
}
/* CSS untuk keterangan status unit */
.unit-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 20px; /* Jarak antar item keterangan */
    margin-top: 15px; /* Jarak dari judul "UNIT TERSEDIA" */
    margin-bottom: 20px; /* Jarak ke grid unit */
    align-items: center;
    font-family: 'Poppins', sans-serif;
    color: #555;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px; /* Jarak antara ikon dan teks */
}

.legend-box {
    width: 20px; /* Ukuran kotak ikon */
    height: 20px;
    border: 1px solid #ccc; /* Border default */
    border-radius: 3px; /* Sedikit border-radius */
}

.legend-box.sold {
    background-color: red;
    border-color: red;
}

.legend-box.booked {
    background-color: #117c6b; /* Hijau tema */
    border-color: #117c6b;
}

.legend-box.available {
    background-color: white;
    border-color: #ccc;
}

    </style>
</head>
<body>

    <?php
    // Koneksi Database
    $host = "localhost"; // Host
$username = "root"; // Username
$password = ""; // Password (kosongkan jika Anda tidak mengatur password untuk root)
$database = "db_perumahan"; // Nama database

$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
    ?>

    <div class="navbar">
        <div class="nav-left">
            <img src="gambar/Logo_Green.png" style="height: 80px;" alt="Logo Green" />
        </div>

        <div class="nav-center">
            <a href="Home.php">HOME</a>
            <a href="About.php">ABOUT US</a>
            <a href="Product.php">PRODUCT</a>
            <a href="Contact.php">CONTACT US</a>
            <a href="Login.php" class="login-btn">Login Agent</a>
        </div>

        <div class="nav-right">
            <img src="gambar/Logo_Bintang.jpg" style="height: 80px;" alt="Logo Bintang" />
        </div>
    </div>

    <header class="header-bg">
        <img src="gambar/iklan1.png" alt="Gambar Latar Belakang Header">
        <div class="header-content">
            <h1 class="header-title">DETAIL PRODUCT</h1>
            <nav class="breadcrumb">
                <a href="Home.php">Home</a> <span>></span> <a href="Product.php">Product</a> <span>></span> <span>Detail</span>
            </nav>
        </div>
    </header>

    <div class="container">

        <?php
        // Ambil ID rumah dari URL
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id_rumah = $_GET['id'];

            // Query untuk mengambil detail rumah berdasarkan ID
            $sql = "SELECT * FROM tb_rumah WHERE id_rumah = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_rumah);
            $stmt->execute();
            $result = $stmt->get_result();
            $rumah = $result->fetch_assoc();
            $stmt->close();

            if ($rumah) {

                // Gambar Rumah (Full Width di dalam Container)
                if (!empty($rumah['foto_rumah'])) {
                    echo '<div class="house-image-container">';
                    echo "<img src='" . htmlspecialchars($rumah['foto_rumah']) . "' alt='Foto Rumah' class='house-image'>";
                    echo '</div>';
                }

               // Harga Rumah dan Type Rumah
                if (!empty($rumah['harga_rumah']) || !empty($rumah['type_rumah'])) {
                    echo "<div class='house-price-row'>"; // Menggabungkan menjadi satu baris
                if (!empty($rumah['type_rumah'])) {
                        echo "<p class='house-type'>" . htmlspecialchars($rumah['type_rumah']) . "</p>";
                    }
                     if (!empty($rumah['harga_rumah'])) {
                        echo "<h2 class='house-price'>IDR " . number_format(htmlspecialchars($rumah['harga_rumah']), 0, ',', '.') . "</h2>";
                    }
                    echo "</div>"; // Tutup div pembungkus
                }


                // Deskripsi
                if (!empty($rumah['deskripsi'])) {
                    echo "<section class='detail-section'>";
                    echo "<div class='detail-header'>";
                    echo "<i class='fas fa-chevron-down detail-icon'></i>";
                    echo "<h2 class='detail-title'>DESCRIPTION</h2>";
                    echo "</div>";
                    echo "<p class='detail-paragraph'>" . nl2br(htmlspecialchars($rumah['deskripsi'])) . "</p>";
                    echo "</section>";
                }

                // Additional Details (Tabel 2 Kolom)
                echo "<section class='detail-section'>";
                echo "<div class='detail-header'>";
                echo "<i class='fas fa-chevron-down detail-icon'></i>";
                echo "<h2 class='detail-title'>ADDITIONAL DETAILS</h2>";
                echo "</div>";
                echo "<div class='overflow-x-auto'>";
                echo "<table class='detail-table'>";
                echo "<tbody>";

                // Ambil konten spesifikasi_detail dari database
                $spesifikasi_detail_content = htmlspecialchars($rumah['spesifikasi_detail'] ?? '');

                // Pisahkan setiap baris berdasarkan newline
                $lines = explode("\n", $spesifikasi_detail_content);

                foreach ($lines as $line) {
                    // Trim whitespace dari awal/akhir baris
                    $line = trim($line);

                    // Pastikan baris tidak kosong
                    if (!empty($line)) {
                        // Pisahkan label dan value berdasarkan KOMA PERTAMA
                        $parts = explode(':', $line, 2); // Batasi split hanya pada ':' pertama

                        $label = '';
                        $value = '';

                        if (count($parts) >= 1) {
                            $label = trim($parts[0]);
                        }
                        if (count($parts) >= 2) {
                            $value = trim($parts[1]);
                        }

                        // Tampilkan sebagai baris tabel jika ada label
                                                // Tampilkan sebagai baris tabel jika ada label
                        if (!empty($label)) {
                            echo "<tr>";
                            echo "<th>" . $label . "</th>";
                            echo "<td>" . $value . "</td>";
                            echo "</tr>";
                        }
                    }
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo "</section>";

                // SITE PLAN & FLOOR PLAN
                echo "<section class='detail-section'>";
                echo "<div class='detail-header'>";
                echo "<i class='fas fa-chevron-down detail-icon'></i>";
                echo "<h2 class='detail-title'>SITE PLAN & FLOOR PLAN</h2>";
                echo "</div>";

                echo "<div class='site-plan-toggle'>";
                echo "<div class='toggle-header' onclick='toggleSitePlan()'>";
                echo "<span class='toggle-icon'>+</span> Site Plan";
                echo "</div>";
                echo "<div id='sitePlanImageContainer' class='image-container' style='display: none;'>";
                if (!empty($rumah['site_plan'])) {
                    echo "<img src='" . htmlspecialchars($rumah['site_plan']) . "' alt='Site Plan' class='toggle-image'>";
                }
                echo "</div>";
                echo "</div>";

                echo "<div class='floor-plan-toggle'>";
                echo "<div class='toggle-header' onclick='toggleFloorPlan()'>";
                echo "<span class='toggle-icon'>+</span> Floor Plan";
                echo "</div>";
                echo "<div id='floorPlanImageContainer' class='image-container' style='display: none;'>";
                if (!empty($rumah['floor_plan'])) {
                    echo "<img src='" . htmlspecialchars($rumah['floor_plan']) . "' alt='Floor Plan' class='toggle-image'>";
                }
                echo "</div>";
                echo "</div>";
                echo "</section>";

// Tampilkan Unit Tersedia
                    echo "<section class='detail-section'>";
                    echo "<div class='detail-header'>";
                    echo "<i class='fas fa-chevron-down detail-icon'></i>";
                    echo "<h2 class='detail-title'>UNIT RUMAH</h2>";
                    echo "</div>";
                    echo "<div class='unit-legend'>";
                    echo "<div class='legend-item'><div class='legend-box sold'></div> Terjual</div>";
                    echo "<div class='legend-item'><div class='legend-box booked'></div> Terbooking</div>";
                    echo "<div class='legend-item'><div class='legend-box available'></div> Tersedia</div>";
                    echo "</div>";
                    echo "<div class='unit-grid'>"; 

// Query untuk mengambil unit berdasarkan id_rumah
$sql_unit = "SELECT nama_blok, status FROM tb_unit WHERE id_rumah = ?";
$stmt_unit = $conn->prepare($sql_unit);
$stmt_unit->bind_param("i", $id_rumah);
$stmt_unit->execute();
$result_unit = $stmt_unit->get_result();

// Tampilkan data unit
while ($unit = $result_unit->fetch_assoc()) {
    $status_class = '';
    switch ($unit['status']) {
        case 'tersedia':
            $status_class = 'unit-available';
            break;
        case 'terjual':
            $status_class = 'unit-sold';
            break;
        case 'terbooking':
            $status_class = 'unit-booked';
            break;
        default:
            $status_class = ''; // Pastikan ada kelas default jika status tidak cocok
            break;
    }
    // Buat setiap kotak unit menjadi tautan ke halaman detailnya, meneruskan id_rumah dari rumah saat ini
    // id_rumah sudah tersedia di variabel $id_rumah dari permintaan GET
    echo "<a href='detail_rumah.php?id=" . htmlspecialchars($id_rumah) . "&blok=" . urlencode($unit['nama_blok']) . "' class='unit-box " . $status_class . "'>" . htmlspecialchars($unit['nama_blok']) . "</a>";
}

echo "</div>"; // Tutup unit-grid
echo "</section>"; // Tutup section

$stmt_unit->close();
            }
        }
?>
<div class="content-spacer"></div>
</div> 
   <footer>
        <div class="container">
            <div class="contact-info">
                <p>Jl. Bypass Soekarno Hatta - Terminal Km12, Kecamatan Alang-Alang Lebar, Kota Palembang, Sumatera Selatan, 30151</p>
                <div class="phone-email">
                    <p>Telepon: (0711) 5645669</p>
                    <p>Email: info@greenresortcity.com</p>
                </div>
            </div>
            <div class="tagline">
                <p>#RumahIdamanKeluargaBahagia</p>
            </div>
            <div class="social-icons">
                <div>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="far fa-envelope"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
        <hr style="border-color: #fff; margin: 10px 0;">
        <div class="copyright">
            © 2025 Green Resort
        </div>
    </footer>



    <script>
        function toggleSitePlan() {
            var container = document.getElementById("sitePlanImageContainer");
            var icon = document.querySelector(".site-plan-toggle .toggle-icon");
            if (container.style.display === "none") {
                container.style.display = "block";
                icon.textContent = "-";
            } else {
                container.style.display = "none";
                icon.textContent = "+";
            }
        }

        function toggleFloorPlan() {
            var container = document.getElementById("floorPlanImageContainer");
            var icon = document.querySelector(".floor-plan-toggle .toggle-icon");
            if (container.style.display === "none") {
                container.style.display = "block";
                icon.textContent = "-";
            } else {
                container.style.display = "none";
                icon.textContent = "+";
            }
        }
    </script>
    </body>
    </html>
