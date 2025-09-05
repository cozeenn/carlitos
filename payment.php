<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GCash Payment - Carlito's Pool</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f2027, #2c5364);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      padding: 2rem;
    }
    .payment-container {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(10px);
      padding: 3rem;
      border-radius: 16px;
      text-align: center;
      max-width: 600px;
      width: 100%;
      box-shadow: 0 0 20px #00f0ff55;
    }
    h2 {
      color: #00f0ff;
      margin-bottom: 1rem;
    }
    p {
      font-size: 1rem;
      margin-bottom: 1rem;
    }
    .qr-code {
      margin: 1.5rem 0;
    }
    .back-link {
      color: #00f0ff;
      text-decoration: none;
      display: inline-block;
      margin-top: 2rem;
    }
    .back-link:hover {
      text-decoration: underline;
    }
    form {
      margin-top: 2rem;
    }
    input[type="file"] {
      margin: 0.5rem 0;
    }
    button {
      padding: 0.5rem 1.2rem;
      font-weight: bold;
      background: #00f0ff;
      border: none;
      color: black;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background: #00c0cc;
    }
  </style>

  <script>
    // Redirect after successful upload
    window.onload = function () {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('success') === '1') {
        alert('Payment screenshot uploaded successfully! Redirecting to homepage...');
        window.location.href = 'index.html';
      }
    };
  </script>
</head>
<body>
  <div class="payment-container">
    <h2>GCash Payment</h2>
    <p>To confirm your booking, please scan the QR code below using your GCash app and send the payment.</p>

    <div class="qr-code">
      <img src="qrcode.jpg" alt="GCash QR Code" style="max-width: 250px; border-radius: 10px;">
    </div>

    <p><strong>Amount:</strong> ₱2,000.00 (or your rate)</p>
    <p><strong>Account Name:</strong> Carlito’s Pool</p>
    <p><strong>GCash Number:</strong> 0917-123-4567</p>

    <p>Once paid, upload your payment screenshot below to confirm your booking.</p>

    <form action="upload_payment.php" method="post" enctype="multipart/form-data">
      <input type="file" name="payment_screenshot" accept="image/*" required>
      <input type="hidden" name="booking_id" value="<?= isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '' ?>">
      <button type="submit">Upload Screenshot</button>
    </form>

    <a href="index.html" class="back-link">← Back to Homepage</a>
  </div>
</body>
</html>
