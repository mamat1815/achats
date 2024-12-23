<?php
// $pdo = mysqli_connect("localhost","root","","achats");
?>

<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=achats", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
