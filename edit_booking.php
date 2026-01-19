<?php
session_start();
include 'db.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM appointments WHERE id=$id");
$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $time = $_POST['time'];

    $stmt = $conn->prepare("UPDATE appointments SET date=?, time=? WHERE id=?");
    $stmt->bind_param("ssi", $date, $time, $id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Appointment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h2>Edit Appointment</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Date</label>
      <input type="date" name="date" class="form-control" value="<?= $row['date'] ?>" required>
    </div>
    <div class="mb-3">
      <label>Time</label>
      <input type="time" name="time" class="form-control" value="<?= $row['time'] ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
</div>
</body>
</html>
