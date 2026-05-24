// ─────────────────────────────────────────────────────────────
// FOODLoop – js/dashboard.js (Dashboard Controls & Charts)
// ─────────────────────────────────────────────────────────────

async function loadDashboardStats() {
    try {
        const stats = await apiFetch('get_dashboard_stats.php');
        if (stats.success) {
            // Update statistic cards
            const salesEl = document.getElementById('stat-sales-today');
            if (salesEl) salesEl.innerText = parseFloat(stats.sales_today).toFixed(2);
            
            const activeEl = document.getElementById('stat-active-orders');
            if (activeEl) activeEl.innerText = stats.active_orders;
            
            const lowQtyEl = document.getElementById('stat-low-stock-count');
            if (lowQtyEl) lowQtyEl.innerText = stats.low_stock_count;
            
            const lowListEl = document.getElementById('stat-low-stock-list');
            if (lowListEl) lowListEl.innerText = stats.low_stock_list;

            // Render Recent Activity table
            const tbody = document.getElementById('recent-activity-table-body');
            if (tbody) {
                tbody.innerHTML = '';
                if (!stats.activities || stats.activities.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#bdc3c7;">No recent activities.</td></tr>';
                } else {
                    stats.activities.forEach(act => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${act.time}</td>
                            <td>${act.action}</td>
                            <td><span class="badge ${act.badge_class}">${act.status}</span></td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            }

            // Initialize/Update charts with dynamic data
            initCharts(stats.weekly_trend, stats.top_items);
        }
    } catch (err) {
        console.error('Failed to load dashboard stats:', err);
    }
}

function initCharts(weeklyTrend = null, topItems = null) {
    if (typeof Chart === 'undefined') return;
    
    // Set datasets
    const trendLabels = (weeklyTrend && weeklyTrend.labels) ? weeklyTrend.labels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const trendData = (weeklyTrend && weeklyTrend.data) ? weeklyTrend.data : [0, 0, 0, 0, 0, 0, 0];
    
    const itemsLabels = (topItems && topItems.length > 0) ? topItems.map(i => i.name) : ['No Data'];
    const itemsData = (topItems && topItems.length > 0) ? topItems.map(i => i.percentage) : [100];
    const itemsColors = (topItems && topItems.length > 0) ? ['#3498db', '#e74c3c', '#f1c40f', '#9b59b6'] : ['#bdc3c7'];

    // Register ChartDataLabels plugin if available
    if (typeof ChartDataLabels !== 'undefined') {
        try {
            Chart.register(ChartDataLabels);
        } catch (e) {
            console.warn('ChartDataLabels already registered or failed:', e);
        }
    }

    const ctxSales = document.getElementById('salesChart');
    if (ctxSales) {
        if (salesChartInstance) {
            salesChartInstance.data.labels = trendLabels;
            salesChartInstance.data.datasets[0].data = trendData;
            salesChartInstance.update();
        } else {
            salesChartInstance = new Chart(ctxSales, {
                type: 'bar',
                data: {
                    labels: trendLabels,
                    datasets: [{ label: 'Daily Revenue (₱)', data: trendData, backgroundColor: '#2ecc71', borderRadius: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    plugins: {
                        datalabels: {
                            color: '#fff', anchor: 'end', align: 'bottom',
                            font: { weight: 'bold' }, formatter: (v) => '₱' + parseFloat(v).toLocaleString()
                        }
                    }
                }
            });
        }
    }

    const ctxItems = document.getElementById('itemsChart');
    if (ctxItems) {
        if (itemsChartInstance) {
            itemsChartInstance.data.labels = itemsLabels;
            itemsChartInstance.data.datasets[0].data = itemsData;
            itemsChartInstance.data.datasets[0].backgroundColor = itemsColors;
            itemsChartInstance.update();
        } else {
            itemsChartInstance = new Chart(ctxItems, {
                type: 'doughnut',
                data: {
                    labels: itemsLabels,
                    datasets: [{ data: itemsData, backgroundColor: itemsColors, borderWidth: 0 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff', font: { weight: 'bold', size: 12 },
                            formatter: (v) => (v === 100 && itemsLabels[0] === 'No Data') ? '' : v + '%'
                        }
                    }
                }
            });
        }
    }
}

// ── POLLING CONTROLLERS ───────────────────────────────────────

let dashboardIntervalId = null;

function startDashboardPolling() {
    stopDashboardPolling();
    loadDashboardStats();
    dashboardIntervalId = setInterval(loadDashboardStats, 15000); // 15 seconds
}

function stopDashboardPolling() {
    if (dashboardIntervalId) {
        clearInterval(dashboardIntervalId);
        dashboardIntervalId = null;
    }
}
