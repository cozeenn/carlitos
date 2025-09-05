<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=carlitos_pool", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$booking_id = $_SESSION['last_booking_id'] ?? null;

if ($_FILES['proof']['error'] === UPLOAD_ERR_OK && $booking_id) {
    $filename = basename($_FILES['proof']['name']);
    $target = "uploads/" . time() . "_" . $filename;
    move_uploaded_file($_FILES['proof']['tmp_name'], $target);

    $stmt = $pdo->prepare("UPDATE bookings SET proof_path = ?, status = 'Pending' WHERE id = ?");
    $stmt->execute([$target, $booking_id]);

    echo "<h3>Thank you! Your payment is under verification.</h3><a href='index.html'>Back to homepage</a>";
} else {
    echo "Upload failed or no booking found.";
}
?>
