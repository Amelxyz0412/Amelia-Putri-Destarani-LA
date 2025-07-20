<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_perumahan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$sql = "SELECT id_kontak, nama, email, telepon, type_rumah, tanggal_kirim, pesan FROM tb_kontak ORDER BY tanggal_kirim DESC";
$result = $conn->query($sql);

$daftarKontak = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $daftarKontak[] = $row;
    }
} else {
    $pesanKosong = "Belum ada pesan masuk.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbox</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
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

        /* Styles untuk Chatbox */
        .chatbox-container {
            display: flex;
            border: 1px solid #e0f2f1;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 600px; /* Sesuaikan tinggi sesuai kebutuhan */
            flex: 1;
            margin-bottom: 20px;
        }

        .chat-list {
            width: 300px;
            border-right: 1px solid #e0f2f1;
            padding: 20px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
        }

        .chat-list h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.2em;
            color: #333;
        }

        .chat-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .chat-list li {
            padding: 10px;
            border-bottom: 1px solid #e0f2f1;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .chat-list li:last-child {
            border-bottom: none;
        }

        .chat-list li:hover,
        .chat-list li.active {
            background-color: #e0f2f1;
        }

        .chat-list .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .chat-list .user-info i {
            margin-right: 10px;
            font-size: 1.2em;
            color: #666;
        }

        .chat-list .user-info i.fa-envelope {
            color: #007bff; /* Warna ikon email */
        }

        .chat-list .user-info span {
            font-weight: 500;
            color: #333;
        }

        .chat-list .last-message {
            font-size: 0.9em;
            color: #777;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .chat-window {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background-color: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px solid #e0f2f1;
            display: flex;
            align-items: center;
            justify-content: space-between; /* Added for delete button positioning */
        }

        .chat-header .user-info {
            display: flex;
            align-items: center;
        }

        .chat-header .user-info i {
            margin-right: 10px;
            font-size: 1.5em;
            color: #117c6b;
        }

        .chat-header .user-info span {
            font-weight: 600;
            color: #333;
            font-size: 1.1em;
        }

        .message-area {
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-contact-details {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            position: relative; /* Needed for positioning the delete button */
        }

        .form-contact-details p {
            margin-bottom: 8px;
        }

        .form-contact-details p strong {
            font-weight: bold;
        }

        .contact-message {
            padding: 15px;
            border: 1px solid #e0f2f1;
            border-radius: 5px;
            background-color: #fff;
            margin-top: 10px;
            white-space: pre-line; /* Untuk mempertahankan format pesan */
        }

        .contact-message strong {
            font-weight: bold;
            color: #117c6b;
            display: block;
            margin-bottom: 5px;
        }

        .input-area {
            display: none; /* Sembunyikan area input */
        }

        .delete-button {
            background-color: #dc3545; /* Red color for delete */
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        /* Responsive adjustments untuk layout admin */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                padding-bottom:10px;
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
                flex-direction: column;
            }
        }

        /* Responsive adjustments untuk chatbox */
        @media (max-width: 768px) {
            .chatbox-container {
                flex-direction: column;
                height: auto;
            }
            .chat-list {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e0f2f1;
                height: auto;
            }
            .chat-window {
                height: auto;
            }
            .has-submenu {
        position: relative; /* Penting untuk penempatan submenu */
    }

    .submenu {
        list-style: none;
        padding: 0;
        margin: 0;
        padding-left: 20px; /* Indentasi untuk submenu */
        display: none; /* Sembunyikan submenu secara default */
    }

    .submenu li a {
        font-size: 14px;
        padding: 10px 15px;
        margin-bottom: 10px; /* Jarak antar item submenu */
        display: block; /* Agar link mengisi seluruh area */
        color: #117c6b; /* Warna teks submenu */
        text-decoration: none;
        border-radius: 4px; /* Sudut membulat pada item submenu */
        transition: background-color 0.3s ease;
    }

    .submenu li a:hover {
        background-color: #e0f2f1; /* Warna hover untuk item submenu */
    }

    /* Tampilkan submenu jika parent-nya memiliki kelas 'active' */
    .sidebar-nav ul li.active .submenu {
        display: block;
    }
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
                    <li>
                        <a href="Dashboard_admin.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="has-submenu">
            <a href="#" onclick="toggleSubmenu(event)">
                <i class="fas fa-home"></i>
                <span>Data Rumah</span>
            </a>
            <ul class="submenu">
                <li><a href="kategori_rumah.php">Kategori Rumah</a></li>
                <li><a href="unit_rumah.php">Unit Rumah</a></li>
            </ul>
        </li>
                    <li>
    <a href="kotak_masuk.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'kotak_masuk.php') ? 'active' : ''; ?>">
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
                <h2>Kotak Masuk</h2>
                <div class="admin-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Amelia Putri Destarani</span>
                </div>
            </header>
            <div class="content-area">
                <div class="chatbox-container">
                    <div class="chat-list">
                        <h3>Kotak Masuk</h3>
                        <ul id="chatList">
                            <?php if (isset($pesanKosong)): ?>
                                <li style="text-align: center; color: #777;"><?php echo $pesanKosong; ?></li>
                            <?php else: ?>
                                <?php foreach ($daftarKontak as $kontak): ?>
                                       <li class="chat-item" data-contact-id="<?php echo $kontak['id_kontak']; ?>">
                                            <div class="user-info">
                                                <i class="fas fa-envelope"></i>
                                                <span><?php echo htmlspecialchars($kontak['nama']); ?></span>
                                            </div>
                                            <div class="last-message">
                                                <?php if (!empty($kontak['pesan'])): ?>
                                                <?php echo substr(htmlspecialchars($kontak['pesan']), 0, 50) . (strlen($kontak['pesan']) > 50 ? '...' : ''); ?>
                                            <?php else: ?>
                                                Tertarik dengan <?php echo htmlspecialchars($kontak['type_rumah']); ?>
                                            <?php endif; ?>
                                            </div>
                                        </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="chat-window" id="chatWindow">
                        <div class="chat-header">
                            <div class="user-info">
                                <i class="fas fa-user-circle"></i>
                                <span id="chatTitle">Pilih Pesan</span>
                            </div>
                            <button id="deleteMessageButton" class="delete-button" style="display: none;">
                                <i class="fas fa-trash-alt"></i> Hapus Pesan
                            </button>
                        </div>
                        <div class="message-area" id="messageArea">
                            <p id="defaultMessage" style="text-align: center; color: #777;">Silakan pilih pesan dari daftar untuk melihat detailnya.</p>
                            <?php if (!isset($pesanKosong)): ?>
                                <?php foreach ($daftarKontak as $kontak): ?>
                                        <div class="form-contact-details" data-contact-id="<?php echo $kontak['id_kontak']; ?>" style="display: none;">
                                            <p><strong>Nama:</strong> <?php echo htmlspecialchars($kontak['nama']); ?></p>
                                            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($kontak['telepon']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($kontak['email']); ?></p>
                                            <p><strong>Tipe Rumah:</strong> <?php echo htmlspecialchars($kontak['type_rumah']); ?></p>
                                            <p><strong>Tanggal Kirim:</strong> <?php echo date('d-m-Y H:i:s', strtotime($kontak['tanggal_kirim'])); ?></p>
                                            <?php if (!empty($kontak['pesan'])): ?>
                                                <div class="contact-message">
                                                    <strong>Pesan:</strong>
                                                    <?php echo htmlspecialchars($kontak['pesan']); ?>
                                                </div>
                                            <?php else: ?><div class="contact-message">
                                            <strong>Pesan:</strong>
                                            Tertarik dengan tipe rumah <?php echo htmlspecialchars($kontak['type_rumah']); ?>.
                                        </div>
                                        <?php endif; ?>
                                        </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="input-area">
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
   <script>
    document.addEventListener('DOMContentLoaded', function() {
    // SCRIPT UNTUK SUBMENU DATA RUMAH
    const dataRumahMenuItem = document.querySelector('.sidebar-nav ul li.has-submenu');
    if (dataRumahMenuItem) {
        const dataRumahLink = dataRumahMenuItem.querySelector('a');
        const submenu = dataRumahMenuItem.querySelector('.submenu');

        // Menutup submenu saat halaman dimuat
        submenu.style.display = 'none';

        dataRumahLink.addEventListener('click', function(event) {
            event.preventDefault();
            dataRumahMenuItem.classList.toggle('active');
            // Toggle tampilan submenu
            if (submenu.style.display === 'none') {
                submenu.style.display = 'block';
            } else {
                submenu.style.display = 'none';
            }
        });

        // Logika untuk membuat menu "Data Rumah" tetap aktif
        const currentPage = window.location.pathname.split('/').pop();
        const dataRumahSubPages = ['kategori_rumah.php', 'unit_rumah.php'];

        if (dataRumahSubPages.includes(currentPage) || currentPage === 'Data_Rumah.php') {
            dataRumahMenuItem.classList.add('active');
            submenu.style.display = 'block'; // Tampilkan submenu jika berada di salah satu subhalaman
        } else {
            dataRumahMenuItem.classList.remove('active');
            submenu.style.display = 'none'; // Sembunyikan submenu jika tidak berada di subhalaman
        }
    }

    // --- SCRIPT UNTUK INTERAKSI CHAT (Khusus Halaman Kotak Masuk) ---
    const chatListItems = document.querySelectorAll('.chat-item');
    const chatTitle = document.getElementById('chatTitle');
    const messageArea = document.getElementById('messageArea');
    const formContactDetails = document.querySelectorAll('.form-contact-details');
    const deleteMessageButton = document.getElementById('deleteMessageButton');
    const defaultMessageParagraph = document.getElementById('defaultMessage'); // Get the default message paragraph

    let activeContactId = null; // To store the currently active contact ID

    // Cek apakah elemen chat ada di halaman ini sebelum melanjutkan
    if (chatListItems.length > 0) { // Changed condition to just check chatListItems existence
        chatListItems.forEach(item => {
            item.addEventListener('click', function() {
                chatListItems.forEach(li => li.classList.remove('active')); // Hapus active dari semua item chat
                this.classList.add('active'); // Tambahkan active ke item chat yang diklik

                activeContactId = this.dataset.contactId; // Set the active contact ID
                const userName = this.querySelector('.user-info span').textContent;

                if (activeContactId) {
                    chatTitle.textContent = userName; // Update judul chat
                    deleteMessageButton.style.display = 'inline-flex'; // Show delete button
                    
                    // Sembunyikan pesan default jika ada
                    if (defaultMessageParagraph) {
                        defaultMessageParagraph.style.display = 'none';
                    }
                    
                    // Sembunyikan semua detail kontak dan tampilkan yang sesuai
                    formContactDetails.forEach(detail => detail.style.display = 'none');
                    const detailsToShow = document.querySelector(`.form-contact-details[data-contact-id="${activeContactId}"]`);
                    if (detailsToShow) {
                        detailsToShow.style.display = 'block';
                    }
                } else {
                    // Jika tidak ada contactId (misalnya item chat kosong atau default)
                    chatTitle.textContent = 'Pilih Pesan';
                    deleteMessageButton.style.display = 'none'; // Hide delete button
                    if (defaultMessageParagraph) {
                        defaultMessageParagraph.style.display = 'block';
                    }
                    formContactDetails.forEach(detail => detail.style.display = 'none');
                }
            });
        });

        // Logika untuk memilih chat pertama secara otomatis saat halaman dimuat
        const firstChatItem = document.querySelector('.chat-item');
        if (firstChatItem) {
            firstChatItem.classList.add('active'); // Aktifkan item chat pertama
            activeContactId = firstChatItem.dataset.contactId; // Set initial active contact ID
            const userName = firstChatItem.querySelector('.user-info span').textContent;

            if (activeContactId) {
                chatTitle.textContent = userName;
                deleteMessageButton.style.display = 'inline-flex'; // Show delete button
                if (defaultMessageParagraph) {
                    defaultMessageParagraph.style.display = 'none';
                }
                formContactDetails.forEach(detail => detail.style.display = 'none');
                const detailsToShow = document.querySelector(`.form-contact-details[data-contact-id="${activeContactId}"]`);
                if (detailsToShow) {
                    detailsToShow.style.display = 'block';
                }
            }
        }

        // --- Delete Message Functionality ---
        deleteMessageButton.addEventListener('click', function() {
            if (activeContactId && confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
                // Send AJAX request to delete the message
                fetch('delete_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_kontak=' + activeContactId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Remove the deleted message from the UI
                        const deletedChatItem = document.querySelector(`.chat-item[data-contact-id="${activeContactId}"]`);
                        if (deletedChatItem) {
                            deletedChatItem.remove();
                        }
                        const deletedDetails = document.querySelector(`.form-contact-details[data-contact-id="${activeContactId}"]`);
                        if (deletedDetails) {
                            deletedDetails.remove();
                        }

                        // Reset chat window to default state
                        chatTitle.textContent = 'Pilih Pesan';
                        deleteMessageButton.style.display = 'none';
                        if (defaultMessageParagraph) {
                            defaultMessageParagraph.style.display = 'block';
                        }
                        activeContactId = null; // Clear active contact
                        
                        // Check if there are any remaining messages
                        if (document.querySelectorAll('.chat-item').length === 0) {
                            const chatList = document.getElementById('chatList');
                            chatList.innerHTML = '<li style="text-align: center; color: #777;">Belum ada pesan masuk.</li>';
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghubungi server.');
                });
            }
        });

    } // Akhir dari if (chatListItems.length > 0)
});
</script>
</body>
</html>