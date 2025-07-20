<?php
// get_blok.php
header('Content-Type: application/json');

// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    error_log("Koneksi database gagal: " . $conn->connect_error);
    echo json_encode(['blok' => [], 'error' => 'Koneksi database gagal.']);
    exit();
}

// Cek jika permintaan untuk mengambil blok rumah dikirim via POST
if (isset($_POST['id_rumah'])) {
    $id_rumah_param = (int)$_POST['id_rumah']; // Pastikan ini integer dan lindungi

    // --- PERUBAHAN UTAMA DI SINI: SELECT id_unit, nama_blok, status ---
    // Query untuk mengambil id_unit, nama_blok, DAN STATUS dari tb_unit berdasarkan id_rumah
    $stmt_get_blok = $conn->prepare("SELECT id_unit, nama_blok, status FROM tb_unit WHERE id_rumah = ? ORDER BY nama_blok ASC");
    $stmt_get_blok->bind_param("i", $id_rumah_param);
    $stmt_get_blok->execute();
    $result_nama_blok = $stmt_get_blok->get_result();

    $blok_data = [];
    if ($result_nama_blok) {
        if ($result_nama_blok->num_rows > 0) {
            while ($row = $result_nama_blok->fetch_assoc()) {
                $blok_data[] = [
                    'id_unit' => $row['id_unit'], // <<<--- PASTIKAN KOLOM 'id_unit' DIAMBIL DI SINI
                    'nama_blok' => $row['nama_blok'],
                    'status' => $row['status']
                ];
            }
        }
    } else {
        error_log("Error executing query in get_blok.php: " . $stmt_get_blok->error);
        echo json_encode(['blok' => [], 'error' => 'Gagal mengambil data blok.']);
        exit();
    }
    $stmt_get_blok->close();
    echo json_encode(['blok' => $blok_data]);
    exit();
} else {
    echo json_encode(['blok' => [], 'error' => 'Parameter id_rumah tidak ditemukan.']);
    exit();
}

$conn->close();
?>