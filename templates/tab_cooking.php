<!-- FOODLoop – templates/tab_cooking.php (Cooking Station & Stock Deductions) -->
<div id="tab-cooking-station" class="app-tab hidden">
    <div class="flex-header">
        <h2>Cooking Station</h2>
    </div>

    <div class="cooking-layout">
        <!-- Left Column: Dish Selection & Servings -->
        <div class="cooking-col-left">
            <div class="section-box">
                <h3 style="margin-top:0;">Dish Settings</h3>
                <div style="margin-bottom:15px;">
                    <label for="cook-dish-select" style="font-weight:600; font-size:13px; color:var(--text-primary);">Select Dish to Cook</label>
                    <select id="cook-dish-select" onchange="onCookDishChanged()">
                        <option value="">-- Select a Dish --</option>
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <label for="cook-servings-qty" style="font-weight:600; font-size:13px; color:var(--text-primary);">Servings to Cook</label>
                    <input type="number" id="cook-servings-qty" value="10" min="1" step="1" oninput="onCookServingsChanged()">
                </div>

                <div id="cook-dish-info" class="cook-dish-info-card hidden">
                    <div class="cook-dish-info-content">
                        <span id="cook-dish-current-servings">Current Servings Available: 0</span>
                    </div>
                </div>
            </div>

            <div class="section-box">
                <h3 style="margin-top:0;">Add Extra Ingredient</h3>
                <p style="font-size:12px; color:var(--text-secondary); margin-bottom:12px;">Add an additional ingredient to this cooking batch if needed.</p>
                <div class="form-row" style="display:flex; gap:8px; align-items:center;">
                    <div style="flex:2; margin:0;">
                        <select id="cook-extra-ingredient-select" style="margin:0;">
                            <option value="">-- Choose Ingredient --</option>
                        </select>
                    </div>
                    <div style="flex:1; margin:0;">
                        <button type="button" class="btn-secondary" style="width:100%; padding: 12px 10px; margin:0;" onclick="addExtraIngredientToRecipe()">Add</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Ingredient Deductions & Stock -->
        <div class="cooking-col-right">
            <div class="section-box">
                <h3 style="margin-top:0;">Recipe Ingredients & Stock Levels</h3>
                <p style="font-size:13px; color:var(--text-secondary); margin-bottom:15px;">
                    The quantities below are calculated based on default recipe ratios. You can manually adjust the deduction amounts.
                </p>

                <table>
                    <thead>
                        <tr>
                            <th>Ingredient Name</th>
                            <th>Per Serving</th>
                            <th>Total Needed</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                            <th>Deduction Qty</th>
                            <th style="width:50px; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cook-ingredients-tbody">
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--text-muted); padding:20px;">
                                Please select a dish to see recipe ingredients.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div id="cook-warning-message" class="cook-warning-message hidden">
                    <!-- Warning alert if stock is insufficient -->
                </div>

                <div class="cook-actions" style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn-secondary" onclick="resetCookingStation()">Clear</button>
                    <button type="button" id="btn-confirm-cook" class="form-button" onclick="confirmCooking()">Confirm Cooking</button>
                </div>
            </div>
        </div>
    </div>
</div>
