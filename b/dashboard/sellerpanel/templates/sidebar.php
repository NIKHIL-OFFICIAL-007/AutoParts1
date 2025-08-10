<?php
// Verify session is active and user is seller
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../../welcome/login.php");
    exit;
}
?>

<!-- Seller Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="logo">
      <i class="fa-solid fa-car"></i>
      <span>AutoParts</span>
    </div>
    
    <div class="seller-info">
      <div class="seller-avatar">
        <?php echo isset($_SESSION['full_name']) ? substr($_SESSION['full_name'], 0, 1) : 'S'; ?>
      </div>
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
      <a href="seller.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'seller.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-gauge"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="seller.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'seller.php' ? 'active' : ''; ?>">
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