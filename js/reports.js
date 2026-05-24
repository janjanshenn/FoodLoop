// ─────────────────────────────────────────────────────────────
// FOODLoop – js/reports.js (Sales Reports & Exports)
// ─────────────────────────────────────────────────────────────

// Bootloader called when entering the reports tab
async function loadTransactions() {
    const dateInput = document.getElementById('report-date');
    const monthInput = document.getElementById('report-month');
    
    // Set default values if empty
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    if (monthInput && !monthInput.value) {
        monthInput.value = new Date().toISOString().slice(0, 7);
    }
    
    await loadReportData();
}

// Toggle date/month input display based on selected filter type
function toggleReportType() {
    const type = document.getElementById('report-type').value;
    const dateInput = document.getElementById('report-date');
    const monthInput = document.getElementById('report-month');
    const title = document.getElementById('report-summary-title');
    
    if (type === 'daily') {
        if (dateInput) dateInput.style.display = 'inline-block';
        if (monthInput) monthInput.style.display = 'none';
        if (title) title.innerText = 'Daily Summary';
    } else {
        if (dateInput) dateInput.style.display = 'none';
        if (monthInput) monthInput.style.display = 'inline-block';
        if (title) title.innerText = 'Monthly Summary';
    }
    loadReportData();
}

// Fetch transaction data based on active filters
async function loadReportData() {
    const type = document.getElementById('report-type').value;
    let url = 'get_transactions.php';
    
    if (type === 'daily') {
        const dateVal = document.getElementById('report-date').value;
        if (dateVal) url += `?date=${dateVal}`;
    } else {
        const monthVal = document.getElementById('report-month').value;
        if (monthVal) url += `?month=${monthVal}`;
    }
    
    try {
        const rows = await apiFetch(url);
        renderTransactionsTable(rows);
        updateReportMetrics(rows);
    } catch (err) {
        console.error('Failed to load transaction data:', err);
    }
}

// Update the metrics cards (Total Sales, Total Orders, Avg Order Value)
function updateReportMetrics(rows) {
    let totalSales = 0;
    const totalOrders = rows.length;
    
    rows.forEach(row => {
        totalSales += parseFloat(row.total);
    });
    
    const avgOrder = totalOrders > 0 ? totalSales / totalOrders : 0;
    
    const revEl = document.getElementById('report-total-revenue');
    const ordersEl = document.getElementById('report-total-orders');
    const avgEl = document.getElementById('report-avg-order');
    
    if (revEl) revEl.innerText = `₱${totalSales.toFixed(2)}`;
    if (ordersEl) ordersEl.innerText = totalOrders;
    if (avgEl) avgEl.innerText = `₱${avgOrder.toFixed(2)}`;
}

// Render the transaction data in the reports table
function renderTransactionsTable(rows) {
    const tbody = document.getElementById('transactions-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#bdc3c7;">No transactions found.</td></tr>';
        return;
    }
    rows.forEach(row => {
        const dt = new Date(row.created_at);
        // Format as "May 24, 10:30 AM"
        const formattedDate = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' + 
                              dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>#${row.id}</td>
            <td>${formattedDate}</td>
            <td>${row.cashier}</td>
            <td>${row.items_summary}</td>
            <td>₱${parseFloat(row.total).toFixed(2)}</td>`;
        tbody.appendChild(tr);
    });
}

function exportPDF() {
    const tbody = document.getElementById('transactions-table-body');
    if (!tbody || tbody.innerText.includes("No transactions found") || tbody.innerText.includes("Loading transactions")) {
        alert("No transaction data available to export.");
        return;
    }
    
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const type = document.getElementById('report-type').value;
        const selectedVal = type === 'daily' 
            ? document.getElementById('report-date').value 
            : document.getElementById('report-month').value;
        
        doc.setFont("helvetica", "bold");
        doc.setFontSize(18);
        doc.text("FOODLoop Sales Report", 14, 20);
        
        doc.setFont("helvetica", "normal");
        doc.setFontSize(10);
        const dateStr = new Date().toLocaleDateString('en-PH', { dateStyle: 'long' });
        const timeStr = new Date().toLocaleTimeString('en-PH', { timeStyle: 'short' });
        doc.text(`Report Period: ${type.toUpperCase()} (${selectedVal})`, 14, 28);
        doc.text(`Generated on: ${dateStr} at ${timeStr} by ${currentUser || 'Admin'}`, 14, 34);
        
        doc.autoTable({
            html: '#reports-table',
            startY: 40,
            theme: 'grid',
            headStyles: { fillColor: [42, 77, 62], textColor: [255, 255, 255] },
            styles: { font: 'helvetica', fontSize: 9 },
            margin: { top: 40 }
        });
        
        doc.save(`foodloop_${type}_report_${selectedVal}.pdf`);
    } catch (err) {
        console.error("PDF export failed:", err);
        alert("Failed to export PDF: " + err.message);
    }
}

function exportExcel() {
    const tbody = document.getElementById('transactions-table-body');
    if (!tbody || tbody.innerText.includes("No transactions found") || tbody.innerText.includes("Loading transactions")) {
        alert("No transaction data available to export.");
        return;
    }
    
    try {
        const table = document.getElementById('reports-table');
        if (!table) { alert("Reports table not found."); return; }
        
        const type = document.getElementById('report-type').value;
        const selectedVal = type === 'daily' 
            ? document.getElementById('report-date').value 
            : document.getElementById('report-month').value;
            
        const wb = XLSX.utils.table_to_book(table, { sheet: "Sales Summary" });
        XLSX.writeFile(wb, `foodloop_${type}_report_${selectedVal}.xlsx`);
    } catch (err) {
        console.error("Excel export failed:", err);
        alert("Failed to generate Excel: " + err.message);
    }
}
