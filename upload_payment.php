<?php
$host = "localhost";
$dbname = "carlitos_pool";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_FILES['payment_screenshot']) && isset($_POST['booking_id'])) {
        $bookingId = $_POST['booking_id'];
        $fileName = basename($_FILES['payment_screenshot']['name']);
        $targetDir = "uploads/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["payment_screenshot"]["tmp_name"], $targetFile)) {
            $stmt = $pdo->prepare("UPDATE bookings SET payment_screenshot = :screenshot WHERE id = :id");
            $stmt->execute(['screenshot' => $fileName, 'id' => $bookingId]);

            // ✅ Redirect to homepage after successful upload
            header("Location: index.html");
            exit;
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "Missing file or booking ID.";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
