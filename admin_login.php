<?php
session_start();

// Prevent login page from being cached after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=carlitos_pool", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get admin using username and hashed password
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($_POST['password'], $admin['password'])) {
        $_SESSION['admin'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_full_name'] = $admin['full_name'] ?? '';
        $_SESSION['admin_email'] = $admin['email'] ?? '';

        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Login failed. Invalid username or password.";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
