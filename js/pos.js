// ─────────────────────────────────────────────────────────────
// FOODLoop – js/pos.js (Point of Sale Menu & Order Processing)
// ─────────────────────────────────────────────────────────────

function renderPosMenu(items) {
    const grid = document.querySelector('.menu-grid');
    if (!grid) return;
    grid.innerHTML = '';
    items.forEach(item => {
        const imgSrc = item.image ? `uploads/${item.image}` : getDefaultImage(item.name);
        const div = document.createElement('div');
        const servings = parseInt(item.servings);
        const isOutOfStock = servings <= 0;
        div.className = `menu-item ${isOutOfStock ? 'out-of-stock' : ''}`;
        div.dataset.category = item.category || 'Main Dish';
        
        if (isOutOfStock) {
            div.onclick = () => alert(`"${item.name}" is sold out!`);
        } else {
            div.onclick = () => addPosItem(item.name, parseFloat(item.price), servings);
        }
        
        div.innerHTML = `
            ${isOutOfStock ? '<span class="out-of-stock-badge">Sold Out</span>' : ''}
            <img src="${imgSrc}" alt="${item.name}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-bottom:6px;">
            <h4>${item.name}</h4>
            <p class="pos-price">₱${parseFloat(item.price).toFixed(2)} <span style="font-size:11px;color:var(--text-muted);">(${servings} left)</span></p>`;
        grid.appendChild(div);
    });
}

function filterPosMenu(category, btnEl) {
    // Update active tab
    document.querySelectorAll('#pos-cat-tabs .cat-tab').forEach(b => b.classList.remove('active'));
    btnEl.classList.add('active');
    // Show/hide items
    document.querySelectorAll('#pos-menu-grid .menu-item').forEach(card => {
        const match = category === 'All' || card.dataset.category === category;
        card.style.display = match ? '' : 'none';
    });
}

function addPosItem(name, price, maxServings) {
    const existing = posOrderItems.find(i => i.name === name);
    if (existing) {
        if (existing.qty >= maxServings) {
            alert(`Cannot add more. Only ${maxServings} servings available for "${name}".`);
            return;
        }
        existing.qty++;
    } else {
        if (maxServings <= 0) {
            alert(`"${name}" is sold out.`);
            return;
        }
        posOrderItems.push({ name, price, qty: 1 });
    }
    posTotal += price;
    renderPosOrder();
}

function renderPosOrder() {
    const list = document.getElementById('pos-order-list');
    if (!list) return;
    list.innerHTML = '';
    posOrderItems.forEach((item, index) => {
        const row = document.createElement('div');
        row.className = 'order-item-row';
        row.innerHTML = `
            <span>${item.qty}x ${item.name}</span>
            <span class="pos-qty-price">₱${(item.price * item.qty).toFixed(2)}
                <button style="background:none;border:none;color:#e74c3c;cursor:pointer;margin-left:6px;box-shadow:none;" onclick="removePosItem(${index})">✕</button>
            </span>`;
        list.appendChild(row);
    });
    const totalEl = document.getElementById('pos-total');
    if (totalEl) totalEl.innerText = posTotal.toFixed(2);
}

function removePosItem(index) {
    posTotal -= posOrderItems[index].price * posOrderItems[index].qty;
    posOrderItems.splice(index, 1);
    renderPosOrder();
}

async function submitPosOrder() {
    if (posOrderItems.length === 0) { alert('No items in order!'); return; }
    try {
        const data = await apiFetch('save_transaction.php', 'POST', {
            items: posOrderItems, total: posTotal, cashier: currentUser || 'Staff'
        });
        if (data.success) {
            alert(`✅ Transaction #${data.transaction_id} recorded!`);
            posOrderItems = []; posTotal = 0;
            renderPosOrder();
            loadMenu(); // Refresh stock in POS view
        } else {
            alert(data.error || 'Failed to save transaction.');
        }
    } catch (err) { alert('Failed to save transaction.'); }
}
