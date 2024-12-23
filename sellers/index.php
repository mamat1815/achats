<?php 
?>

<?php
// Setup konfigurasi
require_once __DIR__ . '/../config/config.php';
session_start();

if (!isset($_SESSION)) {
    echo "User not logged in.";
    exit();
}

if (!isset($_SESSION['sellerId']) || $_SESSION['role'] !== 'seller') {
    echo "<script>alert('Kamu bukan penjual'); window.location.href = '../index.php';</script>";
    exit;
}

$user_id = $_SESSION['userId'];

// Pastikan variabel $pdo tersedia
if (!isset($pdo)) {
    echo "Database connection not found.";
    exit();
}

// Fetch data seller
// $sellerQuery = "SELECT * FROM sellers WHERE userId = ?";
// $stmt = $pdo->prepare($sellerQuery);
// $stmt->execute([$user_id]);
// $seller = $stmt->fetch();
require __DIR__ . '/../config/functions/fetch-seller.php';

$seller = fetchSeller($user_id);
if (!$seller) {
    echo "Seller not found.";
    exit();
}

$userQuery = "SELECT * FROM users WHERE id = ?";
$stmtUser = $pdo->prepare($userQuery);
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

// Fetch statistik penjualan dan transaksi
$statsQuery = "SELECT COUNT(*) as total_sales, SUM(amount) as total_revenue FROM transactions WHERE sellerId = ?";
$stmt = $pdo->prepare($statsQuery);
$stmt->execute([$seller['sellerId']]);
$stats = $stmt->fetch();

// Fetch riwayat transaksi
$transactionsQuery = "SELECT * FROM transactions WHERE sellerId = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($transactionsQuery);
$stmt->execute([$seller['sellerId']]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/seller.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
 

</head>
<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <a href="index.php"class="active">Dashboard</a>
        <a href="products.php" >Produk</a>
        <a href="services.php">Jasa</a>
        <a href="config/functions/logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($user['fullName']); ?></h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Design Request</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Total Penjualan: <?php echo $stats['total_sales']; ?></p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Total Pendapatan: Rp<?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Transaksi Terbaru: <?php echo count($transactions); ?></p>
                        </div>
                    </a>
                </div>
            </div>

           
        </div>

        <div class="row">
            <div class="col-md-12 card">
                <div class="card-body">
                    <h2>Statistik Penjualan</h2>
                    <table class="charts-css column">

                        <caption> Front End Developer Salary </caption>

                        <tbody>
                            <tr>
                                <!-- Ini Untuk Pemasukan Dari Jasa -->
                            <td style="--size: calc( 40 / <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> )"> $40K </td>
                            </tr>
                            <tr>
                                <!-- Ini Untuk Pemasukan Dari Produk -->
                            <td style="--size: calc( 60 / <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> )"> $60K </td>
                            </tr>
                            <tr>
                                <!-- Ini Untuk Pemasukan Dari Total -->
                            <td style="--size: calc( 70 / <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> )"> $60K </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>


        <section class="transactions">
            <h2>Riwayat Transaksi</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Status</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                            <td>Rp<?php echo number_format($transaction['amount'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="manage-products">
            <h2>Manajemen Produk/Jasa</h2>
            <a href="add_product.php" class="btn btn-primary">Tambah Produk/Jasa</a>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
