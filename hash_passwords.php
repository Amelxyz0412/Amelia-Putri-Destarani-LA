<?php
// hash_passwords.php (HANYA UNTUK SEKALI JALAN SAAT MIGRASI)

$host = 'localhost';
$db = 'db_perumahan';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Koneksi database berhasil.<br>";
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Ambil semua user
$stmt = $pdo->query("SELECT id_admin, username, password FROM tb_login");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    // Hanya hash password yang belum di-hash (misalnya yang masih pendek)
    // Atau Anda bisa men-hash ulang semua, tergantung strategi Anda
    if (strlen($user['password']) <= 25) { // Asumsi password lama max 25 karakter
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);

        // Update password di database
        $update_stmt = $pdo->prepare("UPDATE tb_login SET password = :hashed_password WHERE id_admin = :id_admin");
        $update_stmt->bindParam(':hashed_password', $hashed_password);
        $update_stmt->bindParam(':id_admin', $user['id_admin']);
        $update_stmt->execute();
        echo "Password untuk user '{$user['username']}' berhasil di-hash dan diperbarui.<br>";
    } else {
        echo "Password untuk user '{$user['username']}' tampaknya sudah di-hash (panjang: " . strlen($user['password']) . ").<br>";
    }
}

echo "Proses hashing selesai. HARAP HAPUS FILE INI DARI SERVER ANDA!";
?>