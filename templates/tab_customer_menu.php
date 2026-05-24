<!-- FOODLoop – templates/tab_customer_menu.php (Customer Food Catalog) -->
<div id="tab-cust-menu" class="customer-tab">
    <div class="flex-header">
        <h2>Mga Ulam</h2>
        <input type="text" id="food-search" placeholder="Search ulam..." class="food-search-input">
    </div>

    <!-- Category Filter Tabs for Customer -->
    <div class="cat-tabs" id="customer-cat-tabs">
        <button class="cat-tab active" onclick="filterCustomerMenu('All', this)">🍴 All</button>
        <button class="cat-tab" onclick="filterCustomerMenu('Main Dish', this)">🍽️ Main Dish</button>
        <button class="cat-tab" onclick="filterCustomerMenu('Beverage', this)">🥤 Beverage</button>
        <button class="cat-tab" onclick="filterCustomerMenu('Dessert', this)">🍨 Dessert</button>
        <button class="cat-tab" onclick="filterCustomerMenu('Snacks', this)">🍟 Snacks</button>
    </div>

    <div class="customer-food-grid">
        <!-- Dynamically populated by JavaScript loadCustomerMenu() -->
        <p style="color:#bdc3c7;padding:20px;grid-column:1/-1;text-align:center;">Loading menu items...</p>
    </div>
</div>
