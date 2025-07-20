<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
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

        /* Styling untuk Carousel */
        .carousel-container {
            width: 100%;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }

        .carousel-wrapper {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .carousel-item {
            min-width: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        .carousel-item img {
            display: block;
            width: 100%;
            height: auto;
        }

        .carousel-controls {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            transform: translateY(-50%);
        }

        .carousel-controls button {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .carousel-controls button:hover {
            opacity: 1;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .carousel-indicators button {
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .carousel-indicators button.active {
            background-color: #fff;
            opacity: 1;
        }

        .more-info-box {
    background-color: #117c6b;
    color: white;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 1.2em;
    padding: 10px 20px;
    display: inline-block;
    width: 150px; /* Contoh lebar tetap */
    text-align: center; /* Agar teks tetap di tengah kotak */
}

       /* Styling untuk Welcome Section */
.welcome-section {
    text-align: center;
    padding: 40px;
    margin-bottom: 40px;
}

.welcome-title {
    font-size: 2.5em;
    color: #117c6b;
    margin-bottom: 20px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
}

.welcome-description-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px; /* Mengurangi margin bawah antar baris deskripsi */
    gap: 20px;
}

.welcome-description-left, .welcome-description-right {
    flex: 1;
    text-align: justify; /* Pastikan ini tetap ada */
    /* HAPUS BARIS INI --> text-align: left; */
    font-size: 1em;
    color: #333;
    line-height: 1.6;
    font-family: 'Poppins', sans-serif;
    margin-bottom: 5px;
}

/* Responsive adjustments untuk layar kecil */
@media (max-width: 768px) {
    .welcome-description-container {
        flex-direction: column;
        gap: 10px;
        margin-bottom: 10px; /* Sesuaikan margin bawah saat ditumpuk */
    }

    .welcome-description-left, .welcome-description-right {
        text-align: left; /* Kembali ke rata kiri saat ditumpuk */
        margin-bottom: 8px; /* Sesuaikan margin bawah pada paragraf saat ditumpuk */
    }
}

        /* Styling untuk Info Area dengan Gambar dan Teks */
        .info-area {
            display: flex;
            justify-content: space-around;
            padding: 20px;
            text-align: center;
            background-color: #f9f9f9;
            color: #117c6b; /* Warna hijau yang Anda gunakan */
        }

        .info-item {
            flex: 1;
        }

        .info-item img {
            height: 70px;
            margin-bottom: 5px;
        }

        .counter {
            font-size: 1.8em; /* Ukuran font counter diperkecil */
            color: #117c6bb; /* Warna hijau untuk counter */
            margin-bottom: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .label {
    font-size: 1.2em;
    color: #444; /* Warna abu-abu yang lebih solid */
    opacity: 0.9; /* Opasitas sedikit lebih tinggi */
    font-family: 'Montserrat', sans-serif;
    font-weight: 600; /* Membuat font lebih tebal */
}

        .description-text {
            display: none;
        }

        /* Styling untuk Facilities Section */
/* Styling untuk Facilities Box */
.facilities-box {
    background-color: #117c6b;
    color: white;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 1.2em;
    padding: 10px 20px;
    display: inline-block;
    width: 150px; /* Lebar yang sama dengan more-info-box */
    text-align: center; /* Agar teks tetap di tengah kotak */
}

/* Styling untuk Facilities Section (setelah perubahan) */
.facilities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid */
    gap: 20px;
    margin-top: 20px; /* Jarak antara kotak "Our Facilities" dan grid */
    padding: 30px 20px; /* Tambahkan padding atas dan bawah */
    background-color: #f9f9f9;
    color: #117c6b; /* Mengubah warna ikon menjadi hijau (mirip dengan warna tema) */
}

.facility-card {
    background-color: white; /* Latar belakang putih untuk card */
    padding: 20px;
    text-align: center;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid #e0e0e0; /* Tambahkan border tipis agar terlihat lebih terpisah */
}

.facility-card img {
    height: 50px;
    margin-bottom: 10px;
    opacity: 0.7; /* Sedikit transparan */
    filter: grayscale(50%) sepia(100%) hue-rotate(150deg) saturate(200%); /* Efek warna agar senada hijau (opsional) */
}

.facility-name {
    font-size: 1.3em;
    color: #333;
    margin-bottom: 10px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 600;
}

.facility-description {
    font-size: 1em;
    color: #555;
    line-height: 1.6;
}

#why-vuera-section i {
    color: #117c6b; /* Warna hijau yang Anda gunakan */
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

    <div class="carousel-container">
        <div class="carousel-wrapper">
            <div class="carousel-item">
                <img src="gambar/iklan4.jpg" alt="Gambar 1" />
            </div>
            <div class="carousel-item">
                <img src="gambar/iklan5.jpg" alt="Gambar 2" />
            </div>
            <div class="carousel-item">
                <img src="gambar/iklan6.jpg" alt="Gambar 3" />
            </div>
        </div>
        <div class="carousel-controls">
            <button class="prev-btn">&#10094;</button>
            <button class="next-btn">&#10095;</button>
        </div>
        <div class="carousel-indicators">
        </div>
    </div>

    <div class="welcome-section">
    <h1 class="welcome-title">Welcome to Green Resort – Perumahan Terbaik di Palembang</h1>
    <div class="welcome-description-container">
        <p class="welcome-description-left">Green Resort merupakan The largest integrated housing development by PT Bintang Agung Property yang sudah berdiri sejak 2013. Proyek ini memiliki luas 17 Ha yang akan dibangun sebanyak 830 unit rumah. Terlebih, kini telah beroperasional ratusan fasilitas pendidikan, rekreasi, kesehatan, agama dan retail untuk memenuhi semua kebutuhan warga baik yang berada di dalam maupun sekitar kawasan. </p>
        <p class="welcome-description-right">Dalam mengembangkan proyeknya, Green Resort telah dikenal sebagai hunian yang mengutamakan kepuasan penghuninya dengan selalu memberikan produk hunian yang berkualitas serta pelayanan yang baik. Dengan mengandalkan pengetahuan, pengalaman, dedikasi dan komitmen guna membentuk lingkungan kehidupan yang harmonis dan berkelanjutan.</p>
    </div>
</div>

    <div style="text-align: left; margin-bottom: 15px;">
        <div class="more-info-box">More Info</div>
    </div>

    <div class="info-area">
    <div class="info-item">
        <i class="fas fa-map fa-3x"></i>
        <div id="land-area-counter" class="counter" data-target="170000">0</div>
        <div class="label">Land Area</div>
        <div class="description-text">Luas keseluruhan area pengembangan.</div>
    </div>
    <div class="info-item">
        <i class="fas fa-home fa-3x"></i>
        <div id="residential-counter" class="counter" data-target="830">0</div>
        <div class="label">Residential Cluster</div>
        <div class="description-text">Jumlah kelompok hunian yang tersedia.</div>
    </div>
    <div class="info-item">
        <i class="fas fa-tree fa-3x"></i>
        <div id="facilities-counter" class="counter" data-target="6">0+</div>
        <div class="label">Public Facilities</div>
        <div class="description-text">Fasilitas umum yang dapat diakses oleh penghuni.</div>
    </div>
    <div class="info-item">
        <i class="fas fa-users fa-3x"></i>
        <div id="residents-counter" class="counter" data-target="1000">0</div>
        <div class="label">Residents</div>
        <div class="description-text">Perkiraan total jumlah penduduk yang tinggal.</div>
    </div>
    <div class="info-item">
        <i class="fas fa-shopping-bag fa-3x"></i>
        <div id="outlets-counter" class="counter" data-target="100">0</div>
        <div class="label">Retail Outlets</div>
        <div class="description-text">Jumlah toko dan unit komersial yang ada.</div>
    </div>
</div>
    <div style="text-align: left; margin-top: 30px; margin-bottom: 20px;">
    <div class="facilities-box">Our Facilities</div>
</div>
<div class="facilities-grid">
    <div class="facility-card">
        <i class="fas fa-shopping-cart fa-3x"></i> <h3 class="facility-name">SHOPPING<br>CENTER</h3>
        <p class="facility-description">Beragam Pusat Perbelanjaan Modern dan Ternama.</p>
    </div>
    <div class="facility-card">
        <i class="fas fa-graduation-cap fa-3x"></i> <h3 class="facility-name">EDUCATION<br>CENTER</h3>
        <p class="facility-description">Fasilitas Pendidikan Lengkap bertaraf internasional.</p>
    </div>
    <div class="facility-card">
        <i class="fas fa-hospital fa-3x"></i> <h3 class="facility-name">HEALTH<br>CENTER</h3>
        <p class="facility-description">Fasilitas kesehatan berkelas dunia untuk keluarga.</p>
    </div>
    <div class="facility-card">
        <i class="fas fa-bed fa-3x"></i> <h3 class="facility-name">HOTEL</h3>
        <p class="facility-description">Beragam hotel berbintang yang siap mendukung kegiatan bisnis dan liburan keluarga anda.</p>
    </div>
    <div class="facility-card">
        <i class="fas fa-futbol fa-3x"></i> <h3 class="facility-name">SPORT<br>CENTER</h3>
        <p class="facility-description">Fasilitas Olahraga berkelas mulai dari lapangan golf hingga stadion internasional.</p>
    </div>
    <div class="facility-card">
        <i class="fas fa-bus fa-3x"></i> <h3 class="facility-name">TRANSPORTATION</h3>
        <p class="facility-description">Aksesibilitas dan konektivitas tanpa batas yang menghubungkan dengan berbagai lokasi lainnya.</p>
    </div>
    <div class="facility-card">
        <i class="fas fa-heart fa-3x"></i> <h3 class="facility-name">LIFESTYLE<br>CENTER</h3>
        <p class="facility-description">Ragam fasilitas pusat gaya hidup yang mendukung kebutuhan hiburan.</p>
    </div>
    <div class="facility-card">
        <i class="fas fa-building fa-3x"></i> <h3 class="facility-name">CONVENTION<br>HALL</h3>
        <p class="facility-description">President University Convention Center lokasi pameran & konser musik.</p>
    </div>
</div>
    <div style="text-align: left; margin-top: 30px; margin-bottom: 20px;">
    <div class="facilities-box">Green Resort City Map</div>
</div>
<div style="padding: 20px;">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.6278425445657!2d104.6896095!3d-2.922885700000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3b7498a6c71f7b%3A0xe2586e2d900304ec!2sPerumahan%20Green%20Resort%20Km12!5e0!3m2!1sid!2sid!4v1746160099481!5m2!1sid!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>
<div id="why-vuera-section" style="text-align: center; padding: 40px 20px; background-color: #f9f9f9;">
    <h2 style="color: #117c6b; font-family: 'Poppins', sans-serif; font-weight: 600; margin-bottom: 30px; font-size: 2em;">Kenapa harus Green Resort?</h2>
    <div class="grid-container" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; max-width: 1200px; margin: 0 auto;">
        <div style="text-align: left; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-shield-alt fa-3x"></i>
            <div>
                <h3 style="color: #333; font-family: 'Montserrat', sans-serif; font-weight: 600; margin-bottom: 5px;">AMAN</h3>
                <p style="color: #555; font-family: 'Poppins', sans-serif; line-height: 1.6;">Semua properti yang ada di Green Resort telah di cek legalitas & perizinannya sehingga aman untuk transaksi.</p>
            </div>
        </div>
        <div style="text-align: left; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-smile-beam fa-3x"></i>
            <div>
                <h3 style="color: #333; font-family: 'Montserrat', sans-serif; font-weight: 600; margin-bottom: 5px;">ANTI RIBET</h3>
                <p style="color: #555; font-family: 'Poppins', sans-serif; line-height: 1.6;">Team Green Resort siap bantu Anda sampai dapat properti yang sesuai keinginan.</p>
            </div>
        </div>
        <div style="text-align: left; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-thumbs-up fa-3x"></i>
            <div>
                <h3 style="color: #333; font-family: 'Montserrat', sans-serif; font-weight: 600; margin-bottom: 5px;">JAMINAN LOLOS KPR</h3>
                <p style="color: #555; font-family: 'Poppins', sans-serif; line-height: 1.6;">Atau uang Anda kembali 100%.</p>
                <p style="color: #888; font-size: 0.9em; font-family: 'Poppins', sans-serif;">*Syarat & Ketentuan Berlaku</p>
            </div>
        </div>
        <div style="text-align: left; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-clock fa-3x"></i>
            <div>
                <h3 style="color: #333; font-family: 'Montserrat', sans-serif; font-weight: 600; margin-bottom: 5px;">SERAH TERIMA TEPAT WAKTU</h3>
                <p style="color: #555; font-family: 'Poppins', sans-serif; line-height: 1.6;">Setiap properti akan di serah terimakan tanpa terlambat.</p>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
    <footer style="background-color: #117c6b; color: white; padding: 15px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr auto 1fr; grid-template-rows: auto auto; align-items: center; gap: 5px;">
        <div style="text-align: center; grid-column: 1 / 4;">
            <p style="font-size: 0.9em; font-family: 'Poppins', sans-serif; margin-bottom: 3px;">Jl. Bypass Soekarno Hatta - Terminal Km12, Kecamatan Alang-Alang Lebar, Kota Palembang, Sumatera Selatan, 30151</p>
            <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 3px;">
                <p style="font-size: 0.9em; font-family: 'Poppins', sans-serif;">Telepon: (0711) 5645669</p>
                <p style="font-size: 0.9em; font-family: 'Poppins', sans-serif;">Email: info@greenresortcity.com</p>
            </div>
        </div>
        <div style="text-align: left; grid-row: 2; grid-column: 1;">
            <p style="font-size: 1em; font-family: 'Poppins', sans-serif; margin-bottom: 3px;">#RumahIdamanKeluargaBahagia</p>
        </div>
        <div style="text-align: right; grid-row: 2; grid-column: 3;">
            <div style="display: flex; gap: 20px; justify-content: flex-end;">
                <a href="#" style="text-decoration: none; color: white; font-size: 1.2em;"><i class="fab fa-whatsapp"></i></a>
                <a href="#" style="text-decoration: none; color: white; font-size: 1.2em;"><i class="far fa-envelope"></i></a>
                <a href="#" style="text-decoration: none; color: white; font-size: 1.2em;"><i class="fab fa-youtube"></i></a>
                <a href="#" style="text-decoration: none; color: white; font-size: 1.2em;"><i class="fab fa-instagram"></i></a>
                <a href="#" style="text-decoration: none; color: white; font-size: 1.2em;"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </div>
<hr style="border: 1px solid ##117c6b; margin: 10px 0;">
    <div style="text-align: center; font-size: 0.8em; font-family: 'Poppins', sans-serif;">
        &copy; 2025 Green Resort
    </div>
</footer>
    </div>

    <script>
  const carouselContainer = document.querySelector('.carousel-container');
  const carouselWrapper = document.querySelector('.carousel-wrapper');
  const carouselItems = document.querySelectorAll('.carousel-item');
  const prevBtn = document.querySelector('.prev-btn');
  const nextBtn = document.querySelector('.next-btn');
  const indicatorsContainer = document.querySelector('.carousel-indicators');
  let currentIndex = 0;

  function updateCarousel() {
   carouselWrapper.style.transform = `translateX(-${currentIndex * 100}%)`; // Perhatikan tanda kutip terbalik (`)
   updateIndicators();
  }

  function nextSlide() {
   currentIndex = (currentIndex + 1) % carouselItems.length;
   updateCarousel();
  }

  function prevSlide() {
   currentIndex = (currentIndex - 1 + carouselItems.length) % carouselItems.length;
   updateCarousel();
  }

  function goToSlide(index) {
   currentIndex = index;
   updateCarousel();
  }

  function updateIndicators() {
   indicatorsContainer.innerHTML = ''; // Bersihkan indikator sebelumnya
   carouselItems.forEach((_, index) => {
    const indicator = document.createElement('button');
    indicator.addEventListener('click', () => goToSlide(index));
    if (index === currentIndex) {
     indicator.classList.add('active');
    }
    indicatorsContainer.appendChild(indicator);
   });
  }

  // Membuat indikator awal
  updateIndicators();

  // Otomatisasi slide (opsional)
  setInterval(nextSlide, 3000); // Ganti gambar setiap 3 detik

  // Event listeners untuk tombol navigasi
  prevBtn.addEventListener('click', prevSlide);
  nextBtn.addEventListener('click', nextSlide);

  const counters = document.querySelectorAll('.counter');
  const speed = 200; // Kecepatan animasi (semakin kecil, semakin cepat)

  counters.forEach(counter => {
   const target = parseInt(counter.dataset.target);
   const updateCounter = () => {
    const currentValue = parseInt(counter.innerText);
    if (currentValue < target) {
     const increment = Math.ceil(target / speed);
     counter.innerText = currentValue + increment;
     setTimeout(updateCounter, 1); // Interval waktu animasi
    } else {
     counter.innerText = counter.dataset.target; // Set nilai akhir yang tepat
    }
   };

   updateCounter();
  });
 </script>
</body>