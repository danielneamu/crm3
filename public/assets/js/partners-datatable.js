let partnersTable;

$(document).ready(function () {
    partnersTable = $('#partnersTable').DataTable({
        ajax: {
            url: '../api/partners.php?action=list',
            dataSrc: ''
        },
        columns: [
            { data: 'id_parteneri', className: 'text-center' },
            { data: 'name_parteneri' },
            { data: 'type_parteneri' },
            {
                data: 'tags',
                render: function (data) {
                    if (!data) return '-';
                    const tags = data.split(', ');
                    return tags.map(tag => `<span class="badge bg-info">${tag}</span>`).join(' ');
                }
            },
            {
                data: 'contact_count',
                className: 'text-center',
                render: data => data || 0
            },
            {
                data: 'created_at',
                className: 'text-center',
                render: data => data ? new Date(data).toLocaleDateString('en-GB') : '-'
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

    // Load filters
    loadFilters();

    // Custom search
    $('#partnerSearch').on('keyup', function () {
        const searchValue = this.value;
        partnersTable.search(searchValue).draw();
        $('#clearSearch').toggle(searchValue.length > 0);
    });

    $('#clearSearch').on('click', function () {
        $('#partnerSearch').val('');
        partnersTable.search('').draw();
        $(this).hide();
    });

    // Enable edit button on row select
    partnersTable.on('select', function () {
        $('#btnEditPartner').prop('disabled', false);
    });

    partnersTable.on('deselect', function () {
        $('#btnEditPartner').prop('disabled', true);
    });

    // Type filter
    $('#typeFilter').change(function () {
        partnersTable.column(2).search(this.value).draw();
    });

    // Tag filter
    $('#tagFilter').change(function () {
        partnersTable.column(3).search(this.value).draw();
    });
});

function loadFilters() {
    // Load types from data
    $.get('../api/partners.php?action=list', function (data) {
        const types = [...new Set(data.map(p => p.type_parteneri).filter(t => t))];
        types.sort();
        types.forEach(type => {
            $('#typeFilter').append(`<option value="${type}">${type}</option>`);
        });
    });

    // Load tags
    $.get('../api/partners.php?action=tags', function (tags) {
        tags.forEach(tag => {
            $('#tagFilter').append(`<option value="${tag.tag}">${tag.tag}</option>`);
        });
    });
}
