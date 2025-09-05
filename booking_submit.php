<?php
$host = "localhost";
$dbname = "carlitos_pool";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get and sanitize input data
    $full_name   = $_POST['full_name']   ?? null;
    $email       = $_POST['email']       ?? null;
    $pool_id     = $_POST['pool_id']     ?? null;
    $date_start  = $_POST['date_start']  ?? null;
    $time_start  = $_POST['time_start']  ?? null;
    $date_end    = $_POST['date_end']    ?? null;
    $time_end    = $_POST['time_end']    ?? null;
    $guests      = $_POST['guests']      ?? null;

    if (!$full_name || !$email || !$pool_id || !$date_start || !$time_start || !$date_end || !$time_end || !$guests) {
        die("Missing form data. Please fill all fields.");
    }

    // Validate pool_id exists
    $stmt = $pdo->prepare("SELECT id FROM pools WHERE id = ?");
    $stmt->execute([$pool_id]);
    if ($stmt->rowCount() === 0) {
        die("Invalid pool selection.");
    }

    // Insert booking
    $sql = "INSERT INTO bookings (full_name, email, pool_id, date_start, time_start, date_end, time_end, guests)
            VALUES (:full_name, :email, :pool_id, :date_start, :time_start, :date_end, :time_end, :guests)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name'   => $full_name,
        ':email'       => $email,
        ':pool_id'     => $pool_id,
        ':date_start'  => $date_start,
        ':time_start'  => $time_start,
        ':date_end'    => $date_end,
        ':time_end'    => $time_end,
        ':guests'      => $guests
    ]);

    // Get the last inserted booking ID
    $booking_id = $pdo->lastInsertId();

    // Redirect to payment with booking ID
    header("Location: payment.php?id=" . $booking_id);
    exit;

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
