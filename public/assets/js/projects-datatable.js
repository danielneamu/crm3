let projectsTable;

$(document).ready(function () {
    projectsTable = $('#projectsTable').DataTable({
        // CHANGED: Load directly from API instead of JSON file
        ajax: {
            url: '../api/projects.php?action=list',
            dataSrc: 'data'  // API returns {data: [...]}
        },
        columns: [
            {
                data: 'id_project',
                width: '60px',
                className: 'text-center',
                render: function (data) {
                    return data ? `<span style="font-family: monospace; font-size: .85rem;">${data}</span>` : '-';
                }
            },
            {
                data: 'firma',
                render: function (data, type, row) {
                    if (type === 'display') {
                        return `<a href="#" class="text-decoration-none open-status-modal" style="font-size:.95rem;" color: #0a58ca;" data-project-id="${row.id_project}" data-project-name="${row.proiect}" data-project-company="${data}">${data}</a>`;
                    }
                    return data;
                }
            },
            { data: 'proiect',
                render: function (data) {
                    return data ? `<span style="font-size: .95rem;">${data}</span>` : '-';
                }
            },
            {
                data: 'cui',
                render: function (data) {
                    return data ? `<span data-copy="${data}" title="Click to copy" style="font-family: monospace; font-size: 0.85rem;" class="text-muted">${data}</span>` : '-';
                }
            },
            { data: 'agent' },
            {
                data: 'team',
                visible: false,
                searchable: true
            },
            {
                data: 'pt',
                defaultContent: '-',
                className: 'text-start',
                render: function (data) {
                    return data ? `<span data-copy="${data}" title="Click to copy" style="font-family: monospace; font-size: .95rem;" class="text-muted">${data}</span>` : '-';
                }
            },
            {
                data: 'sd',
                defaultContent: '-',
                className: 'text-start',
                render: function (data, type, row, meta) {
                    if ((data != "0")) {
                        var a = data;
                        d = '<a href="https://remedy-web.vodafone.ro/arsys/forms/remedy-ar-lb/PreSales+Process+Optimization/Default+Administrator+View/?eid=000000000' + a + '" target="_blank" class="text-decoration-none" style="font-family: monospace; font-size: .95rem; color: #0a58ca;">' + a + "</a>";
                        return d;
                    } else {
                        return `<span style="font-family: monospace; font-size: .95rem;">-</span>`;
                    }
                },
            },
            {
                data: 'eft',
                defaultContent: '-',
                className: 'text-start',
                render: function (data) {
                    return data ? `<span data-copy="${data}" title="Click to copy" style="font-family: monospace; font-size: .95rem;">${data}</span>` : '-';
                }
            },
            {
                data: 'sfdc',
                defaultContent: '-',
                className: 'text-start',
                render: function (data) {
                    return data ? `<span data-copy="${data}" title="Click to copy" style="font-family: monospace; font-size: .95rem;">${data}</span>` : '-';
                }
            },
            {
                data: 'create_date',
                className: 'text-center',
                render: function (data, type, row) {
                    if (!data || data === '-') {
                        return '-';
                    }

                    // Incoming format is dd-mm-yyyy
                    const parts = data.split('-');
                    const day = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10) - 1; // JS months 0-11
                    const year = parseInt(parts[2], 10);
                    const date = new Date(year, month, day);

                    if (isNaN(date.getTime())) {
                        return data;
                    }

                    if (type === 'sort' || type === 'type') {
                        // Return timestamp for proper sorting
                        return date.getTime();
                    }

                    // Display: 28-Oct-2025
                    const monthName = date.toLocaleString('en-GB', { month: 'short' });
                    return `<span style="font-size: 0.8rem;">${day}-${monthName}-${year}</span>`;
                }
            },

            {
                data: 'last_update',
                className: 'text-center',
                render: function (data, type, row) {

                    // Handle empty / placeholder
                    if (!data || data === '-') {
                        return '-';
                    }

                    // Expecting dd-mm-yyyy
                    const parts = data.split('-');
                    const day = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10) - 1; // JS months 0-11
                    const year = parseInt(parts[2], 10);

                    const date = new Date(year, month, day);

                    if (isNaN(date.getTime())) {
                        return `<span style="font-size: 0.8rem;">${data}</span>`;
                    }

                    // For sorting, return numeric timestamp
                    if (type === 'sort' || type === 'type') {
                        return date.getTime();
                    }

                    // For display, format as dd-MMM-yyyy
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
                        d = '<span class="badge rounded-pill bg-info-subtle text-bg-info fw-normal">' + b + "</span>";
                    } else if (b == "Design") {
                        d = '<span class="badge roudned-pill bg-primary fw-bold">' + b + "</span>";
                    } else if (b == "Completed") {
                        d = '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis fw-normal">' + b + "</span>";
                    } else if (b == "Pending") {
                        d = '<span class="badge rounded-pill text-bg-dark">' + b + "</span>";
                    } else if (b == "Contract Signed") {
                        d = '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">' + b + "</span>";
                    } else if (
                        b == "No Solution" ||
                        b == "Offer Refused" ||
                        b == "Cancelled"
                    ) {
                        d = '<span class="badge rounded-pill bg-dark-subtle text-secondary">' + b + "</span>";
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

                    if (!data) return "";

                    if (data === "Presales") {
                        return `<span class="badge rounded-pill text-bg-info fw-normal border border-danger">${data}</span>`;
                    }
                    else if (data === "Engineer" || data === "Partner") {
                        return `<span class="badge rounded-pill bg-warning-subtle text-dark fw-normal border border-success">${data}</span>`;
                    }

                    return `<span class="badge rounded-pill text-bg-light fw-normal border primary-border">${data}</span>`;
                }


            },
            {
                data: 'dl',
                className: 'text-center',
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') return data || '';

                    if (!data || data === '-' || data === '' || data === null)
                        return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis ">Closed</span>';

                    let dlDate;
                    if (/^\d{2}-\d{2}-\d{4}$/.test(data)) {
                        const [day, month, year] = data.split('-').map(Number);
                        dlDate = new Date(year, month - 1, day);
                    } else {
                        dlDate = new Date(data);
                    }

                    if (isNaN(dlDate)) return '-';

                    if (dlDate.getFullYear() === 1970)
                        return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">Closed</span>';

                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    dlDate.setHours(0, 0, 0, 0);

                    const diff = dlDate - today;
                    const twoDays = 2 * 24 * 60 * 60 * 1000;
                    const formatted = dlDate.toLocaleDateString('en-GB');

                    const badge =
                        diff < 0 ? 'danger' :
                            diff < twoDays ? 'warning' :
                                'light text-dark';

                    return `<span class="badge rounded-pill bg-${badge}-subtle text-${badge}-emphasis fw-normal">${formatted}</span>`;
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

                    if (tcv > 0 && months > 0) {

                        let aov;

                        if (months <= 12) {
                            // If duration is 1 to 12 months → do NOT annualize
                            aov = tcv;
                        } else {
                            // If duration > 12 months → convert to annual value
                            aov = tcv / (months / 12);
                        }

                        aov = Math.round(aov);
                        return aov.toLocaleString() + ' €';
                    }

                    return '0 €';
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
        dom: '<"row mb-3"<"col-sm-12"r>>' +
            '<"row"<"col-sm-12"t>>' +
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
        projectsTable.column(12).search(selectedStatus).draw();
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