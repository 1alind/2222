<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

define('PRODUCTS_FILE', __DIR__ . '/../shop/products.json');

// Load products
function loadProducts() {
    if (file_exists(PRODUCTS_FILE)) {
        return json_decode(file_get_contents(PRODUCTS_FILE), true) ?: [];
    }
    return [];
}

// Save products
function saveProducts($products) {
    return file_put_contents(PRODUCTS_FILE, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $products = loadProducts();
    
    if ($action === 'delete') {
        $productId = $_POST['id'] ?? null;
        $products = array_filter($products, fn($p) => $p['id'] !== $productId);
        $products = array_values($products);
        if (saveProducts($products)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save']);
        }
        exit;
    }
    
    if ($action === 'toggle_hidden') {
        $productId = $_POST['id'] ?? null;
        foreach ($products as &$p) {
            if ($p['id'] === $productId) {
                $p['hidden'] = !($p['hidden'] ?? false);
                break;
            }
        }
        if (saveProducts($products)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save']);
        }
        exit;
    }
}

$products = loadProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>22 Show - Product Management</title>
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
                <span class="badge"><?php echo count($products); ?></span>
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
            <h1>Products Management</h1>
            <div class="topbar-right">
                <a href="add-product.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        </div>
        
        <!-- CONTENT -->
        <div class="products-content">
            
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h2>No Products Yet</h2>
                    <p>Create your first product to get started</p>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                $src = $product['images'][0] ?? '';
                                if (!empty($src) && strpos($src, 'http') !== 0) {
                                    $src = '../' . $src;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($product['title']['english'] ?? ''); ?>">
                                <?php if (isset($product['hidden']) && $product['hidden']): ?>
                                    <div class="hidden-overlay"><i class="fas fa-eye-slash"></i> Hidden</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($product['title']['english'] ?? 'N/A'); ?></h3>
                                <p class="product-price"><?php echo htmlspecialchars($product['price'] ?? 'N/A'); ?></p>
                                <p class="product-desc"><?php echo htmlspecialchars(substr($product['desc']['english'] ?? '', 0, 60)) . '...'; ?></p>
                                
                                <div class="product-meta">
                                    <span class="badge badge-<?php echo htmlspecialchars($product['type'] ?? 'general'); ?>">
                                        <?php echo htmlspecialchars($product['type'] ?? 'General'); ?>
                                    </span>
                                    <span class="badge">
                                        <i class="fas fa-image"></i> <?php echo count($product['images'] ?? []); ?>
                                    </span>
                                </div>
                                <div style="margin-top: 10px; font-size: 11px; color: rgba(255,255,255,0.4);">
                                    <?php if(!empty($product['added_by'])): ?>
                                        <p><i class="fas fa-user-plus"></i> Added by: <?php echo htmlspecialchars($product['added_by']); ?></p>
                                    <?php endif; ?>
                                    <?php if(!empty($product['created_at'])): ?>
                                        <p><i class="fas fa-calendar-alt"></i> Added: <?php echo date('Y-m-d', $product['created_at']); ?></p>
                                    <?php endif; ?>
                                    <?php if(!empty($product['last_edited_by'])): ?>
                                        <p style="margin-top:4px;"><i class="fas fa-user-edit"></i> Last edit by: <?php echo htmlspecialchars($product['last_edited_by']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="product-actions">
                                <a href="edit-product.php?id=<?php echo urlencode($product['id']); ?>" class="btn-action edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="toggleHidden('<?php echo htmlspecialchars($product['id']); ?>')" class="btn-action toggle" title="<?php echo (isset($product['hidden']) && $product['hidden']) ? 'Show' : 'Hide'; ?>">
                                    <i class="fas fa-<?php echo (isset($product['hidden']) && $product['hidden']) ? 'eye' : 'eye-slash'; ?>"></i>
                                </button>
                                <button onclick="deleteProduct('<?php echo htmlspecialchars($product['id']); ?>')" class="btn-action delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=delete&id=' + encodeURIComponent(productId)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete'));
            }
        });
    }
}

function toggleHidden(productId) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=toggle_hidden&id=' + encodeURIComponent(productId)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update'));
        }
    });
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}
</script>

<script src="admin-translate.js?v=1"></script>
</body>
</html>