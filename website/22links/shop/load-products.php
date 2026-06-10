<?php
$json_data = file_get_contents(__DIR__ . '/products.json');
$products = json_decode($json_data, true);

if ($products && is_array($products)) {
    foreach ($products as $product) {
        // Skip hidden products
        if (isset($product['hidden']) && $product['hidden']) {
            continue;
        }
        
        $prod_id = htmlspecialchars($product['id']);
        $price = htmlspecialchars($product['price']);
        $badge = htmlspecialchars($product['badge']);
        $type = isset($product['type']) ? htmlspecialchars($product['type']) : 'general';
        $images = $product['images'];
        
        $title_badini = htmlspecialchars($product['title']['badini']);
        $title_sorani = htmlspecialchars($product['title']['sorani']);
        $title_arabic = htmlspecialchars($product['title']['arabic']);
        $title_english = htmlspecialchars($product['title']['english']);
        
        $desc_badini = htmlspecialchars($product['desc']['badini']);
        $desc_sorani = htmlspecialchars($product['desc']['sorani']);
        $desc_arabic = htmlspecialchars($product['desc']['arabic']);
        $desc_english = htmlspecialchars($product['desc']['english']);
        ?>

        <div class="product-card" id="<?php echo $prod_id; ?>" data-type="<?php echo $type; ?>">
            <div class="product-image-wrapper">
                <!-- Image Slider -->
                <div class="images-slider">
                    <?php 
                    foreach ($images as $index => $img_url) {
                        $active_class = ($index === 0) ? 'active' : '';
                        echo '<img src="' . htmlspecialchars($img_url) . '" class="slide ' . $active_class . '" alt="Product Image">';
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
                    <span class="badge"><?php echo $badge; ?></span>
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
                
                <div class="product-meta">
                    <span class="price"><?php echo $price; ?></span>
                </div>
                
                <button class="order-btn" onclick="openOrderModal('<?php echo $prod_id; ?>')">
                    <i class="fa-brands fa-whatsapp"></i> <span class="btn-text">جهێ كرنێ ب واتساپێ</span>
                </button>
            </div>
        </div>

        <?php
    }
} else {
    echo '<p style="color: var(--text-secondary); text-align: center;">No products found.</p>';
}
?>
