/**
 * Reports - DataTable Display and Styling
 * Handles table rendering, sorting, and formatting
 */

let reportDataTable = null;

/**
 * Render report data in table
 * Dynamically creates columns based on data structure
 * 
 * @param {array} data - Report data array
 * @param {string} reportType - Report type (for specific formatting)
 */
function renderReportTable(data, reportType) {
    if (!data || data.length === 0) {
        clearReportTable();
        return;
    }

    // Clear existing table first
    clearReportTable();

    const firstRow = data[0];
    const columns = Object.keys(firstRow);

    // Build header row
    const headerRow = $('<tr></tr>');
    columns.forEach((col, index) => {
        const header = formatColumnHeader(col);
        const sortIcon = index === 0 ? ' <i class="bi bi-arrow-down"></i>' : '';
        headerRow.append(`<th data-column="${col}">${header}${sortIcon}</th>`);
    });
    $('#reportTable thead tr').html(headerRow.html());

    // Build body rows with formatted values
    const tbody = $('#reportTable tbody');
    tbody.empty();

    data.forEach((row, rowIndex) => {
        const tr = $('<tr></tr>');
        tr.attr('data-row-index', rowIndex);

        columns.forEach(col => {
            const value = formatCellValue(row[col], col, reportType);
            const cell = $('<td></td>').html(value);

            // Add classes for specific column types
            if (col.includes('tcv') || col.includes('aov') || col.includes('revenue') || col.includes('avg')) {
                cell.addClass('text-end');
            }
            if (col.includes('date') || col.includes('updated') || col.includes('activity')) {
                cell.addClass('text-center');
            }
            if (col.includes('count') || col.includes('projects') || col.includes('days')) {
                cell.addClass('text-center');
            }

            tr.append(cell);
        });

        tbody.append(tr);
    });

    // Initialize DataTable after DOM is ready
    initializeDataTable();
    displayReportSummary(data, reportType);
}

/**
 * Initialize DataTable on report results
 * Properly handles cleanup and reinitalization
 */
function initializeDataTable() {
    // First, completely destroy any existing DataTable
    if (reportDataTable !== null) {
        try {
            reportDataTable.destroy();
            reportDataTable = null;
        } catch (e) {
            console.warn('Error destroying existing DataTable:', e);
        }
    }

    // Ensure table exists and is ready
    const table = $('#reportTable');
    if (table.length === 0) {
        console.error('Table element not found');
        return;
    }

    try {
        // Initialize new DataTable
        reportDataTable = table.DataTable({
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
            order: [[0, 'desc']],
            responsive: true,
            scrollX: true,
            stateSave: false,

            // DOM configuration
            dom: '<"row mb-3"<"col-sm-12"r>>' +
                '<"row"<"col-sm-12"t>>' +
                '<"row mt-3"<"col-sm-12 col-md-5 d-flex align-items-center"li><"col-sm-12 col-md-7"p>>',

            // Language settings
            language: {
                search: 'Search:',
                lengthMenu: '_MENU_',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                infoEmpty: 'No records found',
                infoFiltered: '(filtered from _MAX_ total records)',
                zeroRecords: 'No matching records found',
                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Processing...</span></div>',
                paginate: {
                    first: '<i class="bi bi-skip-start-fill"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    last: '<i class="bi bi-skip-end-fill"></i>'
                }
            }
        });

        // Add custom styling classes
        $('#reportTable').addClass('table-hover');
        $('#reportTable thead').addClass('table-light');

        // Setup event listeners
        setupTableEventListeners();

    } catch (error) {
        console.error('Error initializing DataTable:', error);
        reportDataTable = null;
    }
}

/**
 * Setup event listeners for table interactions
 */
function setupTableEventListeners() {
    if (!reportDataTable) return;

    // Row click to show full content
    $('#reportTable tbody').off('click').on('click', 'tr', function () {
        $(this).toggleClass('highlight');
    });

    // Search box styling
    const searchInput = $('#reportTable_filter input');
    if (searchInput.length) {
        searchInput.addClass('form-control form-control-sm');
    }

    const searchLabel = $('#reportTable_filter label');
    if (searchLabel.length) {
        searchLabel.addClass('d-flex align-items-center');
    }

    // Length menu styling
    const lengthSelect = $('#reportTable_length select');
    if (lengthSelect.length) {
        lengthSelect.addClass('form-select form-select-sm');
    }

    const lengthLabel = $('#reportTable_length label');
    if (lengthLabel.length) {
        lengthLabel.addClass('d-flex align-items-center');
    }
}

/**
 * Clear report table
 * Removes all data and destroys DataTable
 */
function clearReportTable() {
    // Destroy DataTable if it exists
    if (reportDataTable !== null) {
        try {
            reportDataTable.destroy();
        } catch (e) {
            console.warn('Error destroying DataTable:', e);
        }
        reportDataTable = null;
    }

    // Clear table content
    $('#reportTable tbody').empty();
    $('#reportTable thead tr').empty();
}

