// ─────────────────────────────────────────────────────────────
// FOODLoop – js/menu.js (Menu Management)
// ─────────────────────────────────────────────────────────────

let editingMenuId = null;

async function loadMenu() {
    try {
        const items = await apiFetch('get_menu.php');
        window.allMenuItems = items;
        renderMenuTable(items);
        if (typeof renderPosMenu === 'function') renderPosMenu(items);
        if (typeof populateCookDishDropdown === 'function') populateCookDishDropdown(items);
    } catch (err) {
        console.error('Failed to load menu:', err);
    }
}

function renderMenuTable(items) {
    const tbody = document.getElementById('menu-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';
    items.forEach(item => {
        const imgSrc = item.image ? `uploads/${item.image}` : getDefaultImage(item.name);
        const catKey  = (item.category || 'Main Dish').toLowerCase().replace(' ', '-');
        const isFeatured = parseInt(item.is_featured || 0) === 1;
        const starChar = isFeatured ? '★' : '☆';
        const starColor = isFeatured ? '#F1C40F' : '#bdc3c7';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>#M${String(item.id).padStart(3, '0')}</td>
            <td><img src="${imgSrc}" alt="${item.name}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;"></td>
            <td>${item.name}</td>
            <td><span class="category-badge ${catKey}">${item.category || 'Main Dish'}</span></td>
            <td>₱${parseFloat(item.price).toFixed(2)}</td>
            <td>${item.servings}</td>
            <td class="admin-only" style="text-align:center;">
                <span class="featured-star-toggle" onclick="toggleFeaturedItem(${item.id})" style="font-size:22px; cursor:pointer; color:${starColor}; user-select:none; display:inline-block; transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                    ${starChar}
                </span>
            </td>
            <td class="admin-only">
                <div style="display: flex; gap: 6px;">
                    <button class="btn-action-edit" onclick="startEditMenuItem(${item.id})">Edit</button>
                    <button class="btn-action-delete" onclick="deleteMenuItem(${item.id}, this)">Delete</button>
                </div>
            </td>`;
        if (currentRole !== 'admin') {
            tr.querySelectorAll('.admin-only').forEach(el => el.style.display = 'none');
        }
        tbody.appendChild(tr);
    });
}

function startEditMenuItem(id) {
    if (!window.allMenuItems) return;
    const item = window.allMenuItems.find(i => i.id === id);
    if (!item) {
        alert('Menu item not found.');
        return;
    }
    
    editingMenuId = id;
    
    // Populate form fields
    document.getElementById('menu-name').value = item.name;
    document.getElementById('menu-price').value = item.price;
    document.getElementById('menu-category').value = item.category || 'Main Dish';
    document.getElementById('menu-servings').value = item.servings;
    
    // Image preview
    const preview = document.getElementById('menu-image-preview');
    if (preview) {
        const imgSrc = item.image ? `uploads/${item.image}` : getDefaultImage(item.name);
        preview.innerHTML = `<img src="${imgSrc}" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--border-color);"><br><span style="font-size:11px;color:var(--text-muted);">Current Photo (leave blank to keep)</span>`;
    }
    
    // Change headers and buttons
    const formTitle = document.getElementById('menu-form-title');
    if (formTitle) {
        formTitle.innerText = `Edit Record Details (ID: #M${String(id).padStart(3, '0')})`;
    }
    const saveBtn = document.getElementById('menu-save-btn');
    if (saveBtn) {
        saveBtn.innerText = 'Update Record';
    }
    const cancelBtn = document.getElementById('menu-cancel-btn');
    if (cancelBtn) {
        cancelBtn.classList.remove('hidden');
    }
    
    // Smooth scroll to form
    const formTitleEl = document.getElementById('menu-form-title');
    if (formTitleEl) {
        formTitleEl.scrollIntoView({ behavior: 'smooth' });
    }
}

function cancelEditMenuItem() {
    editingMenuId = null;
    
    // Reset form fields
    document.getElementById('menu-name').value = '';
    document.getElementById('menu-price').value = '';
    document.getElementById('menu-category').selectedIndex = 0;
    document.getElementById('menu-servings').value = '10';
    document.getElementById('menu-image').value = '';
    
    const preview = document.getElementById('menu-image-preview');
    if (preview) {
        preview.innerHTML = '';
    }
    
    // Reset headers and buttons
    const formTitle = document.getElementById('menu-form-title');
    if (formTitle) {
        formTitle.innerText = 'Add New Record Details';
    }
    const saveBtn = document.getElementById('menu-save-btn');
    if (saveBtn) {
        saveBtn.innerText = 'Save Record';
    }
    const cancelBtn = document.getElementById('menu-cancel-btn');
    if (cancelBtn) {
        cancelBtn.classList.add('hidden');
    }
}

async function saveMenuItem() {
    const name      = document.getElementById('menu-name').value.trim();
    const price     = parseFloat(document.getElementById('menu-price').value);
    const category  = document.getElementById('menu-category').value;
    const servings  = parseInt(document.getElementById('menu-servings').value);
    const imageFile = document.getElementById('menu-image').files[0];

    if (!name || isNaN(price) || price <= 0 || isNaN(servings) || servings < 0) { 
        alert('Enter a valid name, price, and servings stock.'); 
        return; 
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('price', price);
    formData.append('category', category);
    formData.append('servings', servings);
    if (imageFile) formData.append('image', imageFile);

    const isEdit = editingMenuId !== null;
    if (isEdit) {
        formData.append('id', editingMenuId);
    }

    try {
        const endpoint = isEdit ? 'update_menu.php' : 'add_menu.php';
        const res  = await fetch(`${API}/${endpoint}`, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            alert(isEdit ? '✅ Menu item updated!' : '✅ Menu item saved!');
            cancelEditMenuItem();
            loadMenu();
            if (typeof loadCustomerMenu === 'function') loadCustomerMenu(); // Refresh customer menu stock
        } else {
            alert(data.error || 'Failed to save.');
        }
    } catch (err) { alert('Failed to save menu item.'); }
}

async function deleteMenuItem(id, btn) {
    if (!confirm('Delete this menu item?')) return;
    try {
        const data = await apiFetch('delete_menu.php', 'POST', { id });
        if (data.success) { btn.closest('tr').remove(); loadMenu(); }
    } catch (err) { alert('Failed to delete item.'); }
}

// Live preview when admin picks a photo
document.addEventListener('DOMContentLoaded', () => {
    const imgInput = document.getElementById('menu-image');
    if (imgInput) {
        imgInput.addEventListener('change', function () {
            const preview = document.getElementById('menu-image-preview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.innerHTML = `<img src="${e.target.result}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #2ecc71;">`;
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.innerHTML = '';
            }
        });
    }
});

// Toggle featured status of an item on the landing page carousel
async function toggleFeaturedItem(itemId) {
    try {
        const data = await apiFetch('toggle_featured.php', 'POST', { id: itemId });
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Featured status updated!', 'success');
            } else {
                alert(data.message);
            }
            loadMenu();
        } else {
            alert(data.error || 'Failed to update featured status.');
        }
    } catch (err) {
        console.error('Failed to toggle featured item:', err);
        alert('Database or connection error while toggling featured status.');
    }
}

// Bind function to global scope to support inline html triggers
window.toggleFeaturedItem = toggleFeaturedItem;
