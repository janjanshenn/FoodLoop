// ─────────────────────────────────────────────────────────────
// FOODLoop – js/feedback.js (Interactive Feedback System & Moderation)
// ─────────────────────────────────────────────────────────────

let currentFeedbackRating = 0;
let adminFeedbacksList = [];
let currentAdminRatingFilter = 'All';
let feedbackIntervalId = null;

// ── POLLING FOR REAL-TIME UPDATES ─────────────────────────────

function startFeedbackPolling() {
    // Clear any existing polling timer first
    stopFeedbackPolling();

    // Load immediately
    loadAdminFeedback();

    // Set interval to pull new feedback every 5 seconds
    feedbackIntervalId = setInterval(loadAdminFeedback, 5000);
}

function stopFeedbackPolling() {
    if (feedbackIntervalId) {
        clearInterval(feedbackIntervalId);
        feedbackIntervalId = null;
    }
}

// ── CUSTOMER MODAL DIALOG CONTROLS ────────────────────────────

function openFeedbackModal() {
    const modal = document.getElementById('feedback-modal');
    if (!modal) return;

    // Reset Form Fields
    const nameInput = document.getElementById('feedback-name');
    if (nameInput) {
        // Prefill with logged-in user if exists, otherwise empty
        nameInput.value = (typeof currentUser !== 'undefined' && currentUser) ? currentUser : '';
    }

    const commentsTextarea = document.getElementById('feedback-comments');
    if (commentsTextarea) commentsTextarea.value = '';

    // Reset Stars
    currentFeedbackRating = 0;
    document.getElementById('feedback-rating-value').value = 0;
    resetStarsHighlight();

    // Show Modal
    modal.classList.add('open');
}

function closeFeedbackModal() {
    const modal = document.getElementById('feedback-modal');
    if (modal) {
        modal.classList.remove('open');
    }
}

// ── STAR RATING INTERACTIVITY ──────────────────────────────────

