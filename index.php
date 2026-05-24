<?php
// ─────────────────────────────────────────────────────────────
// FOODLoop – index.php (Main Dynamic App Shell)
// ─────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Strict'
    ]);
    session_start();
}

$loggedIn = false;
$username = '';
$role = '';

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    $loggedIn = true;
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOODLoop - Digital Cafeteria Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300..700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Style.css?v=1.8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <!-- jsPDF & SheetJS CDNs for Export PDF & Excel features -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>

    <!-- 1. AUTH SCREEN -->
    <?php include 'templates/auth.php'; ?>

    <!-- 2. ADMIN/STAFF APP LAYOUT -->
    <div id="app-layout" class="app-layout hidden">
        <?php include 'templates/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include 'templates/tab_dashboard.php'; ?>
            <?php include 'templates/tab_menu.php'; ?>
            <?php include 'templates/tab_stock.php'; ?>
            <?php include 'templates/tab_pos.php'; ?>
            <?php include 'templates/tab_reports.php'; ?>
            <?php include 'templates/tab_reservations.php'; ?>
            <?php include 'templates/tab_cooking.php'; ?>
            <?php include 'templates/tab_admin_feedback.php'; ?>
        </div>
    </div>

    <!-- 3. CUSTOMER APP LAYOUT -->
    <div id="customer-layout" class="app-layout hidden">
        <?php include 'templates/sidebar_customer.php'; ?>

        <div class="main-content">
            <?php include 'templates/tab_customer_menu.php'; ?>
            <?php include 'templates/tab_customer_reservations.php'; ?>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Floating Feedback Button -->
    <button id="floating-rate-btn" class="floating-feedback-btn hidden" onclick="openFeedbackModal()">
        <span>⭐</span>
        <span>RATE US</span>
    </button>

    <!-- Feedback Popup Modal -->
    <div id="feedback-modal" class="feedback-modal-overlay">
        <div class="feedback-modal-card">
            <button class="feedback-close-btn" onclick="closeFeedbackModal()">✕</button>
            <div class="feedback-modal-header">
                <span class="feedback-modal-icon">⭐</span>
                <h3 class="feedback-modal-title">Rate Your Experience</h3>
                <p class="feedback-modal-desc">We value your thoughts about FOODLoop!</p>
            </div>
            <form id="feedback-form" onsubmit="submitFeedbackForm(event)">
                <label for="feedback-name">Your Name</label>
                <input type="text" id="feedback-name" placeholder="Enter your name" required>

                <label style="margin-top: 10px; display: block;">Your Rating</label>
                <div class="star-rating-selector" id="feedback-stars">
                    <span class="star-icon" data-value="1" onclick="setFeedbackRating(1)" onmouseover="highlightStars(1)" onmouseout="resetStarsHighlight()">★</span>
                    <span class="star-icon" data-value="2" onclick="setFeedbackRating(2)" onmouseover="highlightStars(2)" onmouseout="resetStarsHighlight()">★</span>
                    <span class="star-icon" data-value="3" onclick="setFeedbackRating(3)" onmouseover="highlightStars(3)" onmouseout="resetStarsHighlight()">★</span>
                    <span class="star-icon" data-value="4" onclick="setFeedbackRating(4)" onmouseover="highlightStars(4)" onmouseout="resetStarsHighlight()">★</span>
                    <span class="star-icon" data-value="5" onclick="setFeedbackRating(5)" onmouseover="highlightStars(5)" onmouseout="resetStarsHighlight()">★</span>
                </div>
                <input type="hidden" id="feedback-rating-value" value="0">

                <label for="feedback-comments">Comments (Optional)</label>
                <textarea id="feedback-comments" placeholder="Describe your dining experience, service, or food recommendations..."></textarea>

                <div class="feedback-actions">
                    <button type="button" class="btn-secondary" onclick="closeFeedbackModal()">Cancel</button>
                    <button type="submit" id="feedback-submit-btn">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Session State bridge -->
    <script>
        const sessionUser = <?php echo json_encode($username); ?>;
        const sessionRole = <?php echo json_encode($role); ?>;
    </script>

    <!-- Sequentially load scripts to maintain global state architecture -->
    <script src="js/app.js?v=1.1"></script>
    <script src="js/auth.js?v=1.1"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/menu.js?v=1.1"></script>
    <script src="js/stock.js"></script>
    <script src="js/pos.js"></script>
    <script src="js/reports.js"></script>
    <script src="js/reservations.js"></script>
    <script src="js/cooking.js?v=1.1"></script>
    <script src="js/customer.js"></script>
    <script src="js/feedback.js?v=1.4"></script>

    <!-- Auto-login / Initial screen check -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (sessionUser && sessionRole) {
                currentUser = sessionUser;
                currentRole = sessionRole;
                const isAdmin = sessionRole === 'admin';
                const isStaff = sessionRole === 'staff';

                if (isAdmin || isStaff) {
                    document.querySelectorAll('.admin-only').forEach(el => {
                        el.style.display = isAdmin ? '' : 'none';
                    });
                    const roleDisplay = document.getElementById('user-role-display');
                    if (roleDisplay) roleDisplay.innerText = isAdmin ? 'Admin' : 'Staff';

                    const dashboardBtn = document.getElementById('nav-dashboard');
                    switchTab('tab-dashboard', dashboardBtn);
                    navTo('app-layout');
                    loadMenu();
                    loadStock();
                } else {
                    const nameDisplay = document.getElementById('customer-name-display');
                    if (nameDisplay) nameDisplay.innerText = currentUser;
                    const menuBtn = document.getElementById('nav-cust-menu');
                    switchCustomerTab('tab-cust-menu', menuBtn);
                    navTo('customer-layout');
                    loadCustomerMenu();
                }
            } else {
                navTo('screen-home');
                toggleAuthForm('form-login');
            }
            if (typeof updateFeedbackButtonVisibility === 'function') {
                updateFeedbackButtonVisibility();
            }
        });
    </script>
</body>
</html>
