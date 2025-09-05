<?php
$pdo = new PDO("mysql:host=localhost;dbname=carlitos_pool", "root", "");
$pools = $pdo->query("SELECT id, name FROM pools WHERE status = 'available'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reservation Form | Carlito’s Private Pool</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #0f2027, #609bae, #4c8ca8);
      margin: 0;
      padding: 0;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .form-container {
      background: rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(10px);
      padding: 3rem;
      border-radius: 15px;
      box-shadow: 0 0 20px #00f0ff55;
      width: 100%;
      max-width: 500px;
    }

    h2 {
      text-align: center;
      color: #00f0ff;
      font-family: 'Great Vibes', cursive;
      font-size: 2.5rem;
      margin-bottom: 2rem;
    }

    label {
      font-weight: 600;
      margin-top: 1rem;
      display: block;
      color: #ffffffcc;
    }

    input, select {
      width: 100%;
      padding: 0.75rem;
      margin-top: 0.5rem;
      border-radius: 8px;
      border: none;
      background: #fff;
      color: #000;
      font-size: 1rem;
    }

    button {
      margin-top: 2rem;
      width: 100%;
      padding: 0.75rem;
      border: none;
      border-radius: 8px;
      background-color: #00f0ff;
      color: #000;
      font-weight: bold;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background-color: #00c4d6;
    }

    .back-link {
      margin-top: 1.5rem;
      display: block;
      text-align: center;
      color: #00f0ff;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 600px) {
      .form-container {
        padding: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Book Your Stay</h2>
    <form action="booking_submit.php" method="POST">
      <label for="full_name">Full Name</label>
      <input type="text" id="full_name" name="full_name" required>

      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required>

      <label for="pool_id">Choose a Villa or Pool</label>
      <select name="pool_id" id="pool_id" required>
        <option value="">-- Select --</option>
        <?php foreach ($pools as $pool): ?>
          <option value="<?= htmlspecialchars($pool['id']) ?>"><?= htmlspecialchars($pool['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="date_start">Check-In Date</label>
      <input type="date" id="date_start" name="date_start" required>

      <label for="time_start">Check-In Time</label>
      <input type="time" id="time_start" name="time_start" required>

      <label for="date_end">Check-Out Date</label>
      <input type="date" id="date_end" name="date_end" required>

      <label for="time_end">Check-Out Time</label>
      <input type="time" id="time_end" name="time_end" required>

      <label for="guests">Number of Guests</label>
      <input type="number" id="guests" name="guests" min="1" required>

      <button type="submit">Reserve Now</button>
    </form>
    <a href="index.html" class="back-link">← Back to Home</a>
  </div>
</body>
</html>
