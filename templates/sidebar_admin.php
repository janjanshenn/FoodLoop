<!-- FOODLoop – templates/sidebar_admin.php (Admin & Staff Sidebar Navigation) -->
<div class="sidebar">
    <h3 class="sidebar-title">FOODLoop</h3>
    <p class="sidebar-subtitle">Logged in as: <span id="user-role-display" class="sidebar-user-role">Admin</span></p>

    <button class="nav-item active" id="nav-dashboard" onclick="switchTab('tab-dashboard', this)">
        Dashboard
    </button>
    <button class="nav-item admin-only" id="nav-manage-menu" onclick="switchTab('tab-manage-menu', this)">
        Menu
    </button>
    <button class="nav-item" id="nav-ingredients" onclick="switchTab('tab-ingredients', this)">
        Stock
    </button>
    <button class="nav-item" id="nav-pos" onclick="switchTab('tab-pos', this)">
        Transaction
    </button>
    <button class="nav-item admin-only" id="nav-reports" onclick="switchTab('tab-reports', this)">
        Sales Reports
    </button>
    <button class="nav-item" id="nav-reservations" onclick="switchTab('tab-reservations', this)">
        Orders & Reservations
    </button>
    <button class="nav-item" id="nav-cooking-station" onclick="switchTab('tab-cooking-station', this)">
        Cooking Station
    </button>
    <button class="nav-item" id="nav-feedback" onclick="switchTab('tab-admin-feedback', this)">
        Customer Feedback
    </button>
    <button class="nav-item logout-button mt-auto" onclick="logoutUser()">
        Logout
    </button>
</div>
