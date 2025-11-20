/**
 * Reports - Filter Management
 * Handles filter UI interactions, validation, and report selection
 */

let currentReport = null;
let filterOptions = {};

$(document).ready(function () {
    initializeFilters();
    setupEventListeners();
});

/**
 * Initialize filters
 * Load filter options from API and populate dropdowns
 */
function initializeFilters() {
    $.ajax({
        url: '../api/reports.php?action=getFilterOptions',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                filterOptions = response.data;
                populateFilterDropdowns();
            } else {
                showToast('Error', 'Failed to load filters: ' + response.error, 'error');
            }
        },
        error: function () {
            showToast('Error', 'Failed to load filter options', 'error');
        }
    });
}

/**
 * Populate all filter dropdowns with available options
 */
function populateFilterDropdowns() {
    // Team filter
    const teamSelect = $('#filterTeam');
    teamSelect.empty();
    if (filterOptions.teams && filterOptions.teams.length > 0) {
        filterOptions.teams.forEach(team => {
            teamSelect.append(`<option value="${team}">${team}</option>`);
        });
    } else {
        teamSelect.append('<option value="">No teams available</option>');
    }

    // Status filter
    const statusSelect = $('#filterStatus');
    statusSelect.empty();
    if (filterOptions.statuses && filterOptions.statuses.length > 0) {
        filterOptions.statuses.forEach(status => {
            statusSelect.append(`<option value="${status}">${status}</option>`);
        });
    } else {
        statusSelect.append('<option value="">No statuses available</option>');
    }

    // Project Type filter
    const typeSelect = $('#filterProjectType');
    typeSelect.empty();
    if (filterOptions.projectTypes && filterOptions.projectTypes.length > 0) {
        filterOptions.projectTypes.forEach(type => {
            typeSelect.append(`<option value="${type}">${type}</option>`);
        });
    } else {
        typeSelect.append('<option value="">No types available</option>');
    }
}

/**
 * Setup event listeners for report cards and buttons
 */
function setupEventListeners() {
    // Report card selection
    $('.report-card').on('click', function () {
        const reportType = $(this).data('report');
        selectReport(reportType);
    });

    // Filter buttons
    $('#btnRefreshReport').on('click', function () {
        runReport();
    });

    $('#btnResetFilters').on('click', function () {
        resetFilters();
    });

    $('#btnExportCSV').on('click', function () {
        exportReport();
    });

    // Enter key on date inputs runs report
    $('#filterDateFrom, #filterDateTo').on('keypress', function (e) {
        if (e.which === 13) {
            runReport();
        }
    });
}

/**
 * Select a report and show appropriate filters
 * 
 * @param {string} reportType - Type of report (agent_performance, projects_since_april, project_timeline)
 */
