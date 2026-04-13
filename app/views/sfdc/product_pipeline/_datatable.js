/**
 * Product Pipeline DataTable Initialization
 * Handles: sorting, grouping, search, pagination, CSV export, subtotals
 */

(function (window, document, $) {
    'use strict';

    if (typeof $ === 'undefined' || typeof $.fn.dataTable === 'undefined') {
        console.warn('DataTable initialization skipped: jQuery or DataTables not loaded');
        return;
    }

    const ProductPipelineTable = {
        dt: null,
        currentGroupingMode: 'month_team',  // Track active grouping mode

        init: function () {
            const tableEl = $('#sfdcProductPipelineTable');

            if (!tableEl.length) {
                console.warn('Product pipeline table element not found');
                return;
            }

            if ($.fn.DataTable.isDataTable('#sfdcProductPipelineTable')) {
                console.warn('DataTable already initialized');
                return;
            }

            // Build config with proper 'this' context
            const self = this;
            const config = {
                pageLength: 25,
                responsive: false,
                autoWidth: false,
                order: [[14, 'asc']],
                columnDefs: [
                    {
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
                        className: 'dt-description'
                    }
                ],
                rowGroup: {
                    dataSrc: [1, 2, 3],
                    enable: true,
                    startRender: function (rows, group, level) {
                        return self.renderGroupStart(rows, group, level);
                    },
                    endRender: function (rows, group, level) {
                        return self.renderGroupEnd(rows, group, level);
                    }
                },
                footerCallback: function (row, data, start, end, display) {
                    self.updateFooter.call(self, row, data, start, end, display);
                },
                buttons: [
                    {
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
                    }
                ]
            };

            this.dt = tableEl.DataTable(config);
            this.setupEventHandlers();
            this.applyGrouping('month_team');

            console.log('Product Pipeline DataTable initialized successfully');
        },

        setupEventHandlers: function () {
            const self = this;

            // Global search
            const globalSearch = $('#globalSearchProduct');
            if (globalSearch.length) {
                globalSearch.on('input', function () {
                    self.dt.search(this.value).draw();
                });
            }

            // Grouping selector
            const groupingSelect = $('#productGroupingMode');
            if (groupingSelect.length) {
                groupingSelect.on('change', function () {
                    self.applyGrouping(this.value);
                });
            }

            // CSV export button
            const csvButton = $('#exportCsvBtnProduct');
            if (csvButton.length) {
                this.dt.buttons().container().appendTo('body').hide();
                csvButton.on('click', function () {
                    self.dt.button('.buttons-csv-hidden').trigger();
                });
            }

            // Filter form
            document.addEventListener('productFiltersChanged', function (event) {
                const params = event.detail && event.detail.params ? event.detail.params : {};
                const url = new URL(window.location.href);

                ['team', 'agent', 'month', 'quarter', 'year', 'fiscal_period', 'stage', 'product_family'].forEach(function (key) {
                    url.searchParams.delete(key);
                });

                Object.keys(params).forEach(function (key) {
                    url.searchParams.set(key, params[key]);
                });

                window.location.href = url.toString();
            });
        },

        applyGrouping: function (mode) {
            if (!this.dt) {
                console.warn('DataTable not initialized');
                return;
            }

            this.currentGroupingMode = mode;  // Store the active mode
            const self = this;

            switch (mode) {
                case 'none':
                    this.dt.rowGroup().disable();
                    this.dt.order([[14, 'asc']]).draw();
                    break;

                case 'month':
                    this.dt.rowGroup().dataSrc(1);  // Column 1: Group_Month_Label
                    this.dt.rowGroup().enable();
                    this.dt.order.fixed([[1, 'asc']]);
                    this.dt.order([[1, 'asc'], [14, 'asc'], [5, 'desc']]).draw();
                    break;

                case 'team':
                    this.dt.rowGroup().dataSrc(2);  // Column 2: Group_Team_Label
                    this.dt.rowGroup().enable();
                    this.dt.order.fixed([[2, 'asc']]);
                    this.dt.order([[2, 'asc'], [14, 'asc'], [5, 'desc']]).draw();
                    break;

                case 'family':
                    this.dt.rowGroup().dataSrc(3);  // Column 3: Group_Product_Label
                    this.dt.rowGroup().enable();
                    this.dt.order.fixed([[3, 'asc']]);
                    this.dt.order([[3, 'asc'], [14, 'asc'], [5, 'desc']]).draw();
                    break;

                case 'month_team':
                    this.dt.rowGroup().dataSrc([1, 2]);  // Columns 1 & 2
                    this.dt.rowGroup().enable();
                    this.dt.order.fixed([[1, 'asc'], [2, 'asc']]);
                    this.dt.order([[1, 'asc'], [2, 'asc'], [14, 'asc'], [5, 'desc']]).draw();
                    break;

                case 'month_family':
                    this.dt.rowGroup().dataSrc([1, 3]);  // Columns 1 & 3
                    this.dt.rowGroup().enable();
                    this.dt.order.fixed([[1, 'asc'], [3, 'asc']]);
                    this.dt.order([[1, 'asc'], [3, 'asc'], [14, 'asc'], [5, 'desc']]).draw();
                    break;

                default:
                    console.warn('Unknown grouping mode: ' + mode);
            }

            this.dt.columns.adjust();
        },

        renderGroupStart: function (rows, group, level) {
            const colspan = 24;
            const mode = this.currentGroupingMode;

            // Determine label based on CURRENT grouping mode, not level
            let label = 'Group';

            if (mode === 'none') {
                return $('<tr/>');  // No grouping
            }

            if (mode === 'month') {
                label = 'Month';
            } else if (mode === 'team') {
                label = 'Team';
            } else if (mode === 'family') {
                label = 'Family';
            } else if (mode === 'month_team') {
                // Multi-level: level 0 = Month, level 1 = Team
                label = level === 0 ? 'Month' : 'Team';
            } else if (mode === 'month_family') {
                // Multi-level: level 0 = Month, level 1 = Family
                label = level === 0 ? 'Month' : 'Family';
            }

            return $('<tr/>')
                .addClass('dtrg-level-' + level)
                .append(
                    $('<td/>', {
                        colspan: colspan,
                        html: label + ': <strong>' + this.escapeHtml(group) + '</strong> ' +
                            '<span class="text-muted">(' + rows.count() + ' rows)</span>'
                    })
                );
        },

        renderGroupEnd: function (rows, group, level) {
            const arrovTotal = this.sumColumnFromRows(rows, 14);
            const aovMultiTotal = this.sumColumnFromRows(rows, 15);
            const mode = this.currentGroupingMode;

            let label = 'Subtotal';

            if (mode === 'month') {
                label = 'Month subtotal';
            } else if (mode === 'team') {
                label = 'Team subtotal';
            } else if (mode === 'family') {
                label = 'Family subtotal';
            } else if (mode === 'month_team') {
                label = level === 0 ? 'Month subtotal' : 'Team subtotal';
            } else if (mode === 'month_family') {
                label = level === 0 ? 'Month subtotal' : 'Family subtotal';
            }

            return $('<tr/>')
                .addClass('dtrg-subtotal')
                .append('<td colspan="17" class="text-end fw-semibold">' + label + '</td>')
                .append('<td class="text-end fw-semibold">' + this.formatAmount(arrovTotal) + '</td>')
                .append('<td class="text-end fw-semibold">' + this.formatAmount(aovMultiTotal) + '</td>')
                .append('<td colspan="5"></td>');
        },

        updateFooter: function (row, data, start, end, display) {
            const api = this.api();
            const self = ProductPipelineTable;

            function filteredSum(colIdx) {
                let total = 0;
                api.rows({ search: 'applied' }).nodes().each(function (rowNode) {
                    const cell = rowNode.cells[colIdx];
                    total += self.parseAmount(cell ? cell.textContent : 0);
                });
                return total;
            }

            const footerCells = row.cells;
            if (footerCells[17]) {
                footerCells[17].innerHTML = '<span class="fw-semibold">Grand total</span>';
            }
            if (footerCells[18]) {
                footerCells[18].innerHTML = '<span class="fw-semibold">' + self.formatAmount(filteredSum(18)) + '</span>';
            }
            if (footerCells[19]) {
                footerCells[19].innerHTML = '<span class="fw-semibold">' + self.formatAmount(filteredSum(19)) + '</span>';
            }
        },

        parseAmount: function (value) {
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
                    numStr = numStr.replace(/\./g, '').replace(',', '.');
                } else {
                    numStr = numStr.replace(/,/g, '');
                }
            } else if (lastComma !== -1) {
                const afterComma = numStr.substring(lastComma + 1);
                if (afterComma.length <= 2) {
                    numStr = numStr.replace(',', '.');
                } else {
                    numStr = numStr.replace(/,/g, '');
                }
            } else if (lastDot !== -1) {
                const afterDot = numStr.substring(lastDot + 1);
                if (afterDot.length > 2) {
                    numStr = numStr.replace(/\./g, '');
                }
            }

            const number = parseFloat(numStr);
            return isNaN(number) ? 0 : number;
        }, 

        formatAmount: function (value) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        },

        sumColumnFromRows: function (rows, columnIndex) {
            let total = 0;
            rows.nodes().each((row) => {
                const cell = row.cells[columnIndex];
                total += this.parseAmount(cell ? cell.textContent : 0);
            });
            return total;
        },

        escapeHtml: function (text) {
            return $('<div>').text(text == null ? '' : String(text)).html();
        }
    };

    // Initialize on DOM ready
    $(document).ready(function () {
        ProductPipelineTable.init();
    });

    // Expose to window for external access if needed
    window.ProductPipelineTable = ProductPipelineTable;

})(window, document, jQuery);