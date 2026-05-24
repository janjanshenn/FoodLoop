// ─────────────────────────────────────────────────────────────
// FOODLoop – js/reservations.js (Reservation Controllers & Progress Timeline)
// ─────────────────────────────────────────────────────────────

let customerReservationsIntervalId = null;
let adminReservationsIntervalId = null;

async function loadCustomerReservations() {
    const grid = document.getElementById('customer-reservations-grid');
    if (!grid) return;
    try {
        const reservations = await apiFetch('get_reservations.php');
        grid.innerHTML = '';
        if (!reservations || reservations.length === 0) {
            grid.innerHTML = `
                <div class="res-empty-state">
                    <div class="res-empty-icon">🍲</div>
                    <div class="res-empty-title">No Reservations Found</div>
                    <div class="res-empty-desc">You haven't reserved any delicious ulam yet. Browse our selection and secure your servings!</div>
                    <button class="res-empty-btn" onclick="switchCustomerTab('tab-cust-menu', document.getElementById('nav-cust-menu'))">Browse Menu</button>
                </div>
            `;
            return;
        }
        reservations.forEach(res => {
            const dt = new Date(res.created_at);
            const formattedDate = dt.toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
            const price = parseFloat(res.price);
            const quantity = parseInt(res.quantity) || 1;
            const totalCost = price * quantity;
            const imgSrc = getDefaultImage(res.item_name);
            
            const statusClass = res.status.toLowerCase();
            const canCancel = res.status === 'Pending' || res.status === 'Confirmed';
            
            // Build visual timeline for non-cancelled reservations
            let timelineHTML = '';
            if (res.status !== 'Cancelled') {
                let step1Class = 'completed'; // Step 1: Reserved is always completed
                let step2Class = '';
                let step3Class = '';
                let progressWidth = 0;
                
                if (res.status === 'Pending') {
                    step2Class = 'active';
                    progressWidth = 50;
                } else if (res.status === 'Confirmed') {
                    step2Class = 'completed';
                    step3Class = 'active';
                    progressWidth = 100;
                } else if (res.status === 'Completed') {
                    step2Class = 'completed';
                    step3Class = 'completed';
                    progressWidth = 100;
                }
                
                timelineHTML = `
                    <div class="res-timeline">
                        <div class="res-timeline-progress" style="width: ${progressWidth}%;"></div>
                        <div class="timeline-step ${step1Class}">
                            <div class="step-dot"></div>
                            <span class="step-label">${res.order_type === 'Cart Order' ? 'Ordered' : 'Reserved'}</span>
                        </div>
                        <div class="timeline-step ${step2Class}">
                            <div class="step-dot"></div>
                            <span class="step-label">${res.order_type === 'Cart Order' ? 'Preparing' : 'Confirmed'}</span>
                        </div>
                        <div class="timeline-step ${step3Class}">
                            <div class="step-dot"></div>
                            <span class="step-label">${res.order_type === 'Cart Order' ? 'Served' : 'Served'}</span>
                        </div>
                    </div>
                `;
            } else {
                timelineHTML = `
                    <div style="margin: 20px 0 16px 0; text-align: center; font-size: 13px; font-weight: 600; padding: 10px; border-radius: var(--radius-s); background-color: #FEF2F2; border: 1px dashed #FCA5A5; color: #DC2626; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <span>✕ ${res.order_type === 'Cart Order' ? 'Order' : 'Reservation'} Cancelled</span>
                    </div>
                `;
            }
            
            const card = document.createElement('div');
            card.className = `res-card status-${statusClass}`;
            
            const badgeType = res.order_type === 'Cart Order' 
                ? `<span style="font-size: 11px; background-color: rgba(217, 119, 6, 0.08); color: var(--color-accent); border: 1px solid rgba(217, 119, 6, 0.15); padding: 2px 6px; border-radius: 12px; margin-left: 6px; vertical-align: middle;">🛒 Order</span>`
                : `<span style="font-size: 11px; background-color: rgba(66, 135, 245, 0.08); color: #4287f5; border: 1px solid rgba(66, 135, 245, 0.15); padding: 2px 6px; border-radius: 12px; margin-left: 6px; vertical-align: middle;">📅 Reserve</span>`;

            card.innerHTML = `
                <div class="res-card-image-wrapper">
                    <img src="${imgSrc}" alt="${res.item_name}" class="res-card-image" onerror="this.src='img/Logo.png'">
                    <span class="res-id-badge">#R${String(res.id).padStart(3, '0')}</span>
                </div>
                <div class="res-card-details">
                    <div class="res-header-row" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px;">
                        <h3 class="res-item-name" style="margin:0; display:flex; align-items:center;">${res.item_name}${badgeType}</h3>
                        <span class="badge-status ${statusClass}">${res.status}</span>
                    </div>
                    <div class="res-info-row">
                        <span class="res-info-label">${res.order_type === 'Cart Order' ? 'Ordered On:' : 'Reserved On:'}</span>
                        <span class="res-info-value">${formattedDate}</span>
                    </div>
                    <div class="res-info-row">
                        <span class="res-info-label">Quantity:</span>
                        <span class="res-info-value font-highlight" style="background-color: var(--border-color-light); border: 1px solid var(--border-color); padding: 2px 8px; border-radius: var(--radius-s);">${quantity} serving${quantity > 1 ? 's' : ''}</span>
                    </div>
                    <div class="res-info-row">
                        <span class="res-info-label">Total Cost:</span>
                        <span class="res-info-value font-highlight text-success">₱${totalCost.toFixed(2)}</span>
                    </div>
                    
                    ${timelineHTML}
                    
                    ${canCancel ? `
                    <div class="res-action-row">
                        <button class="btn-cancel-reservation" onclick="updateReservationStatus(${res.id}, 'cancel', 'customer')">✕ ${res.order_type === 'Cart Order' ? 'Cancel Order' : 'Cancel Reservation'}</button>
                    </div>
                    ` : ''}
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (err) {
        grid.innerHTML = '<div style="text-align:center;color:#e74c3c;padding:30px;grid-column: 1/-1;">⚠️ Failed to load reservations. Database connection issue.</div>';
    }
}

async function loadAdminReservations() {
    const tbody = document.getElementById('admin-reservations-table-body');
    if (!tbody) return;
    try {
        const reservations = await apiFetch('get_reservations.php');
        window.allAdminReservations = reservations || [];
        
        // Compute stats counts
        let pending = 0;
        let confirmed = 0;
        let completed = 0;
        
        window.allAdminReservations.forEach(res => {
            if (res.status === 'Pending') pending++;
            else if (res.status === 'Confirmed') confirmed++;
            else if (res.status === 'Completed') completed++;
        });
        
        // Update stats widgets
        const pendingEl = document.getElementById('res-stat-pending');
        const confirmedEl = document.getElementById('res-stat-confirmed');
        const completedEl = document.getElementById('res-stat-completed');
        
        if (pendingEl) pendingEl.innerText = pending;
        if (confirmedEl) confirmedEl.innerText = confirmed;
        if (completedEl) completedEl.innerText = completed;
        
        // Determine active filter status
        const activeFilterBtn = document.querySelector('#admin-res-filters .filter-pill.active');
        const filterVal = activeFilterBtn ? activeFilterBtn.innerText.trim() : 'All';
        
        // Filter and Render
        let filtered = window.allAdminReservations;
        if (filterVal !== 'All') {
            filtered = window.allAdminReservations.filter(res => res.status === filterVal);
        }
        
        renderAdminReservationsTable(filtered);
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#e74c3c;">Failed to load reservations.</td></tr>';
    }
}

function renderAdminReservationsTable(reservations) {
    const tbody = document.getElementById('admin-reservations-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (!reservations || reservations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#bdc3c7;padding: 20px;">No reservations found matching this status.</td></tr>';
        return;
    }
    
    reservations.forEach(res => {
        const dt = new Date(res.created_at);
        const formattedDate = dt.toLocaleString('en-PH', { dateStyle: 'short', timeStyle: 'short' });
        const price = parseFloat(res.price);
        const quantity = parseInt(res.quantity) || 1;
        const total = price * quantity;
        
        const statusClass = res.status.toLowerCase();
        let actions = '';
        if (res.status === 'Pending') {
            actions = `
                <button class="btn-action-confirm" onclick="updateReservationStatus(${res.id}, 'confirm', 'admin')">✓ Confirm</button>
                <button class="btn-action-cancel" onclick="updateReservationStatus(${res.id}, 'cancel', 'admin')">✕ Cancel</button>
            `;
        } else if (res.status === 'Confirmed') {
            actions = `
                <button class="btn-action-complete" onclick="updateReservationStatus(${res.id}, 'complete', 'admin')">✓ Complete</button>
                <button class="btn-action-cancel" onclick="updateReservationStatus(${res.id}, 'cancel', 'admin')">✕ Cancel</button>
            `;
        } else {
            actions = '<span style="color:#bdc3c7; font-style: italic; font-size: 13px;">N/A</span>';
        }
        
        const tr = document.createElement('tr');
        if (res.status === 'Pending') {
            tr.className = 'admin-row-pending';
        }
        
        const badgeType = res.order_type === 'Cart Order' 
            ? `<span style="font-size: 11px; background-color: rgba(217, 119, 6, 0.08); color: var(--color-accent); border: 1px solid rgba(217, 119, 6, 0.15); padding: 2px 6px; border-radius: 12px; margin-left: 6px; vertical-align: middle;">🛒 Order</span>`
            : `<span style="font-size: 11px; background-color: rgba(66, 135, 245, 0.08); color: #4287f5; border: 1px solid rgba(66, 135, 245, 0.15); padding: 2px 6px; border-radius: 12px; margin-left: 6px; vertical-align: middle;">📅 Reserve</span>`;

        tr.innerHTML = `
            <td style="font-weight: 700; color: var(--color-accent);">#R${String(res.id).padStart(3, '0')}</td>
            <td style="font-weight: 600; color: var(--text-primary);"><span style="font-size: 12px; background-color: var(--border-color-light); border: 1px solid var(--border-color); padding: 4px 8px; border-radius: 20px; color: var(--text-secondary);">@${res.username}</span></td>
            <td style="font-weight: 600; display: flex; align-items: center; gap: 4px;">${res.item_name}${badgeType}</td>
            <td style="font-weight: 600; color: var(--text-primary);">₱${price.toFixed(2)}</td>
            <td><span style="font-weight: 600; background-color: var(--border-color-light); border-radius: var(--radius-s); padding: 4px 10px; font-size: 13px; color: var(--text-primary); border: 1px solid var(--border-color);">${quantity}</span></td>
            <td style="font-weight: 700; color: var(--color-success);">₱${total.toFixed(2)}</td>
            <td style="color: var(--text-secondary); font-size: 13px;">${formattedDate}</td>
            <td><span class="badge-status ${statusClass}">${res.status}</span></td>
            <td><div style="display: flex; gap: 6px;">${actions}</div></td>
        `;
        tbody.appendChild(tr);
    });
}

function filterAdminReservations(status, btnEl) {
    const filterContainer = document.getElementById('admin-res-filters');
    if (filterContainer) {
        filterContainer.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    }
    if (btnEl) btnEl.classList.add('active');
    
    if (!window.allAdminReservations) return;
    
    let filtered = window.allAdminReservations;
    if (status !== 'All') {
        filtered = window.allAdminReservations.filter(res => res.status === status);
    }
    
    renderAdminReservationsTable(filtered);
}

async function updateReservationStatus(resId, action, role) {
    let confirmMsg = `Are you sure you want to ${action} this reservation?`;
    if (action === 'complete') {
        confirmMsg = `Complete reservation #${resId} and serve the food? This will record a transaction sale.`;
    }
    if (!confirm(confirmMsg)) return;
    
    try {
        const data = await apiFetch('update_reservation_status.php', 'POST', { id: resId, action });
        if (data.success) {
            alert(`Reservation status updated to: ${action === 'confirm' ? 'Confirmed' : action === 'cancel' ? 'Cancelled' : 'Completed'}`);
            
            if (role === 'admin') {
                loadAdminReservations();
                if (typeof loadMenu === 'function') loadMenu(); // refresh admin menu/POS table
                if (typeof loadDashboardStats === 'function') loadDashboardStats(); // refresh sales totals/active orders
            } else {
                loadCustomerReservations();
                if (typeof loadCustomerMenu === 'function') loadCustomerMenu(); // refresh customer menu stock
            }
        } else {
            alert(data.error || 'Failed to update reservation status.');
        }
    } catch (err) {
        alert('Database error while updating reservation status.');
    }
}

// ── POLLING CONTROLLERS ───────────────────────────────────────

function startCustomerReservationsPolling() {
    stopCustomerReservationsPolling();
    loadCustomerReservations();
    customerReservationsIntervalId = setInterval(loadCustomerReservations, 5000);
}

function stopCustomerReservationsPolling() {
    if (customerReservationsIntervalId) {
        clearInterval(customerReservationsIntervalId);
        customerReservationsIntervalId = null;
    }
}

function startAdminReservationsPolling() {
    stopAdminReservationsPolling();
    loadAdminReservations();
    adminReservationsIntervalId = setInterval(loadAdminReservations, 5000);
}

function stopAdminReservationsPolling() {
    if (adminReservationsIntervalId) {
        clearInterval(adminReservationsIntervalId);
        adminReservationsIntervalId = null;
    }
}
