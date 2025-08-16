<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product data
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error'] = 'Product not found';
        header('Location: manage_products.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch categories for dropdown
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch sellers (users with seller role)
try {
    $sellers = $pdo->query("SELECT id, full_name FROM users WHERE role = 'seller' ORDER BY full_name")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $compatible_models = trim($_POST['compatible_models']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);
    $warranty = trim($_POST['warranty']);
    $delivery_time = trim($_POST['delivery_time']);
    $seller_id = (int)$_POST['seller_id'];

    // Basic validation
    $errors = [];
    if (empty($name)) $errors['name'] = 'Product name is required';
    if (empty($brand)) $errors['brand'] = 'Brand is required';
    if ($price <= 0) $errors['price'] = 'Price must be greater than 0';
    if (empty($category_id)) $errors['category_id'] = 'Category is required';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET 
                name = ?, brand = ?, vehicle_type = ?, compatible_models = ?, 
                price = ?, category_id = ?, description = ?, availability = ?, 
                warranty = ?, delivery_time = ?, seller_id = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?");
            
            $stmt->execute([
                $name, $brand, $vehicle_type, $compatible_models,
                $price, $category_id, $description, $availability,
                $warranty, $delivery_time, $seller_id, $product_id
            ]);
            
            $_SESSION['success'] = 'Product updated successfully';
            header('Location: manage_products.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Edit Product</h2>
        </div>
        
        <form method="post" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if (isset($errors['database'])): ?>
                <div class="md:col-span-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?= $errors['database'] ?>
                </div>
            <?php endif; ?>
            
            <!-- Product Information -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name*</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" 
                    class="w-full px-3 py-2 border <?= isset($errors['name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['name'] ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Brand*</label>
                <input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" 
                    class="w-full px-3 py-2 border <?= isset($errors['brand']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['brand'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['brand'] ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
                <input type="text" name="vehicle_type" value="<?= htmlspecialchars($product['vehicle_type']) ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price*</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" 
                    class="w-full px-3 py-2 border <?= isset($errors['price']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['price'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['price'] ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category*</label>
                <select name="category_id" class="w-full px-3 py-2 border <?= isset($errors['category_id']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['category_id'] ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seller*</label>
                <select name="seller_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <?php foreach ($sellers as $seller): ?>
                        <option value="<?= $seller['id'] ?>" <?= $product['seller_id'] == $seller['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($seller['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Full-width fields -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Compatible Models</label>
                <textarea name="compatible_models" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($product['compatible_models']) ?></textarea>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <!-- Additional Information -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Availability</label>
                <select name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="In Stock" <?= $product['availability'] == 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                    <option value="Out of Stock" <?= $product['availability'] == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Warranty</label>
                <input type="text" name="warranty" value="<?= htmlspecialchars($product['warranty']) ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Time</label>
                <input type="text" name="delivery_time" value="<?= htmlspecialchars($product['delivery_time']) ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div class="md:col-span-2 flex justify-end space-x-4 pt-4">
                <a href="manage_products.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>