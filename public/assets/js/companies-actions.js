// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('globalToast');
    const toastBody = document.getElementById('toastMessage');

    const colors = {
        success: 'bg-success text-white',
        error: 'bg-danger text-white',
        warning: 'bg-warning text-dark',
        info: 'bg-info text-white'
    };

    toast.className = `toast align-items-center border-0 ${colors[type] || colors.success}`;
    toastBody.textContent = message;

    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
}

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
                showToast('Company saved successfully!', 'success');
            } else {
                showToast('Error: ' + (response.error || 'Unknown error'), 'error');
            }
        },
        error: function () {
            showToast('Failed to save company', 'error');
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
                showToast('Company deleted', 'success');
            } else {
                showToast('Error: ' + response.error, 'error');
            }
        },
        error: function () {
            showToast('Failed to delete company', 'error');
        }
    });
}