function highlightStars(rating) {
    const stars = document.querySelectorAll('#feedback-stars .star-icon');
    stars.forEach((star, idx) => {
        if (idx < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function resetStarsHighlight() {
    const stars = document.querySelectorAll('#feedback-stars .star-icon');
    stars.forEach((star, idx) => {
        if (idx < currentFeedbackRating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function setFeedbackRating(rating) {
    currentFeedbackRating = rating;
    document.getElementById('feedback-rating-value').value = rating;
    resetStarsHighlight();
}

// ── SUBMIT FEEDBACK ───────────────────────────────────────────

async function submitFeedbackForm(event) {
    event.preventDefault();

    const nameInput = document.getElementById('feedback-name');
    const commentsInput = document.getElementById('feedback-comments');
    const ratingVal = parseInt(document.getElementById('feedback-rating-value').value);

    if (!nameInput || nameInput.value.trim() === '') {
        if (typeof showToast === 'function') showToast('Please enter your name.', 'warning');
        return;
    }

    if (ratingVal < 1 || ratingVal > 5) {
        if (typeof showToast === 'function') showToast('Please select a star rating between 1 and 5.', 'warning');
        return;
    }

    const submitBtn = document.getElementById('feedback-submit-btn');
    const origText = submitBtn.innerText;
    submitBtn.innerText = 'Submitting...';
    submitBtn.disabled = true;

    try {
        const payload = {
            username: nameInput.value.trim(),
            rating: ratingVal,
            comments: commentsInput ? commentsInput.value.trim() : ''
        };

        const res = await apiFetch('save_feedback.php', 'POST', payload);

        if (res.success) {
            if (typeof showToast === 'function') {
                showToast(res.message || 'Thank you for your feedback! ⭐', 'success');
            } else {
                alert('Thank you for your feedback!');
            }
            closeFeedbackModal();

            // Refresh Admin view if the administrator tab is open/active
            const adminFeedbackTab = document.getElementById('tab-admin-feedback');
            if (adminFeedbackTab && !adminFeedbackTab.classList.contains('hidden')) {
                loadAdminFeedback();
            }
        } else {
            if (typeof showToast === 'function') showToast(res.error || 'Failed to submit feedback.', 'warning');
        }
    } catch (err) {
        console.error('Error submitting feedback:', err);
        if (typeof showToast === 'function') showToast('Network/Database error while submitting feedback.', 'warning');
    } finally {
        submitBtn.innerText = origText;
        submitBtn.disabled = false;
    }
}

// ── ADMIN FEEDBACK INTERFACE ──────────────────────────────────

async function loadAdminFeedback() {
    const listContainer = document.getElementById('feedback-list-container');
    if (!listContainer) return;

    try {
        const data = await apiFetch('get_feedback.php');

        if (data.success) {
            adminFeedbacksList = data.feedbacks || [];

            // 1. Update Metrics Cards
            const avgValEl = document.getElementById('rating-avg-val');
            if (avgValEl) avgValEl.innerText = data.average_rating.toFixed(1);

            const avgStarsEl = document.getElementById('rating-avg-stars');
            if (avgStarsEl) {
                avgStarsEl.innerHTML = getDisplayStarsHTML(Math.round(data.average_rating));
            }

            const totalCountEl = document.getElementById('rating-total-count');
            if (totalCountEl) totalCountEl.innerText = `${data.total_count} review${data.total_count === 1 ? '' : 's'}`;

            // 2. Update Rating Distribution Progress Bars
            for (let stars = 1; stars <= 5; stars++) {
                const distInfo = data.distribution[stars] || { count: 0, percentage: 0 };

                const fillEl = document.getElementById(`dist-fill-${stars}`);
                if (fillEl) {
                    fillEl.style.width = `${distInfo.percentage}%`;
                }

                const countEl = document.getElementById(`dist-count-${stars}`);
                if (countEl) {
                    countEl.innerText = distInfo.count;
                }
            }

            // 3. Render list items
            renderFilteredFeedback();
        } else {
            listContainer.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #E74C3C; padding: 40px 0;">Error: ${data.error}</div>`;
        }
    } catch (err) {
        console.error('Failed to load feedback details:', err);
        listContainer.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #E74C3C; padding: 40px 0;">Failed to fetch feedback logs from database.</div>`;
    }
}

function getDisplayStarsHTML(starsCount) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= starsCount) {
            html += '<span style="color:#F1C40F;">★</span>';
        } else {
            html += '<span style="color:#EAE6DF;">★</span>';
        }
    }
    return html;
}

function renderFilteredFeedback() {
    const listContainer = document.getElementById('feedback-list-container');
    if (!listContainer) return;

    // Retrieve search filter values
    const searchInput = document.getElementById('feedback-search-input');
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';

    const filtered = adminFeedbacksList.filter(fb => {
        // Star Rating filter
        const matchRating = currentAdminRatingFilter === 'All' || parseInt(fb.rating) === parseInt(currentAdminRatingFilter);

        // Search query filter (matches comments or username)
        const matchSearch = searchQuery === '' ||
            fb.username.toLowerCase().includes(searchQuery) ||
            (fb.comments && fb.comments.toLowerCase().includes(searchQuery));

        return matchRating && matchSearch;
    });

    listContainer.innerHTML = '';

    if (filtered.length === 0) {
        listContainer.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 50px 0;">
                <div style="font-size: 32px; margin-bottom: 8px;">💬</div>
                <strong>No reviews found</strong>
                <p style="margin: 4px 0 0 0; font-size: 13px;">No feedback match the selected criteria.</p>
            </div>
        `;
        return;
    }

    filtered.forEach(fb => {
        const card = document.createElement('div');
        card.className = 'feedback-card';

        // Formatting timestamp nicely
        let dateStr = fb.created_at;
        try {
            const dateObj = new Date(fb.created_at);
            dateStr = dateObj.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' +
                dateObj.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
        } catch (e) { }

        const commentsText = fb.comments ? fb.comments : '<em>No comment written.</em>';
        const starsHTML = getDisplayStarsHTML(parseInt(fb.rating));

        card.innerHTML = `
            <div class="feedback-card-header">
                <span class="feedback-card-author">${escapeHTML(fb.username)}</span>
                <span class="feedback-card-date">${dateStr}</span>
            </div>
            <div class="feedback-card-stars">
                ${starsHTML}
            </div>
            <div class="feedback-card-comment" style="margin-bottom: 0;">
                ${commentsText}
            </div>
        `;

        listContainer.appendChild(card);
    });
}

function filterAdminFeedback(ratingVal, btnEl) {
    // Toggle active pill classes
    const pillsContainer = document.getElementById('feedback-rating-filters');
    if (pillsContainer) {
        pillsContainer.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    }
    btnEl.classList.add('active');

    currentAdminRatingFilter = ratingVal;
    renderFilteredFeedback();
}

async function deleteFeedbackEntry(id) {
    if (!confirm('Are you sure you want to delete this customer feedback? This action cannot be undone.')) {
        return;
    }

    try {
        const res = await apiFetch('delete_feedback.php', 'POST', { id });
        if (res.success) {
            if (typeof showToast === 'function') showToast(res.message || 'Feedback deleted.', 'success');
            loadAdminFeedback();
        } else {
            if (typeof showToast === 'function') showToast(res.error || 'Failed to delete.', 'warning');
        }
    } catch (err) {
        console.error('Error deleting feedback:', err);
        if (typeof showToast === 'function') showToast('Database error during deletion.', 'warning');
    }
}

// ── UTILITIES ──────────────────────────────────────────────────

function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Set up event listener for the live search input and initialize visibility
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('feedback-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            renderFilteredFeedback();
        });
    }
    // Set initial visibility of feedback button
    setTimeout(updateFeedbackButtonVisibility, 100);
});

function updateFeedbackButtonVisibility() {
    const rateBtn = document.getElementById('floating-rate-btn');
    if (!rateBtn) return;
    if (typeof currentRole !== 'undefined' && currentRole === 'customer') {
        rateBtn.classList.remove('hidden');
    } else {
        rateBtn.classList.add('hidden');
    }
}
