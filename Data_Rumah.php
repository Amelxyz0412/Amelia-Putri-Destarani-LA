<?php
session_start(); // Session start should be the very first line

// Database connection configuration (still included but not actively used on this page)
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Close database connection as it's not needed for this page's display
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rumah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles from dashboard_admin.php (remains the same) */
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

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            padding-left: 20px; /* Indentation for submenu */
            display: none; /* Hide submenu by default */
        }

        .submenu li a {
            font-size: 14px;
            padding: 10px 15px;
            margin-bottom: 10px;
            display: block;
            color: #117c6b;
            text-decoration: none;
        }

        .submenu li a:hover {
            background-color: #e0f2f1;
        }
        /* Show submenu if its parent is active */
        .sidebar-nav ul li.active .submenu {
            display: block;
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

        /* No specific styles for .content-area as it will be empty now */
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
                    <li>
                        <a href="Dashboard_admin.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="has-submenu">
                        <a href="#" class="active" onclick="toggleSubmenu(event)">
                            <i class="fas fa-home"></i>
                            <span>Data Rumah</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="kategori_rumah.php">Kategori Rumah</a></li>
                            <li><a href="unit_rumah.php">Unit Rumah</a></li>
                        </ul>
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
                <h2>Data Rumah</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span> </div>
            </header>
            </main>
    </div>

    <script>
        // Run script after DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            const dataRumahMenuItem = document.querySelector('.sidebar-nav ul li.has-submenu');
            const dataRumahLink = dataRumahMenuItem.querySelector('a');

            // Add event listener for clicks on the "Data Rumah" <a> element
            dataRumahLink.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent direct navigation to '#'
                dataRumahMenuItem.classList.toggle('active'); // Toggle 'active' class on the parent <li>
            });

            // Logic to keep the menu active based on the current page
            const currentPage = window.location.pathname.split('/').pop();

            // List of pages that are part of the "Data Rumah" submenu
            const dataRumahSubPages = ['kategori_rumah.php', 'unit_rumah.php'];

            // Set 'active' class for the main "Data Rumah" menu if one of its sub-pages is active
            // Or if Data_Rumah.php itself is being accessed (as a parent placeholder)
            if (dataRumahSubPages.includes(currentPage) || currentPage === 'Data_Rumah.php') {
                dataRumahMenuItem.classList.add('active');
            } else {
                dataRumahMenuItem.classList.remove('active');
            }

            // Set 'active' class for other menu items that are not part of "Data Rumah" submenu
            const otherMenuItems = document.querySelectorAll('.sidebar-nav ul li:not(.has-submenu) a');
            otherMenuItems.forEach(item => {
                // Check if the item's href matches the currentPage
                if (item.getAttribute('href') === currentPage) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            // Ensure Dashboard is not active if the current page is not Dashboard
            const dashboardLink = document.querySelector('.sidebar-nav ul li a[href="Dashboard_admin.php"]');
            if (currentPage === 'Dashboard_admin.php') {
                dashboardLink.classList.add('active');
            } else {
                dashboardLink.classList.remove('active');
            }
        });
    </script>
</body>
</html>