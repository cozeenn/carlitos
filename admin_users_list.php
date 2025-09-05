<?php
session_start();

if (!isset($_SESSION['admin'])) {
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

    // Fetch current admin info
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username");
    $stmt->execute(['username' => $_SESSION['admin']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        die("<div style='color:#f44336;text-align:center;margin-top:2rem;'>Admin profile not found. Please check your database or login again.</div>");
    }

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_username = trim($_POST['username']);
        $new_password = trim($_POST['password']);

        if ($new_username && $new_password) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET username = :username, password = :password WHERE id = :id");
            $stmt->execute([
                'username' => $new_username,
                'password' => $hashed,
                'id' => $admin['id']
            ]);
            $_SESSION['admin'] = $new_username;
            $message = "Profile updated successfully!";
        } else {
            $message = "Please fill in all fields.";
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Admin Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Poppins', sans-serif; background: #23255a; color: #fff; }
    .container { max-width: 400px; margin: 4rem auto; background: #1a1c3a; padding: 2rem; border-radius: 12px; }
    h2 { color: #00f0ff; margin-bottom: 2rem; }
    label { display: block; margin-bottom: 0.5rem; }
    input[type="text"], input[type="password"] {
      width: 100%; padding: 0.7rem; margin-bottom: 1.2rem; border-radius: 6px; border: none;
    }
    .btn { background: #00f0ff; color: #23255a; border: none; padding: 0.7rem 2rem; border-radius: 30px; font-weight: bold; cursor: pointer; }
    .btn:hover { background: #00c0cc; }
    .message { margin-bottom: 1rem; color: #00f0ff; }
    a { color: #00f0ff; text-decoration: none; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Admin Profile</h2>
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" value="<?= htmlspecialchars($admin['username']) ?>" required />

      <label for="password">New Password</label>
      <input type="password" name="password" id="password" required />

      <button type="submit" class="btn">Update Profile</button>
    </form>
    <br>
    <a href="admin_dashboard.php">Back to Dashboard</a>
  </div>
</body>
</html>
