<?php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "db_ternak");
$id_produk = $_GET['id_hewan'];

$query = "SELECT * FROM hewan WHERE id_hewan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data yang diperlukan dari form
    $user_id = $_SESSION['user_id'];
    $jumlah = $_POST['jumlah']; // Ambil jumlah dari input pengguna
    $total_harga = $produk['harga'] * $jumlah;

    // Jika ingin menyimpan alamat dan no_hp di transaksi, tambahkan pada query
    if (isset($_POST['alamat']) && isset($_POST['no_hp'])) {
        $alamat = $_POST['alamat'];
        $no_hp = $_POST['no_hp'];
        
        // Simpan detail transaksi ke tabel transaksi, pastikan kolom alamat dan no_hp ada dalam tabel
        $query = "INSERT INTO transaksi (id_pengguna, id_hewan, jumlah, total_harga, alamat, no_hp, status) VALUES (?, ?, ?, ?, ?, ?, 'Proses')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiss", $user_id, $id_produk, $jumlah, $total_harga, $alamat, $no_hp);
    } else {
        // Jika alamat dan no_hp tidak ada, masukkan transaksi tanpa kolom tersebut
        $query = "INSERT INTO transaksi (id_pengguna, id_hewan, jumlah, total_harga, status) VALUES (?, ?, ?, ?, 'Proses')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $user_id, $id_produk, $jumlah, $total_harga);
    }

    // Eksekusi query
    $stmt->execute();

    // Dapatkan id transaksi terakhir
    $transaksi_id = $conn->insert_id;

    // Redirect ke halaman bayar dengan ID transaksi
    header("Location: bayar.php?id_transaksi=$transaksi_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Produk</title>
    <link rel="stylesheet" href="STYLE/beli.css">
</head>
<body>
    <div class="container">
        <h2>Detail Produk</h2>
        <p>Jenis Hewan: <?php echo $produk['jenis_hewan']; ?></p>
        <p>Spesies: <?php echo $produk['spesies']; ?></p>
        <p>Umur: <?php echo $produk['usia']; ?></p>
        <p>Harga: Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>

        <form method="post">
            <label for="jumlah">Jumlah Pembelian:</label>
            <input type="number" id="jumlah" name="jumlah" value="1" min="1" required>

            <!-- Input untuk alamat dan nomor HP, bisa dibiarkan kosong atau wajib diisi jika dibutuhkan -->
            <label for="alamat">Alamat Pengiriman:</label>
            <input type="text" id="alamat" name="alamat" required>

            <label for="no_hp">Nomor Telepon:</label>
            <input type="text" id="no_hp" name="no_hp" required>

            <button type="submit">Lanjutkan Pembelian</button>
        </form>

        <a href="dashboard.php">Kembali</a>
    </div>
</body>
</html>
