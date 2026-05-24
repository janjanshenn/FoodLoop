// ─────────────────────────────────────────────────────────────
// FOODLoop – js/customer.js (Customer Ordering & Reservations)
// ─────────────────────────────────────────────────────────────

async function loadCustomerMenu() {
    try {
        const items = await apiFetch('get_menu.php');
        renderCustomerMenu(items);
    } catch (err) { console.error('Failed to load customer menu:', err); }
}

function renderCustomerMenu(items) {
    const grid = document.querySelector('.customer-food-grid');
    if (!grid) return;
    grid.innerHTML = '';
    items.forEach(item => {
        const imgSrc = item.image ? `uploads/${item.image}` : getDefaultImage(item.name);
        const price  = parseFloat(item.price);
        const servings = parseInt(item.servings);
        const isOutOfStock = servings <= 0;
        const div    = document.createElement('div');
        div.className = `customer-food-card ${isOutOfStock ? 'out-of-stock' : ''}`;
        div.dataset.category = item.category || 'Main Dish';
        
        const escapedName = item.name.replace(/'/g, "\\'");
        
        div.innerHTML = `
            ${isOutOfStock ? '<span class="out-of-stock-badge">Sold Out</span>' : ''}
            <img src="${imgSrc}" alt="${item.name}">
            <div class="card-content">
                <h4>${item.name}</h4>
                <div class="food-price-row" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <p class="text-warning food-price" style="margin: 0;">₱${price.toFixed(2)}</p>
                    <span class="stock-pill ${servings <= 5 ? 'low-stock' : 'in-stock'}">${servings} servings left</span>
                </div>
                <div class="food-action-row flex-row align-center gap-xs">
                    <!-- Default Actions (Add to Cart / Reserve) -->
                    <div class="default-actions flex-row gap-xs w-full">
                        <button class="food-add-btn w-half" ${isOutOfStock ? 'disabled' : ''} onclick="addToCart(${item.id}, '${escapedName}', ${price}, ${servings}, this)">Add to Cart</button>
                        <button class="food-reserve-btn w-half" ${isOutOfStock ? 'disabled' : ''} onclick="showInlineReserve(this)">Reserve</button>
                    </div>
                    
                    <!-- Inline Quantity Selector (Hidden by default) -->
                    <div class="inline-reserve-actions hidden flex-row align-center justify-between w-full">
                        <div class="qty-btn-group-inline flex-row align-center">
                            <button type="button" class="inline-qty-btn" onclick="adjustInlineQty(this, -1)">−</button>
                            <span class="inline-qty-val" data-max="${servings}">1</span>
                            <button type="button" class="inline-qty-btn" onclick="adjustInlineQty(this, 1)">+</button>
                        </div>
                        <div class="flex-row gap-xs">
                            <button class="btn-confirm-inline" onclick="confirmInlineReserve(this, '${escapedName}', ${price})">✓</button>
                            <button class="btn-cancel-inline" onclick="hideInlineReserve(this)">✕</button>
                        </div>
                    </div>
                </div>
            </div>`;
        grid.appendChild(div);
    });
}

function filterCustomerMenu(category, btnEl) {
    // Update active tab
    document.querySelectorAll('#customer-cat-tabs .cat-tab').forEach(b => b.classList.remove('active'));
    btnEl.classList.add('active');
    // Show/hide cards
    document.querySelectorAll('.customer-food-grid .customer-food-card').forEach(card => {
        const match = category === 'All' || card.dataset.category === category;
        card.style.display = match ? '' : 'none';
    });
    // Reset search input so it doesn't conflict
    const searchInput = document.getElementById('food-search');
    if (searchInput) searchInput.value = '';
}

function addToCart(menuId, itemName, price, maxServings, btnElement) {
    const existing = customerCart.find(i => i.menu_id === menuId);
    if (existing) {
        if (existing.qty >= maxServings) {
            alert(`Cannot add more. Only ${maxServings} servings available for "${itemName}".`);
            return;
        }
        existing.qty++;
    } else {
        customerCart.push({ menu_id: menuId, name: itemName, price: price, qty: 1, maxServings: maxServings });
    }
    customerCartTotal += price;
    
    const cartCountEl = document.getElementById('cart-count');
    if (cartCountEl) {
        const totalQty = customerCart.reduce((sum, item) => sum + item.qty, 0);
        cartCountEl.innerText = totalQty;
    }
    
    const orig = btnElement.innerText;
    btnElement.innerText = '✓ Added!';
    btnElement.style.background = '#2ecc71';
    setTimeout(() => { btnElement.innerText = orig; btnElement.style.background = ''; }, 1500);
    renderCustomerCart();
}

function showInlineReserve(btn) {
    const card = btn.closest('.customer-food-card');
    if (!card) return;
    const defaultActions = card.querySelector('.default-actions');
    const inlineActions = card.querySelector('.inline-reserve-actions');
    const qtyVal = card.querySelector('.inline-qty-val');
    
    if (defaultActions && inlineActions) {
        defaultActions.classList.add('hidden');
        inlineActions.classList.remove('hidden');
        if (qtyVal) qtyVal.innerText = '1';
    }
}

// Global window mappings for cart helpers to avoid event listener scoping issues
window.adjustCartQty = adjustCartQty;
window.removeFromCart = removeFromCart;

function hideInlineReserve(btn) {
    const card = btn.closest('.customer-food-card');
    if (!card) return;
    const defaultActions = card.querySelector('.default-actions');
    const inlineActions = card.querySelector('.inline-reserve-actions');
    const qtyVal = card.querySelector('.inline-qty-val');
    
    if (defaultActions && inlineActions) {
        inlineActions.classList.add('hidden');
        defaultActions.classList.remove('hidden');
        if (qtyVal) qtyVal.innerText = '1';
    }
}

function adjustInlineQty(btn, delta) {
    const card = btn.closest('.customer-food-card');
    if (!card) return;
    const qtyVal = card.querySelector('.inline-qty-val');
    if (!qtyVal) return;
    
    const maxVal = parseInt(qtyVal.dataset.max) || 0;
    let currentVal = parseInt(qtyVal.innerText) || 1;
    
    currentVal += delta;
    if (currentVal < 1) currentVal = 1;
    if (currentVal > maxVal) {
        currentVal = maxVal;
        if (typeof showToast === 'function') showToast(`Only ${maxVal} servings available!`, 'warning');
    }
    
    qtyVal.innerText = currentVal;
}

async function confirmInlineReserve(btn, itemName, price) {
    const card = btn.closest('.customer-food-card');
    if (!card) return;
    const qtyVal = card.querySelector('.inline-qty-val');
    if (!qtyVal) return;
    
    const qty = parseInt(qtyVal.innerText) || 1;
    const maxVal = parseInt(qtyVal.dataset.max) || 0;
    
    if (maxVal <= 0) {
        if (typeof showToast === 'function') showToast(`"${itemName}" is sold out.`, 'warning');
        hideInlineReserve(btn);
        return;
    }
    
    if (qty > maxVal) {
        if (typeof showToast === 'function') showToast(`Cannot reserve more than ${maxVal} servings.`, 'warning');
        return;
    }
    
    try {
        const data = await apiFetch('save_reservation.php', 'POST', {
            item_name: itemName,
            price: price,
            quantity: qty
        });
        if (data.success) {
            if (typeof showToast === 'function') showToast(`✅ ${qty}x ${itemName} reserved successfully!`, 'success');
            hideInlineReserve(btn);
            loadCustomerMenu(); // refresh stock
            const custResTab = document.getElementById('tab-cust-reservations');
            if (custResTab && !custResTab.classList.contains('hidden')) {
                if (typeof loadCustomerReservations === 'function') loadCustomerReservations();
            }
        } else {
            if (typeof showToast === 'function') showToast(data.error || 'Failed to place reservation.', 'warning');
        }
    } catch (err) {
        if (typeof showToast === 'function') showToast('Database error while placing reservation.', 'warning');
    }
}

function renderCustomerCart() {
    const container = document.getElementById('cart-items-body');
    if (!container) return;
    container.innerHTML = '';
    if (!customerCart.length) {
        container.innerHTML = '<p class="sidebar-cart-empty">Your cart is empty.</p>';
    } else {
        customerCart.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'cart-item-row';
            div.innerHTML = `
                <div class="cart-item-details">
                    <span class="cart-item-name">${item.name}</span>
                    <span class="cart-item-subtotal">₱${(item.price * item.qty).toFixed(2)}</span>
                </div>
                <div class="cart-item-actions">
                    <div class="qty-adjuster">
                        <button onclick="adjustCartQty(${index}, -1)">−</button>
                        <span>${item.qty}</span>
                        <button onclick="adjustCartQty(${index}, 1)">+</button>
                    </div>
                    <button class="cart-remove-btn" onclick="removeFromCart(${index})">✕</button>
                </div>
            `;
            container.appendChild(div);
        });
    }
    const totalEl = document.getElementById('cart-total-price');
    if (totalEl) totalEl.innerText = customerCartTotal.toFixed(2);
}

