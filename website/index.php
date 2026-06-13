<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        $settingsFile = __DIR__ . '/data/settings.json';
        $settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
        echo htmlspecialchars($settings['store_name'] ?? '22 Show'); 
    ?> - Links</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script>
        const storeSettings = <?php echo json_encode($settings); ?>;
    </script>
</head>
<body>

    <div class="container">

        <div class="logo-container">
        <?php
            $logop = './logo.txt';
            if (file_exists($logop)) {
                echo file_get_contents($logop);
            } else {
                echo "// Error: Script file not found at $logop";
            }
        ?>
        </div>

        <div class="header-container">
            <h1><?php echo htmlspecialchars($settings['store_name'] ?? '22 Show'); ?></h1> 
            <span id="emoji-slider">👕</span>
        </div>
        
        <div class="description">
            <?php echo nl2br(htmlspecialchars(str_replace('<br>', "\n", $settings['description'] ?? "بو فروتنا جل و بەرگێن گەنجان\nدهوك - تاخێ سەرهلدان، نێزیك پاركا سەرهلدان"))); ?>
        </div>

<div id="btnstbl">
    <button class="link-card btn1" onclick="openUrl('whatsapp')">
        <img src="https://1alind.sirv.com/Images/whatsapp_logo.png" alt="WA">
        <span class="link-text">WhatsApp</span>
    </button>

    <button class="link-card btn2" onclick="openUrl('instagram')">
        <img src="https://1alind.sirv.com/Images/instagram.png" alt="IG">
        <span class="link-text">Instagram</span>
    </button>

    <button class="link-card btn3" onclick="openUrl('tiktok')">
        <img src="https://1alind.sirv.com/Images/tiktok_logo.png" alt="TT">
        <span class="link-text">TikTok</span>
    </button>

    <button class="link-card btn4" onclick="openUrl('snapchat')">
        <img src="https://1alind.sirv.com/Images/snapchat_logo.png" alt="SC">
        <span class="link-text">Snapchat</span>
    </button>

    <button class="link-card btn5" onclick="saveContact()">
        <svg width="25px" height="25px" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
    <circle cx="32" cy="32" r="30" fill="#ffffff"/>

    <circle cx="25" cy="24" r="6" fill="none" stroke="#000000" stroke-width="3"/>

    <path d="M15 40c0-6 4-10 10-10s10 4 10 10"
          fill="none"
          stroke="#000000"
          stroke-width="3"
          stroke-linecap="round"/>

    <line x1="44" y1="24" x2="44" y2="36"
          stroke="#000000"
          stroke-width="3"
          stroke-linecap="round"/>

    <line x1="38" y1="30" x2="50" y2="30"
          stroke="#000000"
          stroke-width="3"
          stroke-linecap="round"/>
</svg>
        <span class="link-text">Save Contact</span>
    </button>

    <button class="link-card btn6" onclick="openUrl('applemaps')">
        <img src="https://1alind.sirv.com/Images/AppleMaps_logo.png" alt="AP">
        <span class="link-text">Apple Maps</span>
    </button>

    <button class="link-card btn7" onclick="openUrl('googlemaps')">
        <img src="https://1alind.sirv.com/Images/GoogleMaps_logo.png" alt="GM">
        <span class="link-text">Google Maps</span>
    </button>

<button class="link-card brn8" onclick="openUrl('shop')">
        <span class="btn-icon">🖼️</span>
        
        <div class="btn-text-container">
            <span class="link-text-shop">Collection Gallery & Showroom</span>
            <span class="beta-subtext">only available in Kurdistan region, republic of Iraq 🇮🇶.</span>
        </div>
    </button>

</div>

        <div class="map-container">
            <iframe src="https://maps.google.com/maps?width=600&height=400&hl=en&q=22show&t=&z=15&ie=UTF8&iwloc=B&output=embed" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>

        <div class="footer-location">
            .تاخێ سەرهلدان
            <i class="fa-solid fa-location-dot"></i>
        </div>

       <footer class="copyright-section">
    <p>&copy; <?php echo date('Y'); ?> <strong>22 Show</strong>. All rights reserved.</p>
    <p style="margin-top: 5px; font-size: 11px;">
        <a href="privacy.php" style="color: #777; text-decoration: none;">Privacy Policy</a> | 
        <a href="terms.php" style="color: #777; text-decoration: none;">Terms of Service</a>
    </p>
</footer>

        </div>

    <script>
    <?php
        $scriptPath = './script.js';
        if (file_exists($scriptPath)) {
            echo file_get_contents($scriptPath);
        } else {
            echo "// Error: Script file not found at $scriptPath";
        }
    ?>
    </script>

</body>
</html>
