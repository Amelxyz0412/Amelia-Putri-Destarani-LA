<?php
$servername = "localhost"; // Ganti dengan server Anda
$username = "root"; // Ganti dengan username Anda
$password = ""; // Ganti dengan password Anda
$dbname = "db_perumahan"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_POST['message'])) {
    $message = $_POST['message'];
    $name = $_POST['name'] ?? 'Pengunjung Anonim'; // Nilai default jika nama tidak ada
    $email = $_POST['email'] ?? ''; // Email mungkin tidak selalu ada
    $senderType = $_POST['sender_type'] ?? 'user'; // Tambahkan untuk menentukan pengirim

    $user_id = null; // Inisialisasi user_id

    // Cari user berdasarkan nama
    $sql_user = "SELECT id FROM tb_user WHERE user_name = ?";
    $stmt_user = $conn->prepare($sql_user);
    if ($stmt_user) {
        $stmt_user->bind_param("s", $name);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();

        if ($result_user->num_rows > 0) {
            $row_user = $result_user->fetch_assoc();
            $user_id = $row_user['id'];
        } else {
            // Buat user baru
            $sql_insert_user = "INSERT INTO tb_user (user_name, user_email, timestamp) VALUES (?, ?, NOW())";
            $stmt_insert_user = $conn->prepare($sql_insert_user);
            if ($stmt_insert_user) {
                $stmt_insert_user->bind_param("ss", $name, $email);
                if ($stmt_insert_user->execute()) {
                    $user_id = $conn->insert_id;
                } else {
                    $error_message = "Error membuat user baru: " . $stmt_insert_user->error;
                    echo $error_message;
                    error_log("[send_message.php] " . $error_message);
                    $stmt_insert_user->close();
                    $conn->close();
                    exit();
                }
                $stmt_insert_user->close();
            } else {
                $error_message = "Error preparing statement untuk insert user: " . $conn->error;
                echo $error_message;
                error_log("[send_message.php] " . $error_message);
                $conn->close();
                exit();
            }
        }
        $stmt_user->close();

        // Simpan pesan jika user_id berhasil didapatkan
        if ($user_id !== null) {
            $sql_message = "INSERT INTO tb_messages (user_id, sender_type, message, timestamp) VALUES (?, ?, ?, NOW())";
            $stmt_message = $conn->prepare($sql_message);
            if ($stmt_message) {
                $stmt_message->bind_param("iss", $user_id, $senderType, $message);
                if ($stmt_message->execute()) {
                    echo "Pesan berhasil disimpan.";
                } else {
                    $error_message = "Error menyimpan pesan: " . $stmt_message->error;
                    echo $error_message;
                    error_log("[send_message.php] " . $error_message);
                }
                $stmt_message->close();
            } else {
                $error_message = "Error preparing statement untuk insert pesan: " . $conn->error;
                echo $error_message;
                error_log("[send_message.php] " . $error_message);
            }
        } else {
            echo "Gagal mendapatkan atau membuat user ID.";
            error_log("[send_message.php] Gagal mendapatkan atau membuat user ID untuk nama: " . $name);
        }

    } else {
        $error_message = "Error preparing statement untuk select user: " . $conn->error;
        echo $error_message;
        error_log("[send_message.php] " . $error_message);
    }

} else {
    echo "Data pesan tidak diterima.";
    error_log("[send_message.php] Data pesan tidak diterima.");
}

$conn->close();
?>