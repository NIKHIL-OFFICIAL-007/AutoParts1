<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; text-align: center; padding: 50px; }
    .box { background: #fff; padding: 40px; border-radius: 10px; display: inline-block; box-shadow: 0 0 10px #aaa; }
    a.logout { color: white; background: #3498db; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    a.logout:hover { background: #2980b9; }
  </style>
</head>
<body>
  <div class="box">
    <h1>Welcome, Admin 🛠️</h1>
    <p>Hello <?= htmlspecialchars($_SESSION['full_name']) ?>! You are logged in as <strong>Admin</strong>.</p>
    <a class="logout" href="logout.php">Logout</a>
  </div>
</body>
</html>
