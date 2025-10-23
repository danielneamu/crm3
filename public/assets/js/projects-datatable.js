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
            { data: 'team' },
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
        pageLength: 25,
        scrollX: true,
        responsive: true,
        select: {
            style: 'single',
            selector: 'tr'
        },
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ projects",
            infoEmpty: "No projects found",
            infoFiltered: "(filtered from _MAX_ total projects)",
            zeroRecords: "No matching projects found",
            emptyTable: "No projects available"
        }
    });
});
