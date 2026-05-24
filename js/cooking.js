// ─────────────────────────────────────────────────────────────
// FOODLoop – js/cooking.js (Cooking Station & Inventory Deductions)
// ─────────────────────────────────────────────────────────────

const RECIPES = {
    'Classic Pork Adobo': [
        { name: 'Pork', qtyPerServing: 0.15, unit: 'kg' },
        { name: 'Garlic', qtyPerServing: 10, unit: 'g' },
        { name: 'Soy Sauce', qtyPerServing: 0.02, unit: 'L' },
        { name: 'Cooking Oil', qtyPerServing: 0.01, unit: 'L' }
    ],
    'Sinigang na Baboy': [
        { name: 'Pork', qtyPerServing: 0.15, unit: 'kg' },
        { name: 'Onions', qtyPerServing: 0.02, unit: 'kg' }
    ],
    'Pancit Canton Espesyal': [
        { name: 'Chicken', qtyPerServing: 0.05, unit: 'kg' },
        { name: 'Garlic', qtyPerServing: 5, unit: 'g' },
        { name: 'Cooking Oil', qtyPerServing: 0.01, unit: 'L' }
    ],
    'Lumpiang Shanghai': [
        { name: 'Pork Belly', qtyPerServing: 0.08, unit: 'kg' },
        { name: 'Garlic', qtyPerServing: 5, unit: 'g' },
        { name: 'Cooking Oil', qtyPerServing: 0.02, unit: 'L' }
    ],
    'Extra Rice': [
        { name: 'Rice', qtyPerServing: 0.15, unit: 'kg' }
    ],
    'Sizzling Sisig': [
        { name: 'Pork Belly', qtyPerServing: 0.12, unit: 'kg' },
        { name: 'Onions', qtyPerServing: 0.03, unit: 'kg' },
        { name: 'Cooking Oil', qtyPerServing: 0.01, unit: 'L' }
    ],
    'Chopsuey': [
        { name: 'Chicken', qtyPerServing: 0.05, unit: 'kg' },
        { name: 'Garlic', qtyPerServing: 5, unit: 'g' },
        { name: 'Onions', qtyPerServing: 0.02, unit: 'kg' },
        { name: 'Cooking Oil', qtyPerServing: 0.01, unit: 'L' }
    ],
    'Bicol Express': [
        { name: 'Pork Belly', qtyPerServing: 0.12, unit: 'kg' },
        { name: 'Garlic', qtyPerServing: 10, unit: 'g' },
        { name: 'Onions', qtyPerServing: 0.02, unit: 'kg' }
    ]
};

let currentCookRecipe = [];

function populateCookDishDropdown(items) {
    const select = document.getElementById('cook-dish-select');
    if (!select) return;
    const prevVal = select.value;
    select.innerHTML = '<option value="">-- Select a Dish --</option>';
    
    // Filter to only display 'Main Dish' items
    const mainDishes = items.filter(item => (item.category || '').trim().toLowerCase() === 'main dish');
    
    mainDishes.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = `${item.name} (${item.category})`;
        select.appendChild(opt);
    });
    if (prevVal) select.value = prevVal;
}

function populateCookIngredientDropdown(stockItems) {
    const select = document.getElementById('cook-extra-ingredient-select');
    if (!select) return;
    select.innerHTML = '<option value="">-- Choose Ingredient --</option>';
    stockItems.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = `${item.name} (${item.quantity} ${item.unit} left)`;
        select.appendChild(opt);
    });
}

function onCookDishChanged() {
    const select = document.getElementById('cook-dish-select');
    if (!select) return;
    const dishId = parseInt(select.value);
    const infoCard = document.getElementById('cook-dish-info');
    const currentServingsSpan = document.getElementById('cook-dish-current-servings');
    if (!dishId || !window.allMenuItems) {
        if (infoCard) infoCard.classList.add('hidden');
        currentCookRecipe = [];
        renderCookingIngredients();
        return;
    }
    const dish = window.allMenuItems.find(d => d.id === dishId);
    if (!dish) return;
    if (currentServingsSpan) {
        currentServingsSpan.innerText = `Current Servings Available: ${dish.servings}`;
    }
    if (infoCard) infoCard.classList.remove('hidden');
    const recipeIngredients = RECIPES[dish.name] || [];
    const stockItems = window.allStockItems || [];
    currentCookRecipe = recipeIngredients.map(recipeIng => {
        const stockMatch = stockItems.find(s => s.name.trim().toLowerCase() === recipeIng.name.trim().toLowerCase());
        return {
            id: stockMatch ? stockMatch.id : null,
            name: recipeIng.name,
            qtyPerServing: recipeIng.qtyPerServing,
            totalNeeded: 0,
            deductionQty: 0,
            currentStock: stockMatch ? parseFloat(stockMatch.quantity) : 0,
            unit: stockMatch ? stockMatch.unit : recipeIng.unit,
            status: stockMatch ? stockMatch.status : 'Out of Stock'
        };
    });
    onCookServingsChanged();
}

function onCookServingsChanged() {
    const servingsInput = document.getElementById('cook-servings-qty');
    if (!servingsInput) return;
    let servings = parseInt(servingsInput.value);
    if (isNaN(servings) || servings <= 0) {
        servings = 0;
    }
    currentCookRecipe.forEach(ing => {
        ing.totalNeeded = ing.qtyPerServing * servings;
        ing.deductionQty = ing.totalNeeded;
    });
    renderCookingIngredients();
}

function onDeductionOverride(ingName, value) {
    const ing = currentCookRecipe.find(i => i.name === ingName);
    if (!ing) return;
    let val = parseFloat(value);
    if (isNaN(val) || val < 0) {
        val = 0;
    }
    ing.deductionQty = val;
    validateStockAndDeductions();
}

