<?php
$pdo = new PDO("mysql:host=localhost;dbname=carlitos_pool", "root", "");
$stmt = $pdo->query("SELECT date_start, date_end FROM bookings");
$dates = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dates[] = [
        'start' => $row['date_start'],
        'end' => date('Y-m-d', strtotime($row['date_end'] . ' +1 day')), // FullCalendar is exclusive of end date
        'display' => 'background',
        'color' => '#ff4b2b'
    ];
}
header('Content-Type: application/json');
echo json_encode($dates);