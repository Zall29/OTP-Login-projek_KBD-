<?php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "db_ternak");

// Data dari form sebelumnya
$id_transaksi = $_POST['id_transaksi'];
$metode_pembayaran = $_POST['metode_pembayaran'];

// Ambil detail transaksi
$query = "SELECT transaksi.*, hewan.jenis_hewan, hewan.harga 
          FROM transaksi 
          JOIN hewan ON transaksi.id_hewan = hewan.id_hewan 
          WHERE transaksi.id_transaksi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_transaksi);
$stmt->execute();
$result = $stmt->get_result();
$transaksi = $result->fetch_assoc();

// Fungsi untuk membuat token
function generateToken($data) {
    return bin2hex(random_bytes(16));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
    $password_input = $_POST['password'];
    $user_id = $_SESSION['user_id'];

    // Ambil password pengguna
    $query = "SELECT password FROM user WHERE id_pengguna = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($password_input, $user['password'])) {
        // Tokenisasi pembayaran
        $token_pembayaran = generateToken($metode_pembayaran);

        // Simpan transaksi dengan token
        $query = "UPDATE transaksi SET status = 'selesai', metode_pembayaran = ?, token_pembayaran = ? WHERE id_transaksi = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $metode_pembayaran, $token_pembayaran, $id_transaksi);
        $stmt->execute();

        echo "<script>
                alert('Pembayaran berhasil!');
                window.location.href = 'dashboard.php';
              </script>";
        exit();
    } else {
        echo "<script>alert('Password salah. Coba lagi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Password</title>
    <link rel="stylesheet" href="STYLE/bayar.css">
</head>
<body>
    <h2>Konfirmasi Password</h2>
    <p>Nama Produk: <?php echo htmlspecialchars($transaksi['jenis_hewan']); ?></p>
    <p>Harga: Rp <?php echo number_format($transaksi['harga'], 0, ',', '.'); ?></p>
    <p>Alamat: <?php echo htmlspecialchars($transaksi['alamat']); ?></p>
    <p>No Hp: <?php echo htmlspecialchars($transaksi['no_hp']); ?></p>
    <p>Total Pembayaran: Rp <?php echo number_format($transaksi['harga'] * $transaksi['jumlah'], 0, ',', '.'); ?></p>
    
    <!-- Form Konfirmasi Password -->
    <form method="post">
        <input type="hidden" name="id_transaksi" value="<?php echo $id_transaksi; ?>">
        <input type="hidden" name="metode_pembayaran" value="<?php echo htmlspecialchars($metode_pembayaran); ?>">
        <label for="password">Masukkan Password untuk Konfirmasi:</label>
        <input type="password" id="password" name="password" required><br><br>
        <button type="submit">Konfirmasi Pembayaran</button>
    </form>

    <a href="bayar.php?id_transaksi=<?php echo $id_transaksi; ?>">Kembali</a>
</body>
</html>
