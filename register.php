<?php
session_start();
$conn = new mysqli("localhost", "root", "", "db_ternak");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];

    // Simpan data pengguna baru ke database
    $query = "INSERT INTO user (username, password, email, alamat, no_hp) VALUES (?, ?, ?, ?, ?)";
    
    // Siapkan query untuk eksekusi
    $stmt = $conn->prepare($query);
    
    // Bind parameter untuk query (sesuaikan dengan jumlah kolom)
    $stmt->bind_param("sssss", $username, $password, $email, $alamat, $no_hp);
    
    // Eksekusi query
    if ($stmt->execute()) {
        echo "Registrasi berhasil. Silakan login.";
    } else {
        echo "Gagal registrasi. Username atau email mungkin sudah terdaftar.";
    }

    // Tutup statement dan koneksi
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <link rel="stylesheet" href="STYLE/register.css">
</head>
<body>
    <h2>Form Registrasi</h2>
    <form action="register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="alamat">Alamat:</label>
        <input type="alamat" id="alamat" name="alamat" required><br>
        <label for="no_hp">No Hp:</label>
        <input type="no_hp" id="no_hp" name="no_hp" required><br>
        
        <button type="submit">Daftar</button>
    </form>
    <p><a href="login.php">Kembali</a></p>
</body>
</html>
