<?php

try {
    $dsn = "mysql:host=" . (getenv('DB_HOST') ?: "db") . ";dbname=" . (getenv('DB_NAME') ?: "achats");
    $pdo = new PDO(
        $dsn,
        getenv('DB_USER') ?: "achatsuser",
        getenv('DB_PASSWORD') ?: "userpassword"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
