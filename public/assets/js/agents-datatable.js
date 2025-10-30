let agentsTable;
let activeStatusFilter = 'active'; // Move to top

// Custom search function BEFORE ready
$.fn.dataTable.ext.search.push(
    function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'agentsTable') return true;
        if (activeStatusFilter === 'all') return true;

        const rowData = agentsTable.row(dataIndex).data();
        const statusAgent = parseInt(rowData.status_agent);

        if (activeStatusFilter === 'active') {
            return statusAgent === 1;
        } else if (activeStatusFilter === 'inactive') {
            return statusAgent === 0;
        }

        return true;
    }
);

$(document).ready(function () {
    agentsTable = $('#agentsTable').DataTable({
        ajax: {
            url: '../api/agents.php?action=list',
            dataSrc: ''
        },
        columns: [
            { data: 'id_agent', className: 'text-center' },
            { data: 'nume_agent' },
            { data: 'cod_agent', className: 'text-center' },
            { data: 'current_team' },
            {
                data: 'active_projects',
                className: 'text-center',
                render: data => data || 0
            },
            {
                data: 'member_since',
                className: 'text-center',
                render: data => data ? new Date(data).toLocaleDateString('en-GB') : '-'
            },
            {
                data: 'status_agent',
                className: 'text-center',
                render: data => data == 1
                    ? '<i class="bi bi-toggle-on text-success fs-4"></i>'
                    : '<i class="bi bi-toggle-off text-danger fs-4"></i>'
            }
        ],
        select: { style: 'single' },
        order: [[1, 'asc']],
        pageLength: 25,
        dom: '<"row mb-3"<"col-sm-12"r>>' +       // Processing with margin bottom
            '<"row"<"col-sm-12"t>>' +                      // Table
            '<"row mt-3"<"col-sm-12 col-md-5 d-flex align-items-center"li><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search:",
            lengthMenu: " _MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ agents",
            infoEmpty: "No projects found",
            infoFiltered: "(filtered from _MAX_ total agents)",
            zeroRecords: "No matching agents found",
            emptyTable: "No agents available",
            processing: '<div class="modern-dt-processing"><div class="modern-spinner"></div><span>Processing...</span></div>'

        },
    });

    loadTeamsFilter();

    // Team filter
    $('#teamFilter').change(function () {
        agentsTable.column(3).search(this.value).draw();
    });



    // Connect custom search input to DataTable
    $('#agentSearch').on('keyup', function () {
        const searchValue = this.value;
        agentsTable.search(searchValue).draw();

        // Show/hide clear button
        if (searchValue) {
            $('#clearSearch').show();
        } else {
            $('#clearSearch').hide();
        }
    });

    // Clear search button (x)
    $('#clearSearch').click(function () {
        $('#agentSearch').val('');
        agentsTable.search('').draw();
        $(this).hide();
    });



    // Status filter buttons
    $('#filterActive').click(function () {
        $('#filterActive, #filterAll, #filterInactive').removeClass('active');
        $(this).addClass('active');
        activeStatusFilter = 'active';
        agentsTable.draw();
    });

    $('#filterAll').click(function () {
        $('#filterActive, #filterAll, #filterInactive').removeClass('active');
        $(this).addClass('active');
        activeStatusFilter = 'all';
        agentsTable.draw();
    });

    $('#filterInactive').click(function () {
        $('#filterActive, #filterAll, #filterInactive').removeClass('active');
        $(this).addClass('active');
        activeStatusFilter = 'inactive';
        agentsTable.draw();
    });

    // Trigger active filter by default
    $('#filterActive').trigger('click');
});

function loadTeamsFilter() {
    $.get('../api/agents.php?action=teams', function (teams) {
        teams.forEach(team => {
            $('#teamFilter').append(`<option value="${team}">${team}</option>`);
        });
    });
}
