<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=carlitos_pool", "root", "");

$id = $_POST['id'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ? AND password = SHA1(?)");
$stmt->execute([$id, $password]);
$user = $stmt->fetch();

if ($user) {
    $_SESSION['edit_user_id'] = $id;
    header("Location: edit_admin.php");
    exit();
} else {
    echo "Password confirmation failed.";
}
