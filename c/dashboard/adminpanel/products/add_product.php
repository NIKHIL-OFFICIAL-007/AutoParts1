<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Enable error reporting
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);



// Get current user information
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_name = $_SESSION['username'] ?? 'Admin';

$error = '';
$success = '';

// Initialize form fields (removed rating as it's not in your database)
$formData = [
    'name' => '',
    'description' => '',
    'price' => 0,
    'brand' => '',
    'vehicle_type' => '',
    'compatible_models' => '',
    'image_path' => '',
    'availability' => 'In Stock',
    'category' => '',
    'warranty' => '',
    'seller_id' => $current_user_id,
    'delivery_time' => '2-4 Days',
    'quantity' => 1
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input (removed rating from form data)
        $formData = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'price' => (float)$_POST['price'],
            'brand' => trim($_POST['brand']),
            'vehicle_type' => trim($_POST['vehicle_type']),
            'compatible_models' => trim($_POST['compatible_models']),
            'availability' => $_POST['availability'],
            'category' => trim($_POST['category']),
            'warranty' => trim($_POST['warranty']),
            'seller_id' => $current_user_id,
            'delivery_time' => trim($_POST['delivery_time']),
            'quantity' => (int)$_POST['quantity']
        ];

        // Validate required fields
        $requiredFields = ['name', 'price', 'brand', 'vehicle_type', 'category'];
        foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
                throw new Exception(ucfirst($field) . " is required");
            }
        }

        // Handle file upload
        $imagePath = '';
        if (!empty($_FILES['image']['name'])) {
            // ... [keep existing file upload code] ...
        } elseif (!empty($_POST['image_url'])) {
            $imagePath = trim($_POST['image_url']);
        }

        // Modified SQL query - removed rating column
        $stmt = $pdo->prepare("INSERT INTO products (
            name, description, price, brand, vehicle_type, compatible_models, 
            image_path, availability, category, warranty, seller_id, 
            delivery_time, quantity, created_by
        ) VALUES (
            :name, :description, :price, :brand, :vehicle_type, :compatible_models, 
            :image_path, :availability, :category, :warranty, :seller_id, 
            :delivery_time, :quantity, :created_by
        )");

        $params = array_merge($formData, [
            'image_path' => $imagePath,
            'created_by' => $current_user_id
        ]);
        
        $stmt->execute($params);

        $success = "Product added successfully!";
        // Reset form
        $formData = array_fill_keys(array_keys($formData), '');
        $formData['availability'] = 'In Stock';
        $formData['delivery_time'] = '2-4 Days';
        $formData['quantity'] = 1;
        $formData['price'] = 0;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- ... [keep existing header and messages code] ... -->

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <!-- Basic Information Section -->
            <div>
                <h2 class="text-lg font-medium text-gray-800 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand *</label>
                        <select id="brand" name="brand" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Brand</option>
                            <option value="Exide" <?= $formData['brand'] === 'Exide' ? 'selected' : '' ?>>Exide</option>
                            <option value="Bosch" <?= $formData['brand'] === 'Bosch' ? 'selected' : '' ?>>Bosch</option>
                            <option value="Amaron" <?= $formData['brand'] === 'Amaron' ? 'selected' : '' ?>>Amaron</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Category</option>
                            <option value="Batteries" <?= $formData['category'] === 'Batteries' ? 'selected' : '' ?>>Batteries</option>
                            <option value="Engine" <?= $formData['category'] === 'Engine' ? 'selected' : '' ?>>Engine Parts</option>
                            <option value="Brakes" <?= $formData['category'] === 'Brakes' ? 'selected' : '' ?>>Brakes</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type *</label>
                        <select id="vehicle_type" name="vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="Car" <?= $formData['vehicle_type'] === 'Car' ? 'selected' : '' ?>>Car</option>
                            <option value="Bike" <?= $formData['vehicle_type'] === 'Bike' ? 'selected' : '' ?>>Bike</option>
                            <option value="Truck" <?= $formData['vehicle_type'] === 'Truck' ? 'selected' : '' ?>>Truck</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($formData['description']) ?></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="compatible_models" class="block text-sm font-medium text-gray-700 mb-1">Compatible Models</label>
                        <input type="text" id="compatible_models" name="compatible_models" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., Maruti Swift, Hyundai i20" value="<?= htmlspecialchars($formData['compatible_models']) ?>">
                    </div>
                </div>
            </div>

            <!-- Pricing & Inventory Section -->
            <div>
                <h2 class="text-lg font-medium text-gray-800 mb-4">Pricing & Inventory</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₹) *</label>
                        <input type="number" step="0.01" min="0" id="price" name="price" value="<?= htmlspecialchars($formData['price']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" min="0" id="quantity" name="quantity" value="<?= htmlspecialchars($formData['quantity']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="availability" class="block text-sm font-medium text-gray-700 mb-1">Availability *</label>
                        <select id="availability" name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="In Stock" <?= $formData['availability'] === 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                            <option value="Out of Stock" <?= $formData['availability'] === 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                            <option value="Pre-order" <?= $formData['availability'] === 'Pre-order' ? 'selected' : '' ?>>Pre-order</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="delivery_time" class="block text-sm font-medium text-gray-700 mb-1">Delivery Time</label>
                        <input type="text" id="delivery_time" name="delivery_time" value="<?= htmlspecialchars($formData['delivery_time']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 2-4 Days">
                    </div>
                    
                    <div>
                        <label for="warranty" class="block text-sm font-medium text-gray-700 mb-1">Warranty</label>
                        <input type="text" id="warranty" name="warranty" value="<?= htmlspecialchars($formData['warranty']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 24 months">
                    </div>
                </div>
            </div>

            <!-- Seller & Image Section -->
            <div>
                <h2 class="text-lg font-medium text-gray-800 mb-4">Seller & Image</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seller</label>
                        <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                            <?= htmlspecialchars($current_user_name) ?>
                        </div>
                        <input type="hidden" name="seller_id" value="<?= htmlspecialchars($current_user_id) ?>">
                    </div>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Max file size: 2MB (JPG, JPEG, PNG, GIF)</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">OR Image URL</label>
                        <input type="url" id="image_url" name="image_url" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="https://example.com/image.jpg">
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-save mr-2"></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>