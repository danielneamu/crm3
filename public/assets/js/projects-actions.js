let selectedRow = null;
let isMultiSelectMode = false;

$(document).ready(function () {
    // Enable/disable Edit button on row selection
    projectsTable.on('select', function (e, dt, type, indexes) {
        selectedRow = projectsTable.rows(indexes).data()[0];
        $('#btnEditProject').prop('disabled', false);
    });

    projectsTable.on('deselect', function () {
        selectedRow = null;
        $('#btnEditProject').prop('disabled', true);
    });

    $('#createDate').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
        orientation: 'bottom auto',
        endDate: new Date(),
        container: '#projectModal'  // ← Keep this
    });



    // Add Project
    $('#btnAddProject').click(function () {
        $('#projectModalLabel').text('Add Project');


        $('#projectForm')[0].reset();
        $('#projectId').val('');
        $('#agent').prop('disabled', true).html('<option value="">Select Team First</option>');

        // Re-enable fields
        $('#company').prop('disabled', false);
        $('#team').prop('disabled', false);
        $('#createDate').prop('disabled', false).prop('readonly', true);


        // Remove hidden inputs if they exist
        $('#hiddenCompany, #hiddenTeam, #hiddenAgent').remove();

        // Set contract duration default to 1 month
        $('#contractDuration').val('1');

        // ✅ Set date value MANUALLY (don't use setDate yet)
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();
        $('#createDate').val(`${day}-${month}-${year}`);

        console.log('Date manually set to:', $('#createDate').val());

        loadDropdowns();
        $('#projectModal').modal('show');
    });


    // Edit Project
    $('#btnEditProject').click(function () {
        if (!selectedRow) return;

        $('#projectModalLabel').text('Edit Project');
        loadDropdowns(() => {
            populateForm(selectedRow);

            // Disable fields that shouldn't be edited
            $('#company').prop('disabled', true);
            $('#team').prop('disabled', true);
            $('#agent').prop('disabled', true);
            $('#createDate').prop('disabled', true);

            // Add hidden inputs with correct backend field names
            if ($('#hiddenCompany').length === 0) {
                $('#projectForm').append(`
                <input type="hidden" id="hiddenCompany" name="company_project" value="${selectedRow.company_id}">
                <input type="hidden" id="hiddenAgent" name="agent_project" value="${selectedRow.agent_id}">
            `);
            } else {
                $('#hiddenCompany').val(selectedRow.company_id);
                $('#hiddenAgent').val(selectedRow.agent_id);
            }
        });
        $('#projectModal').modal('show');
    });



    // Initialize datepicker when modal is shown
    $('#projectModal').on('shown.bs.modal', function () {

        // Only update for Add mode (not Edit)
        if (!selectedRow && $('#createDate').val()) {
            // Tell datepicker to update its internal state with the current input value
            $('#createDate').datepicker('update');
            console.log('Datepicker updated, value:', $('#createDate').val());
        }

        // Initialize Select2 when modal is shown
        if (!$('#partners').hasClass('select2-hidden-accessible')) {
            $('#partners').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#projectModal'),
                placeholder: 'Select partners',
                allowClear: true,
                width: '100%'
            });
        }

    });

    // Destroy Select2 when Project Modal is hidden
    $('#projectModal').on('hidden.bs.modal', function () {
        if ($('#partners').hasClass('select2-hidden-accessible')) {
            $('#partners').select2('destroy');
        }
    });

    // Deselect rows when Project Modal modal is closed
    $('#projectModal').on('hidden.bs.modal', function () {
        projectsTable.rows().deselect();
        selectedRow = null;
        $('#btnEditProject').prop('disabled', true);
    });

    // Team change - load agents
    $('#team').change(function () {
        const team = $(this).val();
        if (!team) {
            $('#agent').prop('disabled', true).html('<option value="">Select Team First</option>');
            return;
        }

        $.get('../api/dropdowns.php?team=' + encodeURIComponent(team), function (agents) {
            $('#agent').prop('disabled', false).html('<option value="">Select Agent</option>');
            agents.forEach(agent => {
                $('#agent').append(`<option value="${agent.id_agent}">${agent.nume_agent}</option>`);
            });
        });
    });

    // Save Project
    $('#btnSaveProject').click(function () {
        if (!$('#projectForm')[0].checkValidity()) {
            $('#projectForm')[0].reportValidity();
            return;
        }

        const formData = new FormData($('#projectForm')[0]);
        const isEdit = $('#projectId').val() !== '';

        // Handle partners explicitly
        const partners = $('#partners').val() || [];

        // Remove any existing partner entries from FormData
        formData.delete('partners');
        formData.delete('partners[]');

        // Add partners back - even if empty
        if (partners.length > 0) {
            partners.forEach(partnerId => {
                formData.append('partners[]', partnerId);
            });
        } else {
            // Send empty string to signal "clear all partners"
            formData.append('partners[]', '');
        }


        if (isEdit) {
            // For PUT, convert FormData to URLSearchParams
            const urlEncodedData = new URLSearchParams(formData).toString();

            $.ajax({
                url: '../api/projects.php',
                method: 'PUT',
                data: urlEncodedData,
                contentType: 'application/x-www-form-urlencoded',
                success: function (response) {
                    $('#projectModal').modal('hide');

                    // CHANGED: Just reload table immediately (no JSON regeneration)
                    showToast('Success', 'Project updated successfully', 'success');
                    projectsTable.ajax.reload(null, false);
                },
                error: function (xhr) {
                    showToast('Error', 'Failed to save project: ' + xhr.responseText, 'error');
                }
            });
        } else {
            // For POST, use FormData as-is
            $.ajax({
                url: '../api/projects.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#projectModal').modal('hide');

                    // CHANGED: Just reload table immediately (no JSON regeneration)
                    showToast('Success', 'Project created successfully', 'success');
                    projectsTable.ajax.reload(null, false);
                },
                error: function (xhr) {
                    showToast('Error', 'Failed to save project: ' + xhr.responseText, 'error');
                }
            });
        }
    });


    //* END DOCUMENT READY
});

