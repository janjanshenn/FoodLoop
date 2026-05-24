<?php
// Establish database connection directly without setting JSON API headers
$host     = 'localhost';
$db_name  = 'foodloop_db';
$db_user  = 'root';
$db_pass  = '';

try {
    $auth_pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $auth_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $auth_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Check if the is_featured column exists, auto-create if not
    $checkColumn = $auth_pdo->query("SHOW COLUMNS FROM menu LIKE 'is_featured'")->fetch();
    if (!$checkColumn) {
        $auth_pdo->exec("ALTER TABLE menu ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
        // Seed default featured items to avoid empty carousel on fresh startup
        $auth_pdo->exec("UPDATE menu SET is_featured = 1 WHERE name IN ('Classic Pork Adobo', 'Sinigang na Baboy', 'Sizzling Sisig', 'Lumpiang Shanghai')");
    }

    // Fetch featured items (limit to 8)
    $stmtFeatured = $auth_pdo->query("SELECT * FROM menu WHERE is_featured = 1 LIMIT 8");
    $featuredItems = $stmtFeatured->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featuredItems = [];
}
?>
<!-- FOODLoop – templates/auth.php (Home Screen & Authentication Forms) -->
<div id="screen-home" class="container screen-home-container">

    <div class="home-bg" style="background-image: url('img/Screenshot%202026-03-13%20152317.png');"></div>
    <div class="home-overlay"></div>

    <div class="home-header-top">
        <img src="img/Logo.png" alt="FOODLoop Logo" class="home-logo-centered" onerror="this.src='img/Logo.png'">
    </div>

    <div class="home-main-content">
        <div class="home-carousel-section">
            <div class="card-slider-container">
                <h2 class="ulam-title">Featured Menu</h2>
                <div class="card-track">
                    <?php if (empty($featuredItems)): ?>
                        <!-- Fallback static items if none are selected yet -->
                        <div class="food-card" style="--i: 0;">
                            <img src="img/sizzling-pork-sisig-manila-main.webp" alt="Pork Sisig">
                            <div class="food-card-info">
                                <span class="food-card-name">Sizzling Sisig</span>
                                <span class="food-card-price">₱150.00</span>
                            </div>
                        </div>
                        <div class="food-card" style="--i: 1;">
                            <img src="img/chicken adobo.jpg" alt="Chicken Adobo">
                            <div class="food-card-info">
                                <span class="food-card-name">Chicken Adobo</span>
                                <span class="food-card-price">₱50.00</span>
                            </div>
                        </div>
                        <div class="food-card" style="--i: 2;">
                            <img src="img/Killer-Pork-Sinigang.jpg" alt="Sinigang">
                            <div class="food-card-info">
                                <span class="food-card-name">Pork Sinigang</span>
                                <span class="food-card-price">₱60.00</span>
                            </div>
                        </div>
                        <div class="food-card" style="--i: 3;">
                            <img src="img/kare-kare-beef-tripe-500x500.webp" alt="Kare-Kare">
                            <div class="food-card-info">
                                <span class="food-card-name">Beef Kare-Kare</span>
                                <span class="food-card-price">₱65.00</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($featuredItems as $index => $item): ?>
                            <?php
                            $defaultImages = [
                                'Classic Pork Adobo' => 'img/chicken adobo.jpg',
                                'Sinigang na Baboy' => 'img/Killer-Pork-Sinigang.jpg',
                                'Pancit Canton Espesyal' => 'img/pancit.jpg',
                                'Lumpiang Shanghai' => 'img/lumpia.webp',
                                'Extra Rice' => 'img/rice.webp',
                                'Sprite (Bottle)' => 'img/Sprite.png',
                                'Coca-Cola (Bottle)' => 'img/Coke.webp',
                                'Sizzling Sisig' => 'img/sizzling-pork-sisig-manila-main.webp',
                                'Chopsuey' => 'img/ChopSuey.jpg',
                                'Bicol Express' => 'img/Bicol-Express.jpg',
                                'Beef Bulalo Soup' => 'img/bulalo.jpg'
                            ];
                            $imgPath = isset($defaultImages[$item['name']]) ? $defaultImages[$item['name']] : 'img/Logo.png';
                            if (!empty($item['image'])) {
                                $imgPath = 'uploads/' . $item['image'];
                            }
                            ?>
                            <div class="food-card" style="--i: <?php echo $index; ?>;">
                                <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="food-card-info">
                                    <span class="food-card-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="food-card-price">₱<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="home-admin-section">
            <!-- LOGIN FORM -->
            <div class="admin-login-card" id="form-login">
                <h2 class="admin-title">LOGIN</h2>
                <form onsubmit="event.preventDefault(); loginUser();">
                    <div class="login-form-container">
                        <label class="form-label">Username</label>
                        <input type="text" id="login-username" placeholder="Enter username" required
                            class="form-input-full">

                        <label class="form-label-spaced">Password</label>
                        <input type="password" id="login-password" placeholder="••••••••" required
                            class="form-input-full">

                        <button type="submit" class="form-button-full mt-25">Login</button>
                    </div>
                </form>
                <div style="text-align:center; margin-top:15px; font-size:14px;">
                    <a href="#" onclick="toggleAuthForm('form-register')"
                        style="color:#2ecc71; text-decoration:none; margin-right:15px;">Register</a>
                    <a href="#" onclick="toggleAuthForm('form-reset')"
                        style="color:#bdc3c7; text-decoration:none;">Forgot Password?</a>
                </div>
            </div>

            <!-- REGISTER FORM -->
            <div class="admin-login-card hidden" id="form-register">
                <h2 class="admin-title">REGISTER</h2>
                <form onsubmit="event.preventDefault(); registerUser();">
                    <div class="login-form-container">
                        <div id="reg-error-msg" style="color: #e74c3c; font-size: 14px; margin-bottom: 10px; text-align: center; display: none;"></div>
                        
                        <label class="form-label">Username</label>
                        <input type="text" id="reg-username" placeholder="Choose a username" required
                            class="form-input-full">

                        <label class="form-label-spaced">Email Address</label>
                        <input type="email" id="reg-email" placeholder="Enter your email" required
                            class="form-input-full">

                        <label class="form-label-spaced">Password</label>
                        <input type="password" id="reg-password" placeholder="Min. 8 characters" required
                            class="form-input-full">

                        <label class="form-label-spaced">Confirm Password</label>
                        <input type="password" id="reg-confirm-password" placeholder="Confirm password" required
                            class="form-input-full">

                        <button type="submit" class="form-button-full mt-25"
                            style="background-color:#2980b9;">Create Account</button>
                    </div>
                </form>
                <div style="text-align:center; margin-top:15px; font-size:14px;">
                    <a href="#" onclick="toggleAuthForm('form-login')"
                        style="color:#bdc3c7; text-decoration:none;">Back to Login</a>
                </div>
            </div>

            <!-- RESET PASSWORD FORM -->
            <div class="admin-login-card hidden" id="form-reset">
                <h2 class="admin-title">RESET PASSWORD</h2>
                
                <!-- Step 1: Request OTP -->
                <form id="reset-step1" onsubmit="event.preventDefault(); requestResetOTP();">
                    <div class="login-form-container">
                        <label class="form-label">Email Address</label>
                        <input type="email" id="reset-email" placeholder="Enter your email" required
                            class="form-input-full">

                        <button type="submit" class="form-button-full mt-25" style="background-color:#e67e22;">Send OTP</button>
                    </div>
                </form>

                <!-- Step 2: Verify OTP and set new password -->
                <form id="reset-step2" class="hidden" onsubmit="event.preventDefault(); submitNewPassword();">
                    <div class="login-form-container">
                        <label class="form-label">Enter OTP</label>
                        <input type="text" id="reset-otp" placeholder="6-digit OTP" required
                            class="form-input-full">

                        <label class="form-label-spaced">New Password</label>
                        <input type="password" id="reset-new-password" placeholder="Min. 8 characters" required
                            class="form-input-full">

                        <button type="submit" class="form-button-full mt-25" style="background-color:#2ecc71;">Save Password</button>
                    </div>
                </form>
                <div style="text-align:center; margin-top:15px; font-size:14px;">
                    <a href="#" onclick="toggleAuthForm('form-login')"
                        style="color:#bdc3c7; text-decoration:none;">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
