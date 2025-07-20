<?php
session_start();

// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id_pembayaran = $conn->real_escape_string($_GET['id']);

    // Mulai transaksi untuk memastikan konsistensi data
    $conn->begin_transaction();

    try {
        // 1. Ambil id_pembelian yang terkait sebelum menghapus pembayaran
        // Kita butuh id_pembelian untuk mengupdate status di tb_pembelian dan tb_unit
        $sql_get_id_pembelian = "SELECT id_pembelian FROM tb_pembayaran WHERE id_pembayaran = '$id_pembayaran'";
        $result_get_id_pembelian = $conn->query($sql_get_id_pembelian);

        if ($result_get_id_pembelian->num_rows > 0) {
            $row_pembayaran = $result_get_id_pembelian->fetch_assoc();
            $id_pembelian_terkait = $row_pembayaran['id_pembelian'];

            // 2. Hapus data pembayaran dari tb_pembayaran
            $sql_delete_pembayaran = "DELETE FROM tb_pembayaran WHERE id_pembayaran = '$id_pembayaran'";
            if (!$conn->query($sql_delete_pembayaran)) {
                throw new Exception("Terjadi kesalahan saat menghapus data pembayaran: " . $conn->error);
            }

            // 3. Periksa apakah masih ada pembayaran lain untuk id_pembelian ini
            // Jika tidak ada pembayaran lain, berarti pembelian ini sepenuhnya "dibatalkan"
            $sql_check_other_payments = "SELECT COUNT(*) AS total_payments FROM tb_pembayaran WHERE id_pembelian = '$id_pembelian_terkait'";
            $result_check_other_payments = $conn->query($sql_check_other_payments);
            $row_total_payments = $result_check_other_payments->fetch_assoc();
            $total_payments = $row_total_payments['total_payments'];

            // Jika tidak ada lagi pembayaran yang terkait dengan id_pembelian ini
            if ($total_payments == 0) {
                // 4. Ambil type_rumah dan blok_rumah dari tb_pembelian berdasarkan id_pembelian yang terkait
                // Ini sesuai dengan cara Anda menghubungkan tb_pembelian dan tb_unit di skrip tambah_pembayaran.php
                $sql_get_unit_details_from_pembelian = "SELECT type_rumah, blok_rumah FROM tb_pembelian WHERE id_pembelian = '$id_pembelian_terkait'";
                $result_get_unit_details_from_pembelian = $conn->query($sql_get_unit_details_from_pembelian);

                if ($result_get_unit_details_from_pembelian->num_rows > 0) {
                    $row_unit_details = $result_get_unit_details_from_pembelian->fetch_assoc();
                    $type_rumah_terkait = $row_unit_details['type_rumah'];
                    $blok_rumah_terkait = $row_unit_details['blok_rumah'];

                    // 5. Update status unit di tb_unit menjadi 'Terbooking'
                    // Menggunakan type_unit dan nama_blok untuk mengidentifikasi unit yang tepat
                    $sql_update_unit_status = "UPDATE tb_unit SET status = 'Terbooking' WHERE type_unit = '$type_rumah_terkait' AND nama_blok = '$blok_rumah_terkait'";
                    if (!$conn->query($sql_update_unit_status)) {
                        throw new Exception("Terjadi kesalahan saat mengubah status unit: " . $conn->error);
                    }
                } else {
                    // Log atau tangani jika detail unit tidak ditemukan di tb_pembelian
                    // Ini seharusnya tidak terjadi jika data konsisten
                }

                // 6. Update status_pembelian di tb_pembelian menjadi 'Terbooking'
                $sql_update_pembelian_status = "UPDATE tb_pembelian SET status_pembelian = 'Terbooking' WHERE id_pembelian = '$id_pembelian_terkait'";
                if (!$conn->query($sql_update_pembelian_status)) {
                    throw new Exception("Terjadi kesalahan saat mengubah status pembelian: " . $conn->error);
                }
            }

            // Commit transaksi jika semua query berhasil
            $conn->commit();
            $_SESSION['pesan'] = "<div style='color: green; margin-bottom: 10px;'>Data pembayaran berhasil dihapus dan status terkait diperbarui.</div>";
            header("Location: data_pembayaran.php");
            exit(); // Penting untuk menghentikan eksekusi setelah redirect
        } else {
            throw new Exception("ID Pembayaran tidak ditemukan.");
        }

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $conn->rollback();
        $_SESSION['pesan'] = "<div style='color: red; margin-bottom: 10px;'>" . $e->getMessage() . "</div>";
        header("Location: data_pembayaran.php");
        exit(); // Penting untuk menghentikan eksekusi setelah redirect
    }
} else {
    $_SESSION['pesan'] = "<div style='color: red; margin-bottom: 10px;'>ID Pembayaran tidak valid.</div>";
    header("Location: data_pembayaran.php");
    exit(); // Penting untuk menghentikan eksekusi setelah redirect
}

$conn->close();
?>