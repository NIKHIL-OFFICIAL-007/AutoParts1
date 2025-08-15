<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Verify admin role
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../unauthorized.php");
    exit;
}

// Fetch statistics
$stats = [];
$queries = [
    'total_products' => "SELECT COUNT(*) FROM products",
    'total_sellers' => "SELECT COUNT(*) FROM users WHERE role = 'seller'",
    'total_customers' => "SELECT COUNT(*) FROM users WHERE role = 'customer'",
    'total_orders' => "SELECT COUNT(*) FROM orders",
    'recent_orders' => "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5",
    'active_sellers' => "SELECT u.id, u.full_name, COUNT(p.id) as product_count 
                         FROM users u LEFT JOIN products p ON u.id = p.seller_id 
                         WHERE u.role = 'seller' GROUP BY u.id ORDER BY product_count DESC LIMIT 5"
];

foreach ($queries as $key => $query) {
    $result = $conn->query($query);
    if ($key === 'recent_orders' || $key === 'active_sellers') {
        $stats[$key] = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $stats[$key] = $result->fetch_row()[0];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - AutoParts</title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1abc9c',
            'primary-dark': '#16a085',
            secondary: '#3498db',
            dark: '#2c3e50',
            light: '#ecf0f1',
            gray: '#95a5a6',
            danger: '#e74c3c',
            success: '#2ecc71',
            warning: '#f39c12'
          },
          fontFamily: {
            inter: ['Inter', 'sans-serif'],
          },
          animation: {
            fadeIn: 'fadeIn 0.5s ease forwards',
            pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite'
          },
          keyframes: {
            fadeIn: {
              '0%': { opacity: '0', transform: 'translateY(20px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' },
            }
          }
        }
      }
    }
  </script>
  <style type="text/tailwindcss">
    @layer utilities {
      .scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
      }
      .scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
      }
      .scrollbar::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
      }
      .scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
      }
      .form-input:focus {
        border-color: #1abc9c;
        background-color: #fff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.15);
      }
    }
  </style>
</head>
<body class="font-inter bg-gray-50 min-h-screen text-gray-800 leading-relaxed">

