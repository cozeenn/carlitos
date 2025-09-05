<?php
session_start();

// Prevent browser caching for secure pages
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.html");
    exit();
}

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$host = "localhost";
$dbname = "carlitos_pool";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['action'], $_GET['id'])) {
        $action = $_GET['action'];
        $id = (int)$_GET['id'];

        if ($action === 'Verified') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'Verified' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $stmt = $pdo->prepare("SELECT email, full_name FROM bookings WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'reotan040@gmail.com'; 
                    $mail->Password   = 'cugd byeo dkdd dfqr';   
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('yourgmail@gmail.com', 'Carlito’s Pool');
                    $mail->addAddress($user['email'], $user['full_name']);

                    $mail->isHTML(true);
                    $mail->Subject = "🎉 Booking Verified - Carlito's Private Pool";

$mail->Body = "
  <div style='font-family: Arial, sans-serif; font-size: 16px; color: #333;'>
    Hi <strong>" . htmlspecialchars($user['full_name']) . "</strong>,<br><br>

    We're happy to inform you that your booking at <strong>Carlito’s Private Pool & Venue</strong> has been <span style='color: green; font-weight: bold;'>verified and approved</span>! ✅<br><br>

    <strong>📅 Booking Details:</strong><br>
    • <strong>Full Name:</strong> " . htmlspecialchars($user['full_name']) . "<br>
    • <strong>Email:</strong> " . htmlspecialchars($user['email']) . "<br>
    • <strong>Check-In:</strong> " . htmlspecialchars($booking['date_start']) . " at " . htmlspecialchars($booking['time_start']) . "<br>
    • <strong>Check-Out:</strong> " . htmlspecialchars($booking['date_end']) . " at " . htmlspecialchars($booking['time_end']) . "<br>
    • <strong>Guests:</strong> " . htmlspecialchars($booking['guests']) . "<br><br>

    <strong>📌 Reminder:</strong><br>
    If you haven’t uploaded your payment screenshot yet, kindly do so through our payment page <a href='http://localhost/carlitos/payment.html'>here</a>.<br><br>

    For any concerns or changes, feel free to contact us.<br><br>

    —<br>
    <strong>Carlito’s Private Pool & Venue</strong><br>
    📍 Meycauayan, Bulacan<br>
    📧 carlitospool@email.com
  </div>
";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email sending failed: {$mail->ErrorInfo}");
                }
            }

            header("Location: admin_dashboard.php?page=" . ($_GET['page'] ?? 1) . "&indicator=verified");
            exit();
        }
        if ($action === 'Rejected') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'Rejected' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header("Location: admin_dashboard.php?page=" . ($_GET['page'] ?? 1) . "&indicator=rejected");
            exit();
        }
        if ($action === 'Delete') {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header("Location: admin_dashboard.php?page=" . ($_GET['page'] ?? 1) . "&indicator=deleted");
            exit();
        }
    }

    if (isset($_GET['fb_action'], $_GET['fb_id'])) {
        if ($_GET['fb_action'] === 'Delete') {
            $fb_id = (int)$_GET['fb_id'];
            $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = :id");
            $stmt->execute(['id' => $fb_id]);
            header("Location: admin_dashboard.php?indicator=fb_deleted");
            exit();
        }
    }

    // Bulk delete selected feedbacks via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedbacks'])) {
        $tokens = isset($_POST['fb_ids']) && is_array($_POST['fb_ids']) ? $_POST['fb_ids'] : [];
        $ids = [];
        $composites = [];
        foreach ($tokens as $tok) {
            if (substr($tok, 0, 3) === 'id:') {
                $ids[] = (int)substr($tok, 3);
            } elseif (substr($tok, 0, 4) === 'key:') {
                $b64 = substr($tok, 4);
                $json = base64_decode($b64, true);
                if ($json !== false) {
                    $data = json_decode($json, true);
                    if (is_array($data) && isset($data['name'], $data['feedback'], $data['created_at'])) {
                        $composites[] = $data;
                    }
                }
            }
        }
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id IN ($placeholders)");
            $stmt->execute($ids);
        }
        if (!empty($composites)) {
            $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE name = :name AND feedback = :feedback AND created_at = :created_at LIMIT 1");
            foreach ($composites as $c) {
                $stmt->execute([
                    ':name' => $c['name'],
                    ':feedback' => $c['feedback'],
                    ':created_at' => $c['created_at'],
                ]);
            }
        }
        header("Location: admin_dashboard.php?indicator=fb_deleted");
        exit();
    }

    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $total = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $totalPages = ceil($total / $limit);

    $stmt = $pdo->prepare("SELECT * FROM bookings ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch feedbacks from database
$feedbacks = [];
try {
    $stmt = $pdo->query("SELECT id, name, feedback, created_at FROM feedbacks ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if 'id' column does not exist
    try {
        $stmt = $pdo->query("SELECT name, feedback, created_at FROM feedbacks ORDER BY created_at DESC");
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        // If feedback table doesn't exist, ignore for now
    }
}

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Cache-Control" content="no-store" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta charset="UTF-8" />
  <title>Admin Dashboard - Carlito's</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background: linear-gradient(120deg, #0f2027, #609bae, #4c8ca8);
      color: white;
    }

    header {
      background: rgba(0, 0, 0, 0.7);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px #00f0ff55;
    }

    header h1 {
      color: #00f0ff;
      font-size: 1.8rem;
    }

    .logout {
      background: #f44336;
      color: white;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      text-decoration: none;
    }

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      background: rgba(255, 255, 255, 0.03);
      padding: 2rem;
      border-radius: 16px;
      backdrop-filter: blur(10px);
    }

    h2 {
      color: #00f0ff;
      margin-bottom: 1rem;
    }

    .welcome {
      font-size: 1.2rem;
      color: #a8faff;
      margin-bottom: 1rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      color: black;
      border-radius: 10px;
      overflow: hidden;
      margin-top: 1rem;
    }

    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #00f0ff;
      color: black;
      font-weight: bold;
    }

    tr:hover {
      background-color: #f2f2f2;
    }

    .status {
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: bold;
      font-size: 0.9rem;
    }

    .pending { background: #ff9800; color: white; }
    .verified { background: #4caf50; color: white; }
    .rejected { background: #f44336; color: white; }

    .btn {
      background: #00f0ff;
      border: none;
      padding: 6px 10px;
      color: black;
      cursor: pointer;
      font-weight: bold;
      border-radius: 5px;
      margin-right: 4px;
      text-decoration: none;
    }

    .btn:hover { background: #00c0cc; }
    .screenshot-link { color: #00bcd4; text-decoration: none; font-weight: bold; }
    .screenshot-link:hover { text-decoration: underline; }
    .pagination { margin-top: 1.5rem; text-align: center; }
    .pagination a {
      background: #00f0ff;
      color: black;
      padding: 6px 12px;
      margin: 0 4px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
    }
    .pagination a:hover { background: #00bcd4; }
  </style>
</head>
<body>

<header>
  <h1>Admin Dashboard</h1>
  <a class="logout" href="admin_logout.php">Logout</a>
</header>

<div class="container">
  <div class="welcome">Welcome, <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong>!</div>

  <?php if (isset($_GET['indicator']) && $_GET['indicator'] === 'verified'): ?>
    <div style="background:#4caf50;color:#fff;padding:1rem;border-radius:8px;margin-bottom:1rem;text-align:center;">
      Booking has been <strong>verified</strong>.
    </div>
  <?php elseif (isset($_GET['indicator']) && $_GET['indicator'] === 'rejected'): ?>
    <div style="background:#f44336;color:#fff;padding:1rem;border-radius:8px;margin-bottom:1rem;text-align:center;">
      Booking has been <strong>rejected</strong>.
    </div>
  <?php elseif (isset($_GET['indicator']) && $_GET['indicator'] === 'deleted'): ?>
    <div style="background:#23255a;color:#fff;padding:1rem;border-radius:8px;margin-bottom:1rem;text-align:center;">
      Booking has been <strong>deleted</strong>.
    </div>
  <?php elseif (isset($_GET['indicator']) && $_GET['indicator'] === 'fb_deleted'): ?>
    <div style="background:#23255a;color:#fff;padding:1rem;border-radius:8px;margin-bottom:1rem;text-align:center;">
      Feedback has been <strong>deleted</strong>.
    </div>
  <?php endif; ?>

  <a href="admin_users_list.php" class="btn">Edit Admin Profile</a>

  <h2>Booking Records</h2>

  <?php if (count($bookings) > 0): ?>
    <table>
      <tr>
        <th>Full Name</th>
        <th>Email</th>
        <th>Start</th>
        <th>End</th>
        <th>Guests</th>
        <th>Payment</th>
        <th>Status</th> <!-- Added column -->
        <th>Actions</th>
      </tr>
      <?php foreach ($bookings as $booking): ?>
        <tr>
          <td><?= htmlspecialchars($booking['full_name']) ?></td>
          <td><?= htmlspecialchars($booking['email']) ?></td>
          <td><?= htmlspecialchars($booking['date_start']) ?></td>
          <td><?= htmlspecialchars($booking['date_end']) ?></td>
          <td><?= htmlspecialchars($booking['guests']) ?></td>
          <td>
            <?php if (!empty($booking['payment_screenshot'])): ?>
              <a class="screenshot-link" href="uploads/<?= htmlspecialchars($booking['payment_screenshot']) ?>" target="_blank">View</a>
            <?php else: ?>
              <span style="color:#999;">No Upload</span>
            <?php endif; ?>
          </td>
          <td>
            <?php
              $status = strtolower($booking['status']);
              if ($status === 'verified') {
                echo '<span class="status verified">Verified</span>';
              } elseif ($status === 'rejected') {
                echo '<span class="status rejected">Rejected</span>';
              } else {
                echo '<span class="status pending">Pending</span>';
              }
            ?>
          </td>
          <td>
            <a href="?action=Verified&id=<?= $booking['id'] ?>&page=<?= $page ?>" class="btn">Verify</a>
            <a href="?action=Rejected&id=<?= $booking['id'] ?>&page=<?= $page ?>" class="btn" style="background:#f44336;color:white;">Reject</a>
            <a href="?action=Delete&id=<?= $booking['id'] ?>&page=<?= $page ?>" class="btn" style="background:#23255a;color:white;" onclick="return confirm('Are you sure you want to delete this booking?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

    <div class="pagination">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?page=<?= $p ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>

  <?php else: ?>
    <p>No bookings yet.</p>
  <?php endif; ?>

  <h2>Guest Feedback</h2>
  <?php if (count($feedbacks) > 0): ?>
    <form method="post" action="admin_dashboard.php" onsubmit="return confirm('Are you sure you want to delete the selected feedback?');">
      <table>
        <tr>
          <th><input type="checkbox" id="select-all-fb"></th>
          <th>Name</th>
          <th>Feedback</th>
          <th>Date</th>
        </tr>
        <?php foreach ($feedbacks as $fb): ?>
          <tr>
            <td>
              <?php if (isset($fb['id'])): ?>
                <input type="checkbox" name="fb_ids[]" value="id:<?= (int)$fb['id'] ?>">
              <?php else: ?>
                <input type="checkbox" name="fb_ids[]" value='key:<?= htmlspecialchars(base64_encode(json_encode(array("name"=>$fb["name"],"feedback"=>$fb["feedback"],"created_at"=>$fb["created_at"]))), ENT_QUOTES) ?>'>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($fb['name']) ?></td>
            <td><?= htmlspecialchars($fb['feedback']) ?></td>
            <td><?= date('M d, Y H:i', strtotime($fb['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <button type="submit" name="delete_feedbacks" class="btn" style="background:#23255a;color:white;margin-top:10px;">Delete Selected</button>
    </form>
    <script>
      (function() {
        var selectAll = document.getElementById('select-all-fb');
        if (selectAll) {
          selectAll.addEventListener('change', function() {
            var boxes = document.querySelectorAll('input[name="fb_ids[]"]');
            for (var i = 0; i < boxes.length; i++) {
              boxes[i].checked = selectAll.checked;
            }
          });
        }
      })();
    </script>
  <?php else: ?>
    <p>No feedback submitted yet.</p>
  <?php endif; ?>
</div>

</body>
</html>
