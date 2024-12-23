<?php
session_start();
require_once 'config/config.php';

// Mendapatkan sellerId dari URL
$sellerId = $_GET['sellerId'] ?? null;
if (!$sellerId) {
    echo "Seller ID is missing.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses order baru
    $description = $_POST['description'] ?? '';
    $buyerId = $_SESSION['userId'] ?? null; // Ganti ini dengan cara Anda mengambil ID pengguna

    if (!$buyerId) {
        echo "Anda harus login untuk memesan.";
        exit;
    }

    try {
        // Simpan order ke database
        $pdo->beginTransaction();

        $query = "
            INSERT INTO orders (sellerId, buyerId, description, status, createdAt) 
            VALUES (:sellerId, :buyerId, :description, 'pending', NOW())
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'sellerId' => $sellerId,
            'buyerId' => $buyerId,
            'description' => $description
        ]);

        $orderId = $pdo->lastInsertId(); // Ambil ID order baru

        // Proses upload file
        if (!empty($_FILES['orderImage']['name'][0])) {
            $uploadDir = 'uploads/order_images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['orderImage']['name'] as $key => $name) {
                $tmpName = $_FILES['orderImage']['tmp_name'][$key];
                $error = $_FILES['orderImage']['error'][$key];
                $size = $_FILES['orderImage']['size'][$key];

                if ($error === UPLOAD_ERR_OK) {
                    // Validasi ukuran file
                    if ($size > 5 * 1024 * 1024) { // Maksimal 5MB
                        throw new Exception("File $name terlalu besar. Maksimum 5MB.");
                    }

                    // Nama file unik
                    $fileName = uniqid('img_', true) . '.' . pathinfo($name, PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName;

                    // Pindahkan file ke folder upload
                    if (move_uploaded_file($tmpName, $filePath)) {
                        // Simpan path gambar ke tabel `order_images`
                        $query = "INSERT INTO order_images (orderId, imagePath) VALUES (:orderId, :imagePath)";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([
                            'orderId' => $orderId,
                            'imagePath' => $filePath
                        ]);
                    } else {
                        throw new Exception("Gagal mengunggah file $name.");
                    }
                } else {
                    throw new Exception("Error saat mengunggah file $name. Kode error: $error.");
                }
            }
        }

        $pdo->commit();
        echo "Order berhasil dibuat.";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Jasa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/0444f7d0d8.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require 'assets/components/navbar.php'; ?>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header text-center">
                <h3>Kirim Desain Anda</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <img src="<?= htmlspecialchars($seller['img'] ?? 'https://via.placeholder.com/100') ?>" 
                             alt="Seller Image" class="rounded-circle" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="mt-2">Jasa oleh <?= htmlspecialchars($seller['fullName']) ?></h5>
                    </div>

                    <div class="mb-3">
                <label for="orderImage" class="form-label">Kirim Design Anda</label>
                <div class="border p-4 text-center" style="border-radius: 10px;">
                    <input type="file" name="orderImage[]" id="orderImage" multiple accept="image/*" class="form-control">
                    <small class="text-muted">
                        Format yang didukung: PNG/JPG/WebP, Maksimal ukuran: 5MB
                    </small>
                </div>
            </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Tambahkan detail mengenai desain Anda" required></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>