<!-- FOODLoop – templates/tab_stock.php (Stock & Inventory Level Control) -->
<div id="tab-ingredients" class="app-tab hidden">
    <div class="flex-header">
        <h2>Ingredient Stock Management</h2>
    </div>

    <!-- 1. Stock Metrics Row -->
    <div class="metrics-row flex-row gap-s margin-bottom-20">
        <div class="metric-card flex-1 card">
            <span class="metric-label">Total Ingredients</span>
            <span class="metric-value" id="stock-metric-total">0</span>
        </div>
        <div class="metric-card flex-1 card">
            <span class="metric-label text-warning">Low Stock Items</span>
            <span class="metric-value" id="stock-metric-low">0</span>
        </div>
        <div class="metric-card flex-1 card text-danger-card">
            <span class="metric-label text-danger">Out of Stock</span>
            <span class="metric-value" id="stock-metric-out">0</span>
        </div>
    </div>

    <!-- 2. Table Controls (Search & Status Filters) -->
    <div class="table-actions-row flex-row align-center justify-between margin-bottom-15">
        <div class="filter-pills" id="stock-filter-tabs">
            <button class="filter-pill active" onclick="filterStockStatus('All', this)">All</button>
            <button class="filter-pill" onclick="filterStockStatus('In Stock', this)">In Stock</button>
            <button class="filter-pill" onclick="filterStockStatus('Low Stock', this)">Low Stock</button>
        </div>
        <input type="text" id="stock-search" placeholder="Search ingredients..." class="food-search-input">
    </div>

    <!-- 3. Stock Level Inventory Table -->
    <div class="section-box card">
        <h3>Current Inventory</h3>
        <table>
            <thead>
                <tr>
                    <th>Ingredient Name</th>
                    <th>Stock Meter</th>
                    <th>Current Stock</th>
                    <th>Warning Threshold</th>
                    <th>Status</th>
                    <th class="admin-only">Action</th>
                </tr>
            </thead>
            <tbody id="stock-table-body">
                <tr>
                    <td colspan="6" style="text-align:center;color:#bdc3c7;">Loading stock...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 4. Add New Stock Form Panel -->
    <div class="section-box card admin-only margin-top-20">
        <h3>Add New Stock</h3>
        <div class="form-row flex-row gap-m">
            <div class="flex-2">
                <label for="stock-name">Ingredient Name</label><br>
                <input type="text" id="stock-name" placeholder="e.g. Onions" class="w-full">
            </div>
            <div class="flex-1">
                <label for="stock-qty">Quantity</label><br>
                <input type="number" step="any" id="stock-qty" placeholder="0" class="w-full">
            </div>
            <div class="flex-1">
                <label for="stock-unit">Unit</label><br>
                <select id="stock-unit" class="w-full">
                    <option>kg</option>
                    <option>g</option>
                    <option>L</option>
                    <option>pcs</option>
                </select>
            </div>
            <div class="flex-1">
                <label for="stock-threshold">Warning Threshold</label><br>
                <input type="number" step="any" id="stock-threshold" placeholder="5" value="5" class="w-full">
            </div>
        </div>
        <button onclick="addStock()" class="btn-success margin-top-15" style="display:inline-block; width:auto; padding: 10px 24px;">Add Ingredient</button>
    </div>
</div>