function addExtraIngredientToRecipe() {
    const select = document.getElementById('cook-extra-ingredient-select');
    if (!select) return;
    const id = parseInt(select.value);
    if (!id || !window.allStockItems) return;
    const stockItem = window.allStockItems.find(s => s.id === id);
    if (!stockItem) return;
    const exists = currentCookRecipe.some(ing => ing.id === id);
    if (exists) {
        alert(`Ingredient "${stockItem.name}" is already in the list.`);
        select.value = '';
        return;
    }
    currentCookRecipe.push({
        id: stockItem.id,
        name: stockItem.name,
        qtyPerServing: 0,
        totalNeeded: 0,
        deductionQty: 0,
        currentStock: parseFloat(stockItem.quantity),
        unit: stockItem.unit,
        status: stockItem.status
    });
    select.value = '';
    renderCookingIngredients();
}

function removeIngredientFromRecipe(id) {
    currentCookRecipe = currentCookRecipe.filter(ing => ing.id !== id);
    renderCookingIngredients();
}

function validateStockAndDeductions() {
    const warningDiv = document.getElementById('cook-warning-message');
    const submitBtn = document.getElementById('btn-confirm-cook');
    if (!warningDiv || !submitBtn) return;
    const shortfalls = [];
    currentCookRecipe.forEach(ing => {
        if (ing.id && ing.deductionQty > ing.currentStock) {
            shortfalls.push(`${ing.name} (Short by ${(ing.deductionQty - ing.currentStock).toFixed(2)} ${ing.unit})`);
        }
    });
    if (shortfalls.length > 0) {
        warningDiv.innerHTML = `⚠️ <strong>Insufficient Stock!</strong> The following ingredients do not have enough stock: <br> • ${shortfalls.join('<br> • ')}<br>Please replenish stock or adjust deduction amounts.`;
        warningDiv.classList.remove('hidden');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    } else {
        warningDiv.innerHTML = '';
        warningDiv.classList.add('hidden');
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
}

function renderCookingIngredients() {
    const tbody = document.getElementById('cook-ingredients-tbody');
    if (!tbody) return;
    if (currentCookRecipe.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center; color:var(--text-muted); padding:20px;">
                    Please select a dish to see recipe ingredients.
                </td>
            </tr>`;
        validateStockAndDeductions();
        return;
    }
    tbody.innerHTML = '';
    currentCookRecipe.forEach(ing => {
        const isLow = ing.id ? (ing.currentStock <= 5) : true;
        const outOfStock = ing.id ? (ing.currentStock <= 0) : true;
        const statusText = outOfStock ? 'Out of Stock' : (isLow ? 'Low Stock' : 'In Stock');
        const statusClass = outOfStock ? 'out-of-stock' : (isLow ? 'low-stock' : 'in-stock');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:600; color:var(--text-primary);">${ing.name}</td>
            <td>${ing.qtyPerServing > 0 ? (ing.qtyPerServing + ' ' + ing.unit) : '-'}</td>
            <td>${ing.totalNeeded > 0 ? (ing.totalNeeded.toFixed(2) + ' ' + ing.unit) : '-'}</td>
            <td>${ing.id ? (ing.currentStock.toFixed(2) + ' ' + ing.unit) : 'N/A'}</td>
            <td><span class="badge-stock ${statusClass}">${statusText}</span></td>
            <td>
                <input type="number" step="any" min="0" class="cook-qty-input" 
                       value="${ing.deductionQty}" 
                       oninput="onDeductionOverride('${ing.name.replace(/'/g, "\\'")}', this.value)">
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-remove-ing" onclick="removeIngredientFromRecipe(${ing.id})">×</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    validateStockAndDeductions();
}

function resetCookingStation() {
    const select = document.getElementById('cook-dish-select');
    if (select) select.value = '';
    const servingsInput = document.getElementById('cook-servings-qty');
    if (servingsInput) servingsInput.value = '10';
    const infoCard = document.getElementById('cook-dish-info');
    if (infoCard) infoCard.classList.add('hidden');
    currentCookRecipe = [];
    renderCookingIngredients();
}

async function confirmCooking() {
    const select = document.getElementById('cook-dish-select');
    if (!select || !select.value) {
        alert('Please select a dish to cook.');
        return;
    }
    const dishId = parseInt(select.value);
    const servingsInput = document.getElementById('cook-servings-qty');
    const servings = parseInt(servingsInput.value);
    if (isNaN(servings) || servings <= 0) {
        alert('Please enter a valid servings quantity.');
        return;
    }
    const payloadIngredients = currentCookRecipe
        .filter(ing => ing.id && ing.deductionQty > 0)
        .map(ing => ({
            id: ing.id,
            deduction_qty: ing.deductionQty
        }));
    const confirmMsg = `Are you sure you cooked ${servings} servings of "${select.options[select.selectedIndex].text}"?\nThis will deduct stock levels and add servings to the active menu.`;
    if (!confirm(confirmMsg)) return;
    try {
        const res = await apiFetch('cook_item.php', 'POST', {
            menu_id: dishId,
            servings_cooked: servings,
            ingredients: payloadIngredients
        });
        if (res.success) {
            alert('🍽️ Cooking station confirmed! Stock updated and servings added successfully.');
            resetCookingStation();
            await loadMenu();
            await loadStock();
            const dashboard = document.getElementById('tab-dashboard');
            if (dashboard && !dashboard.classList.contains('hidden')) {
                loadDashboardStats();
            }
        } else {
            alert(`Error: ${res.error || 'Failed to cook item.'}`);
        }
    } catch (err) {
        alert(`Error: ${err.message || 'Server connection error.'}`);
    }
}
