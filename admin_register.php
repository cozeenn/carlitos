<?php
$host = "localhost";
$dbname = "carlitos_pool";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user = $_POST['username'];
        $pass = $_POST['password'];

        // Hash the password securely
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

        // Check if user already exists
        $check = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $check->execute([$user]);
        if ($check->rowCount() > 0) {
            echo "<script>alert('Username already exists'); window.location.href='admin_register.html';</script>";
            exit;
        }

        // Insert new admin
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        $stmt->execute([$user, $hashed_pass]);

        echo "<script>alert('Admin registered successfully!'); window.location.href='admin_login.html';</script>";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
