<?php
session_start();

// Prevent caching
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$host = "localhost";
$dbname = "carlitos_pool";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $error = "";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? '';
        $inputUsername = trim($_POST['username'] ?? '');
        $inputPassword = trim($_POST['password'] ?? '');

        if (!empty($id) && !empty($inputUsername) && !empty($inputPassword)) {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
            $stmt->execute([$id]);
            $admin = $stmt->fetch();

            if ($admin && $admin['username'] === $inputUsername && $admin['password'] === sha1($inputPassword)) {
                $_SESSION['edit_user_id'] = $id;
                header("Location: edit_admin_profile.php");
                exit();
            } else {
                $error = "Invalid credentials. Please try again.";
            }
        } else {
            $error = "All fields are required.";
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Confirm Edit - Carlito's</title>
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
    .error {
      margin-top: 1rem;
      color: #ff7777;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Confirm Admin Identity</h2>
    <form method="POST">
      <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" required>

      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>

      <button class="btn" type="submit">Confirm & Edit</button>
    </form>
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
