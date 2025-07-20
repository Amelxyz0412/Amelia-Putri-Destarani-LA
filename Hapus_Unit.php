<?php
// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if (isset($_GET['id_unit']) && is_numeric($_GET['id_unit'])) {
    $id_unit_hapus = $_GET['id_unit'];

    // Jika ada file foto unit yang ingin dihapus, kamu bisa tambahkan kode ambil dan hapus file di sini
    // Contoh (jika kolom foto_unit ada):
    /*
    $sql_select_foto = "SELECT foto_unit FROM tb_unit WHERE id_unit = ?";
    $stmt_select_foto = $conn->prepare($sql_select_foto);
    $stmt_select_foto->bind_param("i", $id_unit_hapus);
    $stmt_select_foto->execute();
    $result_select_foto = $stmt_select_foto->get_result();
    $row_foto = $result_select_foto->fetch_assoc();
    $stmt_select_foto->close();

    $foto_path = $row_foto['foto_unit'] ?? null;
    */

    // Query hapus data unit berdasarkan id_unit
    $sql_hapus = "DELETE FROM tb_unit WHERE id_unit = ?";
    $stmt_hapus = $conn->prepare($sql_hapus);
    $stmt_hapus->bind_param("i", $id_unit_hapus);

    if ($stmt_hapus->execute()) {
        // Jika ingin hapus file foto, tambahkan kode unlink disini
        /*
        if ($foto_path && file_exists($foto_path)) {
            unlink($foto_path);
        }
        */
        header("Location: unit_rumah.php?hapus=sukses");
        exit();
    } else {
        header("Location: unit_rumah.php?hapus=gagal&error=" . urlencode($stmt_hapus->error));
        exit();
    }

    $stmt_hapus->close();
} else {
    header("Location: unit_rumah.php?hapus=invalid");
    exit();
}

$conn->close();
?>
