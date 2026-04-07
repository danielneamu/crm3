/**
 * Dashboard Statistics
 * Loads and displays stat cards and recent projects table
 */

// Store monthly data globally for status history chart
var monthlyData = {
    new: [],
    completed: [],
    signed: []
};

$(document).ready(function () {
    loadDashboardData();
});

/**
 * Load all dashboard data
 */
function loadDashboardData() {
    $.ajax({
        url: '../api/dashboard.php?action=all',
        method: 'GET',
        success: function (data) {
            // Update stat cards
            updateStatCards(data.stats);

            // Update recent projects table
            updateRecentProjects(data.recentProjects);

            // Store monthly data for status history chart
            monthlyData.new = data.monthlyNewProjects || [];
            monthlyData.completed = data.monthlyCompletedProjects || [];
            monthlyData.signed = data.monthlySignedProjects || [];

            // Initialize all charts
            initializeCharts(data);

            // Hide loading overlay
            $('#loadingOverlay').fadeOut();
        },
        error: function (xhr) {
            console.error('Error loading dashboard data:', xhr);
            $('#loadingOverlay').fadeOut();
            alert('Failed to load dashboard data. Check console for details.');
        }
    });
}

/**
 * Update statistics cards with data
 */
function updateStatCards(stats) {
    // Row 1 - Main stats
    $('#totalProjects').text(stats.total_projects || 0);
    $('#completedProjects').text(stats.completed_projects || 0);
    $('#signedProjects').text(stats.signed_projects || 0);
    $('#ongoingProjects').text(stats.ongoing_projects || 0);

    // Row 2 - Monthly stats with comparisons
    $('#openedThisMonth').text(stats.opened_this_month || 0);
    $('#completedThisMonth').text(stats.completed_this_month || 0);
    $('#signedThisMonth').text(stats.signed_this_month || 0);

    // Calculate and display comparisons
    updateComparison('openedComparison', stats.opened_this_month, stats.opened_last_month);
    updateComparison('completedComparison', stats.completed_this_month, stats.completed_last_month);
    updateComparison('signedComparison', stats.signed_this_month, stats.signed_last_month);
}

/**
 * Update comparison display with percentage and previous month value
 */
function updateComparison(elementId, current, previous) {
    const element = $('#' + elementId);

    if (!previous && previous !== 0) {
        element.html('<i class="bi bi-dash"></i> No data');
        return;
    }

    const diff = current - previous;
    const percent = previous > 0 ? ((diff / previous) * 100).toFixed(0) : 0;

    let arrow = '';
    let colorClass = 'text-muted';

    if (diff > 0) {
        arrow = '<i class="bi bi-arrow-up"></i>';
        colorClass = 'text-success';
    } else if (diff < 0) {
        arrow = '<i class="bi bi-arrow-down"></i>';
        colorClass = 'text-danger';
    } else {
        arrow = '<i class="bi bi-dash"></i>';
    }

    element.html(`${arrow} <span class="${colorClass}">${percent}%</span> <span class="text-muted">(${previous})</span>`);
}

/**
 * Update recent projects table
 */
function updateRecentProjects(projects) {
    const tbody = $('#recentProjectsTable tbody');
    tbody.empty();

    if (!projects || projects.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center text-muted">No recent projects</td></tr>');
        return;
    }

    projects.forEach(project => {
        let statusClass = 'bg-secondary';
        let statusLabel = project.current_status || 'Unknown';

        switch (project.current_status) {
            case 'New':
                statusClass = 'bg-info';
                break;
            case 'Design':
                statusClass = 'bg-primary';
                break;
            case 'Qualifying':
                statusClass = 'bg-warning text-dark';
                break;
            default:
                statusClass = 'bg-secondary';
                break;
        }

        const statusBadge = `<span class="badge ${statusClass}">${statusLabel}</span>`;

        const tcv = project.tcv_project
            ? parseFloat(project.tcv_project).toLocaleString('en-US', { minimumFractionDigits: 2 })
            : '0.00';

        const row = `
            <tr>
                <td>${project.id_project}</td>
                <td><a href="projects.php?id=${project.id_project}">${project.name_project || '-'}</a></td>
                <td>${project.name_companies || '-'}</td>
                <td>${project.nume_agent || '-'}</td>
                <td>${formatDate(project.createDate_project)}</td>
                <td>$${tcv}</td>
                <td>${statusBadge}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

/**
 * Format date to readable format
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB');
}
