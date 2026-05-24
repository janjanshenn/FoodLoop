// ─────────────────────────────────────────────────────────────
// FOODLoop – js/auth.js (Authentication Controllers)
// ─────────────────────────────────────────────────────────────

async function loginUser() {
    const username = document.getElementById('login-username').value.trim().toLowerCase();
    const password = document.getElementById('login-password').value;

    try {
        const data = await apiFetch('login.php', 'POST', { username, password });
        if (data.success) {
            currentUser = data.username;
            currentRole = data.role;
            const isAdmin = data.role === 'admin';
            const isStaff = data.role === 'staff';

            if (isAdmin || isStaff) {
                document.querySelectorAll('.admin-only').forEach(el => {
                    el.style.display = isAdmin ? '' : 'none';
                });
                document.getElementById('user-role-display').innerText = isAdmin ? 'Admin' : 'Staff';

                const dashboardBtn = document.getElementById('nav-dashboard');
                switchTab('tab-dashboard', dashboardBtn);
                navTo('app-layout');
                loadMenu();
                loadStock();
            } else {
                // Customer layout
                const nameDisplay = document.getElementById('customer-name-display');
                if (nameDisplay) nameDisplay.innerText = currentUser;
                navTo('customer-layout');
                loadCustomerMenu();
            }
            if (typeof updateFeedbackButtonVisibility === 'function') {
                updateFeedbackButtonVisibility();
            }
        } else {
            alert(data.error || 'Login failed.');
        }
    } catch (err) {
        alert('⚠️ Cannot connect to the server.\nMake sure XAMPP (Apache & MySQL) is running and you are accessing the app through your local server URL (e.g., http://localhost/FoodLoop/index.php).');
    }
}

async function registerUser() {
    const username = document.getElementById('reg-username').value.trim().toLowerCase();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    const confirmPassword = document.getElementById('reg-confirm-password').value;
    const errorDiv = document.getElementById('reg-error-msg');
    
    errorDiv.style.display = 'none';
    errorDiv.innerText = '';

    if (!username || !email || !password || !confirmPassword) {
        errorDiv.innerText = 'All fields are required.';
        errorDiv.style.display = 'block';
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errorDiv.innerText = 'Please enter a valid email address.';
        errorDiv.style.display = 'block';
        return;
    }

    if (password.length < 8) {
        errorDiv.innerText = 'Password must be at least 8 characters long.';
        errorDiv.style.display = 'block';
        return;
    }

    if (password !== confirmPassword) {
        errorDiv.innerText = 'Passwords do not match.';
        errorDiv.style.display = 'block';
        return;
    }

    try {
        const data = await apiFetch('register.php', 'POST', { username, email, password });
        if (data.success) {
            alert('✅ Registration successful! You can now log in.');
            toggleAuthForm('form-login');
            document.getElementById('login-username').value = username;
            document.getElementById('login-password').value = '';
            document.getElementById('reg-username').value = '';
            document.getElementById('reg-email').value = '';
            document.getElementById('reg-password').value = '';
            document.getElementById('reg-confirm-password').value = '';
        } else {
            errorDiv.innerText = data.error || 'Registration failed.';
            errorDiv.style.display = 'block';
        }
    } catch (err) {
        errorDiv.innerText = '⚠️ Registration failed. Database error.';
        errorDiv.style.display = 'block';
    }
}

async function requestResetOTP() {
    const email = document.getElementById('reset-email').value.trim();
    try {
        const data = await apiFetch('forgot_password.php', 'POST', { email });
        if (data.success || data.message) {
            alert('If that email exists, an OTP has been sent. Check the emails.log file.');
            document.getElementById('reset-step1').classList.add('hidden');
            document.getElementById('reset-step2').classList.remove('hidden');
        } else {
            alert(data.error || 'Failed to send OTP.');
        }
    } catch (err) { alert('⚠️ Database error.'); }
}

async function submitNewPassword() {
    const email = document.getElementById('reset-email').value.trim();
    const otp = document.getElementById('reset-otp').value.trim();
    const new_password = document.getElementById('reset-new-password').value;

    try {
        const data = await apiFetch('reset_password.php', 'POST', { email, otp, new_password });
        if (data.success) {
            alert('✅ Password reset successful! You can now log in.');
            toggleAuthForm('form-login');
        } else {
            alert(data.error || 'Password reset failed.');
        }
    } catch (err) { alert('⚠️ Reset failed. Database error.'); }
}

async function loadCaptcha(type) {
    // CAPTCHA security has been removed for easier testing in prototype
}

function toggleAuthForm(formId) {
    ['form-login', 'form-register', 'form-reset'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
    const target = document.getElementById(formId);
    if (target) target.classList.remove('hidden');

    if (formId === 'form-login') loadCaptcha('login');
    if (formId === 'form-register') loadCaptcha('reg');
    if (formId === 'form-reset') {
        document.getElementById('reset-step1').classList.remove('hidden');
        document.getElementById('reset-step2').classList.add('hidden');
    }
}

async function logoutUser() {
    try {
        const data = await apiFetch('logout.php', 'POST');
        if (data.success) {
            currentUser = '';
            currentRole = '';
            if (typeof updateFeedbackButtonVisibility === 'function') {
                updateFeedbackButtonVisibility();
            }
            if (typeof stopFeedbackPolling === 'function') stopFeedbackPolling();
            if (typeof stopDashboardPolling === 'function') stopDashboardPolling();
            if (typeof stopAdminReservationsPolling === 'function') stopAdminReservationsPolling();
            if (typeof stopCustomerReservationsPolling === 'function') stopCustomerReservationsPolling();
            posOrderItems = [];
            posTotal = 0;
            customerCart = [];
            customerCartTotal = 0;
            // Clear UI displays
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) cartCountEl.innerText = '0';
            const cartTotalEl = document.getElementById('cart-total-price');
            if (cartTotalEl) cartTotalEl.innerText = '0.00';
            const usernameInput = document.getElementById('login-username');
            if (usernameInput) usernameInput.value = '';
            const passwordInput = document.getElementById('login-password');
            if (passwordInput) passwordInput.value = '';
            // Go to home
            navTo('screen-home');
            toggleAuthForm('form-login');
        }
    } catch (err) {
        console.error('Logout failed:', err);
        // Fallback redirection in case network fails
        navTo('screen-home');
    }
}

