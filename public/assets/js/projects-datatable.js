let projectsTable;

$(document).ready(function () {
    projectsTable = $('#projectsTable').DataTable({
        ajax: 'data/projects.json',
        columns: [
            {
                data: 'id_project',
                width: '60px',
                className: 'text-center'
            },
            {
                data: 'firma',
                render: function (data, type, row) {
                    if (type === 'display') {
                        return `<a href="#" class="text-primary fw-semibold open-status-modal" data-project-id="${row.id_project}" data-project-name="${row.proiect}">${data}</a>`;
                    }
                    return data;
                }
            },
            { data: 'proiect' },
            { data: 'cui' },
            { data: 'agent' },
            { 
                data: 'team' ,
                visible: false,
                searchable: true
            },
            {
                data: 'pt',
                defaultContent: '-'
            },
            {
                data: 'sd',
                defaultContent: '-'
            },
            {
                data: 'eft',
                defaultContent: '-'
            },
            {
                data: 'sfdc',
                defaultContent: '-'
            },
            {
                data: 'create_date',
                className: 'text-center'
            },
            {
                data: 'last_update',
                className: 'text-center'
            },
            {
                data: 'status',
                render: function (d) {
                    return d ? `<span class="badge bg-info">${d}</span>` : '-';
                },
                className: 'text-center'
            },
            {
                data: 'assigned',
                defaultContent: '-'
            },
            {
                data: 'dl',
                className: 'text-center',
                defaultContent: '-'
            },
            {
                data: 'type',
                defaultContent: '-'
            },
            {
                data: null,
                render: function (data, type, row) {
                    const tcv = parseFloat(row.tcv_project) || 0;
                    const months = parseFloat(row.contract_duration) || 0;

                    if (months > 0 && tcv > 0) {
                        const aov = Math.round(tcv / (months / 12));
                        return aov.toLocaleString() + ' €';
                    }
                    return '-';
                },
                className: 'text-end'
            },
            {
                data: 'on_status',
                render: function (d) {
                    return d == 1
                        ? '<i class="bi bi-toggle-on text-success fs-4"></i>'
                        : '<i class="bi bi-toggle-off text-muted fs-4"></i>';
                },
                className: 'text-center'
            },
            {
                data: 'partners',
                visible: false,
                searchable: true
            },
            {
                data: 'comments',
                visible: false,
                searchable: true
            }
        ],

        order: [[0, 'desc']],
        pageLength: 30,
        lengthMenu: [[30, 60, 100, -1], [30, 60, 100, "All"]],
        scrollX: true,
        responsive: true,
        select: {
            style: 'single',
            selector: 'tr'
        },
        dom: '<"row mb-3"<"col-sm-12"r>>' +       // Processing with margin bottom
            '<"row"<"col-sm-12"t>>' +                      // Table
            '<"row mt-3"<"col-sm-12 col-md-5 d-flex align-items-center"li><"col-sm-12 col-md-7"p>>',

        language: {
            search: "Search:",
            lengthMenu: " _MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ projects",
            infoEmpty: "No projects found",
            infoFiltered: "(filtered from _MAX_ total projects)",
            zeroRecords: "No matching projects found",
            emptyTable: "No projects available"
        }
    });

    // Connect custom search input to DataTable
    $('#projectSearch').on('keyup', function () {
        const searchValue = this.value;
        projectsTable.search(searchValue).draw();

        // Show/hide clear button
        if (searchValue) {
            $('#clearSearch').show();
        } else {
            $('#clearSearch').hide();
        }
    });

    // Clear search button (x)
    $('#clearSearch').click(function () {
        $('#projectSearch').val('');
        projectsTable.search('').draw();
        $(this).hide();
    });

    // TOP FILTERS

    // Team filter dropdown
    $('#teamFilter').on('change', function () {
        const selectedTeam = this.value;
        projectsTable.column(5).search(selectedTeam).draw();
    });

    // Assigned filter dropdown
    $('#assignedFilter').on('change', function () {
        const selectedAssigned = this.value;
        projectsTable.column(13).search(selectedAssigned).draw();
    });

    // Status filter dropdown
    $('#statusFilter').on('change', function () {
        const selectedStatus = this.value;
        projectsTable.column(12).search(selectedStatus).draw();  // Column 12 = Status
    });


    // Custom search function for on_status filtering
    let activeStatusFilter = 'all'; // 'all', 'active', 'inactive'
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            if (activeStatusFilter === 'all') {
                return true;
            }

            const rowData = projectsTable.row(dataIndex).data();
            const onStatus = parseInt(rowData.on_status);

            if (activeStatusFilter === 'active') {
                return onStatus === 1;
            } else if (activeStatusFilter === 'inactive') {
                return onStatus === 0;
            }

            return true;
        }
    );
    // Active/Inactive status filter buttons
    $('#filterActive').click(function () {
        $('#filterActive, #filterAll, #filterInactive').removeClass('active');
        $(this).addClass('active');

        activeStatusFilter = 'active';
        projectsTable.draw();
    });
    $('#filterAll').click(function () {
        $('#filterActive, #filterAll, #filterInactive').removeClass('active');
        $(this).addClass('active');

        activeStatusFilter = 'all';
        projectsTable.draw();
    });
    $('#filterInactive').click(function () {
        $('#filterActive, #filterAll, #filterInactive').removeClass('active');
        $(this).addClass('active');

        activeStatusFilter = 'inactive';
        projectsTable.draw();
    });



    // Populate dropdowns dynamically from table data
    projectsTable.on('init', function () {
        // Populate Team filter
        const uniqueTeams = [];
        projectsTable.column(5).data().unique().sort().each(function (value) {
            if (value && !uniqueTeams.includes(value)) {
                uniqueTeams.push(value);
            }
        });

        $('#teamFilter').html('<option value="">All Teams</option>');
        uniqueTeams.forEach(function (team) {
            $('#teamFilter').append(`<option value="${team}">${team}</option>`);
        });

        // Populate Assigned filter
        const uniqueAssigned = [];
        projectsTable.column(13).data().unique().sort().each(function (value) {
            if (value && value !== '-' && !uniqueAssigned.includes(value)) {
                uniqueAssigned.push(value);
            }
        });

        $('#assignedFilter').html('<option value="">All Assigned</option>');
        uniqueAssigned.forEach(function (assigned) {
            $('#assignedFilter').append(`<option value="${assigned}">${assigned}</option>`);
        });

        // Populate Status filter
        const uniqueStatus = [];
        projectsTable.column(12).data().unique().sort().each(function (value) {
            if (value && !uniqueStatus.includes(value)) {
                uniqueStatus.push(value);
            }
        });

        $('#statusFilter').html('<option value="">All Status</option>');
        uniqueStatus.forEach(function (status) {
            $('#statusFilter').append(`<option value="${status}">${status}</option>`);
        });
    });
    

});