/**
 * Format column header (snake_case to Title Case)
 * 
 * @param {string} col - Column name
 * @returns {string} Formatted header
 */
function formatColumnHeader(col) {
    return col
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Format cell values for display
 * Handles currencies, dates, statuses, and counts
 * 
 * @param {any} value - Cell value
 * @param {string} col - Column name
 * @param {string} reportType - Report type
 * @returns {string} Formatted HTML value
 */
function formatCellValue(value, col, reportType) {
    // Null/empty values
    if (value === null || value === undefined || value === '') {
        return '<span class="text-muted">-</span>';
    }

    // Currency fields (TCV, AOV, Revenue)
    if (col.includes('tcv') || col.includes('aov') || col.includes('revenue') || col.includes('avg_project_value')) {
        return formatCurrency(value);
    }

    // Date fields
    if (col.includes('date') || col.includes('updated') || col.includes('activity')) {
        return formatDateDisplay(value);
    }

    // Count fields
    if (col.includes('count') || col.includes('projects') || col.includes('days_in') || col.includes('change_count')) {
        return parseInt(value) || 0;
    }

    // Status fields with badges
    if (col === 'current_status' || col === 'status' || col === 'last_status' || col === 'previous_status') {
        return formatStatusBadge(value);
    }

    // Text fields - truncate if too long
    if (typeof value === 'string' && value.length > 60) {
        return `<span title="${escapeHtml(value)}" class="cursor-help">${value.substring(0, 57)}...</span>`;
    }

    // Numeric fields
    if (typeof value === 'number') {
        return value.toLocaleString();
    }

    return value;
}

/**
 * Format currency value with EUR symbol
 * 
 * @param {number} value - Currency value
 * @returns {string} Formatted currency
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

/**
 * Format date value for display
 * Converts YYYY-MM-DD to DD-MMM-YYYY
 * 
 * @param {string} dateString - Date string
 * @returns {string} Formatted date
 */
function formatDateDisplay(dateString) {
    if (!dateString) return '<span class="text-muted">-</span>';

    try {
        const date = new Date(dateString + 'T00:00:00Z');
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    } catch (e) {
        return dateString;
    }
}

/**
 * Format status value with colored badge
 * 
 * @param {string} status - Status value
 * @returns {string} HTML badge
 */
function formatStatusBadge(status) {
    const statusColors = {
        'New': 'bg-info',
        'Qualifying': 'bg-warning text-dark',
        'Design': 'bg-primary',
        'Pending': 'bg-secondary',
        'Contract Signed': 'bg-success',
        'Completed': 'bg-success',
        'Cancelled': 'bg-dark',
        'Offer Refused': 'bg-danger',
        'No Solution': 'bg-danger'
    };

    const badgeClass = statusColors[status] || 'bg-secondary';
    return `<span class="badge ${badgeClass}">${status}</span>`;
}

/**
 * Escape HTML special characters
 * Prevents XSS in tooltips
 * 
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Display report summary statistics
 * Shows count and filtering information
 * 
 * @param {array} data - Report data
 * @param {string} reportType - Report type
 */
function displayReportSummary(data, reportType) {
    const recordCount = data.length;
    let summary = `<i class="bi bi-check-circle"></i> Report generated: <strong>${recordCount}</strong> ${recordCount === 1 ? 'record' : 'records'}`;

    // Add report-specific info
    switch (reportType) {
        case 'agent_performance':
            const totalRevenue = data.reduce((sum, row) => sum + (parseFloat(row.total_tcv) || 0), 0);
            summary += ` • Total revenue: <strong>${formatCurrency(totalRevenue)}</strong>`;
            break;

        case 'projects_since_april':
            const projectRevenue = data.reduce((sum, row) => sum + (parseFloat(row.aov) || 0), 0);
            summary += ` • Total AOV: <strong>${formatCurrency(projectRevenue)}</strong>`;
            break;

        case 'project_timeline':
            const timelineRevenue = data.reduce((sum, row) => sum + (parseFloat(row.tcv_project) || 0), 0);
            summary += ` • Total TCV: <strong>${formatCurrency(timelineRevenue)}</strong>`;
            break;
    }

    // Show summary in console
    console.log(`Report Summary: ${recordCount} records`);
}

/**
 * Export visible table data
 * Creates CSV from currently displayed rows
 */
function exportVisibleTableData() {
    if (!reportDataTable) {
        showToast('Error', 'No table data to export', 'error');
        return;
    }

    // Get filtered data from DataTable
    const filteredData = reportDataTable
        .rows({ search: 'applied' })
        .data()
        .toArray();

    if (filteredData.length === 0) {
        showToast('Error', 'No rows to export', 'error');
        return;
    }

    // Export using client-side method
    exportToCSVClientSide(filteredData, getExportFilename(currentReport));
}