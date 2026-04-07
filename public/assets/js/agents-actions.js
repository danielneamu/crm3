let selectedAgent = null;

$(document).ready(function () {
    agentsTable.on('select', function (e, dt, type, indexes) {
        selectedAgent = agentsTable.rows(indexes).data()[0];
        $('#btnEditAgent').prop('disabled', false);
    });

    agentsTable.on('deselect', function () {
        selectedAgent = null;
        $('#btnEditAgent').prop('disabled', true);
    });

    $('#agentModal').on('hidden.bs.modal', function () {
        agentsTable.rows().deselect();
        selectedAgent = null;
        $('#btnEditAgent').prop('disabled', true);
    });

    $('#teamFilter').change(function () {
        agentsTable.column(3).search(this.value).draw();
    });

    // Set active filter by default
    $('#filterActive').trigger('click');


    $('#btnAddAgent').click(function () {
        $('#agentModalLabel').text('Add Agent');
        $('#agentForm')[0].reset();
        $('#agentId').val('');
        $('#teamChangeFields').hide();
        $('#statusAgent').prop('checked', true);
        loadTeams();
        $('#agentModal').modal('show');
    });

    $('#btnEditAgent').click(function () {
        if (!selectedAgent) return;

        $('#agentModalLabel').text('Edit Agent');
        loadTeams(() => {
            $('#agentId').val(selectedAgent.id_agent);
            $('#agentName').val(selectedAgent.nume_agent);
            $('#agentCode').val(selectedAgent.cod_agent);
            $('#currentTeam').val(selectedAgent.current_team);
            $('#oldTeam').val(selectedAgent.current_team);
            $('#statusAgent').prop('checked', selectedAgent.status_agent == 1);
        });
        $('#agentModal').modal('show');
    });

    $('#currentTeam').change(function () {
        const oldTeam = $('#oldTeam').val();
        const newTeam = $(this).val();

        if (oldTeam && newTeam !== oldTeam) {
            $('#teamChangeFields').show();
            $('#effectiveDate').val(new Date().toISOString().split('T')[0]);
        } else {
            $('#teamChangeFields').hide();
        }
    });

    $('#btnSaveAgent').click(function () {
        if (!$('#agentForm')[0].checkValidity()) {
            $('#agentForm')[0].reportValidity();
            return;
        }

        const formData = new FormData($('#agentForm')[0]);
        const isEdit = $('#agentId').val() !== '';

        formData.set('status_agent', $('#statusAgent').is(':checked') ? 1 : 0);

        $.ajax({
            url: '../api/agents.php',
            method: isEdit ? 'PUT' : 'POST',
            data: new URLSearchParams(formData).toString(),
            contentType: 'application/x-www-form-urlencoded',
            success: function () {
                $('#agentModal').modal('hide');
                showToast('Success', isEdit ? 'Agent updated' : 'Agent created', 'success');
                agentsTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                showToast('Error', 'Failed: ' + xhr.responseText, 'error');
            }
        });
    });
});

function loadTeams(callback) {
    $.get('../api/agents.php?action=teams', function (teams) {
        $('#currentTeam').empty().append('<option value="">Select Team</option>');
        teams.forEach(team => {
            $('#currentTeam').append(`<option value="${team}">${team}</option>`);
        });
        if (callback) callback();
    });
}



