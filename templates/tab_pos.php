<!-- FOODLoop – templates/tab_pos.php (POS Cashier Order Terminal) -->
<div id="tab-pos" class="app-tab hidden">
    <h2 class="margin-bottom-20">Transaction Terminal</h2>

    <div class="pos-layout">
        <!-- Category Filter Tabs for POS -->
        <div class="pos-section">
            <div class="cat-tabs" id="pos-cat-tabs">
                <button class="cat-tab active" onclick="filterPosMenu('All', this)">🍴 All</button>
                <button class="cat-tab" onclick="filterPosMenu('Main Dish', this)">🍽️ Main Dish</button>
                <button class="cat-tab" onclick="filterPosMenu('Beverage', this)">🥤 Beverage</button>
                <button class="cat-tab" onclick="filterPosMenu('Dessert', this)">🍨 Dessert</button>
                <button class="cat-tab" onclick="filterPosMenu('Snacks', this)">🍟 Snacks</button>
            </div>
            <div class="menu-grid" id="pos-menu-grid">
                <p style="color:#bdc3c7;padding:20px;">Loading menu items...</p>
            </div>
        </div>

        <div class="order-panel">
            <h3 class="order-panel-title">Current Order</h3>
            <div class="order-items" id="pos-order-list"></div>
            <div class="order-panel-footer">
                <div class="order-total-row">
                    <span>Total Due:</span>
                    <span class="text-success">₱<span id="pos-total">0.00</span></span>
                </div>
                <button class="pos-submit-btn" onclick="submitPosOrder()">Complete Transaction</button>
            </div>
        </div>
    </div>
</div>
