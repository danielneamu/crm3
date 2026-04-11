<?php
$pipelineRows = $pipelineRows ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="table-responsive">
            <table id="sfdcPipelineTable" class="table table-striped table-bordered table-sm align-middle js-sfdc-inline-table w-100">
                <thead class="table-light">
                    <tr>
                        <th>Group Month Sort</th>
                        <th>Group Month</th>
                        <th>Group Team</th>
                        <th>Opp Ref</th>
                        <th>Team</th>
                        <th>Agent</th>
                        <th>Account</th>
                        <th>Opportunity</th>
                        <th>Fiscal Period</th>
                        <th>Stage</th>
                        <th>Probability</th>
                        <th>Created Date</th>
                        <th>Close Date</th>
                        <th>Amount</th>
                        <th>Expected Revenue</th>
                        <th>AOV Multi</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Real Flag</th>
                        <th>Age</th>
                        <th>Contract Term</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pipelineRows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Group_Month_Sort'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Group_Month_Label'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Group_Team_Label'] ?? '') ?></td>

                            <td>
                                <?php $oppRef = trim((string)($row['Opportunity_Reference_ID'] ?? '')); ?>
                                <?= $oppRef !== ''
                                    ? '<span data-copy="' . htmlspecialchars($oppRef, ENT_QUOTES, 'UTF-8') . '" title="Click to copy" style="cursor: pointer;">' . htmlspecialchars($oppRef) . '</span>'
                                    : '-' ?>
                            </td>

                            <td><?= htmlspecialchars($row['Owner_Role'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Opportunity_Owner'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Account_Name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Opportunity_Name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Fiscal_Period'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Stage'] ?? '') ?></td>
                            <td><?= !empty($row['Probability_Percent']) ? number_format((float)$row['Probability_Percent'], 1) . '%' : '-' ?></td>

                            <td><?= htmlspecialchars($row['Created_Date'] ?? '') ?></td>
                            <td class="dt-nowrap dt-compact"
                                data-order="<?= !empty($row['Close_Date']) ? date('Y-m-d', strtotime($row['Close_Date'])) : '' ?>">
                                <?= htmlspecialchars($row['Close_Date'] ?? '') ?>
                            </td>

                            <td class="text-end"><?= number_format((float)($row['Amount'] ?? 0), 2) ?></td>
                            <td class="text-end"><?= number_format((float)($row['Expected_Revenue'] ?? 0), 2) ?></td>
                            <td class="text-end"><?= number_format((float)($row['Annual_Order_Value_Multi'] ?? 0), 2) ?></td>

                            <td class="dt-description">
                                <div class="dt-description__inner">
                                    <?= nl2br(htmlspecialchars($row['Description'] ?? '')) ?>
                                </div>
                            </td>

                            <td
                                class="js-editable-cell"
                                data-id="<?= htmlspecialchars($row['Opportunity_Reference_ID'] ?? '') ?>"
                                data-field="Type"
                                data-value="<?= htmlspecialchars($row['Type'] ?? '') ?>">
                                <span class="sfdc-editable-value">
                                    <?= htmlspecialchars($row['Type'] ?? '') ?>
                                </span>
                            </td>

                            <td
                                class="js-editable-cell"
                                data-id="<?= htmlspecialchars($row['Opportunity_Reference_ID'] ?? '') ?>"
                                data-field="Real_Flag"
                                data-value="<?= htmlspecialchars($row['Real_Flag_Display'] ?? 'Yes') ?>">
                                <span class="sfdc-editable-value">
                                    <?= htmlspecialchars($row['Real_Flag_Display'] ?? 'Yes') ?>
                                </span>
                            </td>

                            <td class="dt-nowrap dt-compact"><?= !empty($row['Age']) ? (int)$row['Age'] : '-' ?></td>
                            <td class="dt-nowrap dt-compact"><?= !empty($row['Contract_Term_Months']) ? (int)$row['Contract_Term_Months'] : '-' ?></td>

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

    #sfdcPipelineTable {
        width: 100% !important;
        font-size: clamp(0.72rem, 0.70rem + 0.12vw, 0.80rem);
    }

    #sfdcPipelineTable th,
    #sfdcPipelineTable td {
        vertical-align: middle;
    }

    #sfdcPipelineTable .dt-nowrap {
        white-space: nowrap;
    }

    #sfdcPipelineTable .dt-compact {
        width: 1%;
    }

    #sfdcPipelineTable .dt-money {
        width: 1%;
    }

    #sfdcPipelineTable .dt-description {
        white-space: normal;
    }

    #sfdcPipelineTable .dt-description__inner {
        max-width: clamp(16ch, 20vw, 24ch);
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.25;
    }

    #sfdcPipelineTable tr.dtrg-group td {
        background: #f8f9fa !important;
        font-weight: 600;
    }

    #sfdcPipelineTable tr.dtrg-level-1 td {
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

    #sfdcPipelineTable tbody tr.dtrg-subtotal>td {
        background-color: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
        font-weight: 600;
        color: #212529;
    }

    #sfdcPipelineTable tbody tr.dtrg-subtotal-month>td {
        background-color: #eef4ff !important;
    }

    #sfdcPipelineTable tbody tr.dtrg-subtotal-team>td {
        background-color: #f8f9fa !important;
    }

    #sfdcPipelineTable tbody tr.dtrg-subtotal>td.text-end {
        text-align: right !important;
    }

    #sfdcPipelineTable tbody tr.dtrg-subtotal>td:first-child {
        padding-left: 1rem;
    }
