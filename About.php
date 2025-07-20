<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
        .about-container {
            max-width: 1200px;
            margin: 20px auto 50px 80px; /* Tambahkan nilai margin-left */
            padding: 20px;
            display: flex; /* Mengaktifkan flexbox untuk mengatur sidebar dan konten */
            align-items: flex-start; /* Agar sidebar dan konten sejajar dari atas */
            position: relative;
        }

        .header-image-container {
            width: 100%;
            overflow: hidden;
            margin-bottom: 20px;
            position: relative;
        }

        .header-image-container img {
            display: block;
            width: 100%;
            height: auto;
        }

        .sidebar-container {
            background-color: #d0ece7; /* Warna biru muda */
            border-radius: 8px; /* Sudut tumpul */
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); /* Efek bayangan tipis */
            padding: 15px;
            width: 200px; /* Set lebar tetap untuk sidebar */
            max-width: 200px; /* Lebar maksimum sidebar */
            min-width: 200px; /* Lebar minimum sidebar */
            margin-right: 20px; /* Memberikan jarak antara sidebar dan konten */
            flex-shrink: 0; /* Mencegah sidebar menyusut */
            position: relative;
            top: -60px; /* Mengangkat kotak agar masuk ke gambar */
            z-index: 10;
        }

        .sidebar-container h2 {
            color: #117c6b;
            margin-top: 0;
            margin-bottom: 10px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.2em;
        }

        .sidebar-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-container ul li a {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            color: #333;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
        }

        .sidebar-container ul li a.active {
            background-color: #117c6b;
            color: white;
            font-weight: 600;
        }

        .content-area {
            flex-grow: 1; /* Agar konten mengisi sisa ruang */
            padding: 20px;
            border: none; /* Hilangkan border agar teks tidak di dalam kotak */
            border-radius: 0;
            display: flex; /* Mengaktifkan flexbox di dalam content area */
            flex-direction: column; /* Mengatur item di dalam content area menjadi kolom */
            min-height: 200px; /* Set tinggi minimum content area */
            background: transparent;
        }

        .content-section {
            display: none;
            min-height: 150px; /* Set tinggi minimum untuk setiap section */
        }

        .content-section.active {
            display: block;
            height: auto;
            overflow-y: visible;
            font-size: 1em; /* Tambahkan properti font-size di sini */
        }

        .content-section h3 {
            color: #117c6b;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .content-section p {
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    color: #555;
    margin-bottom: 15px; /* Beri sedikit jarak antar paragraf */
    text-align: justify; /* Tambahkan properti ini */
}

        /* Styling untuk Footer */
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

      /* Styling untuk Bagian Tim */
.team-section {
    margin-top: 40px;
    padding: 20px;
    text-align: center;
}

.team-section .team-title {
    color: #117c6b;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    margin-bottom: 30px;
    font-size: 2.5em;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(15, 47px); /* Kita bagi menjadi 6 bagian untuk fleksibilitas lebar */
    gap: 30px;
    margin-top: 20px;
    grid-auto-rows: minmax(auto, 200px); /* Set tinggi maksimum baris */
    margin-left: 50px; /* Tambahkan margin kiri */
    margin-right: 50px; /* Tambahkan margin kanan */
}

.team-member {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
    display: flex;
    align-items: stretch;
}

.team-member:hover {
    transform: scale(1.05);
}

.team-member img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
        /* Mengatur lebar berbeda untuk setiap foto berdasarkan 6 kolom */
.team-grid > div:nth-child(1) { grid-column: span 4; } /* Lebih kecil */
.team-grid > div:nth-child(2) { grid-column: span 6; } /* Tetap */
.team-grid > div:nth-child(3) { grid-column: span 5; } /* Lebih kecil */
.team-grid > div:nth-child(4) { grid-column: span 5; } /* Tetap */
.team-grid > div:nth-child(5) { grid-column: span 4; } /* Lebih kecil */
.team-grid > div:nth-child(6) { grid-column: span 6; } /* Lebih kecil */
.team-grid > div:nth-child(7) { grid-column: span 6; } /* Lebih kecil */
.team-grid > div:nth-child(8) { grid-column: span 5; } /* Tetap */
.team-grid > div:nth-child(9) { grid-column: span 4; } /* Lebih kecil */

    </style>
