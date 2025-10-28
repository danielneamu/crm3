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
                        return `<a href="#" class="  text-decoration-none open-status-modal" style="color: #0a58ca;"  data-project-id="${row.id_project}"  data-project-name="${row.proiect}" data-project-company="${data}">${data}</a>`;
                    }
                    return data;
                }
            },
            { data: 'proiect' },
            { data: 'cui' },
            { data: 'agent' },
            {
                data: 'team',
                visible: false,
                searchable: true
            },
            {
                data: 'pt',
                defaultContent: '-',
                className: 'text-center'
            },
            {
                data: 'sd',
                defaultContent: '-',
                className: 'text-center',
                render: function (data, type, row, meta) {
                    if ((data != "0")) {
                        var a = data;
                        d = '<a href="https://remedy-web.vodafone.ro/arsys/forms/remedy-ar-lb/PreSales+Process+Optimization/Default+Administrator+View/?eid=000000000' + a + '" target="_blank" class="  text-decoration-none " style="color: #0a58ca;">' + a + "</a>";
                        return d;
                    } else {
                        return data;
                    }
                },
            },
            {
                data: 'eft',
                defaultContent: '-',
                className: 'text-center'
            },
            {
                data: 'sfdc',
                defaultContent: '-',
                className: 'text-center'
            },
            {
                data: 'create_date',
                className: 'text-center',
                render: function (data, type, row) {
                    // For sorting, return raw data
                    if (type === 'sort' || type === 'type') {
                        return data || '';
                    }

                    if (!data || data === '-') {
                        return '-';
                    }

                    // Parse dd-mm-yyyy format
                    const parts = data.split('-');
                    if (parts.length !== 3) {
                        return `<span style="font-size: 0.8rem;">${data}</span>`;
                    }

                    const day = parts[0];
                    const monthNum = parseInt(parts[1]) - 1;
                    const year = parts[2];

                    // Create date object
                    const date = new Date(year, monthNum, day);

                    // Check if valid
                    if (isNaN(date.getTime())) {
                        return `<span style="font-size: 0.8rem;">${data}</span>`;
                    }

                    // Format as dd-mmm-yyyy
                    const monthName = date.toLocaleString('en-GB', { month: 'short' });

                    return `<span style="font-size: 0.8rem;">${day}-${monthName}-${year}</span>`;
                }
            },
            {
                data: 'last_update',
                className: 'text-center',
                render: function (data, type, row) {
                    // For sorting, return raw data
                    if (type === 'sort' || type === 'type') {
                        return data || '';
                    }

                    if (!data || data === '-') {
                        return '-';
                    }

                    // Parse dd-mm-yyyy format
                    const parts = data.split('-');
                    if (parts.length !== 3) {
                        return `<span style="font-size: 0.8rem;">${data}</span>`;
                    }

                    const day = parts[0];
                    const monthNum = parseInt(parts[1]) - 1;
                    const year = parts[2];

                    // Create date object
                    const date = new Date(year, monthNum, day);

                    // Check if valid
                    if (isNaN(date.getTime())) {
                        return `<span style="font-size: 0.8rem;">${data}</span>`;
                    }

                    // Format as dd-mmm-yyyy
                    const monthName = date.toLocaleString('en-GB', { month: 'short' });

                    return `<span style="font-size: 0.8rem;">${day}-${monthName}-${year}</span>`;
                }
            },
            {
                data: 'status',
                render: function (data, type, row, meta) {
                    var b = data;
                    if (b == "New") {
                        d = '<span class="badge rounded-pill text-bg-secondary">' + b + "</span>";
                    } else if (b == "Qualifying") {
                        d = '<span class="badge rounded-pill text-bg-info">' + b + "</span>";
                    } else if (b == "Design") {
                        d = '<span class="badge roudned-pill text-bg-primary">' + b + "</span>";
                    } else if (b == "Completed") {
                        d = '<span class="badge rounded-pill text-bg-warning">' + b + "</span>";
                    } else if (b == "Pending") {
                        d = '<span class="badge rounded-pill text-bg-dark">' + b + "</span>";
                    } else if (b == "Contract Signed") {
                        d = '<span class="badge rounded-pill text-bg-success">' + b + "</span>";
                    } else if (
                        b == "No Solution" ||
                        b == "Offer Refused" ||
                        b == "Cancelled"
                    ) {
                        d = '<span class="badge rounded-pill text-bg-danger">' + b + "</span>";
                    } else {
                        d = b;
                    }
                    return d;
                },
                className: 'text-center'
            },
            {
                data: 'assigned',
                defaultContent: '-',
                render: function (data) {

                    // If null/empty → return empty string
                    if (!data) return "";

                    // Style based on value
                    if (data === "Presales") {
                        return `<span class="badge rounded-pill text-bg-info">${data}</span>`;
                    }

                    return `<span class="badge rounded-pill text-bg-light">${data}</span>`;
                }


            },
            {
                data: 'dl',
                className: 'text-center',
                render: function (data, type, row) {
                    // For sorting, return raw data
                    if (type === 'sort' || type === 'type') return data || '';

                    // Handle empty or closed cases
                    if (!data || data === '-' || data === '' || data === null)
                        return '<span class="badge text-bg-success">Closed</span>';

                    // Try to parse date from dd-mm-yyyy or ISO
                    let dlDate;
                    if (/^\d{2}-\d{2}-\d{4}$/.test(data)) {
                        const [day, month, year] = data.split('-').map(Number);
                        dlDate = new Date(year, month - 1, day);
                    } else {
                        dlDate = new Date(data);
                    }

                    // Invalid date → show dash
                    if (isNaN(dlDate)) return '-';

                    // Check for Unix epoch → Closed
                    if (dlDate.getFullYear() === 1970)
                        return '<span class="badge text-bg-success">Closed</span>';

                    // Compare to today
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    dlDate.setHours(0, 0, 0, 0);

                    const diff = dlDate - today;
                    const twoDays = 2 * 24 * 60 * 60 * 1000;
                    const formatted = dlDate.toLocaleDateString('en-GB');

                    // Determine badge color
                    const badge =
                        diff < 0 ? 'danger' :        // Overdue
                            diff < twoDays ? 'warning' : // Due soon
                                'light text-dark';           // Future

                    return `<span class="badge text-bg-${badge}">${formatted}</span>`;
                },
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
            emptyTable: "No projects available",
            processing: '<div class="modern-dt-processing"><div class="modern-spinner"></div><span>Processing...</span></div>'

        },
        processing: true
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