</style>
<link href="../public/assets/css/toast.css" rel="stylesheet">
<script src="../public/assets/js/click2copy_handler.js"></script>
<script src="../public/assets/js/toast.js"></script>


<script>
    (function() {
        function escapeHtml(text) {
            return jQuery('<div>').text(text == null ? '' : String(text)).html();
        }

        // Parse and format amounts
        function parseAmount(value) {
            if (value == null) {
                return 0;
            }

            const text = String(value).trim();

            // Strip HTML tags
            const stripped = text.replace(/<[^>]*>/g, '').trim();

            if (stripped === '') {
                return 0;
            }

            // Extract the first number-like sequence (including . and ,)
            // Pattern: optional sign, digits, optional decimal part
            const match = stripped.match(/[-]?\d+(?:[.,]\d+)?/);

            if (!match) {
                return 0;
            }

            let numStr = match[0];

            // Normalize decimal separators
            const lastDot = numStr.lastIndexOf('.');
            const lastComma = numStr.lastIndexOf(',');

            if (lastDot !== -1 && lastComma !== -1) {
                // Both present: comma after dot = comma is decimal (1.234,56)
                if (lastComma > lastDot) {
                    numStr = numStr.replace('.', '').replace(',', '.');
                } else {
                    // Dot after comma = dot is decimal (1,234.56)
                    numStr = numStr.replace(',', '');
                }
            } else if (lastComma !== -1) {
                // Only comma: if followed by 1-2 digits, it's decimal
                const afterComma = numStr.substring(lastComma + 1);
                if (afterComma.length <= 2) {
                    numStr = numStr.replace(',', '.');
                } else {
                    // More than 2 digits after comma = thousands sep
                    numStr = numStr.replace(',', '');
                }
            }

            const number = parseFloat(numStr);
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

        function initPipelineTable() {

            const typeFilter = jQuery('#pipelineTypeFilter');

            if (typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                return;
            }

            const table = jQuery('#sfdcPipelineTable');
            const groupingSelect = jQuery('#pipelineGroupingMode');

            if (!table.length) {
                return;
            }

            if (!jQuery.fn.DataTable.isDataTable('#sfdcPipelineTable')) {
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
                            targets: [3, 11, 12, 19, 20, 21],
                            className: 'dt-nowrap dt-compact'
                        },
                        {
                            targets: [13, 14, 15],
                            className: 'dt-nowrap text-end dt-money'
                        },
                        {
                            targets: [16],
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
                                amount: sumColumnFromRows(rows, 10),
                                expectedRevenue: sumColumnFromRows(rows, 11),
                                aovMulti: sumColumnFromRows(rows, 12),
                                description: sumColumnFromRows(rows, 13)
                            };

                            // Determine label based on the group value
                            // When grouped by Month: group = "January 2026", "February 2026", etc.
                            // When grouped by Team: group = "RO Timisoara Team 2", etc.
                            // When grouped by Month+Team: level 0 = month, level 1 = team
                            let label = 'Subtotal';

                            // Check if group looks like a month name (contains month names)
                            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                            const isMonth = monthNames.some(m => group.includes(m));

                            if (isMonth && level === 0) {
                                label = 'Month subtotal';
                            } else if (!isMonth && level === 0) {
                                label = 'Team subtotal';
                            } else if (level === 1) {
                                label = 'Team subtotal';
                            }

                            return $('<tr/>')
                                .addClass(level === 0 ? 'dtrg-subtotal dtrg-subtotal-month' : 'dtrg-subtotal dtrg-subtotal-team')
                                .append('<td colspan="10" class="text-end fw-semibold">' + label + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.amount) + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.expectedRevenue) + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.aovMulti) + '</td>')
                                .append('<td class="text-end fw-semibold">' + formatAmount(totals.description) + '</td>')
                                .append('<td colspan="5"></td>');
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

                        // Use DOM cell indices (same as sumColumnFromRows)
                        const footerCells = row.cells;
                        if (footerCells[9]) footerCells[9].innerHTML = '<span class="fw-semibold">Grand total</span>';
                        if (footerCells[10]) footerCells[10].innerHTML = '<span class="fw-semibold">' + formatAmount(filteredSum(10)) + '</span>';
                        if (footerCells[11]) footerCells[11].innerHTML = '<span class="fw-semibold">' + formatAmount(filteredSum(11)) + '</span>';
                        if (footerCells[12]) footerCells[12].innerHTML = '<span class="fw-semibold">' + formatAmount(filteredSum(12)) + '</span>';
                        if (footerCells[13]) footerCells[13].innerHTML = '<span class="fw-semibold">' + formatAmount(filteredSum(13)) + '</span>';
                    },
                    buttons: [{
                        extend: 'csvHtml5',
                        text: 'CSV',
                        title: 'SFDC_Pipeline',
                        className: 'buttons-csv-hidden',
                        exportOptions: {
                            columns: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],
                            modifier: {
                                search: 'applied',
                                order: 'applied'
                            }
                        }
                    }]
                });

                dt.buttons().container().appendTo('body').hide();

                jQuery('#exportCsvBtnPipeline').on('click', function() {
                    dt.button('.buttons-csv-hidden').trigger();
                });

                const globalSearch = jQuery('#globalSearchPipeline');

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
                        dt.column(17).search('').draw();
                        return;
                    }

                    if (value === '__EMPTY__') {
                        dt.column(17).search('^$', true, false).draw();
                        return;
                    }

                    dt.column(17).search(value, false, false).draw();
                });

                applyGrouping(groupingSelect.val() || 'month_team');
                dt.columns.adjust();
            }

            if (window.PipelineInlineEdit) {
                window.PipelineInlineEdit.init({
                    tableSelector: '#sfdcPipelineTable',
                    cellSelector: '.js-editable-cell',
                    endpoint: '../api/sfdc_pipeline.php?action=update_won_field',
                    typeOptions: ['Fixed', 'ICT', 'Other']
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPipelineTable);
        } else {
            initPipelineTable();
        }

        document.addEventListener('pipelineFiltersChanged', function(event) {
            const params = event.detail && event.detail.params ? event.detail.params : {};
            const url = new URL(window.location.href);

            ['team', 'agent', 'month', 'quarter', 'year', 'fiscal_period', 'real_flag'].forEach(function(key) {
                url.searchParams.delete(key);
            });

            Object.keys(params).forEach(function(key) {
                url.searchParams.set(key, params[key]);
            });

            window.location.href = url.toString();
        });
    })();
</script>