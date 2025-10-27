let companiesTable;

$(document).ready(function () {
    companiesTable = $('#companiesTable').DataTable({
        ajax: {
            url: '../api/companies.php?action=list',
            dataSrc: ''
        },
        columns: [
            { data: 'id_companies', className: 'text-center' },
            { data: 'name_companies' },
            { data: 'fiscal_code' },
            { data: 'city_companies', defaultContent: '-' },
            { data: 'address', defaultContent: '-' },
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

    // Custom search
    $('#companySearch').on('keyup', function () {
        const searchValue = this.value;
        companiesTable.search(searchValue).draw();
        $('#clearSearch').toggle(searchValue.length > 0);
    });

    $('#clearSearch').on('click', function () {
        $('#companySearch').val('');
        companiesTable.search('').draw();
        $(this).hide();
    });

    // Enable/disable buttons on row select
    companiesTable.on('select', function () {
        $('#btnEditCompany, #btnDeleteCompany').prop('disabled', false);
    });

    companiesTable.on('deselect', function () {
        $('#btnEditCompany, #btnDeleteCompany').prop('disabled', true);
    });
});
