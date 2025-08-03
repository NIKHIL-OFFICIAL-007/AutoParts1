<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart - AutoParts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1e40af',
            secondary: '#f59e0b',
          }
        }
      }
    }
  </script>
  <style>
    .empty-cart-icon {
      font-size: 80px;
      color: #ddd;
      margin-bottom: 20px;
    }
    /* Footer Styles */
    .footer {
      background-color: #172337;
      color: white;
      padding: 30px 0;
    }
    .footer-section {
      display: flex;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    .footer-column {
      flex: 1;
      padding: 0 15px;
    }
    .footer-title {
      font-size: 12px;
      color: #878787;
      margin-bottom: 10px;
      text-transform: uppercase;
    }
    .footer-links {
      list-style: none;
      padding: 0;
    }
    .footer-links li {
      margin-bottom: 8px;
    }
    .footer-links a {
      color: white;
      text-decoration: none;
      font-size: 14px;
    }
    .footer-links a:hover {
      text-decoration: underline;
    }
    .copyright {
      text-align: center;
      padding-top: 20px;
      border-top: 1px solid #454d5e;
      margin-top: 20px;
      font-size: 12px;
    }
    .help-center {
      text-align: center;
      margin-top: 30px;
      font-size: 14px;
    }
    .help-center a {
      color: white;
      text-decoration: none;
    }
    /* Navbar Dropdown */
    .dropdown:hover .dropdown-menu {
      display: block;
    }
  </style>
</head>
<body class="bg-gray-50 font-sans flex flex-col min-h-screen">

<!-- Navbar -->
<nav class="bg-primary text-white px-4 py-3 flex justify-between items-center sticky top-0 z-50 shadow-lg">
  <div class="flex items-center space-x-4">
    <button id="sidebarToggle" class="md:hidden text-white">
      <i class="fas fa-bars text-xl"></i>
    </button>
    <div class="flex items-center">
      <img src="../images/logo.png" alt="AutoParts Logo" class="h-14 md:h-24 w-auto">
    </div>
  </div>
  
  <div class="relative w-1/2 hidden md:block">
    <form id="searchForm" onsubmit="handleSearch(event)" class="flex">
      <input type="text" id="searchInput" placeholder="Search for products, brands and more"
        class="w-full p-2 rounded-l border-none outline-none text-gray-800"
        onfocus="showTrending()" onblur="hideTrending()" autocomplete="off">
      <button type="submit" class="bg-secondary px-4 rounded-r text-white">
        <i class="fas fa-search"></i>
      </button>
    </form>
    <div id="trendingBox" class="absolute hidden bg-white shadow-md rounded w-full mt-1 z-50">
      <div class="px-4 py-2 font-semibold border-b bg-gray-100">🔥 Trending Searches</div>
      <a href="battery.php" class="block px-4 py-2 hover:bg-gray-100">Battery</a>
      <a href="brakepads.php" class="block px-4 py-2 hover:bg-gray-100">Brake Pads</a>
      <a href="airfilter.php" class="block px-4 py-2 hover:bg-gray-100">Air Filter</a>
      <a href="headlight.php" class="block px-4 py-2 hover:bg-gray-100">Headlight</a>
    </div>
  </div>
  
  <div class="flex items-center space-x-6">
    <!-- User Dropdown -->
    <div class="relative group">
      <div class="flex items-center cursor-pointer">
        <i class="fas fa-user-circle text-xl mr-2"></i>
        <span class="text-sm font-medium">
          <?php
          if (isset($_SESSION['full_name'])) {
            $nameParts = explode(' ', $_SESSION['full_name']);
            echo htmlspecialchars($nameParts[0]);
          } else {
            echo 'Buyer';
          }
          ?>
        </span>
        <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200 group-hover:rotate-180"></i>
      </div>
      
      <div class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-50 hidden group-hover:block">
        <div class="py-1">
          <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            <i class="fas fa-user mr-2 text-gray-500"></i> My Profile
          </a>
          <a href="orders.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            <i class="fas fa-box-open mr-2 text-orange-500"></i> Orders
          </a>
          <a href="wishlist.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            <i class="fas fa-heart mr-2 text-red-500"></i> Wishlist
          </a>
          <div class="border-t border-gray-200"></div>
          <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </div>
    </div>
    
    <!-- Cart -->
    <a href="cart.php" class="relative flex items-center text-white hover:text-gray-200 space-x-2">
      <i class="fas fa-shopping-cart text-xl"></i>
      <span class="hidden md:inline">Cart</span>
      <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
        <span class="absolute -top-2 left-4 md:left-8 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center">
          <?= htmlspecialchars($_SESSION['cart_count']) ?>
        </span>
      <?php endif; ?>
    </a>
    
    <!-- Become a Seller -->
    <a href="#" class="flex items-center text-white hover:text-gray-200 space-x-2">
      <i class="fas fa-store text-xl"></i>
      <span class="hidden md:inline">Become a Seller</span>
    </a>
  </div>
</nav>

<!-- Mobile Search -->
<div class="md:hidden bg-white p-3 shadow">
  <form class="flex">
    <input type="text" placeholder="Search for products, brands and more" class="w-full p-2 rounded-l border border-gray-300 outline-none">
    <button class="bg-secondary px-4 rounded-r text-white">
      <i class="fas fa-search"></i>
    </button>
  </form>
</div>

<!-- Main Content - Empty Cart Design -->
<main class="flex-grow container mx-auto px-4 py-8">
  <div class="bg-white rounded-lg shadow-sm p-8 text-center max-w-2xl mx-auto">
    <div class="empty-cart-icon">
      <i class="fas fa-shopping-cart"></i>
    </div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Your cart is empty!</h1>
    <p class="text-gray-600 mb-6">Add items to it now.</p>
    <a href="buyer.php" class="inline-block bg-secondary hover:bg-yellow-600 text-white font-medium py-3 px-8 rounded-md transition-colors">
      Shop now
    </a>
  </div>
</main>

<!-- Footer -->
<footer class="footer mt-auto">
  <div class="footer-section">
    <div class="footer-column">
      <h3 class="footer-title">Policies</h3>
      <ul class="footer-links">
        <li><a href="#">Returns Policy</a></li>
        <li><a href="#">Terms of use</a></li>
        <li><a href="#">Security</a></li>
        <li><a href="#">Privacy</a></li>
      </ul>
    </div>
    
    <div class="footer-column">
      <h3 class="footer-title">About</h3>
      <ul class="footer-links">
        <li><a href="#">Contact Us</a></li>
        <li><a href="#">About Us</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#">Stories</a></li>
      </ul>
    </div>
    
    <div class="footer-column">
      <h3 class="footer-title">Help</h3>
      <ul class="footer-links">
        <li><a href="#">Payments</a></li>
        <li><a href="#">Shipping</a></li>
        <li><a href="#">Cancellation</a></li>
        <li><a href="#">FAQ</a></li>
      </ul>
    </div>
  </div>
  
  <div class="copyright">
    <p>2007-2025 AutoParts.com</p>
  </div>
  
  <div class="help-center">
    <p>Need help? <a href="#">Visit the Help Center</a> or <a href="#">Contact Us</a></p>
  </div>
</footer>

<script>
  // Search functionality
  function showTrending() {
    document.getElementById("trendingBox").classList.remove("hidden");
  }

  function hideTrending() {
    setTimeout(() => {
      document.getElementById("trendingBox").classList.add("hidden");
    }, 200);
  }

  function handleSearch(e) {
    e.preventDefault();
    const query = document.getElementById("searchInput").value.trim().toLowerCase();
    if (query.includes("battery")) {
      window.location.href = "battery.php";
    } else if (query.includes("brake")) {
      window.location.href = "brakepads.php";
    } else if (query.includes("air")) {
      window.location.href = "airfilter.php";
    } else if (query.includes("headlight") || query.includes("light")) {
      window.location.href = "headlight.php";
    } else {
      alert("No results found. Try 'Battery', 'Brake Pads', 'Air Filter', or 'Headlight'.");
    }
  }

  // Mobile sidebar toggle
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  if (sidebarToggle && sidebar && sidebarOverlay) {
    sidebarToggle.addEventListener('click', (e) => {
      e.preventDefault();
      sidebar.classList.toggle('open');
      sidebarOverlay.classList.toggle('hidden');
      document.body.classList.toggle('overflow-hidden');
    });

    sidebarOverlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      sidebarOverlay.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    });
  }
</script>
</body>
</html>