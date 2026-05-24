// ─────────────────────────────────────────────────────────────
// FOODLoop – js/stock.js (Stock Management with Inline Row Editing)
// ─────────────────────────────────────────────────────────────

let activeStockFilter = 'All';
let stockSearchTerm = '';
let editingStockId = null;

async function loadStock() {
    try {
        const items = await apiFetch('get_stock.php');
        window.allStockItems = items;
        
        // 1. Update Metrics Cards
        updateStockMetrics(items);
        
        // 2. Render filtered stock
        applyStockFilters();
        
        // 3. Update cooking recipe components if function exists
        if (typeof populateCookIngredientDropdown === 'function') populateCookIngredientDropdown(items);
    } catch (err) { console.error('Failed to load stock:', err); }
}

function updateStockMetrics(items) {
    const totalEl = document.getElementById('stock-metric-total');
    const lowEl = document.getElementById('stock-metric-low');
    const outEl = document.getElementById('stock-metric-out');
    
    if (!totalEl || !lowEl || !outEl) return;
    
    const total = items.length;
    const low = items.filter(item => {
        const qty = parseFloat(item.quantity) || 0;
        const thresh = parseFloat(item.low_threshold) || 0;
        return qty > 0 && qty <= thresh;
    }).length;
    
    const outOfStock = items.filter(item => {
        const qty = parseFloat(item.quantity) || 0;
        return qty <= 0;
    }).length;
    
    totalEl.innerText = total;
    lowEl.innerText = low;
    outEl.innerText = outOfStock;
}

function filterStockStatus(status, btnEl) {
    // Toggle active pill styling
    const tabsContainer = document.getElementById('stock-filter-tabs');
    if (tabsContainer) {
        tabsContainer.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    }
    btnEl.classList.add('active');
    
    activeStockFilter = status;
    applyStockFilters();
}

function applyStockFilters() {
    if (!window.allStockItems) return;
    
    let filtered = window.allStockItems;
    
    // Status Filter
    if (activeStockFilter === 'In Stock') {
        filtered = filtered.filter(item => {
            const qty = parseFloat(item.quantity) || 0;
            const thresh = parseFloat(item.low_threshold) || 0;
            return qty > thresh;
        });
    } else if (activeStockFilter === 'Low Stock') {
        filtered = filtered.filter(item => {
            const qty = parseFloat(item.quantity) || 0;
            const thresh = parseFloat(item.low_threshold) || 0;
            return qty <= thresh; // includes out of stock as well
        });
    }
    
    // Search Term Filter
    if (stockSearchTerm) {
        filtered = filtered.filter(item => item.name.toLowerCase().includes(stockSearchTerm));
    }
    
    renderStockTable(filtered);
}

function renderStockTable(items) {
    const tbody = document.getElementById('stock-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#bdc3c7;padding:24px;">No matching ingredients found.</td></tr>`;
        return;
    }
    
    items.forEach(item => {
        const qty = parseFloat(item.quantity) || 0;
        const thresh = parseFloat(item.low_threshold) || 0;
        const isOutOfStock = qty <= 0;
        const isLow = qty > 0 && qty <= thresh;
        const isEditing = item.id === editingStockId;
        
        // Progress Meter Percentage & Color
        const maxRef = Math.max(qty, thresh * 3, 10);
        const pct = isOutOfStock ? 0 : Math.min((qty / maxRef) * 100, 100);
        
        let barClass = 'healthy';
        let statusText = 'In Stock';
        let badgeClass = 'success';
        
        if (isOutOfStock) {
            barClass = 'danger';
            statusText = 'Out of Stock';
            badgeClass = 'danger';
        } else if (isLow) {
            barClass = 'warning';
            statusText = 'Low Stock';
            badgeClass = 'warning';
        }
        
        const tr = document.createElement('tr');
        if (isEditing) {
            tr.classList.add('editing-row');
        } else if (isLow || isOutOfStock) {
            tr.classList.add('admin-row-pending'); // soft warning row style
        }
        
        if (isEditing) {
            // Render Inline Editing inputs
            tr.innerHTML = `
                <td>
                    <input type="text" id="inline-stock-name-${item.id}" value="${item.name}" class="inline-edit-input" style="max-width: 140px;">
                </td>
                <td>
                    <div class="stock-meter-container">
                        <div class="stock-meter-bar ${barClass}" style="width: ${pct}%;"></div>
                    </div>
                </td>
                <td>
                    <div class="flex-row gap-xs" style="align-items: center; justify-content: flex-start; max-width: 150px;">
                        <input type="number" step="any" id="inline-stock-qty-${item.id}" value="${qty}" class="inline-edit-input" style="width: 70px;">
                        <select id="inline-stock-unit-${item.id}" class="inline-edit-input" style="width: 60px; height: 30px; padding:0 4px;">
                            <option ${item.unit === 'kg' ? 'selected' : ''}>kg</option>
                            <option ${item.unit === 'g' ? 'selected' : ''}>g</option>
                            <option ${item.unit === 'L' ? 'selected' : ''}>L</option>
                            <option ${item.unit === 'pcs' ? 'selected' : ''}>pcs</option>
                        </select>
                    </div>
                </td>
                <td>
                    <input type="number" step="any" id="inline-stock-threshold-${item.id}" value="${thresh}" class="inline-edit-input" style="max-width: 70px;">
                </td>
                <td><span class="badge ${badgeClass}">${statusText}</span></td>
                <td class="admin-only">
                    <div class="flex-row gap-xs" style="justify-content: flex-start;">
                        <button class="btn-confirm-table" onclick="saveInlineEdit(${item.id})" title="Save">✓</button>
                        <button class="btn-cancel-table" onclick="cancelInlineEdit()" title="Cancel">✕</button>
                    </div>
                </td>`;
        } else {
            // Render Static Read-only row
            tr.innerHTML = `
                <td style="font-weight:600;">${item.name}</td>
                <td>
                    <div class="stock-meter-container">
                        <div class="stock-meter-bar ${barClass}" style="width: ${pct}%;"></div>
                    </div>
                </td>
                <td><strong>${item.quantity} ${item.unit}</strong></td>
                <td>${item.low_threshold} ${item.unit}</td>
                <td><span class="badge ${badgeClass}">${statusText}</span></td>
                <td class="admin-only">
                    <button class="btn-action-edit" onclick="startInlineEdit(${item.id})">Edit</button>
                </td>`;
        }
            
        if (currentRole !== 'admin') {
            tr.querySelectorAll('.admin-only').forEach(el => el.style.display = 'none');
        }
        tbody.appendChild(tr);
    });
}

