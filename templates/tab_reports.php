<!-- FOODLoop – templates/tab_reports.php (Sales History & Data Exports) -->
<div id="tab-reports" class="app-tab hidden">
    <h2>Generated Sales Reports</h2>

    <!-- Summary Metrics Cards Grid -->
    <div class="metrics-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: var(--space-m);">
        <div class="metric-card">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value" id="report-total-revenue">₱0.00</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Total Transactions</div>
            <div class="metric-value" id="report-total-orders">0</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Average Order Value</div>
            <div class="metric-value" id="report-avg-order">₱0.00</div>
        </div>
    </div>

    <div class="section-box">
        <div class="flex-header-small" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-s); flex-wrap: wrap; gap: var(--space-s);">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <h3 id="report-summary-title" style="margin: 0;">Daily Summary</h3>
                
                <!-- Filter Type Dropdown -->
                <select id="report-type" onchange="toggleReportType()" style="padding: 6px 12px; border-radius: var(--radius-s); border: 1px solid var(--border-color); background-color: #FAF9F6; font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer; color: var(--text-primary);">
                    <option value="daily">Daily Report</option>
                    <option value="monthly">Monthly Report</option>
                </select>

                <!-- Daily Date Picker -->
                <input type="date" id="report-date" onchange="loadReportData()" style="padding: 6px 12px; border-radius: var(--radius-s); border: 1px solid var(--border-color); font-size: 13px; font-family: inherit; color: var(--text-primary); background-color: #FAF9F6;">

                <!-- Monthly Month Picker -->
                <input type="month" id="report-month" onchange="loadReportData()" style="display: none; padding: 6px 12px; border-radius: var(--radius-s); border: 1px solid var(--border-color); font-size: 13px; font-family: inherit; color: var(--text-primary); background-color: #FAF9F6;">
            </div>
            
            <div style="display: flex; gap: 8px;">
                <button class="btn-secondary-large" onclick="exportPDF()">Export PDF</button>
                <button onclick="exportExcel()">Generate Excel</button>
            </div>
        </div>

        <table id="reports-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Date & Time</th>
                    <th>Cashier</th>
                    <th>Items Sold</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody id="transactions-table-body">
                <tr>
                    <td colspan="5" style="text-align:center;color:#bdc3c7;">Loading transactions...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
