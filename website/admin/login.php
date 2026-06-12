<?php
session_start();

$admins = [
    'admin123' => 'Super Admin',
    'pass123' => 'Store Manager',
    'shop2026' => 'Shop Assistant'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (isset($admins[$password])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $admins[$password];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>22 Show - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #222 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(20, 20, 30, 0.9);
            border: 1px solid rgba(255, 140, 0, 0.3);
            border-radius: 15px;
            padding: 50px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header i {
            font-size: 50px;
            color: #ff8c00;
            margin-bottom: 15px;
        }
        
        .login-header h1 {
            color: #fff;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .login-header p {
            color: #999;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 140, 0, 0.2);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #ff8c00;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 15px rgba(255, 140, 0, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 140, 0, 0.3);
        }
        
        .error-message {
            background: rgba(255, 59, 48, 0.1);
            border-left: 3px solid #ff3b30;
            color: #ff6b6b;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <i class="fas fa-lock"></i>
        <h1>Admin Panel</h1>
        <p>22 Show Management</p>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="password">Admin Password</label>
            <input type="password" id="password" name="password" placeholder="Enter admin password" required autofocus>
        </div>
        <button type="submit" class="login-btn">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>
</div>

<script src="admin-translate.js?v=<?php echo time(); ?>"></script>
</body>
</html>