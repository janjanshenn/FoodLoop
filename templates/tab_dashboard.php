<!-- FOODLoop – templates/tab_dashboard.php (Dashboard & Metrics Overview) -->
<div id="tab-dashboard" class="app-tab">
    <div class="flex-header">
        <h2>System Overview Dashboard</h2>
    </div>

    <div class="dashboard-grid">
        <div class="stat-card">
            <h3>Total Sales Today</h3>
            <p class="value text-success">₱<span id="stat-sales-today">0.00</span></p>
        </div>
        <div class="stat-card">
            <h3>Active Orders</h3>
            <p class="value text-primary" id="stat-active-orders">0</p>
        </div>
        <div class="stat-card stat-card-warning">
            <h3>Low Stock Items</h3>
            <p class="value text-warning" id="stat-low-stock-count">0</p>
            <p class="text-muted" id="stat-low-stock-list">None</p>
        </div>
    </div>

    <div class="chart-grid admin-only">
        <div class="section-box flex-2-mb0">
            <h3>Weekly Sales Revenue</h3>
            <div class="canvas-wrapper">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="section-box flex-1-mb0">
            <h3>Top Selling Items</h3>
            <div class="canvas-wrapper">
                <canvas id="itemsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="section-box admin-only">
        <h3>Recent Activity Log</h3>
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Action</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="recent-activity-table-body">
                <tr>
                    <td colspan="3" style="text-align:center;color:#bdc3c7;">Loading activity log...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
