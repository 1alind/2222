<?php
// Load admin settings for colors and logo if needed
$settingsFile = __DIR__ . '/admin/data/store_settings.json';
$settings = ['storeName' => '22 Show', 'currency' => 'IQD', 'primaryColor' => '#ffffff', 'accentColor' => '#ff3b30', 'logoUrl' => '', 'address' => 'زاخۆ، تاخێ سەرهلدان'];
if(file_exists($settingsFile)) {
    $raw = file_get_contents($settingsFile);
    $parsed = json_decode($raw, true);
    if($parsed) {
        $settings = array_merge($settings, $parsed);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Privacy Policy - <?php echo htmlspecialchars($settings['storeName']); ?></title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script>
        const staticTranslations = <?php
            $langFile = __DIR__ . '/shop/system_lang.json';
            echo file_exists($langFile) ? file_get_contents($langFile) : '{}';
        ?>;
    </script>
    <style>
        .page-content {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            padding: 30px;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: var(--shadow);
            text-align: left;
        }
        
        .page-content p {
            color: var(--text-color);
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .back-home {
            display: inline-block;
            margin-top: 20px;
            color: #aaa;
            text-decoration: none;
            font-size: 14px;
        }
        
        body.lang-badini .page-content, body.lang-sorani .page-content, body.lang-arabic .page-content {
            text-align: right;
            direction: rtl;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="lang-switcher" style="display: flex; justify-content: flex-end; gap: 5px; margin-bottom: 15px;">
            <button class="lang-btn" onclick="switchLanguage('badini')" style="background: none; border: none; color: #aaa; cursor: pointer;">بادیني</button>
            <button class="lang-btn" onclick="switchLanguage('sorani')" style="background: none; border: none; color: #aaa; cursor: pointer;">سۆرانی</button>
            <button class="lang-btn" onclick="switchLanguage('arabic')" style="background: none; border: none; color: #aaa; cursor: pointer;">العربية</button>
            <button class="lang-btn" onclick="switchLanguage('english')" style="background: none; border: none; color: #aaa; cursor: pointer;">English</button>
        </div>

        <div class="logo-container">
        <?php
            if(!empty($settings['logoUrl'])) {
                echo '<img src="'.htmlspecialchars($settings['logoUrl']).'" alt="Logo" class="main-logo">';
            } else {
                $logoPath = '22show_logo.jpeg';
                echo '<img src="'.$logoPath.'" alt="22 Show Logo" class="main-logo">';
            }
        ?>
        </div>

        <div class="page-content">
            <h2 id="lang-privacyTitle" style="margin-bottom: 20px;">Privacy Policy</h2>
            <p id="lang-privacyContent">At 22 Show, we are committed to protecting your privacy. We collect personal information (such as your name, phone number, and address) solely to process your orders and handle delivery. Your information will not be shared with unauthorized third parties. We use secure methods to ensure your data is safe with us.</p>
            
            <a href="index.php" class="back-home" id="lang-backBtn">Back to Home</a>
        </div>
    </div>
    
    <script>
    <?php
        $scriptPath = './script.js';
        if (file_exists($scriptPath)) {
            echo file_get_contents($scriptPath);
        }
    ?>
    </script>
</body>
</html>
