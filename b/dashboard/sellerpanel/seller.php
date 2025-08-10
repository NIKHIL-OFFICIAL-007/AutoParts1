<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

// Include configuration and functions
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
verifySellerSession();
?>

<?php include 'templates/header.php'; ?>
<?php include 'templates/sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <div class="page-title">
            <i class="fa-solid fa-car-wrench"></i>
            <h1>Sell Vehicle Parts</h1>
        </div>
        <div class="header-actions">
            <button class="btn">
                <i class="fa-solid fa-bell"></i>
            </button>
        </div>
    </div>
    
    <div class="stats-container">
        <div class="stat-card fade-in" style="animation-delay: 0.1s">
            <div class="stat-title">Active Listings</div>
            <div class="stat-value">24</div>
            <div class="stat-change">
                <i class="fa-solid fa-arrow-up"></i>
                <span>12% from last month</span>
            </div>
        </div>
        
        <div class="stat-card fade-in" style="animation-delay: 0.2s">
            <div class="stat-title">Total Earnings</div>
            <div class="stat-value">₹86,450</div>
            <div class="stat-change">
                <i class="fa-solid fa-arrow-up"></i>
                <span>₹12,450 this month</span>
            </div>
        </div>
        
        <div class="stat-card fade-in" style="animation-delay: 0.3s">
            <div class="stat-title">Conversion Rate</div>
            <div class="stat-value">42%</div>
            <div class="stat-change">
                <i class="fa-solid fa-arrow-down"></i>
                <span>3% from last month</span>
            </div>
        </div>
        
        <div class="stat-card fade-in" style="animation-delay: 0.4s">
            <div class="stat-title">Customer Rating</div>
            <div class="stat-value">4.8/5</div>
            <div class="stat-change">
                <i class="fa-solid fa-star"></i>
                <span>98% positive</span>
            </div>
        </div>
    </div>
    
    <div class="sell-container fade-in" style="animation-delay: 0.5s">
        <div class="form-header">
            <h2><i class="fa-solid fa-circle-plus"></i> Add New Product</h2>
        </div>
        
        <form action="submit_part.php" method="POST" enctype="multipart/form-data" class="sell-form">
            <div class="form-group">
                <label class="form-label" for="product_name">
                    <i class="fa-solid fa-box"></i>
                    Product Name
                </label>
                <input type="text" id="product_name" name="product_name" class="form-input" placeholder="e.g. Brake Pads Set" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="part_brand">
                    <i class="fa-solid fa-trademark"></i>
                    Product Brand
                </label>
                <select id="part_brand" name="part_brand" class="form-input" required>
                    <option value="">- Select Brand -</option>
                    <option value="Bosch">Bosch</option>
                    <option value="Amaron">Amaron</option>
                    <option value="Philips">Philips</option>
                    <option value="Mann">Mann</option>
                    <option value="Exide">Exide</option>
                    <option value="Hella">Hella</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="vehicle_brand">
                    <i class="fa-solid fa-car"></i>
                    Vehicle Brand
                </label>
                <select id="vehicle_brand" name="vehicle_brand" class="form-input" required>
                    <option value="">- Select Vehicle Brand -</option>
                    <option value="Maruti">Maruti</option>
                    <option value="Honda">Honda</option>
                    <option value="Hyundai">Hyundai</option>
                    <option value="Kia">Kia</option>
                    <option value="Mahindra">Mahindra</option>
                    <option value="Tata">Tata</option>
                    <option value="Toyota">Toyota</option>
                    <option value="Ford">Ford</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="model">
                    <i class="fa-solid fa-car-side"></i>
                    Vehicle Model
                </label>
                <input type="text" id="model" name="model" class="form-input" placeholder="e.g. Swift Dzire, i20" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="price">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                    Price (₹)
                </label>
                <input type="number" id="price" name="price" class="form-input" placeholder="e.g. 2499" min="1" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="category">
                    <i class="fa-solid fa-list"></i>
                    Category
                </label>
                <select id="category" name="category" class="form-input" required>
                    <option value="">- Select Category -</option>
                    <option value="Engine">Engine Parts</option>
                    <option value="Brakes">Brakes</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Lighting">Lighting</option>
                    <option value="Suspension">Suspension</option>
                    <option value="Interior">Interior</option>
                </select>
            </div>
            
            <div class="form-group full-width">
                <label class="form-label" for="description">
                    <i class="fa-solid fa-file-lines"></i>
                    Product Description
                </label>
                <textarea id="description" name="description" class="form-input" placeholder="Describe the part, its features, and condition..." required></textarea>
            </div>
            
            <div class="form-group full-width">
    <label class="form-label" for="part_image">
        <i class="fa-solid fa-image"></i>
        Product Images
    </label>
    <div class="file-upload">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <div class="file-upload-text">
            <h3>Upload Product Images</h3>
            <p>Click to browse or drag & drop your images here</p>
            <p>PNG, JPG, JPEG up to 5MB</p>
        </div>
        <!-- Change name to part_image[] to receive array of files -->
        <input type="file" id="part_image" name="part_image[]" class="file-input" accept="image/*" multiple required>
    </div>
</div>
            
            <div class="form-group">
                <label class="form-label" for="seller_name">
                    <i class="fa-solid fa-user"></i>
                    Seller Name
                </label>
                <input type="text" id="seller_name" name="seller_name" class="form-input" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="phone">
                    <i class="fa-solid fa-phone"></i>
                    Contact Number
                </label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="state">
                    <i class="fa-solid fa-location-dot"></i>
                    State
                </label>
                <select id="state" name="state" class="form-input" required>
                    <option value="">- Select State -</option>
                    <option value="Tamil Nadu">Tamil Nadu</option>
                    <option value="Kerala">Kerala</option>
                    <option value="Karnataka">Karnataka</option>
                    <option value="Maharashtra">Maharashtra</option>
                    <option value="Delhi">Delhi</option>
                    <option value="Gujarat">Gujarat</option>
                    <option value="Rajasthan">Rajasthan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="district">
                    <i class="fa-solid fa-map"></i>
                    District
                </label>
                <input type="text" id="district" name="district" class="form-input" placeholder="Your district" required>
            </div>
            
            <button type="submit" class="submit-btn">
                <i class="fa-solid fa-paper-plane"></i>
                Post Product
            </button>
        </form>
    </div>
</div>

<?php include 'templates/footer.php'; ?>