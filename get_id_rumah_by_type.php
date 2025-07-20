<?php
// get_id_rumah_by_type.php
header('Content-Type: application/json');

// Koneksi ke Database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    error_log("Koneksi database gagal di get_id_rumah_by_type.php: " . $conn->connect_error);
    echo json_encode(['id_rumah' => null, 'error' => 'Koneksi database gagal.']);
    exit();
}

$response = ['id_rumah' => null, 'error' => ''];

if (isset($_POST['type_rumah'])) {
    $type_rumah_param = $_POST['type_rumah'];

    $stmt = $conn->prepare("SELECT id_rumah FROM tb_rumah WHERE type_rumah = ?");
    if ($stmt) {
        $stmt->bind_param("s", $type_rumah_param);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $response['id_rumah'] = $row['id_rumah'];
        } else {
            $response['error'] = 'Type rumah tidak ditemukan.';
        }
        $stmt->close();
    } else {
        $response['error'] = 'Error persiapan query: ' . $conn->error;
    }
} else {
    $response['error'] = 'Parameter type_rumah tidak ditemukan.';
}

echo json_encode($response);

if (isset($conn)) {
    $conn->close();
}
?>