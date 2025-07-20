<?php
// get_pembelian_details.php

// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit();
}

header('Content-Type: application/json');

if (isset($_GET['id_pembelian'])) {
    $id_pembelian = $conn->real_escape_string($_GET['id_pembelian']);

    // Query untuk mengambil detail nama pembeli, type rumah, dan nama blok
    $sql = "SELECT
                p.nama_pembeli,
                r.type_rumah,
                u.nama_blok
            FROM
                tb_pembelian p
            JOIN
                tb_rumah r ON p.id_rumah = r.id_rumah
            JOIN
                tb_unit u ON p.id_unit = u.id_unit
            WHERE
                p.id_pembelian = '$id_pembelian'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'nama_pembeli' => $row['nama_pembeli'],
            'type_rumah' => $row['type_rumah'],
            'nama_blok' => $row['nama_blok']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Detail pembelian tidak ditemukan.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID Pembelian tidak disediakan.']);
}

$conn->close();
?>