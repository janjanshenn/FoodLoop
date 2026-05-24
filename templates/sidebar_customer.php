<!-- FOODLoop – templates/sidebar_customer.php (Customer Sidebar Navigation & Cart) -->
<div class="sidebar">
    <h3 class="sidebar-title">FOODLoop ordering</h3>
    <p class="sidebar-subtitle">Welcome back: <span id="customer-name-display" class="sidebar-user-role">Suki</span></p>

    <button class="nav-item active" id="nav-cust-menu" onclick="switchCustomerTab('tab-cust-menu', this)">🍽️ Menu</button>
    <button class="nav-item" id="nav-cust-reservations" onclick="switchCustomerTab('tab-cust-reservations', this)">📋 My Orders & Reservations</button>
    
    <div id="sidebar-cart" class="sidebar-cart-container">
        <h4 class="sidebar-cart-header">Cart (<span id="cart-count">0</span>)</h4>
        <div id="cart-items-body" class="sidebar-cart-body">
            <p class="sidebar-cart-empty">Your cart is empty.</p>
        </div>
        <div class="sidebar-cart-footer">
            <span class="sidebar-cart-total">Total: <span class="text-success">₱<span id="cart-total-price">0.00</span></span></span>
        </div>
        <button onclick="checkoutCart()" class="sidebar-checkout-btn">Checkout</button>
    </div>

    <button class="nav-item logout-button mt-auto" onclick="logoutUser()">
        Logout
    </button>
</div>