// Load dropdowns
function loadDropdowns(callback) {
    $.get('../api/dropdowns.php', function (data) {
        $('#company').html('<option value="">Select Company</option>');
        data.companies.forEach(c => {
            $('#company').append(`<option value="${c.id_companies}">${c.name_companies}</option>`);
        });

        $('#team').html('<option value="">Select Team</option>');
        data.teams.forEach(t => {
            $('#team').append(`<option value="${t}">${t}</option>`);
        });

        $('#projectType').html('<option value="">Select Type</option>');
        data.types.forEach(t => {
            $('#projectType').append(`<option value="${t}">${t}</option>`);
        });

        $('#partners').html('');
        data.partners.forEach(p => {
            $('#partners').append(`<option value="${p.id_parteneri}">${p.name_parteneri}</option>`);
        });

        if (callback) callback();
    });
}

// Populate form for editing
function populateForm(data) {
    console.log('populateForm data:', data);

    $('#projectId').val(data.id_project);
    $('#company').val(data.company_id);
    $('#projectName').val(data.proiect);
    $('#team').val(data.team);

    // Manually populate agent dropdown with current agent (even if inactive)
    $('#agent').prop('disabled', true);
    $('#agent').html(`<option value="${data.agent_id}" selected>${data.agent}</option>`);

    $('#projectType').val(data.type);
    $('#tcv').val(data.tcv_project);
    $('#contractDuration').val(data.contract_duration);
    $('#pt').val(data.pt);
    $('#sd').val(data.sd);
    $('#eft').val(data.eft);
    $('#sfdc').val(data.sfdc);
    $('#active').prop('checked', data.on_status == 1);

    if (data.create_date && data.create_date !== '-') {
        // Force datepicker initialization if not done
        if (!$('#createDate').data('datepicker')) {
            $('#createDate').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true
            });
        }
        // Now set the date
        $('#createDate').datepicker('setDate', data.create_date);
    }

    $('#commentProject').val(data.comments || '');

    if (data.partner_ids) {
        const partnerIds = data.partner_ids.split(',');
        $('#partners').val(partnerIds).trigger('change');
    }
}