(function (window, document) {
    'use strict';

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function showCellState(cell, type, message) {
        cell.classList.remove('sfdc-edit-success', 'sfdc-edit-error', 'sfdc-edit-saving');
        cell.classList.add(type);

        if (message) {
            cell.setAttribute('title', message);
        }

        if (type === 'sfdc-edit-success' || type === 'sfdc-edit-error') {
            setTimeout(function () {
                cell.classList.remove(type);
            }, 1800);
        }
    }

    function buildInput(field, value, options) {
        if (field === 'Type') {
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm sfdc-inline-input';

            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = '-- Select --';
            select.appendChild(blank);

            (options.typeOptions || ['Fixed', 'ICT', 'Other']).forEach(function (item) {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                if (String(value || '') === String(item)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            return select;
        }

        if (field === 'Real_Flag') {
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm sfdc-inline-input';

            const yesOption = document.createElement('option');
            yesOption.value = 'Yes';
            yesOption.textContent = 'Yes';
            if (String(value || 'Yes') === 'Yes') {
                yesOption.selected = true;
            }
            select.appendChild(yesOption);

            const noOption = document.createElement('option');
            noOption.value = 'No';
            noOption.textContent = 'No';
            if (String(value || '') === 'No') {
                noOption.selected = true;
            }
            select.appendChild(noOption);

            return select;
        }

        const input = document.createElement('input');
        input.type = 'number';
        input.step = '0.01';
        input.className = 'form-control form-control-sm sfdc-inline-input';
        input.value = value == null ? '' : value;

        return input;
    }

    function saveCell(cell, config, newValue, originalValue) {
        const rowId = cell.dataset.id;
        const field = cell.dataset.field;

        if (!rowId || !field) {
            return Promise.reject(new Error('Missing row id or field.'));
        }

        showCellState(cell, 'sfdc-edit-saving', 'Saving...');

        const formData = new FormData();
        formData.append('id', rowId);
        formData.append('field', field);
        formData.append('value', newValue);

        return fetch(config.endpoint, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (!result.success) {
                    throw new Error(result.error || 'Save failed.');
                }

                const row = result.data && result.data.row ? result.data.row : null;
                const displayValue = String(newValue == null ? '' : newValue);
                const displayHtml = '<span class="sfdc-editable-value">' + escapeHtml(displayValue) + '</span>';

                cell.dataset.value = displayValue;
                cell.innerHTML = displayHtml;

                const tableEl = cell.closest('table');
                if (tableEl && window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(tableEl)) {
                    const dt = jQuery(tableEl).DataTable();
                    dt.cell(cell).data(displayHtml);
                    dt.row(cell.closest('tr')).invalidate('dom').draw(false);
                }

                showCellState(cell, 'sfdc-edit-success', 'Saved');

                document.dispatchEvent(new CustomEvent('pipelineInlineEditSaved', {
                    detail: {
                        id: rowId,
                        field: field,
                        value: displayValue,
                        row: row
                    }
                }));
            })
            .catch(function (error) {
                const fallbackValue = originalValue == null ? '' : String(originalValue);
                const fallbackHtml = '<span class="sfdc-editable-value">' + escapeHtml(fallbackValue) + '</span>';

                cell.dataset.value = fallbackValue;
                cell.innerHTML = fallbackHtml;

                const tableEl = cell.closest('table');
                if (tableEl && window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(tableEl)) {
                    const dt = jQuery(tableEl).DataTable();
                    const cellIndex = dt.cell(cell).index();

                    if (cellIndex) {
                        dt.cell(cellIndex.row, cellIndex.column).data(fallbackHtml);
                        dt.row(cell.closest('tr')).invalidate('dom').draw(false);
                    }
                }

                showCellState(cell, 'sfdc-edit-error', error.message || 'Save failed');

                document.dispatchEvent(new CustomEvent('pipelineInlineEditError', {
                    detail: {
                        id: rowId,
                        field: field,
                        value: fallbackValue,
                        error: error
                    }
                }));
            });
    }

    function activateEditableCell(cell, config) {
        if (cell.dataset.editing === '1') {
            return;
        }

        const field = cell.dataset.field;
        const originalValue = cell.dataset.value != null ? cell.dataset.value : cell.textContent.trim();

        cell.dataset.editing = '1';

        const input = buildInput(field, originalValue, {
            typeOptions: config.typeOptions
        });

        cell.innerHTML = '';
        cell.appendChild(input);
        input.focus();

        if (typeof input.select === 'function') {
            input.select();
        }

        let saveTriggered = false;

        function finalize(save) {
            if (saveTriggered) {
                return;
            }
            saveTriggered = true;
            delete cell.dataset.editing;

            const newValue = input.dataset.forceValue !== undefined ? input.dataset.forceValue : input.value;
            const displayValue = newValue == null ? '' : String(newValue);

            if (!save || displayValue === String(originalValue == null ? '' : originalValue)) {
                cell.innerHTML = '<span class="sfdc-editable-value">' + escapeHtml(originalValue == null ? '' : originalValue) + '</span>';
                cell.dataset.value = originalValue == null ? '' : originalValue;
                return;
            }

            cell.innerHTML = '<span class="sfdc-editable-value">' + escapeHtml(displayValue) + '</span>';
            cell.dataset.value = displayValue;

            saveCell(cell, config, displayValue, originalValue);
        }

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                finalize(true);
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                finalize(false);
            }
        });

        if (field === 'Type' || field === 'Real_Flag') {
            input.addEventListener('change', function () {
                const selectedValue = input.value;
                input.dataset.forceValue = selectedValue;
                finalize(true);
            });

            input.addEventListener('blur', function () {
                finalize(false);
            });
        } else {
            input.addEventListener('blur', function () {
                finalize(true);
            });
        }
    }

    window.PipelineInlineEdit = {
        init: function (config) {
            const settings = Object.assign({
                tableSelector: '.js-sfdc-inline-table',
                cellSelector: '.js-editable-cell',
                endpoint: '/api/sfdc_pipeline.php?action=update_won_field',
                typeOptions: ['Fixed', 'ICT', 'Other']
            }, config || {});

            const table = document.querySelector(settings.tableSelector);

            if (!table) {
                return;
            }

            table.addEventListener('click', function (event) {
                const cell = event.target.closest(settings.cellSelector);

                if (!cell || !table.contains(cell)) {
                    return;
                }

                activateEditableCell(cell, settings);
            });
        }
    };
})(window, document);