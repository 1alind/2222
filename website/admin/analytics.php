<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

define('PRODUCTS_FILE', __DIR__ . '/../shop/products.json');
define('ANALYTICS_FILE', __DIR__ . '/../data/analytics.json');

// Load data
function loadProducts() {
    if (file_exists(PRODUCTS_FILE)) {
        return json_decode(file_get_contents(PRODUCTS_FILE), true) ?: [];
    }
    return [];
}

function loadAnalytics() {
    if (file_exists(ANALYTICS_FILE)) {
        return json_decode(file_get_contents(ANALYTICS_FILE), true) ?: [];
    }
    return [];
}

$products = loadProducts();
$analytics = loadAnalytics();

// Calculate stats
$totalViews = 0;
$totalOrders = 0;
$topProducts = [];

$analyticsMap = [];
foreach ($analytics as $stat) {
    $analyticsMap[$stat['id']] = $stat;
    $totalViews += $stat['views'] ?? 0;
    $totalOrders += $stat['orders'] ?? 0;
}

foreach ($products as $product) {
    $stat = $analyticsMap[$product['id']] ?? ['id' => $product['id'], 'views' => 0, 'orders' => 0];
    $stat['title'] = $product['title']['english'] ?? 'N/A';
    $topProducts[] = $stat;
}

usort($topProducts, fn($a, $b) => ($b['orders'] ?? 0) - ($a['orders'] ?? 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - 22 Show Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="products.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span>Products</span>
                <span class="badge"><?php echo count($products); ?></span>
            </a>
            <a href="analytics.php" class="menu-item active">
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
            <h1>Analytics & Reports</h1>
        </div>
        
        <!-- CONTENT -->
        <div class="analytics-content">
            
            <!-- STATS OVERVIEW -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-views">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Views</span>
                        <span class="stat-value"><?php echo number_format($totalViews); ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Orders</span>
                        <span class="stat-value"><?php echo number_format($totalOrders); ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-conversion">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Conversion Rate</span>
                        <span class="stat-value"><?php echo $totalViews > 0 ? round(($totalOrders / $totalViews) * 100, 2) : 0; ?>%</span>
                    </div>
                </div>
            </div>
            
            <!-- TOP PRODUCTS -->
            <div class="chart-section">
                <h2>Top Performing Products</h2>
                <div class="table-wrapper">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product Name</th>
                                <th>Views</th>
                                <th>Orders</th>
                                <th>Conversion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($topProducts, 0, 10) as $index => $product): ?>
                                <tr>
                                    <td>
                                        <span class="rank">
                                            <?php if ($index === 0): ?>
                                                <i class="fas fa-medal" style="color: #ffd700;"></i>
                                            <?php elseif ($index === 1): ?>
                                                <i class="fas fa-medal" style="color: #c0c0c0;"></i>
                                            <?php elseif ($index === 2): ?>
                                                <i class="fas fa-medal" style="color: #cd7f32;"></i>
                                            <?php else: ?>
                                                <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['title'] ?? 'N/A'); ?></td>
                                    <td><?php echo number_format($product['views'] ?? 0); ?></td>
                                    <td><?php echo number_format($product['orders'] ?? 0); ?></td>
                                    <td>
                                        <?php 
                                            $views = $product['views'] ?? 0;
                                            $rate = $views > 0 ? round(($product['orders'] ?? 0) / $views * 100, 1) : 0;
                                            echo $rate . '%';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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