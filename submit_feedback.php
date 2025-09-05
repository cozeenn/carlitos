<?php
$pdo = new PDO("mysql:host=localhost;dbname=carlitos_pool", "root", "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $feedback = $_POST['feedback'];

    $stmt = $pdo->prepare("INSERT INTO feedbacks (name, feedback) VALUES (?, ?)");
    $stmt->execute([$name, $feedback]);

    header("Location: index.html");
    exit;
}
?>
