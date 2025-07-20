<?php
// Koneksi Database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Mengambil semua data rumah (Anda bisa menambahkan batasan atau kondisi sesuai kebutuhan)
$sql = "SELECT id_rumah, type_rumah, harga_rumah, foto_rumah FROM tb_rumah";
$result = $conn->query($sql);

// Tutup koneksi nanti setelah semua data ditampilkan
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Type</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

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
            height: 80px;
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

        .header-bg {
            position: relative;
            width: 100%;
            height: 300px; /* Sesuaikan tinggi header sesuai kebutuhan */
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center; /* Membuat konten vertikal di tengah */
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
            /* padding: 20px; Anda bisa menyesuaikan padding */
            border-radius: 5px;
            width: 100%; /* Lebar penuh */
            height: 100%; /* Tinggi penuh (menutupi gambar) */
            box-sizing: border-box;
            display: flex; /* Gunakan flex untuk mengatur posisi teks */
            flex-direction: column; /* Atur item menjadi kolom */
            justify-content: center; /* Pusatkan vertikal */
            align-items: center; /* Pusatkan horizontal */
        }

        .header-title {
            font-size: 1.5em;
            margin-bottom: 5px;
            color: white; /* Pastikan teks tetap terlihat */
        }

        .breadcrumb {
            font-size: 0.8em;
            color: #eee;
        }
        .breadcrumb a {
            color: #ddd;
            text-decoration: none;
        }

        .breadcrumb span {
            margin: 0 5px;
        }

        .main-content {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .residential-title {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .residential-icon {
            color: #ffc107; /* Warna kuning seperti di desain Tailwind */
            margin-right: 10px;
            font-size: 1.2em;
        }

        .product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Mengatur 3 kolom yang sama lebar */
    gap: 15px;
}

/* Tambahkan media query jika Anda ingin perilaku yang berbeda di layar yang lebih kecil */
@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Kembali ke responsif di layar kecil */
    }
}

        .product-card {
            position: relative;
            overflow: hidden;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 180px; /* Pastikan tinggi ini sesuai dengan tinggi gambar yang Anda inginkan */
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease-in-out;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Latar belakang hitam transparan */
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
            box-sizing: border-box;
            opacity: 1; /* Kotak selalu terlihat */
            /* transition: opacity 0.3s ease-in-out; Hapus baris ini */
        }

        .product-title {
            font-size: 1.1em;
            margin-bottom: 10px;
            text-align: center; /* Pusatkan teks judul */
        }

        .explore-button {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            text-decoration: none;
        }

        .explore-button:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }
        footer {
            background-color: #117c6b;
            color: white;
            padding: 15px 20px;
            text-align: center;
            margin-top: 20px;
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
    </style>
</head>
<body class="bg-white text-gray-900">
    <div class="navbar">
        <div class="nav-left">
            <img src="gambar/Logo_Green.png" style="height: 80px;" alt="Logo Green" />
        </div>

        <div class="nav-center">
            <a href="Home.php">HOME</a>
            <a href="About.php">ABOUT US</a>
            <a href="Product.php" class="active">PRODUCT</a>
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
            <h1 class="header-title">PRODUCT TYPE</h1>
            <nav class="breadcrumb">
                <a href="Home.php">Home</a> <span>></span> <span>Product Type</span>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <h2 class="residential-title">
            <span class="residential-icon">⋮⋮⋮</span> RESIDENTIAL
        </h2>
        <section aria-label="Residential properties" class="product-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<article class="product-card">';
                    echo '  <div class="product-image">';
                    if (!empty($row["foto_rumah"])) {
                        echo '      <img src="' . $row["foto_rumah"] . '" alt="' . $row["type_rumah"] . '">';
                    } else {
                        echo '      <img src="https://via.placeholder.com/300x200/0000FF/808080/?text=No+Image" alt="Tidak Ada Foto">'; // Gambar placeholder
                    }
                    echo '  </div>';
                    echo '  <div class="product-info-overlay">';
                    echo '      <h3 class="product-title">' . $row["type_rumah"] . '</h3>';
                    echo '      <a href="Detail_rumah.php?id=' . $row["id_rumah"] . '" class="explore-button">Explore</a>';
                    echo '  </div>';
                    echo '</article>';
                }
            } else {
                echo '<p>Tidak ada rumah tersedia saat ini.</p>';
            }
            ?>
        </section>
    </main>

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
    <?php
    $conn->close(); // Menutup koneksi database
    ?>
</body>
</html>