function adjustCartQty(index, delta) {
    const item = customerCart[index];
    if (!item) return;
    
    const newVal = item.qty + delta;
    if (newVal < 1) {
        removeFromCart(index);
        return;
    }
    
    if (newVal > item.maxServings) {
        alert(`Only ${item.maxServings} servings available for "${item.name}".`);
        return;
    }
    
    item.qty = newVal;
    
    // Re-calculate totals
    customerCartTotal = customerCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    
    const cartCountEl = document.getElementById('cart-count');
    if (cartCountEl) {
        const totalQty = customerCart.reduce((sum, item) => sum + item.qty, 0);
        cartCountEl.innerText = totalQty;
    }
    
    renderCustomerCart();
}

function removeFromCart(index) {
    customerCart.splice(index, 1);
    
    // Re-calculate totals
    customerCartTotal = customerCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    
    const cartCountEl = document.getElementById('cart-count');
    if (cartCountEl) {
        const totalQty = customerCart.reduce((sum, item) => sum + item.qty, 0);
        cartCountEl.innerText = totalQty;
    }
    
    renderCustomerCart();
}

async function checkoutCart() {
    if (!customerCart.length) { alert('Your cart is empty!'); return; }
    try {
        const data = await apiFetch('save_cart_order.php', 'POST', {
            items: customerCart
        });
        if (data.success) {
            alert('✅ Order Checked Out Successfully! It is now in the kitchen queue.');
            customerCart = []; customerCartTotal = 0;
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) cartCountEl.innerText = '0';
            renderCustomerCart();
            loadCustomerMenu(); // Refresh stock
            
            // Redirect customer to the Orders & Reservations tab to view their checked out order
            const navResBtn = document.getElementById('nav-cust-reservations');
            if (navResBtn) {
                switchCustomerTab('tab-cust-reservations', navResBtn);
            }
        } else {
            alert(data.error || 'Checkout failed.');
        }
    } catch (err) {
        alert('Failed to connect to checkout API.');
    }
}

// Set up Search Input event listener
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('food-search');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase();
            const activeTab = document.querySelector('#customer-cat-tabs .cat-tab.active');
            const activeCategory = activeTab ? activeTab.textContent.trim().replace(/^\S+\s*/, '') : 'All';
            document.querySelectorAll('.customer-food-card').forEach(card => {
                const title = card.querySelector('h4').innerText.toLowerCase();
                const catMatch = activeCategory === 'All' || card.dataset.category === activeCategory;
                card.style.display = (title.includes(term) && catMatch) ? '' : 'none';
            });
        });
    }
});
