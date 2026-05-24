<!-- FOODLoop – templates/tab_menu.php (Global Menu Item Management) -->
<div id="tab-manage-menu" class="app-tab hidden">
    <div class="flex-header">
        <h2>Manage Global Menu</h2>
    </div>

    <div class="section-box">
        <h3 id="menu-form-title">Add New Record Details</h3>
        <div class="form-row">
            <div class="flex-1">
                <label>Item Name</label><br>
                <input type="text" id="menu-name" placeholder="e.g. Chicken Adobo">
            </div>
            <div class="flex-1">
                <label>Price (₱)</label><br>
                <input type="number" id="menu-price" placeholder="0.00">
            </div>
            <div class="flex-1">
                <label>Category</label><br>
                <select id="menu-category">
                    <option>Main Dish</option>
                    <option>Beverage</option>
                    <option>Dessert</option>
                    <option>Snacks</option>
                </select>
            </div>
            <div class="flex-1">
                <label>Servings Stock</label><br>
                <input type="number" id="menu-servings" placeholder="e.g. 10" value="10" min="0">
            </div>
            <div class="flex-1">
                <label>Photo</label><br>
                <input type="file" id="menu-image" accept="image/*" style="padding:6px 0;">
                <div id="menu-image-preview" style="margin-top:8px;"></div>
            </div>
        </div>
        <div style="display:flex; gap:10px; align-items:center;" class="margin-top-15">
            <button id="menu-save-btn" onclick="saveMenuItem()">Save Record</button>
            <button id="menu-cancel-btn" onclick="cancelEditMenuItem()" class="hidden" style="background-color:#95a5a6;color:#FFF;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;">Cancel Edit</button>
        </div>
    </div>

    <div class="section-box">
        <h3>Current Menu Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Servings</th>
                    <th class="admin-only" style="text-align:center; width: 80px;">Featured</th>
                    <th class="admin-only">Action</th>
                </tr>
            </thead>
            <tbody id="menu-table-body">
                <tr>
                    <td colspan="7" style="text-align:center;color:#bdc3c7;">Loading menu...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
