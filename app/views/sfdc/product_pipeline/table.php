<?php
$productRows = $productRows ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="table-responsive">
            <table id="sfdcProductPipelineTable" class="table table-striped table-bordered table-sm align-middle js-product-pipeline-table w-100">
                <thead class="table-light">
                    <tr>
                        <th>Group Month Sort</th>
                        <th>Group Month</th>
                        <th>Group Team</th>
                        <th>Group Family</th>
                        <th>Product ID</th>
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
                        <th>Product Family</th>
                        <th>Product Name</th>
                        <th>Product Code</th>
                        <th>ARROV</th>
                        <th>AOV Multi</th>
                        <th>Description</th>
                        <th>Age</th>
                        <th>Contract Term</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productRows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Group_Month_Sort'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Group_Month_Label'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Group_Team_Label'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Group_Product_Label'] ?? '') ?></td>

                            <td><?= htmlspecialchars($row['Product_Pipeline_ID'] ?? '') ?></td>

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

                            <td><?= htmlspecialchars($row['Product_Family'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Product_Name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['Product_Code'] ?? '') ?></td>

                            <td class="text-end"><?= number_format((float)($row['Product_Annual_Recurring_Order_Value'] ?? 0), 2) ?></td>
                            <td class="text-end"><?= number_format((float)($row['Annual_Order_Value_Multi'] ?? 0), 2) ?></td>

                            <td class="dt-description">
                                <div class="dt-description__inner">
                                    <?= nl2br(htmlspecialchars($row['Description'] ?? '')) ?>
                                </div>
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
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    #sfdcProductPipelineTable {
        width: 100% !important;
        font-size: clamp(0.72rem, 0.70rem + 0.12vw, 0.80rem);
    }

    #sfdcProductPipelineTable th,
    #sfdcProductPipelineTable td {
        vertical-align: middle;
    }

    #sfdcProductPipelineTable .dt-nowrap {
        white-space: nowrap;
    }

    #sfdcProductPipelineTable .dt-compact {
        width: 1%;
    }

    #sfdcProductPipelineTable .dt-money {
        width: 1%;
    }

    #sfdcProductPipelineTable .dt-description {
        white-space: normal;
    }

    #sfdcProductPipelineTable .dt-description__inner {
        max-width: clamp(16ch, 20vw, 24ch);
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.25;
    }

    #sfdcProductPipelineTable tr.dtrg-group td {
        background: #f8f9fa !important;
        font-weight: 600;
    }

    #sfdcProductPipelineTable tr.dtrg-level-1 td {
        background: #ffffff !important;
        padding-left: 2rem;
    }

    #sfdcProductPipelineTable tr.dtrg-level-2 td {
        background: #ffffff !important;
        padding-left: 4rem;
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

    #sfdcProductPipelineTable tbody tr.dtrg-subtotal>td {
        background-color: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
        font-weight: 600;
        color: #212529;
    }

    #sfdcProductPipelineTable tbody tr.dtrg-subtotal>td.text-end {
        text-align: right !important;
    }

    #sfdcProductPipelineTable tbody tr.dtrg-subtotal>td:first-child {
        padding-left: 1rem;
    }
</style>

<link href="../public/assets/css/toast.css" rel="stylesheet">
<script src="../public/assets/js/click2copy_handler.js"></script>
<script src="../public/assets/js/toast.js"></script>