function selectReport(reportType) {
    currentReport = reportType;

    // Always hide contract signed filters first
    $('#contractSignedFiltersContainer').hide();

    // Hide all conditional filters first
    $('#projectTypeFilterContainer').hide();
    $('#fiscalYearFilterContainer').hide();

    // Reset filters
    resetFilters();

    // Configure filters based on report type
    switch (reportType) {
        case 'agent_performance':
            configureAgentPerformanceFilters();
            break;

        case 'projects_since_april':
            configureProjectsSinceAprilFilters();
            break;

        case 'project_timeline':
            configureProjectTimelineFilters();
            break;

        case 'contract_signed_analysis':
            configureContractSignedAnalysisFilters();
            break;

        default:
            showToast('Error', 'Unknown report type', 'error');
            return;
    }

    // Show report display area
    $('#initialState').hide();
    $('#reportDisplay').show();

    // Scroll to filter panel
       document.getElementById('filterPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Configure filters for Agent Performance report
 * Filters: Date range, Team, Status
 */
function configureAgentPerformanceFilters() {
    // Set default dates (last 12 months)
    const today = new Date();
    const lastYear = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());

    $('#filterDateFrom').val(formatDateForInput(lastYear));
    $('#filterDateTo').val(formatDateForInput(today));

    // Show only Date, Team, Status filters
    $('#filterDateFrom').closest('.col-md-3').show();
    $('#filterDateTo').closest('.col-md-3').show();
    $('#filterTeam').closest('.col-md-3').show();
    $('#filterStatus').closest('.col-md-3').show();

    $('#reportTitle').text('Agent Performance Report');
}

/**
 * Configure filters for Projects Since April report
 * Filters: Fiscal Year, Team only
 */
function configureProjectsSinceAprilFilters() {
    // Show Fiscal Year filter (replaces date range)
    $('#fiscalYearFilterContainer').show();

    // Hide all other conditional filters
    $('#projectTypeFilterContainer').hide();
    $('#filterDateFrom').closest('.col-md-3').hide();
    $('#filterDateTo').closest('.col-md-3').hide();
    $('#filterTeam').closest('.col-md-3').show();
    $('#filterStatus').closest('.col-md-3').hide();

    // Set default fiscal year to current
    $('#filterFiscalYear').val('current');

    $('#reportTitle').text('Projects Since April 1st');
}

/**
 * Configure filters for Project Timeline report
 * Filters: Date range, Team, Status
 */
function configureProjectTimelineFilters() {
    // Show date range filters
    $('#filterDateFrom').closest('.col-md-3').show();
    $('#filterDateTo').closest('.col-md-3').show();
    $('#filterTeam').closest('.col-md-3').show();
    $('#filterStatus').closest('.col-md-3').show();

    // Hide conditional filters
    $('#projectTypeFilterContainer').hide();
    $('#fiscalYearFilterContainer').hide();

    // Set default dates (this year)
    const today = new Date();
    const startOfYear = new Date(today.getFullYear(), 0, 1);

    $('#filterDateFrom').val(formatDateForInput(startOfYear));
    $('#filterDateTo').val(formatDateForInput(today));

    $('#reportTitle').text('Project Timeline Report');
}

/**
 * Configure filters for Contract Signed Analysis report
 */
function configureContractSignedAnalysisFilters() {
    // Show only contract signed filters
    $('#contractSignedFiltersContainer').show();
    $('#fiscalYearFilterContainer').hide();
    $('#projectTypeFilterContainer').hide();
    $('#filterDateFrom').closest('.col-md-3').hide();
    $('#filterDateTo').closest('.col-md-3').hide();
    $('#filterTeam').closest('.col-md-3').hide();
    $('#filterStatus').closest('.col-md-3').hide();

    // Set defaults
    $('#filterDateRange').val('april');
    $('#filterSfdc').val('all');
    $('#filterAov').val('all');
    $('#filterActive').val('all');

    $('#reportTitle').text('Contract Signed Analysis');
}

/**
 * Reset all filters to defaults
 */
function resetFilters() {
    // Clear date inputs
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');

    // Clear multi-selects
    $('#filterTeam').val([]).change();
    $('#filterStatus').val([]).change();
    $('#filterProjectType').val([]).change();

    // Reset fiscal year
    $('#filterFiscalYear').val('current');

    // Contract signed filters
    $('#filterDateRange').val('april');
    $('#filterSfdc').val('all');
    $('#filterAov').val('all');
    $('#filterActive').val('all');
}

/**
 * Run the selected report with current filters
 */
function runReport() {
    if (!currentReport) {
        showToast('Error', 'Please select a report first', 'error');
        return;
    }

    // Show loading spinner
    $('#loadingSpinner').show();
    $('#noDataMessage').hide();
    $('#tableContainer').hide();

    // Build filters object
    const filters = buildFiltersObject();

    // Validate filters for selected report
    if (!validateFilters(filters)) {
        $('#loadingSpinner').hide();
        return;
    }

    // Make API call based on report type
    let apiUrl = '';

    switch (currentReport) {
        case 'agent_performance':
            apiUrl = buildApiUrl('getAgentPerformance', filters);
            break;

        case 'projects_since_april':
            apiUrl = buildApiUrl('getProjectsSinceApril', filters);
            break;

        case 'project_timeline':
            apiUrl = buildApiUrl('getProjectTimeline', filters);
            break;

        case 'contract_signed_analysis':
            apiUrl = buildApiUrl('getContractSignedAnalysis', filters);
            break;            
    }

    $.ajax({
        url: apiUrl,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            $('#loadingSpinner').hide();

            if (response.success && response.data && response.data.length > 0) {
                renderReportTable(response.data, currentReport);
                $('#tableContainer').show();
            } else {
                $('#noDataMessage').show();
                clearReportTable();
            }
        },
        error: function (xhr) {
            $('#loadingSpinner').hide();

            try {
                const error = JSON.parse(xhr.responseText);
                showToast('Error', error.error || 'Failed to load report', 'error');
            } catch (e) {
                showToast('Error', 'Failed to load report', 'error');
            }
        }
    });
}

