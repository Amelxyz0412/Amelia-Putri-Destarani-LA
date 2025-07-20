<?php
// Koneksi ke database
$servername = "localhost"; // Ganti dengan server Anda
$username = "root"; // Ganti dengan username Anda
$password = ""; // Ganti dengan password Anda
$dbname = "db_perumahan"; // Ganti dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Pastikan data POST diterima
if (isset($_POST['message']) && isset($_POST['name']) && isset($_POST['email'])) {
    $message = $_POST['message'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Cari atau buat user_id di tb_user
    $sql_user = "SELECT id FROM tb_user WHERE user_name = ? AND user_email = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("ss", $name, $email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $row_user = $result_user->fetch_assoc();
        $user_id = $row_user['id'];
    } else {
        // Buat user baru jika tidak ditemukan
        $sql_insert_user = "INSERT INTO tb_user (user_name, user_email, timestamp) VALUES (?, ?, NOW())";
        $stmt_insert_user = $conn->prepare($sql_insert_user);
        $stmt_insert_user->bind_param("ss", $name, $email);
        if ($stmt_insert_user->execute()) {
            $user_id = $conn->insert_id; // Dapatkan ID user yang baru dibuat
        } else {
            echo "Error membuat user baru: " . $stmt_insert_user->error;
            $conn->close();
            exit();
        }
        $stmt_insert_user->close();
    }
    $stmt_user->close();

    // Simpan pesan ke tb_messages
    $sql_message = "INSERT INTO tb_messages (user_id, sender_type, message, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt_message = $conn->prepare($sql_message);
    $stmt_message->bind_param("iss", $user_id, 'user', $message);

    if ($stmt_message->execute()) {
        echo "Pesan berhasil disimpan."; // Berikan respons ke JavaScript
    } else {
        echo "Error menyimpan pesan: " . $stmt_message->error;
    }

    $stmt_message->close();

} else {
    echo "Data pesan, nama, atau email tidak diterima.";
}

$conn->close();
?>