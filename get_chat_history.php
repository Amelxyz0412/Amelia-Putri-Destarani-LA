<?php
$servername = "localhost"; // Ganti dengan server Anda
$username = "root"; // Ganti dengan username Anda
$password = ""; // Ganti dengan password Anda
$dbname = "db_perumahan"; // Ganti dengan nama database Anda
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$userId = $_GET['user'] ?? '';

$sql_messages = "SELECT tm.timestamp, tm.message, tm.sender_type, tu.user_name
                FROM tb_messages tm
                JOIN tb_user tu ON tm.user_id = tu.id
                WHERE tm.user_id = ?
                ORDER BY tm.timestamp ASC";
$stmt = $conn->prepare($sql_messages);
$stmt->bind_param("i", $userId); // "i" karena user_id adalah integer
$stmt->execute();
$result_messages = $stmt->get_result();

if ($result_messages->num_rows > 0) {
    while ($row_message = $result_messages->fetch_assoc()) {
        $userName = htmlspecialchars($row_message['user_name']);
        $messageText = htmlspecialchars($row_message['message']);
        $timestamp = date("H:i", strtotime($row_message['timestamp']));
        $isSent = ($row_message['sender_type'] === 'admin');

        echo '<div class="message ' . ($isSent ? 'sent' : 'received') . '">';
        echo '<p>' . $messageText . '</p>';
        echo '<span class="message-time">' . $timestamp . '</span>';
        echo '<span class="user-name">' . $userName . '</span>';
        echo '</div>';
    }
} else {
    echo '<p>Tidak ada pesan untuk pengguna ini.</p>';
}

$stmt->close();
$conn->close();
?>