/**
 * Build filters object from form inputs
 * 
 * @returns {object} Filters object
 */
function buildFiltersObject() {
    const filters = {};

    // Date filters
    const dateFrom = $('#filterDateFrom').val();
    const dateTo = $('#filterDateTo').val();

    if (dateFrom) filters.dateFrom = dateFrom;
    if (dateTo) filters.dateTo = dateTo;

    // Multi-select filters
    const team = $('#filterTeam').val();
    const status = $('#filterStatus').val();
    const projectType = $('#filterProjectType').val();

    if (team && team.length > 0) filters.team = team;
    if (status && status.length > 0) filters.status = status;
    if (projectType && projectType.length > 0) filters.projectType = projectType;

    // Fiscal year filter (for Projects Since April)
    const fiscalYear = $('#filterFiscalYear').val();
    if (fiscalYear) filters.fiscalYear = fiscalYear;

    // Contract Signed filters
    const dateRange = $('#filterDateRange').val();
    const sfdc = $('#filterSfdc').val();
    const aov = $('#filterAov').val();
    const active = $('#filterActive').val();

    if (dateRange) filters.dateRange = dateRange;
    if (sfdc && sfdc !== 'all') filters.sfdc = sfdc;
    if (aov && aov !== 'all') filters.aov = aov;
    if (active && active !== 'all') filters.active = active;

    return filters;
}

/**
 * Validate filters based on report type
 * 
 * @param {object} filters - Filters object
 * @returns {bool} Valid or not
 */
function validateFilters(filters) {
    switch (currentReport) {
        case 'agent_performance':
        case 'project_timeline':
            // Date range is optional but if provided, both must exist
            if ((filters.dateFrom && !filters.dateTo) || (!filters.dateFrom && filters.dateTo)) {
                showToast('Error', 'Please provide both start and end dates or leave both empty', 'error');
                return false;
            }
            break;

        case 'projects_since_april':
            // Fiscal year is required
            if (!filters.fiscalYear) {
                showToast('Error', 'Please select a fiscal year', 'error');
                return false;
            }
            break;
    }

    return true;
}

/**
 * Build API URL with filters as query parameters
 * 
 * @param {string} action - API action
 * @param {object} filters - Filters object
 * @returns {string} API URL
 */
function buildApiUrl(action, filters) {
    let url = `../api/reports.php?action=${action}`;

    // Standard date filters
    if (filters.dateFrom) url += `&dateFrom=${encodeURIComponent(filters.dateFrom)}`;
    if (filters.dateTo) url += `&dateTo=${encodeURIComponent(filters.dateTo)}`;

    // Team filter
    if (filters.team && filters.team.length > 0) {
        url += `&team=${encodeURIComponent(filters.team.join(','))}`;
    }

    // Status filter
    if (filters.status && filters.status.length > 0) {
        url += `&status=${encodeURIComponent(filters.status.join(','))}`;
    }

    // Project type filter
    if (filters.projectType && filters.projectType.length > 0) {
        url += `&projectType=${encodeURIComponent(filters.projectType.join(','))}`;
    }

    // Fiscal year filter
    if (filters.fiscalYear) url += `&fiscalYear=${encodeURIComponent(filters.fiscalYear)}`;

    // Contract signed analysis filters
    if (filters.dateRange) url += `&dateRange=${encodeURIComponent(filters.dateRange)}`;
    if (filters.sfdc) url += `&sfdc=${encodeURIComponent(filters.sfdc)}`;
    if (filters.aov) url += `&aov=${encodeURIComponent(filters.aov)}`;
    if (filters.active) url += `&active=${encodeURIComponent(filters.active)}`;

    return url;
}