// ── INLINE EDIT ACTIONS ────────────────────────────────────────
function startInlineEdit(id) {
    editingStockId = id;
    applyStockFilters();
}

function cancelInlineEdit() {
    editingStockId = null;
    applyStockFilters();
}

async function saveInlineEdit(id) {
    const name = document.getElementById(`inline-stock-name-${id}`).value.trim();
    const quantity = parseFloat(document.getElementById(`inline-stock-qty-${id}`).value);
    const unit = document.getElementById(`inline-stock-unit-${id}`).value;
    const low_threshold = parseFloat(document.getElementById(`inline-stock-threshold-${id}`).value);
    
    if (!name || isNaN(quantity) || isNaN(low_threshold)) {
        if (typeof showToast === 'function') showToast('Please enter valid fields.', 'warning');
        return;
    }
    
    try {
        const data = await apiFetch('update_stock.php', 'POST', {
            id, name, quantity, unit, low_threshold
        });
        
        if (data.success) {
            if (typeof showToast === 'function') showToast('✅ Ingredient updated successfully!', 'success');
            editingStockId = null;
            loadStock();
        } else {
            if (typeof showToast === 'function') showToast(data.error || 'Failed to update stock.', 'warning');
        }
    } catch (err) {
        if (typeof showToast === 'function') showToast('Server connection failed.', 'warning');
    }
}

// ── ADD NEW STOCK ──────────────────────────────────────────────
async function addStock() {
    const name = document.getElementById('stock-name').value.trim();
    const quantity = parseFloat(document.getElementById('stock-qty').value);
    const unit = document.getElementById('stock-unit').value;
    const low_threshold = parseFloat(document.getElementById('stock-threshold').value);
    
    if (!name || isNaN(quantity) || isNaN(low_threshold)) {
        if (typeof showToast === 'function') showToast('Enter a valid name, quantity, and threshold.', 'warning');
        return;
    }
    
    try {
        const data = await apiFetch('update_stock.php', 'POST', {
            id: 0, name, quantity, unit, low_threshold
        });
        
        if (data.success) {
            if (typeof showToast === 'function') showToast(`✅ "${name}" added to inventory!`, 'success');
            document.getElementById('stock-name').value = '';
            document.getElementById('stock-qty').value = '';
            document.getElementById('stock-threshold').value = '5';
            loadStock();
        } else {
            if (typeof showToast === 'function') showToast(data.error || 'Failed to add stock.', 'warning');
        }
    } catch (err) {
        if (typeof showToast === 'function') showToast('Server connection failed.', 'warning');
    }
}

// Set up Search Event Listener
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('stock-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            stockSearchTerm = e.target.value.toLowerCase().trim();
            applyStockFilters();
        });
    }
});
