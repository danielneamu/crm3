let currentProjectId = null;
let projectCompany = null;

$(document).ready(function () {
    // Open status modal on company name click
    $('#projectsTable').on('click', '.open-status-modal', function (e) {
        e.preventDefault();
        currentProjectId = $(this).data('project-id');
        const projectName = $(this).data('project-name');
        const projectCompany = $(this).data('project-company');
        console.log('Opening status modal for project data:', $(this).data());

        $('#statusProjectName').text(projectName);
        $('#statusProjectId').text(currentProjectId);
        $('#statusProjectIdInput').val(currentProjectId);
        $('#statusFirma').text(projectCompany);

        loadStatusHistory(currentProjectId);
        $('#statusModal').modal('show');
    });

    // When modal is shown, set today's date as default
    $('#statusModal').on('shown.bs.modal', function () {
        if (!$('#statusDate').val()) {
            $('#statusDate').val(new Date().toISOString().split('T')[0]);
        }
    });

    // Cancel add status
    $('#btnCancelStatus').click(function () {
        $('#statusForm')[0].reset();
        $('#addStatusForm').collapse('hide');
    });

    // Submit new status
    $('#statusForm').submit(function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.set('project_id', currentProjectId);

        $.ajax({
            url: '../api/status.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                $('#statusForm')[0].reset();
                $('#addStatusForm').collapse('hide');
                loadStatusHistory(currentProjectId);

                // CHANGED: Just reload projects table (no JSON regeneration needed)
                if (typeof projectsTable !== 'undefined') {
                    projectsTable.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                alert('Failed to add status: ' + xhr.responseText);
            }
        });
    });

    // Delete status
    $('#statusHistoryTable').on('click', '.delete-status', function () {
        if (!confirm('Delete this status entry?')) return;

        const statusId = $(this).data('status-id');

        $.ajax({
            url: `../api/status.php?id=${statusId}`,
            method: 'DELETE',
            success: function () {
                loadStatusHistory(currentProjectId);

                // CHANGED: Just reload projects table (no JSON regeneration needed)
                if (typeof projectsTable !== 'undefined') {
                    projectsTable.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                alert('Failed to delete status: ' + xhr.responseText);
            }
        });
    });
});

// Load status history for a project
function loadStatusHistory(projectId) {
    const tbody = $('#statusHistoryBody');
    tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</td></tr>');

    $.get(`../api/status.php?project_id=${projectId}`, function (data) {
        tbody.empty();

        if (data.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox"></i> No status history yet</td></tr>');
            return;
        }

        data.forEach(status => {
            const statusBadgeClass = getStatusBadgeClass(status.status_name);
            const formattedDate = status.changed_at ? formatDate(status.changed_at) : '-';
            const formattedDeadline = status.deadline ? formatDate(status.deadline) : '-';

            tbody.append(`
                <tr>
                    <td class="text-center">${formattedDate}</td>
                    <td class="text-center"><span class="badge ${statusBadgeClass}">${status.status_name}</span></td>
                    <td class="text-center">${formattedDeadline}</td>
                    <td>${status.responsible_party}</td>
                    <td>${status.comment || '-'}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger delete-status" data-status-id="${status.id_status}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    }).fail(function () {
        tbody.html('<tr><td colspan="6" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle"></i> Failed to load status history</td></tr>');
    });
}

// Format date from YYYY-MM-DD to DD-MM-YYYY
function formatDate(dateString) {
    if (!dateString) return '-';
    const parts = dateString.split('-');
    if (parts.length === 3) {
        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }
    return dateString;
}

// Get badge color based on status
function getStatusBadgeClass(status) {
    const statusColors = {
        'New': 'bg-secondary',
        'Qualifying': 'bg-info',
        'Design': 'bg-primary',
        'Completed': 'bg-warning',
        'Pending': 'bg-dark',
        'Contract Signed': 'bg-success',
        'No Solution': 'bg-danger',
        'Offer Refused': 'bg-danger',
        'Cancelled': 'bg-danger'
    };
    return statusColors[status] || 'bg-info';
}