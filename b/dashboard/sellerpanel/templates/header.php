<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default page title if not provided
if (!isset($pageTitle)) {
    $pageTitle = "AutoParts Seller Portal";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Main CSS -->
<link rel="stylesheet" href="assets/css/styles.css" />


</head>
<body>