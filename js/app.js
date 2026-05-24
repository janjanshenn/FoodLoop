// ─────────────────────────────────────────────────────────────
// FOODLoop – js/app.js (Main Framework & Core State)
// ─────────────────────────────────────────────────────────────

const API = 'api';

let currentUser       = '';
let currentRole       = '';
let posOrderItems     = [];
let posTotal          = 0;
let customerCart      = [];
let customerCartTotal = 0;
let salesChartInstance = null;
let itemsChartInstance = null;

// ── UTILITY ───────────────────────────────────────────────────
async function apiFetch(endpoint, method = 'GET', body = null) {
    const options = { method, headers: { 'Content-Type': 'application/json' }, credentials: 'include' };
    if (body) options.body = JSON.stringify(body);
    const res = await fetch(`${API}/${endpoint}`, options);
    return res.json();
}

// ── NAVIGATION ────────────────────────────────────────────────
// Page switcher routing
function navTo(screenId) {
    ['screen-home', 'app-layout', 'customer-layout'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
    const target = document.getElementById(screenId);
    if (target) target.classList.remove('hidden');
}

// Admin Tab switcher
function switchTab(tabId, btnEl) {
    if (typeof stopFeedbackPolling === 'function') stopFeedbackPolling();
    if (typeof stopDashboardPolling === 'function') stopDashboardPolling();
    if (typeof stopAdminReservationsPolling === 'function') stopAdminReservationsPolling();

    const sidebar = btnEl.closest('.sidebar');
    if (sidebar) sidebar.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    btnEl.classList.add('active');
    document.querySelectorAll('.app-tab').forEach(tab => tab.classList.add('hidden'));
    const targetTab = document.getElementById(tabId);
    if (targetTab) targetTab.classList.remove('hidden');
    
    if (tabId === 'tab-dashboard') {
        if (typeof startDashboardPolling === 'function') startDashboardPolling();
    }
    if (tabId === 'tab-reports') loadTransactions();
    if (tabId === 'tab-reservations') {
        if (typeof startAdminReservationsPolling === 'function') startAdminReservationsPolling();
    }
    if (tabId === 'tab-admin-feedback') {
        if (typeof startFeedbackPolling === 'function') startFeedbackPolling();
    }
}

// Customer Tab switcher
function switchCustomerTab(tabId, btnEl) {
    if (typeof stopCustomerReservationsPolling === 'function') stopCustomerReservationsPolling();

    const sidebar = btnEl.closest('.sidebar');
    if (sidebar) sidebar.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    btnEl.classList.add('active');
    document.querySelectorAll('.customer-tab').forEach(tab => tab.classList.add('hidden'));
    const targetTab = document.getElementById(tabId);
    if (targetTab) targetTab.classList.remove('hidden');
    
    if (tabId === 'tab-cust-reservations') {
        if (typeof startCustomerReservationsPolling === 'function') startCustomerReservationsPolling();
    }
}

// ── TOAST NOTIFICATIONS ────────────────────────────────────────
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast-message ${type}`;
    toast.innerText = message;
    
    container.appendChild(toast);
    
    // Auto-remove toast after animation completes (3.25 seconds total)
    setTimeout(() => {
        toast.remove();
    }, 3250);
}

// Initialize layout stats check on DOM load
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        const dashboard = document.getElementById('tab-dashboard');
        if (dashboard && !dashboard.classList.contains('hidden')) {
            loadDashboardStats();
        }
    }, 500);
});

// ── IMAGE HELPER ─────────────────────────────────────────────
const DEFAULT_IMAGES = {
    'Classic Pork Adobo':     'img/chicken adobo.jpg',
    'Sinigang na Baboy':      'img/Killer-Pork-Sinigang.jpg',
    'Pancit Canton Espesyal': 'img/pancit.jpg',
    'Lumpiang Shanghai':      'img/lumpia.webp',
    'Extra Rice':             'img/rice.webp',
    'Sprite (Bottle)':        'img/Sprite.png',
    'Coca-Cola (Bottle)':     'img/Coke.webp',
    'Sizzling Sisig':         'img/sizzling-pork-sisig-manila-main.webp',
    'Chopsuey':               'img/ChopSuey.jpg',
    'Bicol Express':          'img/Bicol-Express.jpg',
    'Beef Bulalo Soup':       'img/bulalo.jpg'
};
function getDefaultImage(name) {
    return DEFAULT_IMAGES[name] || 'img/Logo.png';
}

