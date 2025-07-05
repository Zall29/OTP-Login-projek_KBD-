<?php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "db_ternak");

$query = "SELECT * FROM hewan";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Produk</title>
    <link rel="stylesheet" href="STYLE/dashboard.css">
</head>
<body>
    <h2>Selamat datang di Toko Kami!</h2>

    <h3>Daftar Produk</h3>
    <table border="1">
        <tr>
            <th>Nama Produk</th>
            <th>Spesies</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['jenis_hewan']; ?></td>
            <td><?php echo $row['spesies']; ?></td>
            <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
            <td><a href="beli.php?id_hewan=<?php echo $row['id_hewan']; ?>">Beli</a></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a href="logout.php">Logout</a>
</body>
</html>
