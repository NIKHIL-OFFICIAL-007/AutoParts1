<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get all categories for dropdown
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle search/filter
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$vehicle_type = $_GET['vehicle_type'] ?? '';
$availability = $_GET['availability'] ?? '';

// Build query
$query = "SELECT p.*, c.name as category_name, u.full_name as seller_name 
          FROM products p
          JOIN categories c ON p.category_id = c.id
          LEFT JOIN users u ON p.seller_id = u.id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_id)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if (!empty($vehicle_type)) {
    $query .= " AND p.vehicle_type = ?";
    $params[] = $vehicle_type;
}

if (!empty($availability)) {
    $query .= " AND p.availability = ?";
    $params[] = $availability;
}

$query .= " ORDER BY p.name";

// Execute query
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Auto Parts</h1>
        <a href="products/add_product.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            <i class="fas fa-plus mr-2"></i> Add part
        </a>
    </div>
    

    <!-- Search and Filter Form -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" placeholder="Part name, brand..." 
                       value="<?= htmlspecialchars($search) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
                <select name="vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All</option>
                    <option value="Car" <?= $vehicle_type == 'Car' ? 'selected' : '' ?>>Car</option>
                    <option value="Bike" <?= $vehicle_type == 'Bike' ? 'selected' : '' ?>>Bike</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Availability</label>
                <select name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All</option>
                    <option value="In Stock" <?= $availability == 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                    <option value="Out of Stock" <?= $availability == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Filter
                </button>
                <a href="products/manage_products.php" class="ml-2 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PART</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BRAND</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CATEGORY</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VEHICLE TYPE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PRICE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AVAILABILITY</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No auto parts found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($product['brand']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($product['category_name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($product['vehicle_type']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">$<?= number_format($product['price'], 2) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= $product['availability'] === 'In Stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $product['availability'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="edit_product.php?id=<?= $product['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="delete_product.php?id=<?= $product['id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>