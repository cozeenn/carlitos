<?php
session_start();

if (!isset($_SESSION['edit_user_id'])) {
    header("Location: admin_users_list.php");
    exit();
}

$host = "localhost";
$dbname = "carlitos_pool";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $error = "";
    $success = "";
    $id = $_SESSION['edit_user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newUsername = trim($_POST['username'] ?? '');
        $newPassword = trim($_POST['password'] ?? '');

        if (!empty($newUsername) && !empty($newPassword)) {
            $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
            $stmt->execute([$newUsername, sha1($newPassword), $id]);
            $success = "Account updated successfully.";
        } else {
            $error = "All fields are required.";
        }
    }

    $stmt = $pdo->prepare("SELECT username FROM admin_users WHERE id = ?");
    $stmt->execute([$id]);
    $admin = $stmt->fetch();

    if (!$admin) {
        die("Admin not found.");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Profile - Carlito's</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background: linear-gradient(120deg, #0f2027, #609bae, #4c8ca8);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .form-container {
      background: rgba(255, 255, 255, 0.05);
      padding: 2rem;
      border-radius: 16px;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 0 12px #00f0ff55;
      backdrop-filter: blur(10px);
    }
    h2 {
      text-align: center;
      color: #00f0ff;
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      margin-top: 1rem;
      color: #a8faff;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 0.5rem;
      border-radius: 8px;
      border: none;
      margin-top: 0.25rem;
    }
    .btn {
      margin-top: 1.5rem;
      width: 100%;
      background: #00f0ff;
      color: black;
      padding: 0.7rem;
      border: none;
      font-weight: bold;
      border-radius: 10px;
      cursor: pointer;
    }
    .btn:hover {
      background: #00c0cc;
    }
    .message {
      margin-top: 1rem;
      text-align: center;
    }
    .success {
      color: #90ee90;
    }
    .error {
      color: #ff7777;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Edit Profile</h2>
    <form method="POST">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

      <label for="password">New Password</label>
      <input type="password" name="password" id="password" required>

      <button class="btn" type="submit">Update</button>
    </form>

    <?php if (!empty($error)): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
