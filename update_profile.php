<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

$host = "localhost";
$dbname = "carlitos_pool";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $admin_id = $_SESSION['admin_id'];

    $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $admin_id]);

    $_SESSION['admin_full_name'] = $full_name;
    $_SESSION['admin_email'] = $email;

    header("Location: admin_dashboard.php");
    exit();

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
