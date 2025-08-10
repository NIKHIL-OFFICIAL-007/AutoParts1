<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

// PROPERLY INITIALIZE CART
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_count'] = 0;
}

// Load product data from JSON
$jsonPath = __DIR__ . "/parts.json";
$partsData = [];

if (file_exists($jsonPath)) {
    $jsonContent = file_get_contents($jsonPath);
    $decoded = json_decode($jsonContent, true);
    if (isset($decoded['data']) && is_array($decoded['data'])) {
        $partsData = $decoded['data'];
    }
}

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : null;
$product = null;

// Find the product by ID
if ($productId !== null) {
    foreach ($partsData as $part) {
        if ($part['id'] == $productId) {
            $product = $part;
            break;
        }
    }
}

// If product not found, redirect to buyer page
if (!$product) {
    header("Location: buyer.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- ... (HEAD CONTENT REMAINS THE SAME) ... -->
</head>
<body class="bg-gray-50 font-sans flex flex-col min-h-screen">

<!-- Navbar -->
<!-- ... (NAVBAR REMAINS THE SAME) ... -->

<!-- Main Content -->
<main class="flex-grow container mx-auto px-4 py-8">
  <?php if ($product): ?>
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-6xl mx-auto">
      <!-- ... (BREADCRUMB REMAINS THE SAME) ... -->
      
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- ... (PRODUCT IMAGES REMAINS THE SAME) ... -->
        
        <!-- Product Details -->
        <div class="lg:w-1/2">
          <!-- ... (PRODUCT DETAILS REMAINS THE SAME) ... -->
          
          <div class="flex flex-wrap gap-4">
            <!-- FIXED: Use product ID instead of name -->
            <form method="post" action="add_to_cart.php" class="flex-grow">
              <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
              <button type="submit" class="w-full bg-secondary hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center">
                <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
              </button>
            </form>
            
            <form method="post" action="buy_now.php" class="flex-grow">
              <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
              <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center">
                <i class="fas fa-bolt mr-2"></i>Buy Now
              </button>
            </form>
          </div>
        </div>
      </div>
      
      <!-- ... (REST OF PRODUCT PAGE REMAINS THE SAME) ... -->
    </div>
    
    <!-- Related Products -->
    <div class="mt-12 max-w-6xl mx-auto">
      <h2 class="text-xl font-bold text-gray-800 mb-4">Related Products</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php 
        $relatedCount = 0;
        foreach ($partsData as $part): 
          if ($part['category'] === $product['category'] && $part['id'] !== $product['id'] && $relatedCount < 4):
            $relatedCount++;
        ?>
          <div class="bg-white rounded-xl shadow-md p-4 flex flex-col items-start hover:shadow-lg transition-all duration-300">
            <a href="product.php?id=<?= $part['id'] ?>" class="block w-full">
              <!-- ... (RELATED PRODUCT CONTENT) ... -->
            </a>
            <!-- FIXED: Use product ID instead of name -->
            <form method="post" action="add_to_cart.php" class="w-full mt-auto">
              <input type="hidden" name="product_id" value="<?= $part['id'] ?>">
              <button type="submit" class="w-full bg-secondary hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
              </button>
            </form>
          </div>
        <?php 
          endif;
        endforeach; 
        ?>
      </div>
    </div>
  <?php endif; ?>
</main>

<!-- Mobile Action Bar -->
<div class="sticky-bar md:hidden">
  <div class="flex gap-4">
    <!-- FIXED: Use product ID instead of name -->
    <form method="post" action="add_to_cart.php" class="flex-1">
      <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
      <button type="submit" class="w-full bg-secondary hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center">
        <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
      </button>
    </form>
    
    <form method="post" action="buy_now.php" class="flex-1">
      <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
      <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center">
        <i class="fas fa-bolt mr-2"></i>Buy Now
      </button>
    </form>
  </div>
</div>

<!-- ... (FOOTER AND SCRIPTS REMAIN THE SAME) ... -->
</body>
</html>