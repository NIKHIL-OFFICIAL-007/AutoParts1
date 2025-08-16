<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Enable PDO error reporting
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Pagination configuration
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Initialize filter variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$vehicleTypeFilter = isset($_GET['vehicle_type']) ? $_GET['vehicle_type'] : '';
$availabilityFilter = isset($_GET['availability']) ? $_GET['availability'] : '';

// Base query with explicit column selection to avoid conflicts
$query = "SELECT 
            p.id, 
            p.name, 
            p.brand, 
            p.category, 
            p.vehicle_type, 
            p.price, 
            p.availability, 
            p.image_path, 
            p.description, 
            p.compatible_models,
            p.created_at,
            s.full_name as seller_name
          FROM products p
          JOIN sellers s ON p.seller_id = s.id
          WHERE 1=1";

$params = [];

// Add search conditions
if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR p.compatible_models LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Add category filter
if (!empty($categoryFilter)) {
    $query .= " AND p.category = ?";
    $params[] = $categoryFilter;
}

// Add vehicle type filter
if (!empty($vehicleTypeFilter)) {
    $query .= " AND p.vehicle_type = ?";
    $params[] = $vehicleTypeFilter;
}

// Add availability filter
if (!empty($availabilityFilter)) {
    $query .= " AND p.availability = ?";
    $params[] = $availabilityFilter;
}

try {
    // Count total products for pagination
    $countQuery = "SELECT COUNT(*) as total FROM ($query) as derived";
    $countStmt = $pdo->prepare($countQuery);
    
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key + 1, $value);
        }
    }
    
    $countStmt->execute();
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $perPage);

    // Add sorting and pagination to main query
    $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    
    // Prepare and execute main query
    $stmt = $pdo->prepare($query);
    
    // Bind filter parameters
    $paramIndex = 1;
    foreach ($params as $value) {
        $stmt->bindValue($paramIndex++, $value);
    }
    
    // Bind pagination parameters
    $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch filter options for dropdowns
    $categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll();
    $vehicleTypes = $pdo->query("SELECT DISTINCT vehicle_type FROM products ORDER BY vehicle_type")->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Manage Auto Parts</h1>
        <a href="add_product.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center mt-4 md:mt-0">
            <i class="fas fa-plus mr-2"></i> Add New Part
        </a>
    </div>

    <!-- Search and Filter Bar -->
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4 mb-6">
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white" 
                       placeholder="Part name, brand...">
            </div>
            
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select name="category" id="category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $categoryFilter === $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="vehicle_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vehicle Type</label>
                <select name="vehicle_type" id="vehicle_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                    <option value="">All Types</option>
                    <?php foreach ($vehicleTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type['vehicle_type']) ?>" <?= $vehicleTypeFilter === $type['vehicle_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['vehicle_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="availability" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Availability</label>
                <select name="availability" id="availability" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                    <option value="">All</option>
                    <option value="In Stock" <?= $availabilityFilter === 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                    <option value="Out of Stock" <?= $availabilityFilter === 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                    <option value="Pre-order" <?= $availabilityFilter === 'Pre-order' ? 'selected' : '' ?>>Pre-order</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md w-full md:w-auto">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <?php if (!empty($search) || !empty($categoryFilter) || !empty($vehicleTypeFilter) || !empty($availabilityFilter)): ?>
                    <a href="manage_products.php" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-300 px-4 py-2 rounded-md">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-600">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Part</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Brand</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vehicle Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seller</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Availability</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No auto parts found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img class="h-10 w-10 rounded-md object-cover" src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-md bg-gray-200 dark:bg-gray-500 flex items-center justify-center">
                                                    <i class="fas fa-car text-gray-400 dark:text-gray-300"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($product['name']) ?></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($product['category']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($product['brand']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($product['vehicle_type']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    $<?= number_format($product['price'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($product['seller_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php 
                                        switch($product['availability']) {
                                            case 'In Stock': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                            case 'Out of Stock': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                            case 'Pre-order': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                            default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200';
                                        }
                                        ?>
                                    ">
                                        <?= $product['availability'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="#" onclick="confirmDelete(<?= $product['id'] ?>)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50 dark:bg-gray-600 px-6 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-500">
                <div class="flex-1 flex justify-between sm:hidden">
                    <a href="?page=<?= $page > 1 ? $page - 1 : 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>&vehicle_type=<?= urlencode($vehicleTypeFilter) ?>&availability=<?= urlencode($availabilityFilter) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Previous
                    </a>
                    <a href="?page=<?= $page < $totalPages ? $page + 1 : $totalPages ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>&vehicle_type=<?= urlencode($vehicleTypeFilter) ?>&availability=<?= urlencode($availabilityFilter) ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Next
                    </a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing <span class="font-medium"><?= $offset + 1 ?></span> to <span class="font-medium"><?= min($offset + $perPage, $totalProducts) ?></span> of <span class="font-medium"><?= $totalProducts ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <a href="?page=<?= $page > 1 ? $page - 1 : 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>&vehicle_type=<?= urlencode($vehicleTypeFilter) ?>&availability=<?= urlencode($availabilityFilter) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>&vehicle_type=<?= urlencode($vehicleTypeFilter) ?>&availability=<?= urlencode($availabilityFilter) ?>" class="<?= $i == $page ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 text-blue-600 dark:text-blue-300' : 'bg-white dark:bg-gray-700 border-gray-300 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <a href="?page=<?= $page < $totalPages ? $page + 1 : $totalPages ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>&vehicle_type=<?= urlencode($vehicleTypeFilter) ?>&availability=<?= urlencode($availabilityFilter) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function confirmDelete(productId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete_product.php?id=${productId}`;
            }
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>