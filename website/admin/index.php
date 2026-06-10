<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load products data
$productsFile = __DIR__ . '/../shop/products.json';
$products = [];
if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?: [];
}

// Load analytics data
$analyticsFile = __DIR__ . '/../data/analytics.json';
$analytics = [];
if (file_exists($analyticsFile)) {
    $analytics = json_decode(file_get_contents($analyticsFile), true) ?: [];
}

// Calculate stats
$totalProducts = count($products);
$visibleProducts = count(array_filter($products, fn($p) => !isset($p['hidden']) || !$p['hidden']));
$hiddenProducts = $totalProducts - $visibleProducts;
$totalViews = array_sum(array_column($analytics, 'views'));
$totalOrders = array_sum(array_column($analytics, 'orders'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>22 Show - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-container">

    <!-- HEADER -->
    <div class="admin-header">
        <div class="admin-logo">
            <?php
                $logop = '../logo.txt';
                if (file_exists($logop)) {
                    echo file_get_contents($logop);
                } else {
                    echo "<span style='font-size: 24px; font-weight: bold;'>22 SHOW</span>";
                }
            ?>
        </div>
        <div class="admin-title">Admin Panel</div>
        <div class="admin-subtitle">[ Product Management ]</div>
    </div>

    <!-- BACK BUTTON -->
    <div class="back-btn-wrapper">
        <a href="../" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> <span>Back to Home</span>
        </a>
    </div>

    <!-- STATS -->
    <div class="admin-stats">
        <div class="stat-box">
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?php echo $totalProducts; ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Visible</div>
            <div class="stat-value"><?php echo $visibleProducts; ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Hidden</div>
            <div class="stat-value"><?php echo $hiddenProducts; ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
        </div>
    </div>

    <!-- ADMIN CARDS -->
    <div class="admin-cards">
        
        <a href="products.php" class="admin-card">
            <i class="fas fa-box"></i>
            <span class="admin-card-text">Manage Products</span>
        </a>

        <a href="add-product.php" class="admin-card">
            <i class="fas fa-plus-circle"></i>
            <span class="admin-card-text">Add New Product</span>
        </a>

        <a href="analytics.php" class="admin-card">
            <i class="fas fa-chart-bar"></i>
            <span class="admin-card-text">View Analytics</span>
        </a>

        <button class="admin-card" onclick="logout()" style="border: 1px solid rgba(244, 67, 54, 0.3);">
            <i class="fas fa-sign-out-alt" style="color: #f44336;"></i>
            <span class="admin-card-text" style="color: #f44336;">Logout</span>
        </button>

    </div>

    <!-- RECENT PRODUCTS -->
    <?php if (!empty($products)): ?>
        <div style="margin-top: 30px; width: 100%;">
            <h3 style="text-align: left; font-size: 14px; color: var(--text-secondary); margin-bottom: 12px;">
                <i class="fas fa-history"></i> Recent Products
            </h3>
            <div class="products-preview">
                <?php foreach (array_slice(array_reverse($products), 0, 6) as $product): ?>
                    <div class="product-item">
                        <img src="<?php echo htmlspecialchars($product['images'][0] ?? ''); ?>" alt="Product">
                        <div class="product-item-name">
                            <?php echo htmlspecialchars(substr($product['title']['english'] ?? 'N/A', 0, 15)); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <footer class="admin-footer">
        <p>&copy; <?php echo date('Y'); ?> <strong>22 Show</strong>. All rights reserved.</p>
    </footer>

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
