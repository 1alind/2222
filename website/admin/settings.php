<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$settingsFile = __DIR__ . '/../data/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['store_name'] = $_POST['store_name'] ?? '';
    $settings['whatsapp_number'] = $_POST['whatsapp_number'] ?? '';
    $settings['instagram'] = $_POST['instagram'] ?? '';
    $settings['tiktok'] = $_POST['tiktok'] ?? '';
    $settings['snapchat'] = $_POST['snapchat'] ?? '';
    $settings['apple_maps'] = $_POST['apple_maps'] ?? '';
    $settings['google_maps'] = $_POST['google_maps'] ?? '';
    $settings['latitude'] = $_POST['latitude'] ?? '';
    $settings['longitude'] = $_POST['longitude'] ?? '';
    $settings['description'] = $_POST['description'] ?? '';

    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    $successMessage = "Settings saved successfully!";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>22 Show - Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css?v=<?php echo time(); ?>">
    <style>
        .settings-form {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            max-width: 800px;
            width: 100%;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
        }
        .form-input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 0 2px rgba(0, 229, 255, 0.1);
        }
        .save-btn {
            background: var(--neon-cyan);
            color: #000;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: opacity 0.2s;
            margin-top: 10px;
        }
        .save-btn:hover {
            opacity: 0.9;
        }
        .success-msg {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border: 1px solid #4caf50;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="admin-container">

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
        <div class="admin-title">Store Settings</div>
        <div class="admin-subtitle">[ Configuration ]</div>
    </div>

    <div class="back-btn-wrapper">
        <a href="index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> <span>Back to Dashboard</span>
        </a>
    </div>

    <div class="settings-form">
        <?php if (!empty($successMessage)): ?>
            <div class="success-msg"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="settings.php">
            <div class="form-group">
                <label class="form-label">Store Name</label>
                <input type="text" name="store_name" class="form-input" value="<?php echo htmlspecialchars($settings['store_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">WhatsApp Number (e.g. 9647514333621)</label>
                <input type="text" name="whatsapp_number" class="form-input" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Instagram URL</label>
                <input type="text" name="instagram" class="form-input" value="<?php echo htmlspecialchars($settings['instagram'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">TikTok URL</label>
                <input type="text" name="tiktok" class="form-input" value="<?php echo htmlspecialchars($settings['tiktok'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Snapchat URL</label>
                <input type="text" name="snapchat" class="form-input" value="<?php echo htmlspecialchars($settings['snapchat'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Apple Maps URL</label>
                <input type="text" name="apple_maps" class="form-input" value="<?php echo htmlspecialchars($settings['apple_maps'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Google Maps URL</label>
                <input type="text" name="google_maps" class="form-input" value="<?php echo htmlspecialchars($settings['google_maps'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Latitude (for Save Contact feature)</label>
                <input type="text" name="latitude" class="form-input" value="<?php echo htmlspecialchars($settings['latitude'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Longitude (for Save Contact feature)</label>
                <input type="text" name="longitude" class="form-input" value="<?php echo htmlspecialchars($settings['longitude'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description (HTML allowed)</label>
                <input type="text" name="description" class="form-input" value="<?php echo htmlspecialchars($settings['description'] ?? ''); ?>">
            </div>

            <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save Settings</button>
        </form>
    </div>

</div>

</body>
</html>
