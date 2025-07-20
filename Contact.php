<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_perumahan";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Ambil semua data rumah dari tabel tb_rumah
$sql_tipe_rumah = "SELECT id_rumah, type_rumah FROM tb_rumah";
$result_tipe_rumah = $conn->query($sql_tipe_rumah);

$options_tipe_rumah = [];
if ($result_tipe_rumah->num_rows > 0) {
    while ($row = $result_tipe_rumah->fetch_assoc()) {
        $options_tipe_rumah[] = $row;
    }
}

$status = "";
$message = "";
$userMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $id_rumah = $_POST['project']; // ID rumah dari dropdown
    $userMessage = $_POST['message'];

    $namaLengkap = trim($firstName . " " . $lastName);

    // Ambil nama type_rumah berdasarkan id_rumah
    $project_name = "";
    $stmt_rumah = $conn->prepare("SELECT type_rumah FROM tb_rumah WHERE id_rumah = ?");
    $stmt_rumah->bind_param("i", $id_rumah);
    $stmt_rumah->execute();
    $result = $stmt_rumah->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $project_name = $row['type_rumah'];
    }
    $stmt_rumah->close();

    if (empty($firstName) || empty($email) || empty($phone) || empty($id_rumah) || empty($userMessage)) {
        $status = "warning";
        $message = "Harap isi semua kolom yang bertanda bintang (*).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status = "warning";
        $message = "Format email tidak valid.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tb_kontak (nama, email, telepon, type_rumah, pesan, tanggal_kirim, id_rumah) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssssi", $namaLengkap, $email, $phone, $project_name, $userMessage, $id_rumah);

        if ($stmt->execute()) {
            $status = "success";
            $message = "Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.";
            $_POST = array();
        } else {
            $status = "error";
            $message = "Terjadi kesalahan saat mengirim pesan: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us</title>
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
    height: 80px; /* Sesuaikan jika perlu, di kode sebelumnya 50px */
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


        .contact-page-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background-color: #f4f6f8;
        }

        .contact-wrapper {
            display: flex;
            gap: 40px;
            max-width: 900px;
            align-items: center;
            flex-wrap: wrap;
        }

        .contact-form-card {
            background-color: #fff;
            color: #333;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            flex: 1;
            min-width: 280px;
        }

        .contact-form-card h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2.2em;
            margin-bottom: 30px;
            color: #117c6b;
            text-align: center;
        }

        .name-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 1.1em;
            color: #117c6b;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            color: #333;
            background-color: #f9f9f9;
            box-sizing: border-box;
        }

        .form-group select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="currentColor" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: 95% 50%;
            background-size: 16px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            box-shadow: 0 0 8px rgba(17, 124, 107, 0.3);
            border-color: #117c6b;
        }

        .form-group .required {
            color:rgb(163, 63, 63);
            margin-left: 5px;
        }

        .submit-button-container {
            display: flex;
            justify-content: center;
        }

        .submit-button {
            background-color: #117c6b;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            cursor: pointer;
            font-weight: bold;
        }

        .submit-button:hover {
            background-color: #0e6655; /* Warna hover yang lebih gelap */
        }
        .contact-info-container {
            width: 350px;
            min-width: 280px;
        }

        .contact-details-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .contact-details-item i {
            font-size: 2em;
            color: #117c6b;
            margin-right: 20px;
            width: 30px;
            text-align: center;
        }

        .contact-details-item span {
            display: block;
            line-height: 1.7;
            font-size: 1.1em;
        }

        .contact-details-item span.label {
            font-weight: bold;
            color: #117c6b;
            margin-bottom: 5px;
        }

        .contact-details-item span.value {
        color: #333;
        white-space: nowrap;
    }

        .message-box {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            font-size: 1em;
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;border: 1px solid #c3e6cb;
        }

        .message-box.warning {
            background-color: #fff3cd;
            color: #85640a;
            border: 1px solid #ffeeba;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    border-color: #fff; /* Di kode sebelumnya ##117c6b, mungkin typo? */
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

    <div class="contact-page-container">
        <div class="contact-wrapper">
            <div class="contact-form-card">
                <?php if ($status): ?>
                    <div class="message-box <?php echo $status; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
               <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="name-inputs">
        <div class="form-group">
            <label for="first_name">Nama Depan <span class="required">*</span></label>
            <input type="text" id="first_name" name="first_name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="last_name">Nama Belakang</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="phone">Telepon <span class="required">*</span></label>
        <input type="text" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
    </div>
    <div class="form-group">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
    </div>
    <div class="form-group">
    <label for="project">Tertarik dengan Proyek <span class="required">*</span></label>
    <select id="project" name="project" required>
    <option value="">Pilih Proyek</option>
    <?php foreach ($options_tipe_rumah as $tipe): ?>
        <option value="<?php echo $tipe['id_rumah']; ?>"
            <?php echo (isset($_POST['project']) && $_POST['project'] == $tipe['id_rumah']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($tipe['type_rumah']); ?>
        </option>
    <?php endforeach; ?>
</select>

</div>

    <div class="form-group">
        <label for="message">Pesan <span class="required">*</span></label>
        <textarea id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
    </div>
    <div class="submit-button-container">
        <button type="submit" class="submit-button">Submit</button>
    </div>
</form>
            </div>
            <div class="contact-info-container">
                <div class="contact-details-item">
                    <i class="fas fa-envelope"></i>
                    <span>
                        <span class="label">Email</span>
                        <span class="value">info@greenresortcity.com</span>
                    </span>
                </div>
                <div class="contact-details-item">
                    <i class="fas fa-phone"></i>
                    <span>
                        <span class="label">Phone</span>
                        <span class="value">(0711) 5645669</span>
                    </span>
                </div>
                <div class="contact-details-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>
                        <span class="label">Location</span>
                        <span class="value">Jl. Bypass Soekarno Hatta - Terminal Km12, Kecamatan </span>
                        <span class="value">Alang-Alang Lebar, Kota Palembang, Sumatera Selatan.</span>
                    </span>
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
        © 2025 Green Resort
    </div>
</footer>
</body>
</html>

<?php
$conn->close();
?>