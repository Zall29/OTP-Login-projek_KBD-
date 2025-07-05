<?php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "db_ternak");
$id_transaksi = $_GET['id_transaksi'];

$query = "SELECT transaksi.*, hewan.jenis_hewan, hewan.harga 
          FROM transaksi 
          JOIN hewan ON transaksi.id_hewan = hewan.id_hewan 
          WHERE transaksi.id_transaksi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_transaksi);
$stmt->execute();
$result = $stmt->get_result();
$transaksi = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_input = $_POST['password'];
    $user_id = $_SESSION['user_id'];
    $metode_pembayaran = $_POST['metode_pembayaran'];

    // Ambil password dari database
    $query = "SELECT password FROM user WHERE id_pengguna = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($password_input, $user['password'])) {
        // Simpan transaksi dengan metode pembayaran langsung
        $query = "UPDATE transaksi SET status = 'selesai', metode_pembayaran = ? WHERE id_transaksi = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $metode_pembayaran, $id_transaksi);
        $stmt->execute();

        echo "Pembayaran berhasil!";
    } else {
        echo "Password salah. Coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran</title>
    <link rel="stylesheet" href="STYLE/bayar.css">
</head>
<body>
    <h2>Konfirmasi Pembayaran</h2>
    <p>Nama Produk: <?php echo htmlspecialchars($transaksi['jenis_hewan']); ?></p>
    <p>Harga: Rp <?php echo number_format($transaksi['harga'], 0, ',', '.'); ?></p>
    <p>Total Pembayaran: Rp <?php echo number_format($transaksi['harga'] * $transaksi['jumlah'], 0, ',', '.'); ?></p>

    <form method="post">
        <!-- Pilihan Metode Pembayaran -->
        <label for="metode_pembayaran">Pilih Metode Pembayaran:</label>
        <select id="metode_pembayaran" name="metode_pembayaran" required>
            <option value="transfer_bank">Transfer Bank</option>
            <option value="ewallet">E-Wallet</option>
            <option value="cod">Bayar di Tempat (COD)</option>
        </select><br><br>

        <!-- Input Password -->
        <label for="password">Masukkan Password untuk Konfirmasi:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Bayar</button>
    </form>

    <!-- Info Keamanan Pembayaran -->
    <p style="color: gray; font-size: 0.9em;">
        Pembayaran Anda aman. Detail pembayaran akan disimpan secara langsung.
    </p>

    <a href="dashboard.php">Kembali</a>
</body>
</html>