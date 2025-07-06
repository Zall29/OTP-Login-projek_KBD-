<?php
session_start();
require 'vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli("localhost", "root", "", "db_ternak");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah username terdaftar di database
    $query = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_pengguna'];

            // Buat OTP dan simpan dalam database
            $otp = rand(100000, 999999);
            $hashed_otp = password_hash($otp, PASSWORD_BCRYPT);
            $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            $update_otp = "UPDATE user SET otp = ?, otp_expiry = ? WHERE id_pengguna = ?";
            $stmt = $conn->prepare($update_otp);
            $stmt->bind_param("ssi", $hashed_otp, $expiry, $user['id_pengguna']);
            $stmt->execute();

            // Kirim OTP ke email pengguna
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yahoo@gmail.com'; // Ganti dengan email Anda
                $mail->Password = 'isi App Password pribadi'; // Ganti dengan App Password Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('yahoo@gmail.com'); // Ganti dengan email Anda
                $mail->addAddress($user['email']);
                $mail->isHTML(true);
                $mail->Subject = 'OTP Login Anda';
                $mail->Body = "Kode OTP Anda adalah: <b>$otp</b>";
                
                $mail->SMTPDebug = 2; // 1 = errors and messages, 2 = messages only

                $mail->send();
                echo "OTP telah dikirim ke email Anda.";
                
                // Tutup sesi sebelum redirect
                session_write_close();
                header("Location: verify_otp.php");
                exit();
            } catch (Exception $e) {
                echo "Gagal mengirim OTP: {$mail->ErrorInfo}";
            }
        } else {
            echo "Password salah.";
        }
    } else {
        echo "Username tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="STYLE/login.css">
</head>
<body>
    <h2>Form Login</h2>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</body>
</html>