</head>
<body>
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

    <div class="header-image-container">
        <img src="gambar/iklan1.png" alt="Gambar Tentang Kami">
    </div>

    <div class="about-container">
        <div class="sidebar-container">
            <h2>Tentang Kami</h2>
            <ul>
                <li><a href="#sejarah" data-target="sejarah" class="active">Sejarah Perusahaan</a></li>
                <li><a href="#visi-misi" data-target="visi-misi">Visi dan Misi</a></li>
                <li><a href="#master-plan" data-target="master-plan">Master Plan</a></li>
            </ul>
        </div>
        <div class="content-area">
        <div id="sejarah" class="content-section active">
                <h3>Sejarah Perusahaan</h3>
                <p>Green Resort merupakan kawasan hunian yang dikembangkan oleh PT Bintang Agung Property, sebuah perusahaan pengembang yang telah hadir di Kota Palembang sejak tahun 2013. Sejak awal, Green Resort memiliki komitmen untuk menghadirkan hunian berkualitas tinggi yang mengedepankan keamanan dan kenyamanan bagi para penghuninya.</p>
                <p>Jejak pengembangan Green Resort dimulai di area seluas 17 Hektar, di mana direncanakan akan dibangun sebanyak 830 unit rumah. Proyek ini menjadi yang pertama bagi PT Bintang Agung Property dan terus berkembang hingga saat ini. Dalam setiap tahap pengembangannya, Green Resort selalu dikenal sebagai kawasan hunian yang mengutamakan kepuasan penghuninya melalui produk berkualitas dan pelayanan yang prima.</p>
                <p>Berbekal pengetahuan, pengalaman, dedikasi, dan komitmen yang kuat, Green Resort yang dikembangkan oleh PT Bintang Agung Property akan terus berupaya menjadi salah satu kawasan hunian terbaik di Indonesia, menciptakan lingkungan kehidupan yang harmonis dan berkelanjutan bagi masyarakat.</p>
            </div>
            <div id="visi-misi" class="content-section">
                <h3>Visi</h3>
                <p>Menjadi perusahaan pengembang (Developer) Property terbaik dan terpercaya yang mampu bersaing sesuai dengan kelasnya.</p>
                <h3>Misi</h3>
                <p> 1.	Mewujudkan hunian berkualitas tinggi dengan harga terjangkau</p>
                <p> 2.	Mengembangkan perumahan dan permukiman yang bernilai tambah</p>
                <p> 3.	Menciptakan peluang untuk meningkatkan kesejahteraan masyarakat</p>
                <p> 4.	Menjadi yang terdepan dalam bisnis properti berperan serta membangun  negeri dalam bidang properti</p>
                <p> 5.	Menghasilkan profit didukung produk berkualitas, aman dan sehat</p>
                <p> 6.	Menjaga dan meningkatkan kualitas produk dan kepuasan pelanggan</p>
                <p> 7.	Menjadi perusahaan property unggul yang dapat memberikan manfaat dan nilai tambah bagi masyarakat</p>
                <p> 8.	Memberikan pelayanan terbaik dan membuat produk yang berkualitas, lingkungan yang nyaman, aman dan sehat.</p>
                <p> 9.	Membangun manajemen perusahaan yang professional serta menjaga kesinambungan pertumbuhan perusahaan.</p>
                <p>10.	Menjalin hubungan kerja sama dengan mitra usaha yang saling menguntungkan dan  berkelanjutan.</p>
                <p>11.	Memaksimalkan potensi setiap properti yang dikembangkan melalui pengembangan terintegrasi untuk memberi nilai tambah yang tinggi. </p>
                <p>12.	Menciptakan lingkungan kerja yang profesional dan meningkatkan produktivitas perusahaan</p>
            </div>
            <div id="master-plan" class="content-section">
                <h3>Master Plan</h3>
                <p>Perumahan Green Resort terbagi menjadi 7 Cluster dan  memiliki  10  tipe rumah,  serta  dilengkapi dengan fasilitas-fasilitas yang  dapat  digunakan oleh  warga  Perumahan Green Resort Palembang</p>
                <img src="gambar/Master_plan.png" alt="Master Plan Green Resort" style="width: 100%; height: auto; margin-top: 20px;">
            </div>
        </div>
    </div>

    <div class="team-section">
        <h2 class="team-title">... and our amazing team</h2>
        <div class="team-grid">
            <div class="team-member"><img src="gambar/pg1.jpg" alt="Anggota Tim 1"></div>
            <div class="team-member"><img src="gambar/pg2.jpg" alt="Anggota Tim 2"></div>
            <div class="team-member"><img src="gambar/pg3.jpg" alt="Anggota Tim 3"></div>
            <div class="team-member"><img src="gambar/pg4.jpg" alt="Anggota Tim 4"></div>
            <div class="team-member"><img src="gambar/pg5.jpg" alt="Anggota Tim 5"></div>
            <div class="team-member"><img src="gambar/pg6.jpg" alt="Anggota Tim 6"></div>
            <div class="team-member"><img src="gambar/pg7.jpg" alt="Anggota Tim 7"></div>
            <div class="team-member"><img src="gambar/pg8.jpg" alt="Anggota Tim 8"></div>
            <div class="team-member"><img src="gambar/pg9.jpg" alt="Anggota Tim 9"></div>
        </div>
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
        const sidebarLinks = document.querySelectorAll('.sidebar-container ul li a');
        const contentSections = document.querySelectorAll('.content-section');

        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const targetId = this.getAttribute('data-target');

                // Hapus kelas 'active' dari semua link dan section
                sidebarLinks.forEach(link => link.classList.remove('active'));
                contentSections.forEach(section => section.classList.remove('active'));

                // Tambahkan kelas 'active' pada link dan section yang sesuai
                this.classList.add('active');
                document.getElementById(targetId).classList.add('active');

                // Optional: Efek transisi scroll halus ke bagian konten (jika diinginkan)
                document.getElementById(targetId).scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>