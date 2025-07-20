<?php
session_start();
ob_start(); // Menambahkan ini untuk menghindari masalah output

// --- Bagian Koneksi Database ---
$host = 'localhost';
$db = 'db_perumahan'; // Nama database baru
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
// --- Akhir Bagian Koneksi Database ---

// --- Bagian Pemrosesan Login ---
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password_input = $_POST['password']; // Ubah nama variabel untuk input password

    $stmt = $pdo->prepare("SELECT id_admin, username, password, role FROM tb_login WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        // MODIFIKASI KRUSIAL DI SINI: Gunakan password_verify()
        // Bandingkan password yang diinput dengan hash yang tersimpan di database
        if (password_verify($password_input, $user_data['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id_admin'] = $user_data['id_admin'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = $user_data['role'];

            // Arahkan berdasarkan role
            if ($user_data['role'] == 'admin') {
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Login berhasil! Selamat datang, admin!'];
                header("Location: Dashboard_admin.php");
            } elseif ($user_data['role'] == 'pimpinan') {
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Login berhasil! Selamat datang, pimpinan!'];
                header("Location: Dashboard_Pimpinan.php");
            } else {
                $login_error = "Role pengguna tidak valid!";
            }
            exit();
        } else {
            $login_error = "Password salah!"; // Password tidak cocok dengan hash
        }
    } else {
        $login_error = "Username tidak ditemukan!";
    }
}
// --- Akhir Bagian Pemrosesan Login ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>
        Login - Green Resort Palembang
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        /* Custom style for the password eye icon cursor */
        .cursor-pointer {
            cursor: pointer;
        }

        /* Gaya tombol login yang meniru contact us */
        .login-button {
            width: 100%;
            background-color: #117c6b; /* Warna hijau tombol submit di gambar */
            color: white;
            font-weight: bold;
            font-size: 12px; /* Ukuran font awal */
            padding: 12px; /* Padding awal */
            border-radius: 8px; /* Radius tombol submit di gambar */
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .login-button:hover {
            background-color: #0e6655; /* Warna hover yang lebih gelap */
        }

        .login-button:active {
            box-shadow: 0 2px 5px rgba(17, 124, 107, 0.5); /* Efek active yang lebih sesuai */
        }

        /* Gaya latar belakang baru untuk bagian "Pasarkan Properti Terbaik" */
        .pasarkan-properti-bg {
            background-color: #117c6b; /* Tetap warna abu-abu */
        }

        .pasarkan-properti-bg h2 {
            color: #f9f9f9; /* Warna abu-abu terang seperti kolom input */
            font-weight: bold;
            font-size: small; /* Anda bisa sesuaikan ukuran font */
            margin-bottom: 1em; /* Anda bisa sesuaikan margin bawah */
            line-height: 1.3; /* Anda bisa sesuaikan tinggi baris */
        }

        .pasarkan-properti-bg p {
            color: #f9f9f9; /* Warna abu-abu terang seperti kolom input */
            font-weight: bold;
            font-size: x-small; /* Anda bisa sesuaikan ukuran font */
            line-height: 1.2; /* Anda bisa sesuaikan tinggi baris */
        }

        .pasarkan-properti-bg p.text-[8px] {
            color: #f9f9f9; /* Warna abu-abu terang seperti kolom input */
            font-weight: bolder;
            margin-top: 3em; /* Anda bisa sesuaikan margin atas */
            font-size: x-small; /* Anda bisa sesuaikan ukuran font */
        }
        /* Gaya input yang meniru contact us */
        .input-bg {
            background-color: #f9f9f9; /* Latar belakang input yang sangat terang */
            border: 1px solid #e0e0e0; /* Border tipis abu-abu muda */
            border-radius: 8px; /* Bentuk kolom input di gambar */
            color: #333; /* Warna teks input default */
            padding: 12px; /* Padding input di gambar */
            font-size: 1em; /* Ukuran font yang lebih sesuai */
            width: 100%; /* Lebar input 100% */
            box-sizing: border-box; /* Agar padding tidak menambah lebar total */
            outline: none;
        }

        .input-bg::placeholder {
            color: #6b6b6b; /* Warna placeholder abu-abu */
        }

        .input-bg:focus {
            border-color: #117c6b; /* Warna border saat fokus */
            box-shadow: 0 0 8px rgba(17, 124, 107, 0.3); /* Efek fokus hijau muda */
            outline: none; /* Menghilangkan outline default */
        }

        label {
            display: block;
            margin-bottom: 8px; /* Spasi bawah label */
            font-weight: bold;
            font-size: 1.1em; /* Ukuran font label */
            color: #117c6b; /* Warna hijau label di gambar */
        }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center font-sans">
<div class="w-full max-w-6xl flex flex-col md:flex-row min-h-[600px] bg-white">
    <div class="flex-1 flex flex-col items-center justify-center p-8">
        <div class="bg-white p-8 rounded-md shadow-md w-full max-w-md">
            <img alt="Logo Green" class="mb-12 mx-auto" height="100" src="gambar/Logo_Green.png" width="150"/>
            <form method="POST" action="">
                <div class="mb-6">
                    <label for="username">
                        Username
                    </label>
                    <input class="input-bg" id="username" name="username" placeholder="Enter Username" type="text" required/>
                </div>
                <div class="mb-8 relative">
                    <label for="password">
                        Password
                    </label>
                    <input class="input-bg pr-8" id="password" name="password" placeholder="Enter Password" type="password" required/>
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center top-6 text-gray-400 cursor-pointer" onclick="togglePassword()">
                        <i id="eyeIcon" class="fas fa-eye"></i>
                    </span>
                </div>
                <button class="login-button" type="submit" name="login">
                    LOGIN
                </button>
                <?php if (isset($login_error)) : ?>
                    <p class="mt-4 text-red-600 text-xs font-semibold text-center">
                        <?php echo htmlspecialchars($login_error); ?>
                    </p>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="flex-1 p-8 flex flex-col justify-center max-w-md mx-auto pasarkan-properti-bg">
        <h2 class="text-[white] font-bold text-sm mb-4 leading-tight">
            Pasarkan Properti Terbaik
            <br/>
            dari Green Resort
        </h2>
        <img alt="Three people standing in front of a modern apartment building with red and beige exterior and glass balconies" class="mb-6 w-full object-cover" height="300" src="https://storage.googleapis.com/a1aa/image/d52b742b-dc28-4842-9621-d877604e0fec.jpg" width="400"/>
        <p class="text-[white] font-bold text-xs leading-snug">
            Membangun Kota
            <br/>
            Membangun Kehidupan
        </p>
        <p class="text-[8px] font-extrabold mt-12">
            ©Copyright 2025 - Green Resort
        </p>
    </div>
</div>
<script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    }
</script>
</body>
</html>