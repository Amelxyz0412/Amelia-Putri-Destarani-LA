<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Logout</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles dari dashboard_admin.php */
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
            color: #117c6b; /* Warna logout dikembalikan menjadi hijau */
        }

        .sidebar-nav ul li.logout a:hover {
            background-color: #e0f2f1;
        }

        .sidebar-nav ul li a.active {
            background-color: #117c6b;
            color: white;
            font-weight: 600;
        }

        /* Submenu Styles */
        .sidebar-nav ul li.has-submenu {
            position: relative;
        }

        .sidebar-nav ul li.has-submenu .arrow {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }

        .sidebar-nav ul li.has-submenu.active .arrow {
            transform: translateY(-50%) rotate(180deg); /* Putar panah saat aktif */
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: #f9f9f9; /* Warna latar belakang sub-menu */
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: none; /* Sembunyikan secara default */
            padding-left: 20px; /* Beri indentasi */
            margin-top: 5px;
        }

        .sidebar-nav ul li.has-submenu.active .submenu {
            display: block; /* Tampilkan sub-menu saat menu utama aktif */
        }

        .submenu li a {
            display: block;
            color: #333;
            text-decoration: none;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 400;
            transition: background-color 0.3s ease;
        }

        .submenu li a:hover {
            background-color: #e0f2f1;
        }

        .submenu li a.sub-active {
            background-color: #117c6b;
            color: white;
            font-weight: 600;
        }

        .main-content {
            flex: 1;
            background-color: #f4f6f8;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center content horizontally */
            justify-content: center; /* Center content vertically */
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
            width: 100%; /* Make header full width */
            box-sizing: border-box; /* Include padding in width */
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
            align-items: center;
            width: 100%; /* Make content area full width */
            box-sizing: border-box; /* Include padding in width */
        }

        .logout-card {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
            max-width: 90%;
        }

        .logout-icon {
            font-size: 3em;
            color: #117c6b; /* Menggunakan warna hijau untuk ikon logout */
            margin-bottom: 20px;
        }

        .logout-card h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .logout-card p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .logout-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .logout-button {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            text-decoration: none;
            color: white;
        }

        .yes-button {
            background-color: #117c6b; /* Warna hijau untuk tombol logout */
        }

        .yes-button:hover {
            background-color: #0e6b5c;
        }

        .no-button {
            background-color: #6c757d; /* Warna abu-abu untuk tombol batal */
            color: white; /* Teks putih agar terlihat lebih baik di abu-abu */
        }

        .no-button:hover {
            background-color: #5a6268;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            .sidebar-header {
                min-width: auto;
                margin-bottom: 10px;
            }
            .sidebar-nav ul {
                display: flex;
            }
            .sidebar-nav ul li {
                margin-right: 10px;
            }
            .sidebar-nav ul li a {
                padding: 8px 12px;
                font-size: 0.9em;
            }
            .main-content {
                padding-top: 60px; /* Adjust padding for fixed header */
            }
            .main-header {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 100;
            }
            .content-area {
                padding-top: 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                         <a href="Dashboard_pimpinan.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan_pembelian.php">
                            <i class="fas fa-file-invoice"></i>
                            <span>Laporan Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan_pembayaran.php">
                            <i class="fas fa-money-bill-alt"></i>
                            <span>Laporan Pembayaran</span>
                        </a>
                    </li>
                    <li class="logout_pimpinan.php"class="active">
                        <a href="logout_pimpinan.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <h2>Konfirmasi Logout</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Pimpinan</span>
                </div>
            </header>
            <div class="content-area">
                <div class="logout-card">
                    <i class="fas fa-sign-out-alt logout-icon"></i>
                    <h2>Konfirmasi Logout</h2>
                    <p>Anda yakin ingin mengakhiri sesi admin ini dan kembali ke halaman utama?</p>
                    <div class="logout-buttons">
                        <a href="Home.php" class="logout-button yes-button">Ya, Logout</a>
                        <a href="javascript:window.history.back();" class="logout-button no-button">Batal</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>