<script>
    (function() {
        let currentGroupingMode = 'month_team'; // Track active grouping mode

        function escapeHtml(text) {
            return jQuery('<div>').text(text == null ? '' : String(text)).html();
        }

        function parseAmount(value) {
            if (value == null) {
                return 0;
            }

            const text = String(value).trim();
            const stripped = text.replace(/<[^>]*>/g, '').trim();

            if (stripped === '') {
                return 0;
            }

            const match = stripped.match(/-?(?:\d{1,3}(?:[.,]\d{3})+|\d+)(?:[.,]\d+)?/);

            if (!match) {
                return 0;
            }

            let numStr = match[0];

            const lastDot = numStr.lastIndexOf('.');
            const lastComma = numStr.lastIndexOf(',');

            if (lastDot !== -1 && lastComma !== -1) {
                if (lastComma > lastDot) {
                    numStr = numStr.replace('.', '').replace(',', '.');
                } else {
                    numStr = numStr.replace(',', '');
                }
            } else if (lastComma !== -1) {
                const afterComma = numStr.substring(lastComma + 1);
                if (afterComma.length <= 2) {
                    numStr = numStr.replace(',', '.');
                } else {
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

        function renderGroupStart(rows, group, level) {
            const colspan = 24;

            // Determine label based on CURRENT grouping mode, not level
            let label = 'Group';

            if (currentGroupingMode === 'none') {
                return $('<tr/>');
            }

            if (currentGroupingMode === 'month') {
                label = 'Month';
            } else if (currentGroupingMode === 'team') {
                label = 'Team';
            } else if (currentGroupingMode === 'family') {
                label = 'Family';
            } else if (currentGroupingMode === 'month_team') {
                // Multi-level: level 0 = Month, level 1 = Team
                label = level === 0 ? 'Month' : 'Team';
            } else if (currentGroupingMode === 'month_family') {
                // Multi-level: level 0 = Month, level 1 = Family
                label = level === 0 ? 'Month' : 'Family';
            }

            return $('<tr/>')
                .addClass('dtrg-level-' + level)
                .append(
                    $('<td/>', {
                        colspan: colspan,
                        html: label + ': <strong>' + escapeHtml(group) + '</strong> ' +
                            '<span class="text-muted">(' + rows.count() + ' rows)</span>'
                    })
                );
        }

        function renderGroupEnd(rows, group, level) {
            const arrovTotal = sumColumnFromRows(rows, 14);
            const aovMultiTotal = sumColumnFromRows(rows, 15);

            let label = 'Subtotal';

            if (currentGroupingMode === 'month') {
                label = 'Month subtotal';
            } else if (currentGroupingMode === 'team') {
                label = 'Team subtotal';
            } else if (currentGroupingMode === 'family') {
                label = 'Family subtotal';
            } else if (currentGroupingMode === 'month_team') {
                label = level === 0 ? 'Month subtotal' : 'Team subtotal';
            } else if (currentGroupingMode === 'month_family') {
                label = level === 0 ? 'Month subtotal' : 'Family subtotal';
            }

            return $('<tr/>')
                .addClass('dtrg-subtotal')
                .append('<td colspan="14" class="text-end fw-semibold">' + label + '</td>')
                .append('<td class="text-end fw-semibold">' + formatAmount(arrovTotal) + '</td>')
                .append('<td class="text-end fw-semibold">' + formatAmount(aovMultiTotal) + '</td>')
                .append('<td colspan="3"></td>');
        }

        function initProductPipelineTable() {
            if (typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                return;
            }

            const table = jQuery('#sfdcProductPipelineTable');
            const groupingSelect = jQuery('#productGroupingMode');

            if (!table.length) {
                return;
            }

            if (!jQuery.fn.DataTable.isDataTable('#sfdcProductPipelineTable')) {
                const dt = table.DataTable({
                    pageLength: 25,
                    responsive: false,
                    autoWidth: false,
                    order: [
                        [14, 'asc']
                    ],
                    orderFixed: [
                        [14, 'asc']
                    ],
                    columnDefs: [{
                            targets: [0, 1, 2, 3],
                            visible: false,
                            searchable: true
                        },
                        {
                            targets: [4, 5, 13, 14, 21, 22, 23],
                            className: 'dt-nowrap dt-compact'
                        },
                        {
                            targets: [18, 19],
                            className: 'dt-nowrap text-end dt-money'
                        },
                        {
                            targets: [20],
                            className: 'dt-description',
                            visible: false,
                        }
                    ],
                    rowGroup: {
                        dataSrc: [1, 2, 3],
                        enable: true,
                        startRender: function(rows, group, level) {
                            return renderGroupStart(rows, group, level);
                        },
                        endRender: function(rows, group, level) {
                            return renderGroupEnd(rows, group, level);
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

                        const footerCells = row.cells;
                        if (footerCells[13]) footerCells[13].innerHTML = '<span class="fw-semibold">Grand total</span>';
                        if (footerCells[14]) footerCells[14].innerHTML = '<span class="fw-semibold">' + formatAmount(filteredSum(14)) + '</span>';
                        if (footerCells[15]) footerCells[15].innerHTML = '<span class="fw-semibold">' + formatAmount(filteredSum(15)) + '</span>';
                    },
                    buttons: [{
                        extend: 'csvHtml5',
                        text: 'CSV',
                        title: 'SFDC_Product_Pipeline',
                        className: 'buttons-csv-hidden',
                        exportOptions: {
                            columns: [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
                            modifier: {
                                search: 'applied',
                                order: 'applied'
                            }
                        }
                    }]
                });

                dt.buttons().container().appendTo('body').hide();

                jQuery('#exportCsvBtnProduct').on('click', function() {
                    dt.button('.buttons-csv-hidden').trigger();
                });

                const globalSearch = jQuery('#globalSearchProduct');

                globalSearch.on('input', function() {
                    dt.search(this.value).draw();
                });

                function applyGrouping(mode) {
                    currentGroupingMode = mode; // Update current mode

                    if (mode === 'none') {
                        dt.rowGroup().disable();
                        dt.order([
                            [14, 'asc'],
                            [14, 'desc'],
                            [5, 'desc']
                        ]).draw();
                        return;
                    }

                    if (mode === 'month') {
                        dt.rowGroup().dataSrc(1);
                        dt.rowGroup().enable();
                        dt.order.fixed([
                            [1, 'asc']
                        ]);
                        dt.order([
                            [1, 'asc'],
                            [14, 'asc'],
                            [5, 'desc']
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
                            [2, 'asc'],
                            [14, 'asc'],
                            [5, 'desc']
                        ]).draw();
                        return;
                    }

                    if (mode === 'family') {
                        dt.rowGroup().dataSrc(3);
                        dt.rowGroup().enable();
                        dt.order.fixed([
                            [3, 'asc']
                        ]);
                        dt.order([
                            [3, 'asc'],
                            [14, 'asc'],
                            [5, 'desc']
                        ]).draw();
                        return;
                    }

                    if (mode === 'month_team') {
                        dt.rowGroup().dataSrc([1, 2]);
                        dt.rowGroup().enable();
                        dt.order.fixed([
                            [1, 'asc'],
                            [2, 'asc']
                        ]);
                        dt.order([
                            [1, 'asc'],
                            [2, 'asc'],
                            [14, 'asc'],
                            [5, 'desc']
                        ]).draw();
                        return;
                    }

                    if (mode === 'month_family') {
                        dt.rowGroup().dataSrc([1, 3]);
                        dt.rowGroup().enable();
                        dt.order.fixed([
                            [1, 'asc'],
                            [3, 'asc']
                        ]);
                        dt.order([
                            [1, 'asc'],
                            [3, 'asc'],
                            [14, 'asc'],
                            [5, 'desc']
                        ]).draw();
                        return;
                    }
                }

                groupingSelect.on('change', function() {
                    applyGrouping(this.value);
                });

                applyGrouping(groupingSelect.val() || 'month_team');
                dt.columns.adjust();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProductPipelineTable);
        } else {
            initProductPipelineTable();
        }

        document.addEventListener('productFiltersChanged', function(event) {
            const params = event.detail && event.detail.params ? event.detail.params : {};
            const url = new URL(window.location.href);

            ['team', 'agent', 'month', 'quarter', 'year', 'fiscal_period', 'stage', 'product_family'].forEach(function(key) {
                url.searchParams.delete(key);
            });

            Object.keys(params).forEach(function(key) {
                url.searchParams.set(key, params[key]);
            });

            window.location.href = url.toString();
        });
    })();
</script>