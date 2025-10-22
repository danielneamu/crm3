$(document).ready(function () {
    $('#projectsTable').DataTable({
        ajax: 'data/projects.json',
        columns: [
            { data: null, orderable: false, defaultContent: '', width: '10px' },
            { data: 'id_project' },
            { data: 'firma' },
            { data: 'proiect' },
            { data: 'cui' },
            { data: 'agent' },
            { data: 'team' },
            { data: 'pt' },
            { data: 'sd' },
            { data: 'eft' },
            { data: 'sfdc' },
            { data: 'create_date' },
            { data: 'last_update' },
            { data: 'status', render: d => d ? `<span class="badge bg-info">${d}</span>` : '-' },
            { data: 'assigned' },
            { data: 'dl' },
            { data: 'type' },
            {
                data: 'aov',
                render: d => d ? parseInt(d).toLocaleString() + ' €' : '-',
                className: 'text-end'
            },
            { data: 'on_status', render: d => d == 1 ? '<i class="bi bi-toggle-on text-success"></i>' : '<i class="bi bi-toggle-off"></i>' },
            { data: 'partners', visible: false, searchable: true }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        scrollX: true
    });
});
