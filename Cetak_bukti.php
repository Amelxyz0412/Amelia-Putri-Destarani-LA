<?php
// Halaman cetak bukti pembayaran
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$bukti = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT bukti_pembayaran FROM tb_pembayaran WHERE id_pembayaran = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $data = $result->fetch_assoc();
        $bukti = $data['bukti_pembayaran'];
    } else {
        die("Data tidak ditemukan.");
    }
} else {
    die("ID tidak valid.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Bukti Pembayaran</title>
    <style>
        body {
            margin: 0;
            padding: 30px;
            text-align: center;
            font-family: Arial, sans-serif;
            background: #fff;
        }

        img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-print {
            padding: 12px 24px;
            background-color: #117c6b;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-print:hover {
            background-color: #0d6658;
        }

        @media print {
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php if (!empty($bukti)) : ?>
        <img src="<?php echo htmlspecialchars($bukti); ?>" alt="Bukti Pembayaran">
        <br>
        <button class="btn-print" onclick="window.print()">Cetak</button>
    <?php else : ?>
        <p>Bukti pembayaran tidak tersedia.</p>
    <?php endif; ?>
</body>
</html>
