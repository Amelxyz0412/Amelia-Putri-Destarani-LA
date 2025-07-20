<?php
// Konfigurasi koneksi database (sesuaikan dengan informasi database Anda)
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_rumah_hapus = $_GET['id'];

    // Ambil nama file foto untuk dihapus dari folder uploads
    $sql_select_foto = "SELECT foto_rumah FROM tb_rumah WHERE id_rumah = ?";
    $stmt_select_foto = $conn->prepare($sql_select_foto);
    $stmt_select_foto->bind_param("i", $id_rumah_hapus);
    $stmt_select_foto->execute();
    $result_select_foto = $stmt_select_foto->get_result();
    $row_foto = $result_select_foto->fetch_assoc();
    $stmt_select_foto->close();

    $foto_path = $row_foto['foto_rumah'] ?? null;

    // Query untuk menghapus data rumah berdasarkan ID
    $sql_hapus = "DELETE FROM tb_rumah WHERE id_rumah = ?";
    $stmt_hapus = $conn->prepare($sql_hapus);
    $stmt_hapus->bind_param("i", $id_rumah_hapus);

    if ($stmt_hapus->execute()) {
        // Jika penghapusan berhasil, hapus juga file foto jika ada
        if ($foto_path && file_exists($foto_path)) {
            unlink($foto_path);
        }
        header("Location: Kategori_Rumah.php?hapus=sukses");
        exit();
    } else {
        header("Location: Kategori_Rumah.php?hapus=gagal&error=" . urlencode($stmt_hapus->error));
        exit();
    }

    $stmt_hapus->close();
} else {
    header("Location: Kategori_Rumah.php?hapus=invalid");
    exit();
}

$conn->close();
?>