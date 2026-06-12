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
        $product['sizes'] = isset($_POST['sizes']) && is_array($_POST['sizes']) ? $_POST['sizes'] : [];
        
        $product['title'] = [
            'badini' => $_POST['title_badini'] ?? '',
            'sorani' => $_POST['title_sorani'] ?? '',
            'arabic' => $_POST['title_arabic'] ?? '',
            'english' => $_POST['title_english'] ?? ''
        ];
        
        $product['desc'] = $_POST['desc'] ?? '';
        $product['last_edited_at'] = time();
        $product['last_edited_by'] = $_SESSION['admin_name'] ?? 'Unknown';
        
        if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
            $product['images'] = array_filter($_POST['existing_images']);
        } else {
            $product['images'] = [];
        }

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
            
            <form method="POST" class="product-form" enctype="multipart/form-data">
                
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
                            <select name="type" required onchange="handleTypeChange(this)">
                                <option value="general" <?php echo ($product['type'] ?? 'general') === 'general' ? 'selected' : ''; ?>>General</option>
                                <option value="shoes" <?php echo ($product['type'] ?? 'general') === 'shoes' ? 'selected' : ''; ?>>Shoes</option>
                                <option value="perfume" <?php echo ($product['type'] ?? 'general') === 'perfume' ? 'selected' : ''; ?>>Perfume</option>
                                <option value="watch" <?php echo ($product['type'] ?? 'general') === 'watch' ? 'selected' : ''; ?>>Watch</option>
                                <option value="clothing" <?php echo ($product['type'] ?? 'general') === 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                            </select>
                        </div>
                        <div class="form-group" id="optionsGroup" style="display: none;">
                            <label>Available Sizes</label>
                            <div id="sizeCheckboxes" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px;"></div>
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
                    
                    <div id="imagesContainer" style="margin-bottom: 15px;">
                        <p class="section-desc">Existing Images (Uncheck to remove):</p>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php foreach ($product['images'] ?? [] as $img): ?>
                            <div style="text-align: center; background: rgba(255,255,255,0.05); padding: 5px; border-radius: 8px;">
                                <?php
                                $src = $img;
                                if (strpos($src, 'http') !== 0) {
                                    $src = '../' . $src;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($src); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; display: block; margin-bottom: 5px;">
                                <label style="display: flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; color: #a1a1aa; font-size: 13px;">
                                    <input type="checkbox" name="existing_images[]" value="<?php echo htmlspecialchars($img); ?>" checked> Keep
                                </label>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload New Images</label>
                        <input type="file" name="images[]" accept="image/*" multiple style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 8px; color: #fff;">
                    </div>
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
const existingSizes = <?php echo json_encode($product['sizes'] ?? []); ?>;

function handleTypeChange(select) {
    const type = select.value;
    const group = document.getElementById('optionsGroup');
    const container = document.getElementById('sizeCheckboxes');
    container.innerHTML = '';
    
    let options = [];
    if (type === 'shoes') {
        options = ["36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46"];
    } else if (type === 'clothing') {
        options = ["XS", "S", "M", "L", "XL", "XXL", "3XL"];
    } else if (type === 'perfume') {
        options = ["30ml", "50ml", "75ml", "100ml", "125ml", "150ml", "200ml"];
    }
    
    if (options.length > 0) {
        group.style.display = 'block';
        options.forEach(opt => {
            const label = document.createElement('label');
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.background = existingSizes.includes(opt) ? 'rgba(255, 140, 0, 0.2)' : 'rgba(255,255,255,0.05)';
            label.style.padding = '5px 10px';
            label.style.borderRadius = '5px';
            label.style.cursor = 'pointer';
            if(existingSizes.includes(opt)) {
                label.style.border = '1px solid #ff8c00';
            }
            
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.name = 'sizes[]';
            cb.value = opt;
            cb.style.marginRight = '8px';
            cb.checked = existingSizes.includes(opt);
            
            cb.addEventListener('change', function() {
                if (this.checked) {
                    label.style.background = 'rgba(255, 140, 0, 0.2)';
                    label.style.border = '1px solid #ff8c00';
                } else {
                    label.style.background = 'rgba(255,255,255,0.05)';
                    label.style.border = 'none';
                }
            });
            
            label.appendChild(cb);
            label.appendChild(document.createTextNode(opt));
            container.appendChild(label);
        });
    } else {
        group.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', () => handleTypeChange(document.querySelector('select[name="type"]')));

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}
</script>

<script src="admin-translate.js?v=<?php echo time(); ?>"></script>
</body>
</html>