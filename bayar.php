<?php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "db_ternak");
$id_transaksi = $_GET['id_transaksi'];

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Metode Pembayaran</title>
    <link rel="stylesheet" href="STYLE/bayar.css">
</head>
<body>
    <h2>Pilih Metode Pembayaran</h2>
    <p>Nama Produk: <?php echo htmlspecialchars($transaksi['jenis_hewan']); ?></p>
    <p>Harga: Rp <?php echo number_format($transaksi['harga'], 0, ',', '.'); ?></p>
    <p>Alamat: <?php echo htmlspecialchars($transaksi['alamat']); ?></p>
    <p>No Hp: <?php echo htmlspecialchars($transaksi['no_hp']); ?></p>
    <p>Total Pembayaran: Rp <?php echo number_format($transaksi['harga'] * $transaksi['jumlah'], 0, ',', '.'); ?></p>
    
    <!-- Form Pilihan Metode Pembayaran -->
    <form action="konfir_password.php" method="post">
        <input type="hidden" name="id_transaksi" value="<?php echo $id_transaksi; ?>">
        <label for="metode_pembayaran">Pilih Metode Pembayaran:</label>
        <select id="metode_pembayaran" name="metode_pembayaran" required>
            <option value="transfer_bank">BNI</option>
            <option value="transfer_bank">BCA</option>
            <option value="transfer_bank">BRI</option>
            <option value="ewallet">E-Wallet</option>
        </select><br><br>
        <button type="submit">Bayar</button>
    </form>

    <a href="dashboard.php">Kembali</a>
</body>
</html>
