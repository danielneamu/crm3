<?php
$wonRows = $wonRows ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="table-responsive">
            <table id="sfdcWonTable" class="table table-striped table-bordered table-sm align-middle js-sfdc-inline-table w-100">
                <thead class="table-light">
                    <tr>
                        <th>Group Month Sort</th>
                        <th>Group Month</th>
                        <th>Group Team</th>
                        <th>ID</th>
                        <th>Opp Ref</th>
                        <th>Team</th>
                        <th>Agent</th>
                        <th>Account</th>
                        <th>Opportunity</th>
                        <th>Product</th>
                        <th>Family</th>
                        <th>Created Date</th>
                        <th>Close Date</th>
                        <th>AOV Multi</th>
                        <th>Product ARR</th>
                        <th>TCV</th>
                        <th>Revised AOV</th>
                        <th>Revised NPV</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Contract</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wonRows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Group_Month_Sort'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Group_Month_Label'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Group_Team_Label'] ?? '') ?></td>

                            <td><?= (int)$row['id'] ?></td>
                            <td><?= htmlspecialchars($row['Opportunity_Reference_ID'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Owner_Role'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Opportunity_Owner'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Account_Name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Opportunity_Name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Product_Name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Product_Family'] ?? '') ?></td>

                            <td><?= htmlspecialchars($row['Created_Date'] ?? '') ?></td>
                            <td class="dt-nowrap dt-compact"
                                data-order="<?= !empty($row['Close_Date']) ? date('Y-m-d', strtotime($row['Close_Date'])) : '' ?>">
                                <?= htmlspecialchars($row['Close_Date_Display'] ?? $row['Close_Date'] ?? '') ?>
                            </td>
                            <td><?= number_format((float)($row['Annual_Order_Value_Multi'] ?? 0), 2) ?></td>
                            <td><?= number_format((float)($row['Product_Annual_Recurring_Order_Value'] ?? 0), 2) ?></td>
                            <td><?= number_format((float)($row['Product_TCV'] ?? 0), 2) ?></td>

                            <td
                                class="js-editable-cell"
                                data-id="<?= (int)$row['id'] ?>"
                                data-field="Revised_AOV"
                                data-value="<?= htmlspecialchars($row['Revised_AOV'] ?? '0.00') ?>"
                                data-placeholder="<?= htmlspecialchars(number_format((float)($row['Product_Annual_Recurring_Order_Value'] ?? 0), 2, '.', '')) ?>">
                                <span class="sfdc-editable-value">
                                    <?= htmlspecialchars(number_format((float)($row['Calculated_Revised_AOV'] ?? $row['Product_Annual_Recurring_Order_Value'] ?? 0), 2)) ?>
                                </span>
                            </td>

                            <td
                                class="js-editable-cell"
                                data-id="<?= (int)$row['id'] ?>"
                                data-field="Revised_NPV"
                                data-value="<?= htmlspecialchars($row['Revised_NPV'] ?? '0.00') ?>"
                                data-placeholder="<?= htmlspecialchars(number_format((float)($row['Parsed_Description_NPV'] ?? 0), 2, '.', '')) ?>">
                                <span class="sfdc-editable-value">
                                    <?= htmlspecialchars(number_format((float)($row['Calculated_Revised_NPV'] ?? 0), 2)) ?>
                                </span>
                            </td>

                            <td
                                class="js-editable-cell"
                                data-id="<?= (int)$row['id'] ?>"
                                data-field="Type"
                                data-value="<?= htmlspecialchars($row['Type'] ?? '') ?>">
                                <span class="sfdc-editable-value">
                                    <?= htmlspecialchars($row['Type'] ?? '') ?>
                                </span>
                            </td>

                            <td class="dt-description">
                                <div class="dt-description__inner">
                                    <?= nl2br(htmlspecialchars($row['Description'] ?? '')) ?>
                                </div>
                            </td>

                            <td class="dt-nowrap dt-compact">
                                <?= htmlspecialchars($row['Product_Term_months'] ?? '') ?>
                            </td>

                            <td class="dt-nowrap dt-compact">
                                <?php if (!empty($row['Link'])): ?>
                                    <a href="<?= htmlspecialchars($row['Link']) ?>" target="_blank" rel="noopener noreferrer">Open</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    .js-editable-cell {
        cursor: pointer;
        min-width: 110px;
        background-color: #fffdf2;
    }

    .js-editable-cell:hover {
        background-color: #fff3cd;
    }

    .sfdc-edit-success {
        background-color: #d1e7dd !important;
    }

    .sfdc-edit-error {
        background-color: #f8d7da !important;
    }

    .sfdc-edit-saving {
        background-color: #cff4fc !important;
    }

    .sfdc-inline-input {
        min-width: 100px;
    }

    #sfdcWonTable {
        width: 100% !important;
        font-size: clamp(0.72rem, 0.70rem + 0.12vw, 0.80rem);
    }

    #sfdcWonTable th,
    #sfdcWonTable td {
        vertical-align: middle;
    }

    #sfdcWonTable .dt-nowrap {
        white-space: nowrap;
    }

    #sfdcWonTable .dt-compact {
        width: 1%;
    }

    #sfdcWonTable .dt-money {
        width: 1%;
    }

    #sfdcWonTable .dt-description {
        white-space: normal;
    }

    #sfdcWonTable .dt-description__inner {
        max-width: clamp(16ch, 20vw, 24ch);
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.25;
    }

    #sfdcWonTable tr.dtrg-group td {
        background: #f8f9fa !important;
        font-weight: 600;
    }

    #sfdcWonTable tr.dtrg-level-1 td {
        background: #ffffff !important;
        padding-left: 2rem;
    }

    .filter-divider {
        border-right: 1px solid #dee2e6;
        padding-right: 1rem;
        margin-right: 0.5rem;
    }

    .filter-divider-left {
        border-left: 1px solid #dee2e6;
        padding-left: 1rem;
        margin-left: 0.5rem;
    }


    .dtrg-subtotal td {
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        font-weight: 600;
    }

    .dtrg-subtotal-month td {
        background: #245cc5;
    }

    .dtrg-subtotal-team td {
        background: #f8f9fa;
    }

    #sfdcWonTable tbody tr.dtrg-subtotal>td {
        background-color: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
        font-weight: 600;
        color: #212529;
    }

    #sfdcWonTable tbody tr.dtrg-subtotal-month>td {
        background-color: #eef4ff !important;
    }

    #sfdcWonTable tbody tr.dtrg-subtotal-team>td {
        background-color: #f8f9fa !important;
    }

    #sfdcWonTable tbody tr.dtrg-subtotal>td.text-end {
        text-align: right !important;
    }

    #sfdcWonTable tbody tr.dtrg-subtotal>td:first-child {
        padding-left: 1rem;
    }
