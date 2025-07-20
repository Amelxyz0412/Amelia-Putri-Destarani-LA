<?php
session_start();

// Konfigurasi koneksi database (sesuaikan dengan informasi database Anda)
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$row = null; // Initialize $row to null to avoid errors if ID is not found

if (isset($_GET['id'])) {
    $id_pembelian = $conn->real_escape_string($_GET['id']);

    // Modified SQL query to include no_ktp, alamat, and telepon
    $sql = "SELECT id_pembelian, tanggal_pembelian, nama_pembeli, no_ktp, alamat, telepon, type_rumah, blok_rumah, status_pembelian
            FROM tb_pembelian
            WHERE id_pembelian = '$id_pembelian'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Format tanggal for display
        $row["tanggal_pembelian"] = date('d/m/Y', strtotime($row["tanggal_pembelian"]));
    } else {
        // If ID not found, store message in session
        $_SESSION['pesan'] = "<div style='color: red;'>Data pembelian dengan ID '{$id_pembelian}' tidak ditemukan.</div>";
        header("Location: laporan_pembelian.php");
        exit();
    }
} else {
    // If ID not provided, store message in session
    $_SESSION['pesan'] = "<div style='color: red;'>ID pembelian tidak valid.</div>";
    header("Location: laporan_pembelian.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembelian</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .detail-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }

        .detail-title {
            font-size: 2em;
            color: #117c6b;
            margin-bottom: 30px;
            font-weight: 700;
            border-bottom: 3px solid #e0f2f1;
            padding-bottom: 15px;
            text-align: center;
        }

        .detail-info p {
            margin-bottom: 15px;
            line-height: 1.7;
            color: #333;
            font-size: 1em;
        }

        .detail-info strong {
            font-weight: 600;
            color: #117c6b;
            display: inline-block;
            width: 180px; /* Adjust width as needed for alignment */
        }

        .back-button {
            display: block;
            padding: 12px 20px;
            background-color: #117c6b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 30px;
            text-align: center;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #0d6658;
        }
    </style>
</head>
<body>
    <div class="detail-container">
        <h2 class="detail-title">Detail Pembelian</h2>
        <div class="detail-info">
            <?php if ($row): // Only display details if $row is not null ?>
                <p><strong>ID Pembelian:</strong> <?php echo htmlspecialchars($row["id_pembelian"]); ?></p>
                <p><strong>Tanggal Pembelian:</strong> <?php echo htmlspecialchars($row["tanggal_pembelian"]); ?></p>
                <p><strong>Nama Pembeli:</strong> <?php echo htmlspecialchars($row["nama_pembeli"]); ?></p>
                <p><strong>No. KTP:</strong> <?php echo htmlspecialchars($row["no_ktp"]); ?></p>
                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($row["alamat"]); ?></p>
                <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($row["telepon"]); ?></p>
                <p><strong>Type Rumah:</strong> <?php echo htmlspecialchars($row["type_rumah"]); ?></p>
                <p><strong>Blok Rumah:</strong> <?php echo htmlspecialchars($row["blok_rumah"]); ?></p>
                <p><strong>Status Pembelian:</strong> <?php echo htmlspecialchars($row["status_pembelian"]); ?></p>
            <?php else: ?>
                <p>Data pembelian tidak tersedia.</p>
            <?php endif; ?>
        </div>
        <a href="laporan_pembelian.php" class="back-button">Kembali ke Laporan</a>
    </div>
</body>
</html>