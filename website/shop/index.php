<!DOCTYPE html>
<html lang="ku">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        $settingsFile = __DIR__ . '/../data/settings.json';
        $settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
        echo htmlspecialchars($settings['store_name'] ?? '22 Show'); 
    ?> - Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="shop-style.css?v=<?php echo time(); ?>">
    <script>
        const storeSettings = <?php echo json_encode($settings); ?>;
        const staticTranslations = <?php echo file_get_contents(__DIR__ . '/system_lang.json'); ?>;
    </script>
</head>
<body class="lang-badini">

    <div class="shop-container">

        <!-- شريط اختيار اللغات العلوي -->
        <div class="lang-switcher">
            <button class="lang-btn active" onclick="switchLanguage('badini')">بادیني</button>
            <button class="lang-btn" onclick="switchLanguage('sorani')">سۆرانی</button>
            <button class="lang-btn" onclick="switchLanguage('arabic')">العربية</button>
            <button class="lang-btn" onclick="switchLanguage('english')">English</button>
        </div>

        <!-- زر العودة -->
        <div class="back-btn-wrapper">
            <a href="../" class="back-btn" id="backBtn">
                <i class="fa-solid fa-arrow-left"></i> <span>زڤرن بۆ لاپەڕێ سەرەكي</span>
            </a>
        </div>

        <!-- الهيدر -->
        <div class="shop-header">
            <div class="shop-logo">
                <?php
                    $logop = '../logo.txt';
                    if (file_exists($logop)) {
                        echo file_get_contents($logop);
                    } else {
                        echo "<span style='color: #ffffff; font-size: 24px; font-weight: bold;'>22 SHOW</span>";
                    }
                ?>
            </div>
            <p class="shop-subtitle">[ Collection Gallery & Showroom ]</p>
        </div>

        <!-- فلتر البحث والتصنيفات -->
        <div class="shop-filters">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="shopSearch" onkeyup="filterProducts()" placeholder="Search in all languages...">
            </div>
            <div class="category-filters" id="categoryFilters">
                <button class="cat-btn active" data-cat="all" onclick="filterCategory('all')"><i class="fas fa-border-all"></i> <span class="cat-label">All</span></button>
                <button class="cat-btn" data-cat="shoes" onclick="filterCategory('shoes')"><i class="fas fa-shoe-prints"></i> <span class="cat-label">Shoes</span></button>
                <button class="cat-btn" data-cat="clothing" onclick="filterCategory('clothing')"><i class="fas fa-tshirt"></i> <span class="cat-label">Clothing</span></button>
                <button class="cat-btn" data-cat="tshirt" onclick="filterCategory('tshirt')"><i class="fas fa-tshirt"></i> <span class="cat-label">T-Shirt</span></button>
                <button class="cat-btn" data-cat="shirts" onclick="filterCategory('shirts')"><i class="fas fa-user-tie"></i> <span class="cat-label">Shirts</span></button>
                <button class="cat-btn" data-cat="trousers" onclick="filterCategory('trousers')"><i class="fas fa-person"></i> <span class="cat-label">Trousers</span></button>
                <button class="cat-btn" data-cat="jeans" onclick="filterCategory('jeans')"><i class="fas fa-person"></i> <span class="cat-label">Jeans</span></button>
                <button class="cat-btn" data-cat="shorts" onclick="filterCategory('shorts')"><i class="fas fa-person-walking-dashed-line-arrow-right"></i> <span class="cat-label">Shorts</span></button>
                <button class="cat-btn" data-cat="perfume" onclick="filterCategory('perfume')"><i class="fas fa-spray-can"></i> <span class="cat-label">Perfume</span></button>
                <button class="cat-btn" data-cat="watch" onclick="filterCategory('watch')"><i class="fas fa-clock"></i> <span class="cat-label">Watches</span></button>
                <button class="cat-btn" data-cat="accessories" onclick="filterCategory('accessories')"><i class="fas fa-gem"></i> <span class="cat-label">Accessories</span></button>
                <button class="cat-btn" data-cat="glasses" onclick="filterCategory('glasses')"><i class="fas fa-glasses"></i> <span class="cat-label">Glasses</span></button>
                <button class="cat-btn" data-cat="hats" onclick="filterCategory('hats')"><i class="fas fa-hat-cowboy"></i> <span class="cat-label">Hats</span></button>
            </div>
        </div>

        <!-- شبكة المنتجات (تُجلب ديناميكياً من السكربت المنفصل) -->
        <div id="shopContent">
            <?php include __DIR__ . '/load-products.php'; ?>
        </div>

        <!-- ================= نافذة خيارات الطلب المنبثقة (Order Options Modal) ================= -->
        <div id="orderModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                
                <h3 id="modalProductTitle">اسم المنتج</h3>
                <p id="modalProductPrice" class="price">00,000 د.ع</p>
                
                <div class="modal-form">
                    <!-- حقل خيار المقاس -->
                    <div class="form-group" id="sizeGroup">
                        <label id="lblSize" data-badini="قیاس:" data-sorani="قەبارە:" data-arabic="القياس:" data-english="Size:">القياس:</label>
                        <select id="prodSize"></select>
                    </div>

                    <!-- حقل اختيار الكمية -->
                    <div class="form-group">
                        <label id="lblQty" data-badini="چەند دانە:" data-sorani="ژمارەی دانە:" data-arabic="الكمية:" data-english="Quantity:">الكمية:</label>
                        <div class="quantity-control">
                            <button type="button" onclick="updateQty(-1)">-</button>
                            <input type="number" id="prodQty" value="1" min="1" readonly>
                            <button type="button" onclick="updateQty(1)">+</button>
                        </div>
                    </div>

                    <!-- زر التأكيد النهائي والإرسال للواتساب -->
                    <button class="order-btn" onclick="submitToWhatsApp()">
                        <i class="fa-brands fa-whatsapp"></i> <span id="btnModalConfirm">تأكيد الطلب وإرسال</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= نافذة عرض الصور (Image Gallery Modal) ================= -->
        <div id="imageModal" class="modal">
            <div class="modal-content" style="max-width: 90%; max-height: 90vh; text-align: center; background: transparent; box-shadow: none; padding: 0;">
                <span class="close-modal" onclick="closeImageModal()" style="color: #fff; background: rgba(0,0,0,0.5); padding: 5px 10px; border-radius: 5px; right: 10px; top: 10px;">&times;</span>
                <img id="enlargedImg" src="" alt="Enlarged Product" style="max-width: 100%; max-height: 85vh; border-radius: 8px; object-fit: contain; background: #000;">
            </div>
        </div>

        <!-- الفوتر -->
        <footer class="shop-copyright">
            <p id="copyrightText">&copy; <?php echo date('Y'); ?> <strong>22 Show</strong>. All rights reserved.</p>
            <p class="policy-links">
                <a href="../privacy.php" id="privacyLink">Privacy Policy</a> | 
                <a href="../terms.php" id="termsLink">Terms of Service</a>
            </p>
        </footer>

    </div>

    <script src="shop-script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