</style>

<script>
    (function() {
        function escapeHtml(text) {
            return jQuery('<div>').text(text == null ? '' : String(text)).html();
        }

        // Sbtotals
        function parseAmount(value) {
            if (value == null) {
                return 0;
            }

            const text = String(value).replace(/<[^>]*>/g, '').replace(/[^0-9.-]/g, '');
            const number = parseFloat(text);
            return isNaN(number) ? 0 : number;
        }

        function formatAmount(value) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        }

        function sumColumnFromRows(rows, columnIndex) {
            let total = 0;

            rows.nodes().each(function(row) {
                const cell = row.cells[columnIndex];
                total += parseAmount(cell ? cell.textContent : 0);
            });

            return total;
        }




        function initWonTable() {

            const typeFilter = jQuery('#wonTypeFilter');

            if (typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                return;
            }

            const table = jQuery('#sfdcWonTable');
            const groupingSelect = jQuery('#wonGroupingMode');

            if (!table.length) {
                return;
            }

            if (!jQuery.fn.DataTable.isDataTable('#sfdcWonTable')) {
                const dt = table.DataTable({
                    pageLength: 25,
                    responsive: false,
                    autoWidth: false,
                    order: [
                        [12, 'asc']
                    ],
                    orderFixed: [
                        [12, 'asc']
                    ],
                    columnDefs: [{
                            targets: [0, 1, 2],
                            visible: false,
                            searchable: true
                        },
                        {
                            targets: [3, 4, 11, 12, 18, 20, 21],
                            className: 'dt-nowrap dt-compact'
                        },
                        {
                            targets: [13, 14, 15, 16, 17],
                            className: 'dt-nowrap text-end dt-money'
                        },
                        {
                            targets: [19],
                            className: 'dt-description'
                        }
                    ],
                    rowGroup: {
                        dataSrc: [1, 2],
                        enable: true,
                        startRender: function(rows, group, level) {
                            const colspan = 22;

                            if (level === 0) {
                                return $('<tr/>')
                                    .addClass('dtrg-level-0')
                                    .append(
                                        $('<td/>', {
                                            colspan: colspan,
                                            html: 'Month: <strong>' + escapeHtml(group) + '</strong> ' +
                                                '<span class="text-muted">(' + rows.count() + ' rows)</span>'
                                        })
                                    );
                            }

                            return $('<tr/>')
                                .addClass('dtrg-level-1')
                                .append(
                                    $('<td/>', {
                                        colspan: colspan,
                                        html: 'Team: <strong>' + escapeHtml(group) + '</strong> ' +
                                            '<span class="text-muted">(' + rows.count() + ' rows)</span>'
                                    })
                                );
                        },
                        endRender: function(rows, group, level) {
                            const totals = {
                                aovMulti: sumColumnFromRows(rows, 10),
                                productArr: sumColumnFromRows(rows, 11),
                                tcv: sumColumnFromRows(rows, 12),
                                revisedAov: sumColumnFromRows(rows, 13),
                                revisedNpv: sumColumnFromRows(rows, 14)
                            };

                            const label = level === 0 ? 'Month subtotal' : 'Team subtotal';

                            return $('<tr/>')
                                .addClass(level === 0 ? 'dtrg-subtotal dtrg-subtotal-month' : 'dtrg-subtotal dtrg-subtotal-team')
                                .append('<td colspan="10" class="text-end fw-semibold">' + label + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.aovMulti) + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.productArr) + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.tcv) + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.revisedAov) + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.revisedNpv) + '</td>')
                                .append('<td colspan="4"></td>');
                        }
                    },
                    footerCallback: function(row, data, start, end, display) {
                        const api = this.api();

                        function filteredSum(colIdx) {
                            let total = 0;
                            api.rows({
                                search: 'applied'
                            }).nodes().each(function(rowNode) {
                                const cell = rowNode.cells[colIdx];
                                total += parseAmount(cell ? cell.textContent : 0);
                            });
                            return total;
                        }

                        jQuery(api.column(12).footer()).html('<span class="fw-semibold">Grand total</span>');
                        jQuery(api.column(13).footer()).html('<span class="fw-semibold">' + formatAmount(filteredSum(10)) + '</span>');
                        jQuery(api.column(14).footer()).html('<span class="fw-semibold">' + formatAmount(filteredSum(11)) + '</span>');
                        jQuery(api.column(15).footer()).html('<span class="fw-semibold">' + formatAmount(filteredSum(12)) + '</span>');
                        jQuery(api.column(16).footer()).html('<span class="fw-semibold">' + formatAmount(filteredSum(13)) + '</span>');
                        jQuery(api.column(17).footer()).html('<span class="fw-semibold">' + formatAmount(filteredSum(14)) + '</span>');
                    },
                    buttons: [{
                        extend: 'csvHtml5',
                        text: 'CSV',
                        title: 'SFDC_Won',
                        className: 'buttons-csv-hidden',
                        exportOptions: {
                            columns: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
                            modifier: {
                                search: 'applied',
                                order: 'applied'
                            }
                        }
                    }]
                });

                dt.buttons().container().appendTo('body').hide();

                jQuery('#exportCsvBtn').on('click', function() {
                    dt.button('.buttons-csv-hidden').trigger();
                });

                const globalSearch = jQuery('#globalSearch');

                globalSearch.on('input', function() {
                    dt.search(this.value).draw();
                });

                function applyGrouping(mode) {
                    if (mode === 'none') {
                        dt.rowGroup().disable();
                        dt.order([
                            [12, 'asc'],
                            [12, 'desc'],
                            [3, 'desc']
                        ]).draw();
                        return;
                    }

                    if (mode === 'month') {
                        dt.rowGroup().dataSrc(1);
                        dt.rowGroup().enable();
                        dt.order.fixed([
                            [0, 'desc']
                        ]);
                        dt.order([
                            [12, 'asc'],
                            [0, 'desc'],
                            [3, 'desc']
                        ]).draw();
                        return;
                    }

                    if (mode === 'team') {
                        dt.rowGroup().dataSrc(2);
                        dt.rowGroup().enable();
                        dt.order.fixed([
                            [2, 'asc']
                        ]);
                        dt.order([
                            [12, 'asc'],
                            [2, 'asc'],
                            [3, 'desc']
                        ]).draw();
                        return;
                    }

                    dt.rowGroup().dataSrc([1, 2]);
                    dt.rowGroup().enable();
                    dt.order.fixed([
                        [0, 'desc'],
                        [2, 'asc']
                    ]);
                    dt.order([
                        [12, 'asc'],
                        [0, 'desc'],
                        [2, 'asc'],
                        [3, 'desc']
                    ]).draw();
                }

                groupingSelect.on('change', function() {
                    applyGrouping(this.value);
                });

                typeFilter.on('change', function() {
                    const value = this.value;

                    if (value === '') {
                        dt.column(18).search('').draw();
                        return;
                    }

                    if (value === '__EMPTY__') {
                        dt.column(18).search('^$', true, false).draw();
                        return;
                    }

                    dt.column(18).search(value, false, false).draw();
                });

                applyGrouping(groupingSelect.val() || 'month_team');
                dt.columns.adjust();
            }

            if (window.SfdcInlineEdit) {
                window.SfdcInlineEdit.init({
                    tableSelector: '#sfdcWonTable',
                    cellSelector: '.js-editable-cell',
                    endpoint: '../api/sfdc_won.php?action=update_won_field',
                    typeOptions: ['Fixed', 'ICT', 'Other']
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWonTable);
        } else {
            initWonTable();
        }

        document.addEventListener('sfdcFiltersChanged', function(event) {
            const params = event.detail && event.detail.params ? event.detail.params : {};
            const url = new URL(window.location.href);

            ['team', 'agent', 'month', 'quarter', 'year', 'fiscal_period'].forEach(function(key) {
                url.searchParams.delete(key);
            });

            Object.keys(params).forEach(function(key) {
                url.searchParams.set(key, params[key]);
            });

            window.location.href = url.toString();
        });
    })();
</script>