<div class="admin-container flex w-full max-w-[1800px] mx-auto">
  <!-- Admin Sidebar -->
  <div class="sidebar w-64 bg-dark text-white fixed h-full py-6 transition-all duration-300">
    <div class="sidebar-header px-6 pb-5 border-b border-white/10 mb-5">
      <div class="logo flex items-center gap-2.5 text-2xl font-bold mb-7">
        <i class="fa-solid fa-car text-primary"></i>
        <span>AutoParts</span>
      </div>
      
      <div class="admin-info flex items-center gap-3">
        <div class="admin-avatar w-12 h-12 rounded-full bg-primary flex items-center justify-center text-xl font-semibold">
          <?php echo substr($_SESSION['full_name'], 0, 1); ?>
        </div>
        <div>
          <div class="admin-name font-semibold text-base"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
          <div class="admin-role text-xs text-primary">Administrator</div>
        </div>
      </div>
    </div>
    
    <ul class="nav-menu list-none px-4 h-[calc(100%-180px)] overflow-y-auto">
      <li class="nav-item mb-1">
        <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white no-underline transition-all duration-300 gap-3 bg-white/10">
          <i class="fa-solid fa-gauge w-6 text-center"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="products.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-box w-6 text-center"></i>
          <span>Products</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="sellers.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-users w-6 text-center"></i>
          <span>Sellers</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="customers.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-user-group w-6 text-center"></i>
          <span>Customers</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="orders.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-cart-shopping w-6 text-center"></i>
          <span>Orders</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="categories.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-tags w-6 text-center"></i>
          <span>Categories</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="reports.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-chart-pie w-6 text-center"></i>
          <span>Reports</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="settings.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-gear w-6 text-center"></i>
          <span>Settings</span>
        </a>
      </li>
    </ul>
    
    <div class="sidebar-footer px-4 pt-5 border-t border-white/10">
      <a href="../logout.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
        <i class="fa-solid fa-right-from-bracket w-6 text-center"></i>
        <span>Logout</span>
      </a>
    </div>
  </div>
  
  <!-- Main Content -->
  <div class="main-content ml-64 flex-1 p-8 overflow-y-auto">
    <div class="header flex justify-between items-center mb-8">
      <div class="page-title">
        <h1 class="text-3xl font-bold text-dark">Admin Dashboard</h1>
        <p class="text-gray-500">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
      </div>
      <div class="header-actions flex gap-4">
        <div class="relative">
          <button class="bg-white p-2 rounded-lg shadow-sm hover:bg-gray-50">
            <i class="fa-solid fa-bell text-gray-600"></i>
          </button>
          <span class="absolute top-0 right-0 w-2 h-2 bg-danger rounded-full"></span>
        </div>
        <div class="relative">
          <button class="bg-white p-2 rounded-lg shadow-sm hover:bg-gray-50">
            <i class="fa-solid fa-envelope text-gray-600"></i>
          </button>
          <span class="absolute top-0 right-0 w-2 h-2 bg-primary rounded-full"></span>
        </div>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Products</p>
            <h3 class="text-2xl font-bold text-dark"><?php echo number_format($stats['total_products']); ?></h3>
          </div>
          <div class="p-3 rounded-lg bg-primary/10 text-primary">
            <i class="fa-solid fa-box"></i>
          </div>
        </div>
        <p class="text-xs text-success mt-2">
          <i class="fa-solid fa-arrow-up mr-1"></i> 12% from last month
        </p>
      </div>
      
      <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-secondary transition-transform duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Sellers</p>
            <h3 class="text-2xl font-bold text-dark"><?php echo number_format($stats['total_sellers']); ?></h3>
          </div>
          <div class="p-3 rounded-lg bg-secondary/10 text-secondary">
            <i class="fa-solid fa-users"></i>
          </div>
        </div>
        <p class="text-xs text-success mt-2">
          <i class="fa-solid fa-arrow-up mr-1"></i> 5 new this month
        </p>
      </div>
      
      <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-success transition-transform duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Customers</p>
            <h3 class="text-2xl font-bold text-dark"><?php echo number_format($stats['total_customers']); ?></h3>
          </div>
          <div class="p-3 rounded-lg bg-success/10 text-success">
            <i class="fa-solid fa-user-group"></i>
          </div>
        </div>
        <p class="text-xs text-success mt-2">
          <i class="fa-solid fa-arrow-up mr-1"></i> 8% from last month
        </p>
      </div>
      
      <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-warning transition-transform duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 mb-1">Total Orders</p>
            <h3 class="text-2xl font-bold text-dark"><?php echo number_format($stats['total_orders']); ?></h3>
          </div>
          <div class="p-3 rounded-lg bg-warning/10 text-warning">
            <i class="fa-solid fa-cart-shopping"></i>
          </div>
        </div>
        <p class="text-xs text-danger mt-2">
          <i class="fa-solid fa-arrow-down mr-1"></i> 3% from last month
        </p>
      </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="recent-orders bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-dark">Recent Orders</h3>
          <a href="orders.php" class="text-sm text-primary hover:underline">View All</a>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="text-left text-sm text-gray-500 border-b">
                <th class="pb-3">Order ID</th>
                <th class="pb-3">Customer</th>
                <th class="pb-3">Date</th>
                <th class="pb-3">Amount</th>
                <th class="pb-3">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($stats['recent_orders'] as $order): ?>
              <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-3 text-sm">#<?php echo htmlspecialchars($order['id']); ?></td>
                <td class="py-3 text-sm"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td class="py-3 text-sm"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                <td class="py-3 text-sm">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                <td class="py-3">
                  <?php 
                    $statusClass = 'bg-success/10 text-success';
                    if ($order['status'] === 'Pending') $statusClass = 'bg-warning/10 text-warning';
                    if ($order['status'] === 'Cancelled') $statusClass = 'bg-danger/10 text-danger';
                  ?>
                  <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($order['status']); ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Top Sellers -->
      <div class="top-sellers bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-dark">Top Sellers</h3>
          <a href="sellers.php" class="text-sm text-primary hover:underline">View All</a>
        </div>
        
        <div class="space-y-4">
          <?php foreach ($stats['active_sellers'] as $seller): ?>
          <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                <?php echo substr(htmlspecialchars($seller['full_name']), 0, 1); ?>
              </div>
              <div>
                <h4 class="text-sm font-medium text-dark"><?php echo htmlspecialchars($seller['full_name']); ?></h4>
                <p class="text-xs text-gray-500">Seller ID: <?php echo htmlspecialchars($seller['id']); ?></p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-dark"><?php echo $seller['product_count']; ?> products</p>
              <p class="text-xs text-success">
                <i class="fa-solid fa-arrow-up mr-1"></i> 12%
              </p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <a href="products.php?action=add" class="bg-white p-4 rounded-xl shadow-sm flex flex-col items-center justify-center gap-2 text-center hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center">
          <i class="fa-solid fa-plus"></i>
        </div>
        <span class="text-sm font-medium">Add Product</span>
      </a>
      <a href="sellers.php?action=add" class="bg-white p-4 rounded-xl shadow-sm flex flex-col items-center justify-center gap-2 text-center hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 rounded-full bg-secondary/10 text-secondary flex items-center justify-center">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <span class="text-sm font-medium">Add Seller</span>
      </a>
      <a href="reports.php" class="bg-white p-4 rounded-xl shadow-sm flex flex-col items-center justify-center gap-2 text-center hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 rounded-full bg-success/10 text-success flex items-center justify-center">
          <i class="fa-solid fa-chart-simple"></i>
        </div>
        <span class="text-sm font-medium">Generate Report</span>
      </a>
      <a href="settings.php" class="bg-white p-4 rounded-xl shadow-sm flex flex-col items-center justify-center gap-2 text-center hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 rounded-full bg-warning/10 text-warning flex items-center justify-center">
          <i class="fa-solid fa-sliders"></i>
        </div>
        <span class="text-sm font-medium">System Settings</span>
      </a>
    </div>
  </div>
</div>

<script>
// Animate elements on page load
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card');
    const tables = document.querySelectorAll('table');
    const quickActions = document.querySelectorAll('.quick-actions a');
    
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1}s`;
    });
    
    tables.forEach(table => {
        table.style.opacity = '0';
        table.style.animation = 'fadeIn 0.5s ease forwards 0.4s';
    });
    
    quickActions.forEach((action, index) => {
        action.style.opacity = '0';
        action.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1 + 0.5}s`;
    });
});
</script>

</body>
</html>