/**
 * Clear report table
 */
function clearReportTable() {
    $('#tableHeader').empty();
    $('#tableBody').empty();
}

/**
 * Render report data in table
 * Dynamically creates columns based on data
 * 
 * @param {array} data - Report data
 * @param {string} reportType - Report type (for formatting)
 */
function renderReportTable(data, reportType) {
    if (!data || data.length === 0) {
        clearReportTable();
        return;
    }

    const firstRow = data[0];
    const columns = Object.keys(firstRow);

    // Build header
    const headerRow = $('<tr></tr>');
    columns.forEach(col => {
        const header = formatColumnHeader(col);
        headerRow.append(`<th>${header}</th>`);
    });
    $('#tableHeader').html(headerRow);

    // Build body
    const tbody = $('#tableBody');
    tbody.empty();

    data.forEach(row => {
        const tr = $('<tr></tr>');
        columns.forEach(col => {
            const value = formatCellValue(row[col], col, reportType);
            tr.append(`<td>${value}</td>`);
        });
        tbody.append(tr);
    });

    // Initialize DataTable
    initializeDataTable();
}

/**
 * Format column header (convert snake_case to Title Case)
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
 * 
 * @param {any} value - Cell value
 * @param {string} col - Column name
 * @param {string} reportType - Report type
 * @returns {string} Formatted value
 */
function formatCellValue(value, col, reportType) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    // Currency fields
    if (col.includes('tcv') || col.includes('aov') || col.includes('revenue') || col.includes('avg')) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 2
        }).format(value);
    }

    // Date fields
    if (col.includes('date') || col.includes('updated') || col.includes('activity')) {
        return formatDate(value);
    }

    // Count fields
    if (col.includes('count') || col.includes('projects') || col.includes('days')) {
        return parseInt(value) || 0;
    }

    // Status badges
    if (col === 'current_status' || col === 'status' || col === 'last_status') {
        return `<span class="badge ${getStatusBadgeClass(value)}">${value}</span>`;
    }

    // Truncate long strings
    if (typeof value === 'string' && value.length > 50) {
        return `<span title="${value}">${value.substring(0, 47)}...</span>`;
    }

    return value;
}

/**
 * Get status badge CSS class
 * 
 * @param {string} status - Status name
 * @returns {string} CSS class
 */
function getStatusBadgeClass(status) {
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
    return statusColors[status] || 'bg-secondary';
}

/**
 * Format date string (YYYY-MM-DD to DD-MMM-YYYY)
 * 
 * @param {string} dateString - Date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    } catch {
        return dateString;
    }
}

/**
 * Format Date object for input field (YYYY-MM-DD)
 * 
 * @param {Date} date - JavaScript Date object
 * @returns {string} YYYY-MM-DD format
 */
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Initialize DataTable on report results
 */
function initializeDataTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#reportTable')) {
        $('#reportTable').DataTable().destroy();
    }

    // Initialize new DataTable
    $('#reportTable').DataTable({
        pageLength: 25,
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12"r>>' +
            '<"row"<"col-sm-12"t>>' +
            '<"row mt-3"<"col-sm-12 col-md-5 d-flex align-items-center"li><"col-sm-12 col-md-7"p>>',
        language: {
            search: 'Search:',
            lengthMenu: '_MENU_',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'No records found',
            infoFiltered: '(filtered from _MAX_ total records)',
            zeroRecords: 'No matching records found',
            processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Processing...</span></div>'
        }
    });
}

/**
 * Export current report to CSV
 * Delegates to reports-export.js
 */
function exportReport() {
    if (!currentReport) {
        showToast('Error', 'Please run a report first', 'error');
        return;
    }

    const filters = buildFiltersObject();
    triggerCSVExport(currentReport, filters);
}