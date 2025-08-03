<?php
include '../db.php';
session_start();

// Ensure seller is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller' || !isset($_SESSION['user_id'])) {
    echo "Access denied.";
    exit;
}

$part = null;
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name   = $_POST['product_name'];
    $part_brand     = $_POST['part_brand'];
    $vehicle_brand  = $_POST['vehicle_brand'];
    $model          = $_POST['model'];
    $price          = $_POST['price'];
    $description    = $_POST['description'];
    $seller_name    = $_POST['seller_name'];
    $phone          = $_POST['phone'];
    $state          = $_POST['state'];
    $district       = $_POST['district'];
    $seller_id      = $_SESSION['user_id'];

    // Handle image upload
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $imageName = time() . "_" . basename($_FILES["part_image"]["name"]);
    $targetFile = $targetDir . $imageName;

    if (move_uploaded_file($_FILES["part_image"]["tmp_name"], $targetFile)) {
        $dbImagePath = "uploads/" . $imageName;

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO parts (seller_id, product_name, part_brand, vehicle_brand, model, price, description, image_path, seller_name, phone, state, district)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisssssss", $seller_id, $product_name, $part_brand, $vehicle_brand, $model, $price, $description, $dbImagePath, $seller_name, $phone, $state, $district);
        $stmt->execute();

        // Get inserted part
        $part_id = $stmt->insert_id;
        $result = $conn->query("SELECT * FROM parts WHERE id = $part_id");
        $part = $result->fetch_assoc();
    } else {
        $error = "Failed to upload image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sell Part Result - AutoParts</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .part-container {
      display: flex;
      gap: 30px;
    }
    .part-image img {
      max-width: 300px;
      border-radius: 10px;
    }
    .part-details h2 {
      margin-top: 0;
      color: #333;
    }
    .part-details p {
      font-size: 16px;
      margin: 6px 0;
    }
    .highlight {
      font-weight: bold;
      color: #20c261;
    }
    .error {
      color: red;
      font-weight: bold;
    }
    .back-link {
      margin-top: 20px;
      display: inline-block;
      text-decoration: none;
      color: #007BFF;
    }
  </style>
</head>
<body>

<div class="container">
  <?php if ($part): ?>
    <h2><i class="fa-solid fa-check-circle" style="color:green;"></i> Part Posted Successfully!</h2>
    <div class="part-container">
      <div class="part-image">
        <img src="../<?= htmlspecialchars($part['image_path']) ?>" alt="<?= htmlspecialchars($part['product_name']) ?>">
      </div>
      <div class="part-details">
        <h2><?= htmlspecialchars($part['product_name']) ?></h2>
        <p><span class="highlight">Brand:</span> <?= htmlspecialchars($part['part_brand']) ?></p>
        <p><span class="highlight">Vehicle:</span> <?= htmlspecialchars($part['vehicle_brand']) ?> - <?= htmlspecialchars($part['model']) ?></p>
        <p><span class="highlight">Price:</span> ₹<?= number_format($part['price']) ?></p>
        <p><span class="highlight">Description:</span><br><?= nl2br(htmlspecialchars($part['description'])) ?></p>
        <hr>
        <h3>Seller Info</h3>
        <p><span class="highlight">Name:</span> <?= htmlspecialchars($part['seller_name']) ?></p>
        <p><span class="highlight">Phone:</span> <?= htmlspecialchars($part['phone']) ?></p>
        <p><span class="highlight">Location:</span> <?= htmlspecialchars($part['district']) ?>, <?= htmlspecialchars($part['state']) ?></p>
        <p><i class="fa-solid fa-clock"></i> Posted on: <?= date("d M Y, h:i A", strtotime($part['posted_at'])) ?></p>
      </div>
    </div>
    <a class="back-link" href="seller.php"><i class="fa-solid fa-arrow-left"></i> Post Another Part</a>
  <?php elseif ($error): ?>
    <p class="error"><?= $error ?></p>
    <a class="back-link" href="seller.php"><i class="fa-solid fa-arrow-left"></i> Try Again</a>
  <?php else: ?>
    <p class="error">Invalid access. Please submit the form from <a href="seller.php">Sell Part</a> page.</p>
  <?php endif; ?>
</div>

</body>
</html>
