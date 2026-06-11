<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

define('PRODUCTS_FILE', __DIR__ . '/../shop/products.json');

function loadProducts() {
    if (file_exists(PRODUCTS_FILE)) {
        return json_decode(file_get_contents(PRODUCTS_FILE), true) ?: [];
    }
    return [];
}

function saveProducts($products) {
    return file_put_contents(PRODUCTS_FILE, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $products = loadProducts();
        
        $id = sanitizeId($_POST['id'] ?? '');
        $type = $_POST['type'] ?? 'general';
        $badge = $_POST['badge'] ?? '';
        $price = $_POST['price'] ?? '';
        
        // Check if ID already exists
        if (array_key_exists($id, array_flip(array_column($products, 'id')))) {
            $error = 'Product ID already exists!';
        } else {
            $product = [
                'id' => $id,
                'type' => $type,
                'badge' => $badge,
                'price' => $price,
                'images' => [],
                'created_at' => time(),
                'title' => [
                    'badini' => $_POST['title_badini'] ?? '',
                    'sorani' => $_POST['title_sorani'] ?? '',
                    'arabic' => $_POST['title_arabic'] ?? '',
                    'english' => $_POST['title_english'] ?? ''
                ],
                'desc' => $_POST['desc'] ?? ''
            ];
            
            // Handle image uploads
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    if (!empty($tmpName)) {
                        $fileName = time() . '_' . uniqid() . '_' . basename(preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['images']['name'][$key]));
                        $targetPath = $uploadDir . $fileName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $product['images'][] = 'uploads/' . $fileName;
                        }
                    }
                }
            }
            
            $products[] = $product;
            
            if (saveProducts($products)) {
                $success = 'Product added successfully!';
                header('refresh:2;url=products.php');
            } else {
                $error = 'Failed to save product';
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

function sanitizeId($str) {
    return preg_replace('/[^a-z0-9_-]/', '_', strtolower($str));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - 22 Show Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-wrapper">
    
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-cog"></i>
            <span>22 Show Admin</span>
        </div>
        
        <nav class="sidebar-menu">
            <a href="index.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="products.php" class="menu-item active">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="analytics.php" class="menu-item">
                <i class="fas fa-chart-pie"></i>
                <span>Analytics</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <button onclick="logout()" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </div>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- TOP BAR -->
        <div class="topbar">
            <h1>Add New Product</h1>
            <div class="topbar-right">
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <!-- CONTENT -->
        <div class="form-wrapper">
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?> Redirecting...
                </div>
            <?php endif; ?>
            
            <form method="POST" class="product-form" enctype="multipart/form-data">
                
                <!-- BASIC INFO -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Product ID *</label>
                            <input type="text" name="id" placeholder="e.g., prod_shoes" required>
                            <small>Unique identifier (no spaces)</small>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <input type="text" name="type" placeholder="e.g., shoes, watch, clothing" list="typeList" required>
                            <datalist id="typeList">
                                <option value="general"></option>
                                <option value="shoes"></option>
                                <option value="perfume"></option>
                                <option value="watch"></option>
                                <option value="clothing"></option>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label>Badge</label>
                            <input type="text" name="badge" placeholder="e.g., NEW, BEST, LUXURY">
                        </div>
                        <div class="form-group">
                            <label>Price *</label>
                            <input type="text" name="price" placeholder="e.g., 45,000 د.ع" required>
                        </div>
                    </div>
                </div>
                
                <!-- TITLES -->
                <div class="form-section">
                    <h2><i class="fas fa-heading"></i> Titles (Multi-language)</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Title (Badini) *</label>
                            <input type="text" name="title_badini" required>
                        </div>
                        <div class="form-group">
                            <label>Title (Sorani)</label>
                            <input type="text" name="title_sorani">
                        </div>
                        <div class="form-group">
                            <label>Title (Arabic)</label>
                            <input type="text" name="title_arabic">
                        </div>
                        <div class="form-group">
                            <label>Title (English) *</label>
                            <input type="text" name="title_english" required>
                        </div>
                    </div>
                </div>
                
                <!-- DESCRIPTIONS -->
                <div class="form-section">
                    <h2><i class="fas fa-file-alt"></i> Description</h2>
                    <p class="section-desc">Write everything here including sizes or anything else. Press Enter to go to the next line.</p>
                    
                    <div class="form-row">
                        <div class="form-group" style="width: 100%;">
                            <label>Description *</label>
                            <textarea name="desc" rows="5" placeholder="e.g. 38,000 د.ع&#10;Available Sizes: 40, 41, 42" required></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- IMAGES -->
                <div class="form-section">
                    <h2><i class="fas fa-images"></i> Product Images</h2>
                    <p class="section-desc">Upload one or more images</p>
                    
                    <div class="form-group">
                        <input type="file" name="images[]" accept="image/*" multiple required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 8px; color: #fff;">
                    </div>
                </div>
                
                <!-- FORM ACTIONS -->
                <div class="form-actions">
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}
</script>

</body>
</html>