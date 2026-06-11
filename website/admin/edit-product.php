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

$productId = $_GET['id'] ?? null;
$product = null;

if (!$productId) {
    header('Location: products.php');
    exit;
}

$products = loadProducts();
foreach ($products as $p) {
    if ($p['id'] === $productId) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product['type'] = $_POST['type'] ?? 'general';
        $product['badge'] = $_POST['badge'] ?? '';
        $product['price'] = $_POST['price'] ?? '';
        
        $product['title'] = [
            'badini' => $_POST['title_badini'] ?? '',
            'sorani' => $_POST['title_sorani'] ?? '',
            'arabic' => $_POST['title_arabic'] ?? '',
            'english' => $_POST['title_english'] ?? ''
        ];
        
        $product['desc'] = $_POST['desc'] ?? '';
        
        if (isset($_POST['images']) && is_array($_POST['images'])) {
            $product['images'] = array_filter($_POST['images']);
        }
        
        // Update in array
        foreach ($products as &$p) {
            if ($p['id'] === $productId) {
                $p = $product;
                break;
            }
        }
        
        if (saveProducts($products)) {
            $success = 'Product updated successfully!';
            header('refresh:2;url=products.php');
        } else {
            $error = 'Failed to save product';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - 22 Show Admin</title>
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
            <h1>Edit Product</h1>
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
            
            <form method="POST" class="product-form">
                
                <!-- BASIC INFO -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Product ID (Read-only)</label>
                            <input type="text" value="<?php echo htmlspecialchars($product['id']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type">
                                <option value="general" <?php echo ($product['type'] ?? 'general') === 'general' ? 'selected' : ''; ?>>General</option>
                                <option value="shoes" <?php echo ($product['type'] ?? 'general') === 'shoes' ? 'selected' : ''; ?>>Shoes</option>
                                <option value="perfume" <?php echo ($product['type'] ?? 'general') === 'perfume' ? 'selected' : ''; ?>>Perfume</option>
                                <option value="watch" <?php echo ($product['type'] ?? 'general') === 'watch' ? 'selected' : ''; ?>>Watch</option>
                                <option value="clothing" <?php echo ($product['type'] ?? 'general') === 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Badge</label>
                            <input type="text" name="badge" value="<?php echo htmlspecialchars($product['badge'] ?? ''); ?>" placeholder="e.g., NEW, BEST, LUXURY">
                        </div>
                        <div class="form-group">
                            <label>Price *</label>
                            <input type="text" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- TITLES -->
                <div class="form-section">
                    <h2><i class="fas fa-heading"></i> Titles (Multi-language)</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Title (Badini) *</label>
                            <input type="text" name="title_badini" value="<?php echo htmlspecialchars($product['title']['badini'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Title (Sorani)</label>
                            <input type="text" name="title_sorani" value="<?php echo htmlspecialchars($product['title']['sorani'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Title (Arabic)</label>
                            <input type="text" name="title_arabic" value="<?php echo htmlspecialchars($product['title']['arabic'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Title (English) *</label>
                            <input type="text" name="title_english" value="<?php echo htmlspecialchars($product['title']['english'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- DESCRIPTIONS -->
                <div class="form-section">
                    <h2><i class="fas fa-file-alt"></i> Description</h2>
                    <p class="section-desc">Write everything here including sizes or anything else. Press Enter to go to the next line.</p>
                    
                    <div class="form-row">
                        <?php
                        $desc_val = is_array($product['desc']) ? ($product['desc']['badini'] ?? '') : ($product['desc'] ?? '');
                        ?>
                        <div class="form-group" style="width: 100%;">
                            <label>Description *</label>
                            <textarea name="desc" rows="5" required><?php echo htmlspecialchars($desc_val); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- IMAGES -->
                <div class="form-section">
                    <h2><i class="fas fa-images"></i> Product Images</h2>
                    
                    <div id="imagesContainer">
                        <?php foreach ($product['images'] ?? [] as $img): ?>
                            <div class="image-input-group">
                                <input type="url" name="images[]" placeholder="Image URL" value="<?php echo htmlspecialchars($img); ?>">
                                <button type="button" class="btn-remove" onclick="removeImageInput(this)"><i class="fas fa-trash"></i></button>
                            </div>
                        <?php endforeach; ?>
                        <div class="image-input-group">
                            <input type="url" name="images[]" placeholder="Image URL">
                            <button type="button" class="btn-remove" onclick="removeImageInput(this)"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-secondary" onclick="addImageInput()">
                        <i class="fas fa-plus"></i> Add Another Image
                    </button>
                </div>
                
                <!-- FORM ACTIONS -->
                <div class="form-actions">
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addImageInput() {
    const container = document.getElementById('imagesContainer');
    const group = document.createElement('div');
    group.className = 'image-input-group';
    group.innerHTML = `
        <input type="url" name="images[]" placeholder="Image URL">
        <button type="button" class="btn-remove" onclick="removeImageInput(this)"><i class="fas fa-trash"></i></button>
    `;
    container.appendChild(group);
}

function removeImageInput(btn) {
    btn.parentElement.remove();
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}
</script>

</body>
</html>