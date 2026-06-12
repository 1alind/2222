<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

define('PRODUCTS_FILE', __DIR__ . '/../shop/products.json');

function loadProducts() {
    if (file_exists(PRODUCTS_FILE)) {
        return json_decode(file_get_contents(PRODUCTS_FILE), true) ?: [];
    }
    return [];
}

function saveProducts($products) {
    return file_put_contents(PRODUCTS_FILE, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $products = loadProducts();
        
        $id = sanitizeId($_POST['id'] ?? '');
        $type = $_POST['type'] ?? 'general';
        $badge = $_POST['badge'] ?? '';
        $price = $_POST['price'] ?? '';
        
        // Check if ID already exists
        if (array_key_exists($id, array_flip(array_column($products, 'id')))) {
            $error = 'Product ID already exists!';
        } else {
            $product = [
                'id' => $id,
                'type' => $type,
                'badge' => $badge,
                'price' => $price,
                'sizes' => isset($_POST['sizes']) && is_array($_POST['sizes']) ? $_POST['sizes'] : [],
                'images' => [],
                'created_at' => time(),
                'added_by' => $_SESSION['admin_name'] ?? 'Unknown',
                'title' => [
                    'badini' => $_POST['title_badini'] ?? '',
                    'sorani' => $_POST['title_sorani'] ?? '',
                    'arabic' => $_POST['title_arabic'] ?? '',
                    'english' => $_POST['title_english'] ?? ''
                ],
                'desc' => [
                    'badini' => $_POST['desc_badini'] ?? '',
                    'sorani' => $_POST['desc_sorani'] ?? '',
                    'arabic' => $_POST['desc_arabic'] ?? '',
                    'english' => $_POST['desc_english'] ?? ''
                ]
            ];
            
            // Handle image uploads
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    if (!empty($tmpName)) {
                        $fileName = time() . '_' . uniqid() . '_' . basename(preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['images']['name'][$key]));
                        $targetPath = $uploadDir . $fileName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $product['images'][] = 'uploads/' . $fileName;
                        }
                    }
                }
            }
            
            $products[] = $product;
            
            if (saveProducts($products)) {
                $success = 'Product added successfully!';
                header('refresh:2;url=products.php');
            } else {
                $error = 'Failed to save product';
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

function sanitizeId($str) {
    return preg_replace('/[^a-z0-9_-]/', '_', strtolower($str));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - 22 Show Admin</title>
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
            <h1>Add New Product</h1>
            <div class="topbar-right">
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <!-- CONTENT -->
        <div class="form-wrapper">
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?> Redirecting...
                </div>
            <?php endif; ?>
            
            <form method="POST" class="product-form" enctype="multipart/form-data">
                
                <!-- BASIC INFO -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Product ID *</label>
                            <input type="text" name="id" placeholder="e.g., prod_shoes" required>
                            <small>Unique identifier (no spaces)</small>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" required onchange="handleTypeChange(this)">
                                <option value="general">General</option>
                                <option value="shoes">Shoes</option>
                                <option value="perfume">Perfume</option>
                                <option value="watch">Watch</option>
                                <option value="clothing">Clothing</option>
                                <option value="tshirt">T-Shirt</option>
                                <option value="shirts">Shirts</option>
                                <option value="trousers">Trousers</option>
                                <option value="jeans">Jeans</option>
                                <option value="shorts">Shorts</option>
                                <option value="accessories">Accessories</option>
                                <option value="glasses">Glasses</option>
                                <option value="hats">Hats</option>
                            </select>
                        </div>
                        <div class="form-group" id="optionsGroup" style="display: none;">
                            <label>Available Sizes</label>
                            <div id="sizeCheckboxes" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px;"></div>
                        </div>
                        <div class="form-group">
                            <label>Badge</label>
                            <input type="text" name="badge" placeholder="e.g., NEW, BEST, LUXURY">
                        </div>
                        <div class="form-group">
                            <label>Price *</label>
                            <input type="text" name="price" placeholder="e.g., 45,000 د.ع" required>
                        </div>
                    </div>
                </div>
                
                <!-- TITLES -->
                <div class="form-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h2 style="margin: 0;"><i class="fas fa-heading"></i> Titles (Multi-language)</h2>
                        <button type="button" class="btn btn-secondary" onclick="autoTranslate('title')" style="padding: 5px 15px; font-size: 13px;">
                            <i class="fas fa-language"></i> Auto Translate
                        </button>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Title (Badini) *</label>
                            <input type="text" name="title_badini" id="title_badini" required>
                        </div>
                        <div class="form-group">
                            <label>Title (Sorani)</label>
                            <input type="text" name="title_sorani" id="title_sorani">
                        </div>
                        <div class="form-group">
                            <label>Title (Arabic)</label>
                            <input type="text" name="title_arabic" id="title_arabic">
                        </div>
                        <div class="form-group">
                            <label>Title (English) *</label>
                            <input type="text" name="title_english" id="title_english" required>
                        </div>
                    </div>
                </div>
                
                <!-- DESCRIPTIONS -->
                <div class="form-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h2 style="margin: 0;"><i class="fas fa-file-alt"></i> Descriptions</h2>
                        <button type="button" class="btn btn-secondary" onclick="autoTranslate('desc')" style="padding: 5px 15px; font-size: 13px;">
                            <i class="fas fa-language"></i> Auto Translate
                        </button>
                    </div>
                    <p class="section-desc">Write everything here including sizes or anything else. Press Enter to go to the next line.</p>
                    
                    <div class="form-row">
                        <div class="form-group" style="width: 100%;">
                            <label>Description (Badini) *</label>
                            <textarea name="desc_badini" id="desc_badini" rows="3" required></textarea>
                        </div>
                        <div class="form-group" style="width: 100%;">
                            <label>Description (Sorani)</label>
                            <textarea name="desc_sorani" id="desc_sorani" rows="3"></textarea>
                        </div>
                        <div class="form-group" style="width: 100%;">
                            <label>Description (Arabic)</label>
                            <textarea name="desc_arabic" id="desc_arabic" rows="3"></textarea>
                        </div>
                        <div class="form-group" style="width: 100%;">
                            <label>Description (English) *</label>
                            <textarea name="desc_english" id="desc_english" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- IMAGES -->
                <div class="form-section">
                    <h2><i class="fas fa-images"></i> Product Images</h2>
                    <p class="section-desc">Upload one or more images</p>
                    
                    <div class="form-group">
                        <input type="file" name="images[]" accept="image/*" multiple required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 8px; color: #fff;">
                    </div>
                </div>
                
                <!-- FORM ACTIONS -->
                <div class="form-actions">
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function autoTranslate(prefix) {
    const fields = ['english', 'arabic', 'badini', 'sorani'];
    let sourceText = '';
    let sourceLang = '';
    
    for (const lang of fields) {
        const val = document.getElementById(`${prefix}_${lang}`).value.trim();
        if (val) {
            sourceText = val;
            sourceLang = lang;
            break;
        }
    }
    
    if (!sourceText) {
        alert('Please fill at least one language field to translate from.');
        return;
    }
    
    // Disable button and show loading text
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Translating...';
    btn.disabled = true;
    
    const langMap = {
        'english': 'en',
        'arabic': 'ar',
        'badini': 'ku',
        'sorani': 'ku'
    };
    
    const fromLang = langMap[sourceLang];
    
    try {
        for (const target of fields) {
            if (target === sourceLang) continue;
            
            // Don't overwrite if it already has text
            if (document.getElementById(`${prefix}_${target}`).value.trim()) continue;
            
            const toLang = langMap[target];
            const res = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(sourceText)}&langpair=${fromLang}|${toLang}`);
            const data = await res.json();
            
            if (data && data.responseData && data.responseData.translatedText) {
                document.getElementById(`${prefix}_${target}`).value = data.responseData.translatedText;
            }
        }
    } catch(e) {
        console.error('Translation failed', e);
        alert('Translation failed. Please try again.');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function handleTypeChange(select) {
    const type = select.value;
    const group = document.getElementById('optionsGroup');
    const container = document.getElementById('sizeCheckboxes');
    container.innerHTML = '';
    
    let options = [];
    if (type === 'shoes') {
        options = ["36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47"];
    } else if (['clothing', 'tshirt', 'shirts', 'shorts'].includes(type)) {
        options = ["XXS", "XS", "S", "M", "L", "XL", "XXL", "3XL", "4XL", "5XL", "6XL"];
    } else if (['jeans', 'trousers'].includes(type)) {
        options = ["28", "29", "30", "31", "32", "33", "34", "36", "38", "40", "42"];
    } else if (type === 'perfume') {
        options = ["30ml", "50ml", "75ml", "100ml", "125ml", "150ml", "200ml"];
    }
    
    if (options.length > 0) {
        group.style.display = 'block';
        options.forEach(opt => {
            const label = document.createElement('label');
            label.style.display = 'inline-flex';
            label.style.alignItems = 'center';
            label.style.justifyContent = 'center';
            label.style.background = 'rgba(255,255,255,0.05)';
            label.style.border = '1px solid rgba(255,255,255,0.1)';
            label.style.padding = '8px 16px';
            label.style.borderRadius = '8px';
            label.style.cursor = 'pointer';
            label.style.fontWeight = 'bold';
            label.style.transition = 'all 0.2s';
            label.style.userSelect = 'none';
            label.style.minWidth = '50px';
            
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.name = 'sizes[]';
            cb.value = opt;
            cb.style.display = 'none'; // Hide the actual checkbox
            
            cb.addEventListener('change', function() {
                if (this.checked) {
                    label.style.background = 'rgba(255, 140, 0, 0.2)';
                    label.style.borderColor = '#ff8c00';
                    label.style.color = '#ff8c00';
                    label.style.boxShadow = '0 0 10px rgba(255, 140, 0, 0.2)';
                } else {
                    label.style.background = 'rgba(255,255,255,0.05)';
                    label.style.borderColor = 'rgba(255,255,255,0.1)';
                    label.style.color = '';
                    label.style.boxShadow = 'none';
                }
            });
            
            label.appendChild(cb);
            label.appendChild(document.createTextNode(opt));
            container.appendChild(label);
        });
    } else {
        group.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', () => handleTypeChange(document.querySelector('select[name="type"]')));
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}
</script>

<script src="admin-translate.js?v=<?php echo time(); ?>"></script>
</body>
</html>