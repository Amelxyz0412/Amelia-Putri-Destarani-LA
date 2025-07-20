<?php
session_start(); // Pastikan session dimulai jika Anda menggunakan $_SESSION untuk pesan

// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perumahan";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    // Gunakan error_log untuk mencatat error koneksi di server
    error_log("Koneksi database gagal: " . $conn->connect_error);
    $_SESSION['pesan'] = "<div style='color: red;'>Koneksi database gagal.</div>";
    header("Location: data_pembelian.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_pembelian_hapus = htmlspecialchars($_GET['id']); // Sanitasi input ID

    // --- LANGKAH 1: Ambil data type_rumah dan blok_rumah dari tb_pembelian SEBELUM dihapus ---
    $type_rumah_lama = null;
    $blok_rumah_lama = null;

    $stmt_get_old_data = $conn->prepare("SELECT type_rumah, blok_rumah FROM tb_pembelian WHERE id_pembelian = ?");
    $stmt_get_old_data->bind_param("s", $id_pembelian_hapus);
    $stmt_get_old_data->execute();
    $result_old_data = $stmt_get_old_data->get_result();

    if ($result_old_data->num_rows > 0) {
        $row = $result_old_data->fetch_assoc();
        $type_rumah_lama = $row['type_rumah'];
        $blok_rumah_lama = $row['blok_rumah'];
    } else {
        // Jika data pembelian tidak ditemukan, kita tidak bisa mengembalikan status unit
        $_SESSION['pesan'] = "<div style='color: orange;'>Data pembelian tidak ditemukan, tidak dapat memperbarui status unit.</div>";
        // Lanjutkan mencoba menghapus, tapi unit mungkin tidak terpengaruh
    }
    $stmt_get_old_data->close();

    // --- LANGKAH 2: Hapus data dari tb_pembelian ---
    $conn->begin_transaction(); // Mulai transaksi untuk memastikan atomicity

    $sql_hapus = "DELETE FROM tb_pembelian WHERE id_pembelian = ?";
    $stmt_hapus = $conn->prepare($sql_hapus);
    $stmt_hapus->bind_param("s", $id_pembelian_hapus);

    if ($stmt_hapus->execute()) {
        // --- LANGKAH 3: Jika penghapusan berhasil, perbarui status unit di tb_unit ---
        if ($type_rumah_lama && $blok_rumah_lama) {
            // Dapatkan id_rumah dari tb_rumah berdasarkan type_rumah_lama
            $id_rumah_to_revert = null;
            $stmt_get_id_rumah = $conn->prepare("SELECT id_rumah FROM tb_rumah WHERE type_rumah = ?");
            $stmt_get_id_rumah->bind_param("s", $type_rumah_lama);
            $stmt_get_id_rumah->execute();
            $result_id_rumah = $stmt_get_id_rumah->get_result();
            if ($row_id = $result_id_rumah->fetch_assoc()) {
                $id_rumah_to_revert = $row_id['id_rumah'];
            }
            $stmt_get_id_rumah->close();

            if ($id_rumah_to_revert !== null) {
                $stmt_update_unit = $conn->prepare("UPDATE tb_unit SET status = 'tersedia' WHERE id_rumah = ? AND nama_blok = ?");
                $stmt_update_unit->bind_param("is", $id_rumah_to_revert, $blok_rumah_lama);

                if ($stmt_update_unit->execute()) {
                    $conn->commit(); // Commit transaksi jika keduanya berhasil
                    $_SESSION['pesan'] = "<div style='color: green;'>Data pembelian berhasil dihapus dan status unit berhasil dikembalikan ke tersedia.</div>";
                } else {
                    $conn->rollback(); // Rollback jika update unit gagal
                    error_log("Gagal mengembalikan status unit setelah hapus pembelian: " . $stmt_update_unit->error);
                    $_SESSION['pesan'] = "<div style='color: red;'>Data pembelian gagal dihapus atau status unit tidak dapat dikembalikan: " . $stmt_update_unit->error . "</div>";
                }
                $stmt_update_unit->close();
            } else {
                $conn->rollback(); // Rollback jika id_rumah tidak ditemukan
                error_log("ID Rumah tidak ditemukan untuk Type Rumah: " . $type_rumah_lama);
                $_SESSION['pesan'] = "<div style='color: orange;'>Data pembelian berhasil dihapus, namun gagal mengembalikan status unit karena tipe rumah tidak valid.</div>";
            }
        } else {
            $conn->commit(); // Commit transaksi jika penghapusan berhasil tapi tidak ada unit yang perlu di-revert
            $_SESSION['pesan'] = "<div style='color: green;'>Data pembelian berhasil dihapus. Tidak ada unit terkait yang perlu di-revert.</div>";
        }
    } else {
        $conn->rollback(); // Rollback jika penghapusan tb_pembelian gagal
        error_log("Gagal menghapus data pembelian: " . $stmt_hapus->error);
        $_SESSION['pesan'] = "<div style='color: red;'>Gagal menghapus data pembelian: " . $stmt_hapus->error . "</div>";
    }

    $stmt_hapus->close();

    header("Location: data_pembelian.php");
    exit();

} else {
    $_SESSION['pesan'] = "<div style='color: red;'>ID pembelian tidak valid.</div>";
    header("Location: data_pembelian.php");
    exit();
}

$conn->close();
?>