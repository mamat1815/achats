<?php
session_start();
require_once 'config/config.php';

// Ambil ID produk dari URL
$productId = $_GET['id'] ?? null;

if (!$productId) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location.href = 'index.php';</script>";
    exit();
}

// Fetch detail produk
$query = "SELECT p.*, s.sellerId, s.bio, u.fullName AS sellerName 
          FROM products p
          LEFT JOIN sellers s ON p.sellerId = s.sellerId
          LEFT JOIN users u ON s.userId = u.id
          WHERE p.productId = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location.href = 'index.php';</script>";
    exit();
}

// Fetch images terkait produk
$imagesQuery = "SELECT imagePath FROM product_images WHERE productId = ?";
$stmtImages = $pdo->prepare($imagesQuery);
$stmtImages->execute([$productId]);
$productImages = $stmtImages->fetchAll();

// Fetch komentar terkait produk
$commentsQuery = "SELECT c.commentId, c.comment, c.created_at, c.userId, u.fullName 
                  FROM comments c
                  LEFT JOIN users u ON c.userId = u.id
                  WHERE c.productId = ?
                  ORDER BY c.created_at DESC";
$stmtComments = $pdo->prepare($commentsQuery);
$stmtComments->execute([$productId]);
$comments = $stmtComments->fetchAll();

// Tambah komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    if (!isset($_SESSION['userId'])) {
        echo "<script>alert('Silakan login untuk memberikan komentar.'); window.location.href = 'login.php';</script>";
        exit();
    }

    $comment = htmlspecialchars($_POST['comment']);
    $userId = $_SESSION['userId'];

    $addCommentQuery = "INSERT INTO comments (productId, userId, comment) VALUES (?, ?, ?)";
    $stmtAddComment = $pdo->prepare($addCommentQuery);
    $stmtAddComment->execute([$productId, $userId, $comment]);

    echo "<script>alert('Komentar berhasil ditambahkan!'); window.location.href = 'product-detail.php?id=$productId';</script>";
    exit();
}

// Hapus komentar
if (isset($_GET['delete_comment'])) {
    $commentId = $_GET['delete_comment'];
    $userId = $_SESSION['userId'];
    $isSeller = ($_SESSION['sellerId'] == $product['sellerId']);

    $checkQuery = "SELECT userId FROM comments WHERE commentId = ?";
    $stmtCheck = $pdo->prepare($checkQuery);
    $stmtCheck->execute([$commentId]);
    $comment = $stmtCheck->fetch();

    if ($comment && ($comment['userId'] == $userId || $isSeller)) {
        $deleteQuery = "DELETE FROM comments WHERE commentId = ?";
        $stmtDelete = $pdo->prepare($deleteQuery);
        $stmtDelete->execute([$commentId]);

        echo "<script>alert('Komentar berhasil dihapus!'); window.location.href = 'product-detail.php?id=$productId';</script>";
        exit();
    } else {
        echo "<script>alert('Anda tidak memiliki izin untuk menghapus komentar ini!'); window.location.href = 'product-detail.php?id=$productId';</script>";
        exit();
    }
}

// Edit komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'])) {
    $commentId = $_POST['comment_id'];
    $updatedComment = htmlspecialchars($_POST['updated_comment']);
    $userId = $_SESSION['userId'];

    $checkQuery = "SELECT userId FROM comments WHERE commentId = ?";
    $stmtCheck = $pdo->prepare($checkQuery);
    $stmtCheck->execute([$commentId]);
    $comment = $stmtCheck->fetch();

    if ($comment && $comment['userId'] == $userId) {
        $updateQuery = "UPDATE comments SET comment = ? WHERE commentId = ?";
        $stmtUpdate = $pdo->prepare($updateQuery);
        $stmtUpdate->execute([$updatedComment, $commentId]);

        echo "<script>alert('Komentar berhasil diperbarui!'); window.location.href = 'product-detail.php?id=$productId';</script>";
        exit();
    } else {
        echo "<script>alert('Anda tidak memiliki izin untuk mengedit komentar ini!'); window.location.href = 'product-detail.php?id=$productId';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require 'assets/components/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <!-- Detail Produk -->
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <div class="mb-3">
                    <?php if (!empty($productImages)): ?>
                        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($productImages as $index => $image): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo htmlspecialchars($image['imagePath']); ?>" class="d-block w-100" alt="Product Image">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <img src="https://via.placeholder.com/600" class="img-fluid rounded" alt="Product Image">
                    <?php endif; ?>
                </div>
                <p><strong>Harga:</strong> Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                <p><strong>Stok:</strong> <?php echo $product['stock']; ?></p>
                <p><strong>Terjual:</strong> <?php echo $product['sold']; ?></p>
                <p><strong>Deskripsi:</strong><br><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p><strong>Rating:</strong> <?php echo $product['rate']; ?> ⭐</p>

                <!-- Komentar -->
                <div class="mt-5 mb-5">
                    <h4>Komentar</h4>
                    <?php if (isset($_SESSION['userId'])): ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                            </div>
                            <button type="submit" name="add_comment" class="btn btn-primary">Kirim Komentar</button>
                        </form>
                    <?php else: ?>
                        <p class="text-danger">Silakan <a href="login.php">login</a> untuk memberikan komentar.</p>
                    <?php endif; ?>

                    <ul class="list-group mt-4">
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($comment['fullName']); ?></strong>
                                    <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($comment['created_at'])); ?></small>
                                    <?php if (isset($_SESSION['userId']) && ($_SESSION['userId'] == $comment['userId'] || $_SESSION['sellerId'] == $product['sellerId'])): ?>
                                        <div class="mt-2">
                                            <a href="?id=<?php echo $productId; ?>&delete_comment=<?php echo $comment['commentId']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                                            <?php if ($_SESSION['userId'] == $comment['userId']): ?>
                                                <button class="btn btn-warning btn-sm" onclick="editComment(<?php echo $comment['commentId']; ?>, '<?php echo htmlspecialchars($comment['comment']); ?>')">Edit</button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item">Belum ada komentar.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Informasi Penjual dan Pembayaran -->
            <div class="col-md-4">

            <!-- Card Pembayaran -->
                <div class="card mb-3">
                    <div class="card-header">Pembayaran</div>
                    <div class="card-body">
                        <p class="card-text">Harga: Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        <a href="checkout.php?productId=<?php echo $productId; ?>" class="btn btn-success">Beli Sekarang</a>
                    </div>
                </div>
                <!-- Card Penjual -->
                <div class="card mb-3">
                    <div class="card-header">Informasi Penjual</div>
                    <div class="card-body">
                        <h5 class="card-title">Penjual: <?php echo htmlspecialchars($product['sellerName']); ?></h5>
                    </div>
                </div>

                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
