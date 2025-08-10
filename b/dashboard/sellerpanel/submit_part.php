<?php
include __DIR__ . '/includes/db.php';
session_start();

// Ensure seller is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$part = null;
$error = "";

// Define upload error messages
$uploadErrors = [
    UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit (upload_max_filesize)',
    UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit (MAX_FILE_SIZE)',
    UPLOAD_ERR_PARTIAL => 'Partial upload - file only partially received',
    UPLOAD_ERR_NO_FILE => 'No file selected - please choose an image',
    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder - contact administrator',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk - check permissions',
    UPLOAD_ERR_EXTENSION => 'File upload blocked by extension - invalid file type',
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name   = $_POST['product_name'] ?? '';
    $part_brand     = $_POST['part_brand'] ?? '';
    $vehicle_brand  = $_POST['vehicle_brand'] ?? '';
    $model          = $_POST['model'] ?? '';
    $price          = $_POST['price'] ?? '';
    $description    = $_POST['description'] ?? '';
    $seller_name    = $_POST['seller_name'] ?? '';
    $phone          = $_POST['phone'] ?? '';
    $state          = $_POST['state'] ?? '';
    $district       = $_POST['district'] ?? '';
    $seller_id      = $_SESSION['user_id'];

    // Validate required fields
    $requiredFields = [
        'Product Name' => $product_name,
        'Price' => $price,
        'Description' => $description,
        'Seller Name' => $seller_name,
        'Phone' => $phone,
        'State' => $state,
        'District' => $district
    ];
    
    $missingFields = [];
    foreach ($requiredFields as $field => $value) {
        if (empty(trim($value))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $error = "Required fields missing: " . implode(', ', $missingFields);
    }
    // Validate phone number format
    elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Invalid phone number format. Please enter 10 digits.";
    }
    // Validate price
    elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number.";
    }
    // Handle file upload
    $uploadedFiles = [];
    $uploadErrors = [];
    $dbImagePaths = [];

    if (isset($_FILES['part_image']) && is_array($_FILES['part_image']['name'])) {
        $fileCount = count($_FILES['part_image']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['part_image']['error'][$i] === UPLOAD_ERR_OK) {
                $targetDir = "../uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $imageName = time() . "_" . uniqid() . "_" . basename($_FILES["part_image"]["name"][$i]);
                $targetFile = $targetDir . $imageName;
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                
                // Validate image file
                $validExtensions = ["jpg", "jpeg", "png", "gif", "webp"];
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                
                // Check file size
                if ($_FILES["part_image"]["size"][$i] > $maxFileSize) {
                    $uploadErrors[] = "File '".$_FILES["part_image"]["name"][$i]."' is too large. Maximum size is 5MB.";
                    continue;
                }
                // Check file format
                elseif (!in_array($imageFileType, $validExtensions)) {
                    $uploadErrors[] = "Only JPG, JPEG, PNG & GIF files are allowed for '".$_FILES["part_image"]["name"][$i]."'.";
                    continue;
                }
                // Move uploaded file
                elseif (move_uploaded_file($_FILES["part_image"]["tmp_name"][$i], $targetFile)) {
                    $dbImagePaths[] = "uploads/" . $imageName;
                    $uploadedFiles[] = $imageName;
                } else {
                    $uploadErrors[] = "Failed to save image '".$_FILES["part_image"]["name"][$i]."'. Please try again.";
                }
            } elseif ($_FILES['part_image']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $errorCode = $_FILES['part_image']['error'][$i];
                $uploadErrors[] = "Image upload error for '".$_FILES["part_image"]["name"][$i]."': " . 
                                ($uploadErrors[$errorCode] ?? "Unknown error (Code: $errorCode)");
            }
        }
    } else {
        $error = "Please select at least one image file";
    }

    if (empty($dbImagePaths) && empty($error)) {
        $error = "No valid images were uploaded. " . implode(" ", $uploadErrors);
    } elseif (!empty($uploadErrors)) {
        // You might want to show these as warnings rather than errors
        // since some files might have uploaded successfully
        $error = implode(" ", $uploadErrors);
    }

    if (empty($error)) {
        // Convert array of image paths to a string (or JSON if you prefer)
        $imagePathsString = implode(',', $dbImagePaths);

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO parts (seller_id, product_name, part_brand, vehicle_brand, model, price, description, image_path, seller_name, phone, state, district)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisssssss", $seller_id, $product_name, $part_brand, $vehicle_brand, $model, $price, $description, $imagePathsString, $seller_name, $phone, $state, $district);
        
        if ($stmt->execute()) {
            // Get inserted part
            $part_id = $stmt->insert_id;
            $result = $conn->query("SELECT * FROM parts WHERE id = $part_id");
            $part = $result->fetch_assoc();
        } else {
            $error = "Database error: " . $stmt->error;
            
            // Delete any uploaded files if DB insert failed
            foreach ($uploadedFiles as $file) {
                if (file_exists($targetDir . $file)) {
                    unlink($targetDir . $file);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submission Result - AutoParts Seller Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    :root {
      --primary: #1abc9c;
      --primary-dark: #16a085;
      --secondary: #3498db;
      --dark: #2c3e50;
      --light: #ecf0f1;
      --gray: #95a5a6;
      --danger: #e74c3c;
      --success: #2ecc71;
      --warning: #f39c12;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4efe9 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #333;
      padding: 20px;
    }

    .seller-container {
      display: flex;
      width: 100%;
      max-width: 1200px;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* Sidebar */
    .sidebar {
      width: 260px;
      background: var(--dark);
      color: white;
      padding: 25px 0;
      transition: all 0.3s ease;
    }

    .sidebar-header {
      padding: 0 25px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 20px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 30px;
    }

    .logo i {
      color: var(--primary);
    }

    .seller-info {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .seller-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: 600;
    }

    .seller-name {
      font-weight: 600;
      font-size: 16px;
      margin-bottom: 4px;
    }

    .seller-status {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      color: var(--primary);
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--primary);
    }

    .nav-menu {
      list-style: none;
      padding: 0 15px;
    }

    .nav-item {
      margin-bottom: 5px;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 12px 15px;
      border-radius: 8px;
      color: rgba(255,255,255,0.7);
      text-decoration: none;
      transition: all 0.3s ease;
      gap: 12px;
    }

    .nav-link:hover, .nav-link.active {
      background: rgba(255,255,255,0.1);
      color: white;
    }

    .nav-link i {
      width: 24px;
      text-align: center;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      background: white;
      padding: 40px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .page-title {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 24px;
      font-weight: 700;
      color: var(--dark);
    }

    .page-title i {
      color: var(--primary);
    }

    .result-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 30px;
      max-width: 800px;
      margin: 0 auto;
      flex: 1;
      justify-content: center;
    }

    .success-icon {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: rgba(46, 204, 113, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 30px;
    }

    .success-icon i {
      font-size: 50px;
      color: var(--success);
    }

    .success-title {
      font-size: 32px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 15px;
    }

    .success-subtitle {
      font-size: 18px;
      color: var(--gray);
      margin-bottom: 40px;
      max-width: 600px;
    }

    .part-card {
      display: flex;
      width: 100%;
      background: white;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      margin-bottom: 40px;
    }

    .part-image {
      width: 300px;
      background: #f9f9f9;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .part-image img {
      max-width: 100%;
      max-height: 220px;
      border-radius: 10px;
      object-fit: contain;
    }

    .part-details {
      flex: 1;
      padding: 30px;
      text-align: left;
    }

    .part-name {
      font-size: 24px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 15px;
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      margin-bottom: 20px;
    }

    .detail-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .detail-icon {
      color: var(--primary);
      font-size: 18px;
      margin-top: 3px;
    }

    .detail-label {
      font-weight: 600;
      color: var(--dark);
    }

    .detail-value {
      color: var(--gray);
    }

    .description {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid rgba(0,0,0,0.08);
    }

    .seller-info-section {
      background: rgba(26, 188, 156, 0.05);
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
    }

    .seller-info-title {
      font-size: 18px;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .seller-info-title i {
      color: var(--primary);
    }

    .seller-details {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }

    .btn-container {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .btn {
      padding: 14px 28px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-decoration: none;
    }

    .btn-primary {
      background: linear-gradient(120deg, var(--primary), var(--secondary));
      color: white;
      border: none;
      box-shadow: 0 4px 15px rgba(26, 188, 156, 0.3);
    }

    .btn-outline {
      background: transparent;
      border: 2px solid var(--primary);
      color: var(--primary);
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(26, 188, 156, 0.4);
    }

    .btn:active {
      transform: translateY(0);
    }

    .error-container {
      background: rgba(231, 76, 60, 0.1);
      border-radius: 12px;
      padding: 30px;
      text-align: center;
    }

    .error-icon {
      font-size: 50px;
      color: var(--danger);
      margin-bottom: 20px;
    }

    .error-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 15px;
    }

    .error-message {
      font-size: 18px;
      color: var(--danger);
      margin-bottom: 30px;
      max-width: 600px;
      line-height: 1.6;
    }

    .invalid-container {
      background: rgba(243, 156, 18, 0.1);
      border-radius: 12px;
      padding: 30px;
      text-align: center;
    }

    .invalid-icon {
      font-size: 50px;
      color: var(--warning);
      margin-bottom: 20px;
    }

    .invalid-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 15px;
    }

    .invalid-message {
      font-size: 18px;
      color: var(--gray);
      margin-bottom: 30px;
    }

    .upload-tips {
      background: rgba(52, 152, 219, 0.1);
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
      text-align: left;
    }

    .upload-tips h4 {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--secondary);
      margin-bottom: 15px;
    }

    .upload-tips ul {
      padding-left: 20px;
    }

    .upload-tips li {
      margin-bottom: 8px;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .seller-container {
        flex-direction: column;
      }
      
      .sidebar {
        width: 100%;
        padding: 15px;
      }
      
      .nav-menu {
        display: flex;
        overflow-x: auto;
        padding-bottom: 10px;
      }
      
      .nav-item {
        flex: 0 0 auto;
        margin-bottom: 0;
        margin-right: 10px;
      }

      .part-card {
        flex-direction: column;
      }

      .part-image {
        width: 100%;
        padding: 30px;
      }
    }

    @media (max-width: 768px) {
      .detail-grid, .seller-details {
        grid-template-columns: 1fr;
      }

      .btn-container {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<div class="seller-container">
  <!-- Seller Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <i class="fa-solid fa-car"></i>
        <span>AutoParts</span>
      </div>
      
      <div class="seller-info">
        <div class="seller-avatar"><?php echo isset($_SESSION['full_name']) ? substr($_SESSION['full_name'], 0, 1) : 'S'; ?></div>
        <div>
          <div class="seller-name"><?php echo $_SESSION['full_name'] ?? 'Seller'; ?></div>
          <div class="seller-status">
            <div class="status-dot"></div>
            <span>Online</span>
          </div>
        </div>
      </div>
    </div>
    
    <ul class="nav-menu">
      <li class="nav-item">
        <a href="seller.php" class="nav-link">
          <i class="fa-solid fa-gauge"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="seller.php" class="nav-link active">
          <i class="fa-solid fa-plus"></i>
          <span>Sell Parts</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fa-solid fa-box"></i>
          <span>My Products</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fa-solid fa-chart-line"></i>
          <span>Analytics</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fa-solid fa-money-bill"></i>
          <span>Earnings</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fa-solid fa-gear"></i>
          <span>Settings</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="logout.php" class="nav-link">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>
  
  <!-- Main Content -->
  <div class="main-content">
    <div class="header">
      <div class="page-title">
        <i class="fa-solid fa-circle-check"></i>
        <h1>Submission Result</h1>
      </div>
    </div>
    
    <div class="result-container">
      <?php if ($part): ?>
        <div class="success-icon">
          <i class="fa-solid fa-check"></i>
        </div>
        
        <h2 class="success-title">Part Posted Successfully!</h2>
        <p class="success-subtitle">Your product is now live on AutoParts marketplace and visible to buyers</p>
        
        <div class="part-card">
          <div class="part-image">
            <img src="../<?= htmlspecialchars($part['image_path']) ?>" alt="<?= htmlspecialchars($part['product_name']) ?>">
          </div>
          <div class="part-details">
            <h3 class="part-name"><?= htmlspecialchars($part['product_name']) ?></h3>
            
            <div class="detail-grid">
              <div class="detail-item">
                <i class="fa-solid fa-trademark detail-icon"></i>
                <div>
                  <div class="detail-label">Brand</div>
                  <div class="detail-value"><?= htmlspecialchars($part['part_brand']) ?></div>
                </div>
              </div>
              
              <div class="detail-item">
                <i class="fa-solid fa-car detail-icon"></i>
                <div>
                  <div class="detail-label">Vehicle</div>
                  <div class="detail-value"><?= htmlspecialchars($part['vehicle_brand']) ?> - <?= htmlspecialchars($part['model']) ?></div>
                </div>
              </div>
              
              <div class="detail-item">
                <i class="fa-solid fa-indian-rupee-sign detail-icon"></i>
                <div>
                  <div class="detail-label">Price</div>
                  <div class="detail-value">₹<?= number_format($part['price']) ?></div>
                </div>
              </div>
              
              <div class="detail-item">
                <i class="fa-solid fa-calendar detail-icon"></i>
                <div>
                  <div class="detail-label">Posted On</div>
                  <div class="detail-value"><?= date("d M Y, h:i A", strtotime($part['posted_at'])) ?></div>
                </div>
              </div>
            </div>
            
            <div class="description">
              <div class="detail-item">
                <i class="fa-solid fa-file-lines detail-icon"></i>
                <div>
                  <div class="detail-label">Description</div>
                  <div class="detail-value"><?= nl2br(htmlspecialchars($part['description'])) ?></div>
                </div>
              </div>
            </div>
            
            <div class="seller-info-section">
              <h4 class="seller-info-title">
                <i class="fa-solid fa-user"></i>
                Seller Information
              </h4>
              
              <div class="seller-details">
                <div class="detail-item">
                  <i class="fa-solid fa-user detail-icon"></i>
                  <div>
                    <div class="detail-label">Name</div>
                    <div class="detail-value"><?= htmlspecialchars($part['seller_name']) ?></div>
                  </div>
                </div>
                
                <div class="detail-item">
                  <i class="fa-solid fa-phone detail-icon"></i>
                  <div>
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><?= htmlspecialchars($part['phone']) ?></div>
                  </div>
                </div>
                
                <div class="detail-item">
                  <i class="fa-solid fa-location-dot detail-icon"></i>
                  <div>
                    <div class="detail-label">Location</div>
                    <div class="detail-value"><?= htmlspecialchars($part['district']) ?>, <?= htmlspecialchars($part['state']) ?></div>
                  </div>
                </div>
                
                <div class="detail-item">
                  <i class="fa-solid fa-id-card detail-icon"></i>
                  <div>
                    <div class="detail-label">Seller ID</div>
                    <div class="detail-value"><?= htmlspecialchars($part['seller_id']) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="btn-container">
          <a href="seller.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Post Another Part
          </a>
          <a href="#" class="btn btn-outline">
            <i class="fa-solid fa-box"></i>
            View Your Products
          </a>
        </div>
        
      <?php elseif ($error): ?>
        <div class="error-container">
          <div class="error-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          <h2 class="error-title">Submission Failed</h2>
          <p class="error-message"><?= $error ?></p>
          
          <div class="upload-tips">
            <h4><i class="fa-solid fa-lightbulb"></i> Upload Tips:</h4>
            <ul>
              <li>Make sure you selected an image file before submitting</li>
              <li>Supported formats: JPG, PNG, GIF (max 5MB)</li>
              <li>Check that your image is not corrupted</li>
              <li>Try a different image if problems persist</li>
            </ul>
          </div>
          
          <a href="seller.php" class="btn btn-primary">
            <i class="fa-solid fa-rotate-left"></i>
            Try Again
          </a>
        </div>
        
      <?php else: ?>
        <div class="invalid-container">
          <div class="invalid-icon">
            <i class="fa-solid fa-circle-exclamation"></i>
          </div>
          <h2 class="invalid-title">Invalid Access</h2>
          <p class="invalid-message">Please submit the form from the Sell Part page</p>
          <a href="seller.php" class="btn btn-primary">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            Go to Sell Part Page
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>