<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_perumahan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if (isset($_POST['id_kontak'])) {
    $id_kontak = $_POST['id_kontak'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM tb_kontak WHERE id_kontak = ?");
    $stmt->bind_param("i", $id_kontak); // "i" for integer

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pesan berhasil dihapus.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus pesan: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID Kontak tidak ditemukan.']);
}

$conn->close();
?>