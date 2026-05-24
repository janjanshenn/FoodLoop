<!-- FOODLoop – templates/tab_admin_feedback.php (Customer Feedback & Moderation) -->
<div id="tab-admin-feedback" class="app-tab hidden">
    <div class="flex-header">
        <h2>Customer Feedback & Reviews</h2>
    </div>

    <!-- Analytics Dashboard Cards -->
    <div class="feedback-analytics-row">
        <!-- Average Score -->
        <div class="rating-summary-card">
            <h3>Average Score</h3>
            <div class="rating-summary-big" id="rating-avg-val">0.0</div>
            <div class="rating-summary-stars" id="rating-avg-stars">
                <!-- Stars rendered dynamically -->
                ⭐⭐⭐⭐⭐
            </div>
            <div class="rating-summary-count" id="rating-total-count">0 reviews</div>
        </div>

        <!-- Rating Distribution progress bars -->
        <div class="rating-distribution-card">
            <h3>Rating Distribution</h3>
            
            <!-- 5 stars -->
            <div class="distribution-bar-row">
                <div class="dist-label">5 stars</div>
                <div class="dist-progress-bg">
                    <div class="dist-progress-fill" id="dist-fill-5"></div>
                </div>
                <div class="dist-count" id="dist-count-5">0</div>
            </div>

            <!-- 4 stars -->
            <div class="distribution-bar-row">
                <div class="dist-label">4 stars</div>
                <div class="dist-progress-bg">
                    <div class="dist-progress-fill" id="dist-fill-4"></div>
                </div>
                <div class="dist-count" id="dist-count-4">0</div>
            </div>

            <!-- 3 stars -->
            <div class="distribution-bar-row">
                <div class="dist-label">3 stars</div>
                <div class="dist-progress-bg">
                    <div class="dist-progress-fill" id="dist-fill-3"></div>
                </div>
                <div class="dist-count" id="dist-count-3">0</div>
            </div>

            <!-- 2 stars -->
            <div class="distribution-bar-row">
                <div class="dist-label">2 stars</div>
                <div class="dist-progress-bg">
                    <div class="dist-progress-fill" id="dist-fill-2"></div>
                </div>
                <div class="dist-count" id="dist-count-2">0</div>
            </div>

            <!-- 1 star -->
            <div class="distribution-bar-row">
                <div class="dist-label">1 star</div>
                <div class="dist-progress-bg">
                    <div class="dist-progress-fill" id="dist-fill-1"></div>
                </div>
                <div class="dist-count" id="dist-count-1">0</div>
            </div>
        </div>
    </div>

    <!-- Filter Actions -->
    <div class="table-actions-row">
        <div class="filter-pills" id="feedback-rating-filters">
            <button class="filter-pill active" onclick="filterAdminFeedback('All', this)">All Ratings</button>
            <button class="filter-pill" onclick="filterAdminFeedback(5, this)">5 Stars ⭐</button>
            <button class="filter-pill" onclick="filterAdminFeedback(4, this)">4 Stars ⭐</button>
            <button class="filter-pill" onclick="filterAdminFeedback(3, this)">3 Stars ⭐</button>
            <button class="filter-pill" onclick="filterAdminFeedback(2, this)">2 Stars ⭐</button>
            <button class="filter-pill" onclick="filterAdminFeedback(1, this)">1 Star ⭐</button>
        </div>
        <div class="search-box-wrapper" style="width: 250px;">
            <input type="text" id="feedback-search-input" placeholder="Search comments..." style="margin: 0; padding: 8px 12px; height: 36px;">
        </div>
    </div>

    <!-- Feedback Entries Container -->
    <div class="section-box" style="margin-top: 0; min-height: 250px;">
        <div id="feedback-list-container" class="feedback-grid">
            <!-- Rendered via JS -->
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 40px 0;">
                Loading customer reviews...
            </div>
        </div>
    </div>
</div>
