<?php
$json_data = @file_get_contents(__DIR__ . '/products.json');
$products = json_decode($json_data, true);

// Get analytics for trending calculation
$analytics_data = @file_get_contents(__DIR__ . '/../data/analytics.json');
$analytics = $analytics_data ? json_decode($analytics_data, true) : [];
$scores = [];
if (is_array($analytics)) {
    foreach ($analytics as $stat) {
        $id = $stat['id'] ?? '';
        $views = $stat['views'] ?? 0;
        $orders = $stat['orders'] ?? 0;
        $clicks = $stat['clicks'] ?? 0;
        $swipes = $stat['swipes'] ?? 0;
        $whatsapp = $stat['whatsapp'] ?? 0;
        // Interactions score formulation
        $score = $views + $clicks + ($swipes * 2) + ($whatsapp * 5) + ($orders * 5);
        if ($score > 0) {
            $scores[$id] = $score;
        }
    }
}
arsort($scores);
$trendingIds = array_slice(array_keys($scores), 0, 5); // top 5 products are trending

if ($products && is_array($products)) {
    $groupedProducts = [];
    foreach ($products as $product) {
        if (isset($product['hidden']) && $product['hidden']) {
            continue;
        }
        $type = isset($product['type']) && !empty(trim($product['type'])) ? trim($product['type']) : 'General';
        $groupedProducts[$type][] = $product;
    }

    if (empty($groupedProducts)) {
        echo '<p style="color: var(--text-secondary); text-align: center;">No products found.</p>';
    } else {
        foreach ($groupedProducts as $type => $typeProducts) {
            echo '<div class="product-category-section" id="category-' . htmlspecialchars(strtolower($type)) . '">';
            echo '<h2 class="category-title" style="color: #fff; border-bottom: 2px solid var(--border-color); padding-bottom: 10px; margin-top: 40px; margin-bottom: 20px; font-size: 24px; text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-list" style="color: var(--neon-cyan); margin-right: 10px;"></i>' . htmlspecialchars(ucfirst($type)) . '</h2>';
            echo '<div class="products-grid">';
            
            foreach ($typeProducts as $product) {
        
        $prod_id = htmlspecialchars($product['id'] ?? '');
        $price = htmlspecialchars($product['price'] ?? '');
        $badge = htmlspecialchars($product['badge'] ?? '');
        $created_at = $product['created_at'] ?? 0;
        
        // Auto-assign "NEW" if created within 7 days
        if ($created_at > 0 && (time() - $created_at) <= 7 * 24 * 60 * 60) {
            $badge = 'NEW';
        }
        
        // Auto-assign "TRENDING" if it's one of the top interacted products and hasn't been overridden by "NEW" or manually
        if (empty($badge) && in_array($prod_id, $trendingIds) && ($scores[$prod_id] ?? 0) >= 5) {
            $badge = 'TRENDING';
        }
        
        $type = htmlspecialchars($product['type'] ?? 'general');
        $images = $product['images'] ?? [];
        if (!is_array($images)) {
            $images = !empty($images) ? [$images] : [];
        }
        
        $title = $product['title'] ?? [];
        $title_badini = htmlspecialchars($title['badini'] ?? '');
        $title_sorani = htmlspecialchars($title['sorani'] ?? '');
        $title_arabic = htmlspecialchars($title['arabic'] ?? '');
        $title_english = htmlspecialchars($title['english'] ?? '');
        
        $desc_val = $product['desc'] ?? [];
        if (!is_array($desc_val)) {
            $desc_val = ['badini' => $desc_val, 'sorani' => $desc_val, 'arabic' => $desc_val, 'english' => $desc_val];
        }
        $desc_badini = nl2br(htmlspecialchars($desc_val['badini'] ?? ''));
        $desc_sorani = nl2br(htmlspecialchars($desc_val['sorani'] ?? ''));
        $desc_arabic = nl2br(htmlspecialchars($desc_val['arabic'] ?? ''));
        $desc_english = nl2br(htmlspecialchars($desc_val['english'] ?? ''));
        
        $sizes_attr = isset($product['sizes']) && is_array($product['sizes']) ? htmlspecialchars(json_encode($product['sizes'])) : '';
        $ml_attr = isset($product['ml']) && is_array($product['ml']) ? htmlspecialchars(json_encode($product['ml'])) : '';
        ?>

        <div class="product-card" id="<?php echo $prod_id; ?>" data-type="<?php echo $type; ?>" data-sizes="<?php echo $sizes_attr; ?>" data-ml="<?php echo $ml_attr; ?>">
            <div class="product-image-wrapper">
                <!-- Image Slider -->
                <div class="images-slider">
                    <?php 
                    foreach ($images as $index => $img_url) {
                        $active_class = ($index === 0) ? 'active' : '';
                        $src = htmlspecialchars($img_url);
                        if (strpos($src, 'http') !== 0) {
                            $src = '../' . $src;
                        }
                        echo '<img src="' . $src . '" class="slide ' . $active_class . '" alt="Product Image" onclick="openImageModal(this.src)" style="cursor: zoom-in;">';
                    }
                    ?>
                </div>
                
                <?php if (count($images) > 1): ?>
                    <!-- Navigation Arrows -->
                    <button class="slide-nav prev" onclick="changeSlide('<?php echo $prod_id; ?>', -1, event)"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="slide-nav next" onclick="changeSlide('<?php echo $prod_id; ?>', 1, event)"><i class="fa-solid fa-chevron-right"></i></button>
                    
                    <!-- Slider Dots -->
                    <div class="slider-dots">
                        <?php foreach ($images as $index => $img_url): ?>
                            <span class="dot <?php echo ($index === 0) ? 'active' : ''; ?>"></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($badge)): ?>
                    <?php 
                        $badgeClass = 'badge';
                        $uplBadge = strtoupper($badge);
                        if ($uplBadge === 'NEW') {
                            $badgeClass .= ' badge-new';
                        } elseif ($uplBadge === 'TRENDING' || $uplBadge === 'HOT') {
                            $badgeClass .= ' badge-trending';
                        }
                    ?>
                    <span class="<?php echo $badgeClass; ?>"><?php echo $badge; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h3 class="prod-title" 
                    data-badini="<?php echo $title_badini; ?>" 
                    data-sorani="<?php echo $title_sorani; ?>" 
                    data-arabic="<?php echo $title_arabic; ?>" 
                    data-english="<?php echo $title_english; ?>">
                    <?php echo $title_badini; ?>
                </h3>
                
                <div class="prod-desc-container">
                    <p class="prod-desc" 
                        data-badini="<?php echo $desc_badini; ?>" 
                        data-sorani="<?php echo $desc_sorani; ?>" 
                        data-arabic="<?php echo $desc_arabic; ?>" 
                        data-english="<?php echo $desc_english; ?>">
                        <?php echo $desc_badini; ?>
                    </p>
                </div>
                
                <div class="product-meta" style="flex-direction: column; align-items: flex-start; gap: 8px;">
                    <span class="price" data-raw-price="<?php echo $price; ?>"><?php echo $price; ?></span>
                    <?php if (isset($product['sizes']) && is_array($product['sizes']) && count($product['sizes']) > 0): ?>
                        <div class="available-options" style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <?php foreach($product['sizes'] as $sz): ?>
                                <span style="font-size: 11px; padding: 2px 6px; background: rgba(255,255,255,0.1); border-radius: 4px; color: #a1a1aa; border: 1px solid var(--border-color);"><?php echo htmlspecialchars($sz); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (isset($product['ml']) && is_array($product['ml']) && count($product['ml']) > 0): ?>
                        <div class="available-options" style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <?php foreach($product['ml'] as $ml): ?>
                                <span style="font-size: 11px; padding: 2px 6px; background: rgba(255,255,255,0.1); border-radius: 4px; color: #a1a1aa; border: 1px solid var(--border-color);"><?php echo htmlspecialchars($ml); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button class="order-btn" onclick="openOrderModal('<?php echo $prod_id; ?>')">
                    <i class="fa-brands fa-whatsapp"></i> <span class="btn-text">جهێ كرنێ ب واتساپێ</span>
                </button>
            </div>
        </div>

        <?php
            } // end inner loop
            ?>
            </div> <!-- close .products-grid -->
            </div> <!-- close .product-category-section -->
            <?php
        } // end outer loop
    } // end else
} else {
    echo '<p style="color: var(--text-secondary); text-align: center;">No products found.</p>';
}
?>
