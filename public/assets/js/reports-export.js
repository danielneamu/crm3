/**
 * Reports - CSV Export
 * Handles report data export to CSV file
 */

/**
 * Trigger CSV export for current report
 * Downloads file directly to user's computer
 * 
 * @param {string} reportType - Report type (agent_performance, projects_since_april, project_timeline)
 * @param {object} filters - Filters object
 */
function triggerCSVExport(reportType, filters) {
    // Build export URL
    let exportUrl = `../api/reports.php?action=exportCSV&reportType=${encodeURIComponent(reportType)}`;

    // Add filter parameters to URL
    if (filters.dateFrom) exportUrl += `&dateFrom=${encodeURIComponent(filters.dateFrom)}`;
    if (filters.dateTo) exportUrl += `&dateTo=${encodeURIComponent(filters.dateTo)}`;

    if (filters.team && filters.team.length > 0) {
        exportUrl += `&team=${encodeURIComponent(filters.team.join(','))}`;
    }

    if (filters.status && filters.status.length > 0) {
        exportUrl += `&status=${encodeURIComponent(filters.status.join(','))}`;
    }

    if (filters.projectType && filters.projectType.length > 0) {
        exportUrl += `&projectType=${encodeURIComponent(filters.projectType.join(','))}`;
    }

    if (filters.fiscalYear) {
        exportUrl += `&fiscalYear=${encodeURIComponent(filters.fiscalYear)}`;
    }

    // Show toast
    showToast('Exporting', 'Generating CSV file...', 'info', 2000);

    // Trigger download by creating hidden link and clicking it
    const link = document.createElement('a');
    link.href = exportUrl;
    link.style.display = 'none';
    document.body.appendChild(link);

    // Set timeout to allow browser time to prepare download
    setTimeout(function () {
        link.click();
        document.body.removeChild(link);

        // Show success message after brief delay
        setTimeout(function () {
            showToast('Success', 'CSV file downloaded successfully', 'success');
        }, 500);
    }, 100);
}

/**
 * Alternative export method using fetch API (for future use)
 * Allows for more control over download process
 * 
 * @param {string} reportType - Report type
 * @param {object} filters - Filters object
 */
function exportToCSVFetch(reportType, filters) {
    // Build export URL
    let exportUrl = `../api/reports.php?action=exportCSV&reportType=${encodeURIComponent(reportType)}`;

    // Add filter parameters
    if (filters.dateFrom) exportUrl += `&dateFrom=${encodeURIComponent(filters.dateFrom)}`;
    if (filters.dateTo) exportUrl += `&dateTo=${encodeURIComponent(filters.dateTo)}`;

    if (filters.team && filters.team.length > 0) {
        exportUrl += `&team=${encodeURIComponent(filters.team.join(','))}`;
    }

    if (filters.status && filters.status.length > 0) {
        exportUrl += `&status=${encodeURIComponent(filters.status.join(','))}`;
    }

    if (filters.projectType && filters.projectType.length > 0) {
        exportUrl += `&projectType=${encodeURIComponent(filters.projectType.join(','))}`;
    }

    if (filters.fiscalYear) {
        exportUrl += `&fiscalYear=${encodeURIComponent(filters.fiscalYear)}`;
    }

    showToast('Exporting', 'Generating CSV file...', 'info', 2000);

    // Fetch CSV data
    fetch(exportUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            // Create blob URL and download
            const blobUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = getExportFilename(reportType);
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Clean up blob URL
            window.URL.revokeObjectURL(blobUrl);

            showToast('Success', 'CSV file downloaded successfully', 'success');
        })
        .catch(error => {
            console.error('Export error:', error);
            showToast('Error', 'Failed to export CSV file', 'error');
        });
}

/**
 * Generate filename for export based on report type and current date
 * 
 * @param {string} reportType - Report type
 * @returns {string} Filename with .csv extension
 */
function getExportFilename(reportType) {
    const date = new Date();
    const dateStr = date.toISOString().split('T')[0]; // YYYY-MM-DD format

    const filenames = {
        'agent_performance': `agent_performance_${dateStr}.csv`,
        'projects_since_april': `projects_since_april_${dateStr}.csv`,
        'project_timeline': `project_timeline_${dateStr}.csv`
    };

    return filenames[reportType] || `report_${dateStr}.csv`;
}

/**
 * Export client-side data to CSV (for testing/fallback)
 * Converts JavaScript data to CSV format
 * Not used in production (server-side export preferred)
 * 
 * @param {array} data - Array of objects
 * @param {string} filename - Output filename
 */
function exportToCSVClientSide(data, filename) {
    if (!data || data.length === 0) {
        showToast('Error', 'No data to export', 'error');
        return;
    }

    // Get column headers from first row
    const columns = Object.keys(data[0]);

    // Build CSV content
    let csv = '';

    // Add UTF-8 BOM for Excel compatibility
    csv += '\uFEFF';

    // Add header row
    csv += columns.map(col => `"${escapeCSV(col)}"`).join(',') + '\n';

    // Add data rows
    data.forEach(row => {
        csv += columns.map(col => {
            const value = row[col] !== null && row[col] !== undefined ? row[col] : '';
            return `"${escapeCSV(value.toString())}"`;
        }).join(',') + '\n';
    });

    // Create blob and trigger download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const blobUrl = URL.createObjectURL(blob);

    link.setAttribute('href', blobUrl);
    link.setAttribute('download', filename);
    link.style.display = 'none';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(blobUrl);

    showToast('Success', `CSV exported as ${filename}`, 'success');
}

/**
 * Escape special characters for CSV format
 * Handles quotes and newlines in cell values
 * 
 * @param {string} value - Cell value
 * @returns {string} Escaped value
 */
function escapeCSV(value) {
    if (value === null || value === undefined) {
        return '';
    }

    value = value.toString();

    // Escape double quotes by doubling them
    return value.replace(/"/g, '""');
}

/**
 * Copy table data to clipboard as CSV
 * Alternative to file download (for small datasets)
 * 
 * @param {string} tableId - Table element ID
 */
function copyTableToClipboard(tableId) {
    try {
        const table = document.getElementById(tableId);
        const rows = table.querySelectorAll('tr');

        let csv = '';

        // Add UTF-8 BOM
        csv += '\uFEFF';

        rows.forEach((row, index) => {
            const cells = row.querySelectorAll('td, th');
            const rowData = Array.from(cells)
                .map(cell => `"${escapeCSV(cell.textContent.trim())}"`)
                .join(',');

            csv += rowData + '\n';
        });

        // Copy to clipboard
        navigator.clipboard.writeText(csv).then(() => {
            showToast('Success', 'Table data copied to clipboard', 'success');
        }).catch(err => {
            console.error('Failed to copy:', err);
            showToast('Error', 'Failed to copy to clipboard', 'error');
        });
    } catch (error) {
        console.error('Copy error:', error);
        showToast('Error', 'Failed to copy table data', 'error');
    }
}

/**
 * Export report to Excel format (Phase 2+)
 * Currently not implemented - uses CSV instead
 * 
 * @param {string} reportType - Report type
 * @param {object} filters - Filters object
 */
function exportToXLSX(reportType, filters) {
    // Phase 2: Implement XLSX export using SheetJS library
    showToast('Info', 'Excel export coming in Phase 2. Using CSV for now.', 'info');
    triggerCSVExport(reportType, filters);
}