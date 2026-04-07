<?php

/**
 * Row Coloring Toggle Component - FIXED VERSION
 * Adds ability to toggle status-based row coloring on/off
 */
?>

<style>
    .row-coloring-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 8px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .row-coloring-toggle label {
        margin: 0;
        font-size: 13px;
        color: #6c757d;
        cursor: pointer;
        user-select: none;
    }

    .row-coloring-toggle input[type="checkbox"] {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }

    .row-coloring-legend {
        display: flex;
        gap: 16px;
        margin-top: 12px;
        font-size: 12px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
    }
</style>

<script>
    /**
     * Row Coloring Toggle Extension - Debugged
     */
    (function() {
        'use strict';

        const DEFAULT_STATUS_COLORS = {
            'Contract Signed': 'table-success',
            'Design': 'table-primary',
            'Pending': 'table-warning',
            'Cancelled': 'table-danger'
        };

        const rowColoringState = {};

        /**
         * Initialize row coloring toggle for a DataTable
         */
        window.initRowColoringToggle = function(tableId, statusColorMap = null) {
            const statusColors = statusColorMap || DEFAULT_STATUS_COLORS;

            rowColoringState[tableId] = {
                enabled: false,
                statusColors: statusColors,
                statusColumnIndex: null
            };

            const tableEl = document.getElementById(tableId);
            if (!tableEl) {
                console.warn(`Table #${tableId} not found`);
                return;
            }

            // Auto-detect status column index
            detectStatusColumn(tableEl, tableId, statusColors);

            // Create toggle button
            createToggleButton(tableId, statusColors);
        };

        /**
         * Auto-detect which column contains status data
         */
        function detectStatusColumn(tableEl, tableId, statusColors) {
            const headers = tableEl.querySelectorAll('thead th');
            let statusColumnIndex = -1;

            // Look for "Status" header
            headers.forEach((th, index) => {
                if (th.textContent.toLowerCase().includes('status')) {
                    statusColumnIndex = index;
                }
            });

            if (statusColumnIndex >= 0) {
                rowColoringState[tableId].statusColumnIndex = statusColumnIndex;
                console.log(`✓ Status column found at index ${statusColumnIndex}`);
            } else {
                console.warn('⚠ Status column not found in headers');
                // Try to auto-detect by looking for status values in first few columns
                const rows = tableEl.querySelectorAll('tbody tr');
                if (rows.length > 0) {
                    const cells = rows[0].querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        const text = cell.textContent.trim();
                        if (Object.keys(statusColors).includes(text)) {
                            statusColumnIndex = index;
                            rowColoringState[tableId].statusColumnIndex = statusColumnIndex;
                            console.log(`✓ Status column auto-detected at index ${statusColumnIndex}: "${text}"`);
                        }
                    });
                }
            }
        }

        /**
         * Create toggle button
         */
        function createToggleButton(tableId, statusColors) {
            const filterContainer = document.querySelector('.btn-group[role="group"]');

            if (!filterContainer) {
                console.warn('Filter button group not found');
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'row-coloring-toggle';
            wrapper.style.marginLeft = '12px';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = `rowColoringToggle_${tableId}`;

            const label = document.createElement('label');
            label.setAttribute('for', checkbox.id);
            label.innerHTML = '🎨 Color by Status';

            checkbox.addEventListener('change', function() {
                toggleRowColoring(tableId, this.checked);
            });

            wrapper.appendChild(checkbox);
            wrapper.appendChild(label);

            filterContainer.parentElement.insertBefore(wrapper, filterContainer.nextSibling);

  
        }

        /**
         * Toggle row coloring
         */
        function toggleRowColoring(tableId, enable) {
            const tableEl = document.getElementById(tableId);
            const state = rowColoringState[tableId];

            console.log(`${enable ? 'Enabling' : 'Disabling'} row coloring for ${tableId}`);

            if (enable) {
                state.enabled = true;
                tableEl.classList.remove('table-striped');

                // Apply colors
                applyRowColors(tableId, state.statusColors, state.statusColumnIndex);

                // Attach listener
                attachDrawListener(tableId, state.statusColors, state.statusColumnIndex);

            } else {
                state.enabled = false;
                tableEl.classList.add('table-striped');

                // Remove colors
                const rows = tableEl.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    Object.values(state.statusColors).forEach(colorClass => {
                        row.classList.remove(colorClass);
                    });
                });

                detachDrawListener(tableId);
            }
        }

        /**
         * Apply color classes to rows
         */
        function applyRowColors(tableId, statusColors, statusColumnIndex) {
            const tableEl = document.getElementById(tableId);
            const rows = tableEl.querySelectorAll('tbody tr');

            rows.forEach((row, rowIndex) => {
                const cells = row.querySelectorAll('td');

                // Use detected column index, or try common positions
                let status = null;

                if (statusColumnIndex >= 0 && cells[statusColumnIndex]) {
                    status = cells[statusColumnIndex].textContent.trim();
                } else {
                    // Fallback: search all cells
                    for (let cell of cells) {
                        const text = cell.textContent.trim();
                        if (Object.keys(statusColors).includes(text)) {
                            status = text;
                            break;
                        }
                    }
                }

                if (status && statusColors[status]) {
                    const colorClass = statusColors[status];
                    row.classList.add(colorClass);
                    console.log(`Row ${rowIndex}: Applied "${colorClass}" for status "${status}"`);
                }
            });
        }

        /**
         * Attach draw listener
         */
        function attachDrawListener(tableId, statusColors, statusColumnIndex) {
            const table = $(`#${tableId}`).DataTable();

            if (table) {
                table.off('draw.rowColoring');

                table.on('draw.rowColoring', function() {
                    console.log('Table redrawn, reapplying colors...');
                    applyRowColors(tableId, statusColors, statusColumnIndex);
                });
            }
        }

        /**
         * Detach listener
         */
        function detachDrawListener(tableId) {
            const table = $(`#${tableId}`).DataTable();
            if (table) {
                table.off('draw.rowColoring');
            }
        }

        /**
         * Show legend
         */
        function showLegend(tableId, statusColors) {
            const toggle = document.getElementById(`rowColoringToggle_${tableId}`);
            if (!toggle) return;

            const parent = toggle.parentElement;

            let legend = parent.querySelector('.row-coloring-legend');
            if (legend) {
                legend.remove();
                return;
            }

            legend = document.createElement('div');
            legend.className = 'row-coloring-legend';

            const colorDisplay = {
                'table-success': '#d1e7dd',
                'table-primary': '#cfe2ff',
                'table-warning': '#fff3cd',
                'table-danger': '#f8d7da'
            };

            Object.entries(statusColors).forEach(([status, colorClass]) => {
                const item = document.createElement('div');
                item.className = 'legend-item';

                const colorBox = document.createElement('div');
                colorBox.className = 'legend-color';
                colorBox.style.backgroundColor = colorDisplay[colorClass] || '#ccc';

                const label = document.createElement('span');
                label.textContent = status;

                item.appendChild(colorBox);
                item.appendChild(label);
                legend.appendChild(item);
            });

            parent.appendChild(legend);

            parent.addEventListener('mouseleave', function() {
                const leg = this.querySelector('.row-coloring-legend');
                if (leg) leg.remove();
            }, {
                once: true
            });
        }

    })();
</script>