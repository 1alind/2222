<!DOCTYPE html>
<html lang="ku">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        $settingsFile = __DIR__ . '/../data/settings.json';
        $settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
        echo htmlspecialchars($settings['store_name'] ?? '22 Show'); 
    ?> - Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="shop-style.css?v=<?php echo time(); ?>">
    <script>
        const storeSettings = <?php echo json_encode($settings); ?>;
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
            <p class="shop-subtitle">[ Online Shopping - BETA ]</p>
        </div>

        <!-- شبكة المنتجات (تُجلب ديناميكياً من السكربت المنفصل) -->
        <div class="products-grid">
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
