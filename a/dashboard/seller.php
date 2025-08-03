<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sell a Part - AutoParts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Google Fonts: Sleek, Corporate -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Inter', 'Roboto', sans-serif;
      background: linear-gradient(to right, #f2f6fc, #d9f0e4);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .sell-container {
      background: #ffffff;
      width: 100%;
      max-width: 700px;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
      transition: box-shadow 0.3s ease;
    }

    .sell-container:hover {
      box-shadow: 0 14px 32px rgba(0, 0, 0, 0.16);
    }

    h2 {
      text-align: center;
      font-weight: 700;
      font-size: 30px;
      color: #1e2a38;
      margin-bottom: 30px;
    }

    .sell-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .sell-form input,
    .sell-form textarea,
    .sell-form select {
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      padding: 12px 14px;
      border: 1px solid #cbd5e0;
      border-radius: 8px;
      background-color: #f9f9f9;
      transition: all 0.25s ease;
    }

    .sell-form input:focus,
    .sell-form textarea:focus,
    .sell-form select:focus {
      border-color: #1abc9c;
      background-color: #fff;
      outline: none;
      box-shadow: 0 0 0 2px rgba(26, 188, 156, 0.2);
    }

    .sell-form textarea {
      resize: vertical;
    }

    .sell-form input[type="file"] {
      padding: 10px;
      background: #fff;
      border: 1px dashed #bbb;
    }

    .sell-form button {
      background-color: #1abc9c;
      color: white;
      border: none;
      padding: 14px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .sell-form button:hover {
      background-color: #16a085;
      transform: translateY(-2px);
    }

    .sell-form button:active {
      transform: scale(0.98);
    }

    select option {
      font-family: 'Inter', sans-serif;
    }

    ::placeholder {
      color: #a0aec0;
    }

    @media (max-width: 768px) {
      .sell-container {
        padding: 25px;
      }

      h2 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>

<div class="sell-container">
  <h2><i class="fa-solid fa-car-wrench"></i> Sell a Vehicle Part</h2>
  <form action="submit_part.php" method="POST" enctype="multipart/form-data" class="sell-form">

    <input type="text" name="product_name" placeholder="Product Name" required>

    <select name="part_brand" required>
      <option value="">- Select Product Brand -</option>
      <option value="Bosch">Bosch</option>
      <option value="Amaron">Amaron</option>
      <option value="Philips">Philips</option>
      <option value="Mann">Mann</option>
    </select>

    <select name="vehicle_brand" required>
      <option value="">- Select Vehicle Brand -</option>
      <option value="Maruti">Maruti</option>
      <option value="Honda">Honda</option>
      <option value="Hyundai">Hyundai</option>
      <option value="Kia">Kia</option>
      <option value="Mahindra">Mahindra</option>
      <option value="Tata">Tata</option>
    </select>

    <input type="text" name="model" placeholder="Vehicle Model" required>
    <input type="number" name="price" placeholder="Price (INR)" min="1" required>
    <textarea name="description" rows="4" placeholder="Description of the part" required></textarea>

    <input type="file" name="part_image" accept="image/*" required>

    <input type="text" name="seller_name" placeholder="Seller Name" required>
    <input type="tel" name="phone" placeholder="Phone Number (10 digits)" pattern="[0-9]{10}" required>

    <select name="state" required>
      <option value="">- Select State -</option>
      <option value="Tamil Nadu">Tamil Nadu</option>
      <option value="Kerala">Kerala</option>
      <option value="Karnataka">Karnataka</option>
      <option value="Maharashtra">Maharashtra</option>
      <option value="Delhi">Delhi</option>
    </select>

    <input type="text" name="district" placeholder="District" required>

    <button type="submit">Post Part</button>
  </form>
</div>

</body>
</html>
