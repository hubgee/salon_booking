<?php
// Grab values passed via URL
$name    = $_GET['name'] ?? '';
$service = $_GET['service'] ?? '';
$date    = $_GET['date'] ?? '';
$time    = $_GET['time'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking Confirmation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .confirmation-box {
      max-width: 600px;
      margin: 80px auto;
      padding: 40px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      text-align: center;
      animation: fadeInUp 1s ease;
    }
    .confirmation-box h2 {
      color: #b76e79;
      margin-bottom: 20px;
    }
    .confirmation-box p {
      font-size: 1.1rem;
      margin-bottom: 15px;
    }
    .btn-home {
      background-color: #b76e79;
      border: none;
    }
    .btn-home:hover {
      background-color: #a65c67;
      transform: scale(1.05);
      transition: 0.3s;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<div class="confirmation-box">
  <h2>🎉 Booking Confirmed!</h2>
  <p>Thank you, <?php echo htmlspecialchars($name); ?>.</p>
  <p>Your appointment for <strong><?php echo htmlspecialchars($service); ?></strong></p>
  <p>on <strong><?php echo htmlspecialchars($date); ?></strong> at <strong><?php echo htmlspecialchars($time); ?></strong> has been booked.</p>
  <p><strong>Booking Fee: MWK 5,000 (pay at salon)</strong></p>
  <a href="index.html" class="btn btn-home btn-lg mt-3">Go to Home</a>
</div>

</body>
</html>
