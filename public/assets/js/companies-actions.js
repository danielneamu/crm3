

$(document).ready(function () {
    // Add Company
    $('#btnAddCompany').click(function () {
        $('#companyModalLabel').text('Add Company');
        $('#companyForm')[0].reset();
        $('#companyId').val('');
        $('#companyModal').modal('show');
    });

    // Edit Company
    $('#btnEditCompany').click(function () {
        const selectedData = companiesTable.rows({ selected: true }).data()[0];
        if (!selectedData) return;

        $('#companyModalLabel').text('Edit Company');
        loadCompanyData(selectedData.id_companies);
        $('#companyModal').modal('show');
    });

    // Delete Company
    $('#btnDeleteCompany').click(function () {
        const selectedData = companiesTable.rows({ selected: true }).data()[0];
        if (!selectedData) return;

        if (confirm(`Delete company "${selectedData.name_companies}"?`)) {
            deleteCompany(selectedData.id_companies);
        }
    });

    // Save Company
    $('#btnSaveCompany').click(function () {
        saveCompany();
    });
});

function loadCompanyData(companyId) {
    $.get(`../api/companies.php?action=get&id=${companyId}`, function (company) {
        $('#companyId').val(company.id_companies);
        $('#companyName').val(company.name_companies);
        $('#fiscalCode').val(company.fiscal_code);
        $('#city').val(company.city_companies);
        $('#address').val(company.address);
    });
}

function saveCompany() {
    if (!$('#companyForm')[0].checkValidity()) {
        $('#companyForm')[0].reportValidity();
        return;
    }

    const data = {
        id_companies: $('#companyId').val(),
        name_companies: $('#companyName').val(),
        fiscal_code: $('#fiscalCode').val(),
        city_companies: $('#city').val(),
        address: $('#address').val()
    };

    $.ajax({
        url: '../api/companies.php?action=save',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.success) {
                $('#companyModal').modal('hide');
                companiesTable.ajax.reload();
                showToast('Success', 'Company saved successfully!', 'success');
            } else {
                showToast('Error: ',  (response.error || 'Unknown error'), 'error');
            }
        },
        error: function () {
            showToast('Errorr','Failed to save company', 'error');
        }
    });
}

function deleteCompany(companyId) {
    $.ajax({
        url: `../api/companies.php?action=delete&id=${companyId}`,
        method: 'DELETE',
        success: function (response) {
            if (response.success) {
                companiesTable.ajax.reload();
                showToast('Success','Company deleted', 'success');
            } else {
                showToast('Error: ', response.error, 'error');
            }
        },
        error: function () {
            showToast('Error', 'Failed to delete company', 'error');
        }
    });
}


/**
 * Display a global toast notification with progress bar
 * @param {string} title - Toast title
 * @param {string} message - Toast message body
 * @param {string} type - 'success', 'error', 'warning', or 'info'
 * @param {number} delay - Auto-hide delay in ms (default: 5000)
 */
function showToast(title, message, type = 'success', delay = 5000) {
    const toastEl = document.getElementById('globalToast');
    const icon = document.getElementById('toastIcon');
    const header = document.querySelector('#globalToast .toast-header');
    const titleEl = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    const progressBar = document.getElementById('toastProgressBar');

    // Update content
    titleEl.textContent = title;
    messageEl.textContent = message;

    // Toast type configurations
    const types = {
        success: {
            icon: 'bi-check-circle-fill',
            iconColor: 'text-success',
            headerBg: 'bg-success',
            progressColor: '#198754'
        },
        error: {
            icon: 'bi-x-circle-fill',
            iconColor: 'text-danger',
            headerBg: 'bg-danger',
            progressColor: '#dc3545'
        },
        warning: {
            icon: 'bi-exclamation-triangle-fill',
            iconColor: 'text-warning',
            headerBg: 'bg-warning',
            progressColor: '#ffc107'
        },
        info: {
            icon: 'bi-info-circle-fill',
            iconColor: 'text-info',
            headerBg: 'bg-info',
            progressColor: '#0dcaf0'
        }
    };

    const config = types[type] || types.success;

    // Apply styling
    icon.className = `bi ${config.icon} ${config.iconColor} me-2`;
    header.className = `toast-header ${config.headerBg} bg-opacity-10`;
    progressBar.style.backgroundColor = config.progressColor;

    // Reset progress bar
    progressBar.style.transition = 'none';
    progressBar.style.width = '100%';

    // Force browser reflow
    progressBar.offsetHeight;

    // Start animation after a tiny delay
    setTimeout(() => {
        progressBar.style.transition = `width ${delay}ms linear`;
        progressBar.style.width = '0%';
    }, 10);

    // Show toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: delay
    });

    toast.show();

    // Reset on hide
    toastEl.addEventListener('hidden.bs.toast', () => {
        progressBar.style.transition = 'none';
        progressBar.style.width = '100%';
    }, { once: true });
}
