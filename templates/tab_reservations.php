<!-- FOODLoop – templates/tab_reservations.php (Admin Reservations Manager) -->
<div id="tab-reservations" class="app-tab hidden">
    <div class="flex-header">
        <h2>Manage Orders & Reservations</h2>
    </div>
    
    <!-- Reservation Stats Panel -->
    <div class="reservation-stats-grid">
        <div class="res-stat-card stat-pending">
            <span class="stat-label">Pending Approval</span>
            <span class="stat-value" id="res-stat-pending">0</span>
        </div>
        <div class="res-stat-card stat-confirmed">
            <span class="stat-label">Ready / Confirmed</span>
            <span class="stat-value" id="res-stat-confirmed">0</span>
        </div>
        <div class="res-stat-card stat-completed">
            <span class="stat-label">Completed Today</span>
            <span class="stat-value" id="res-stat-completed">0</span>
        </div>
    </div>

    <!-- Filters & Table Container -->
    <div class="section-box">
        <div class="table-actions-row">
            <div class="filter-pills" id="admin-res-filters">
                <button class="filter-pill active" onclick="filterAdminReservations('All', this)">All</button>
                <button class="filter-pill" onclick="filterAdminReservations('Pending', this)">Pending</button>
                <button class="filter-pill" onclick="filterAdminReservations('Confirmed', this)">Confirmed</button>
                <button class="filter-pill" onclick="filterAdminReservations('Completed', this)">Completed</button>
                <button class="filter-pill" onclick="filterAdminReservations('Cancelled', this)">Cancelled</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Item Reserved</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Date/Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="admin-reservations-table-body">
                <tr>
                    <td colspan="9" style="text-align:center;color:#bdc3c7;">Loading reservations...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
