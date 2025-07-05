<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi OTP</title>
    <link rel="stylesheet" href="STYLE/verifyOTP.css">
</head>
<body>
    <h2>Masukkan OTP</h2>
    <form action="verify_otp.php" method="post">
        <label for="otp">OTP:</label>
        <input type="text" id="otp" name="otp" required><br>
        <button type="submit">Verifikasi</button>
    </form>
</body>
</html>

<?php
session_start();
$conn = new mysqli("localhost", "root", "", "db_ternak");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = $_POST['otp'];
    $user_id = $_SESSION['user_id'];

    // Ambil OTP dan waktu kedaluwarsa dari database
    $query = "SELECT otp, otp_expiry FROM user WHERE id_pengguna= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Cek apakah OTP masih valid
    if (new DateTime() < new DateTime($user['otp_expiry']) && password_verify($otp_input, $user['otp'])) {
        $_SESSION['authenticated'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "OTP kedaluwarsa.";
    }
}
?>
