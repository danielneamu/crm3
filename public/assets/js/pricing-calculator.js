/**
 * Pricing Calculator
 * Business case generator with markup calculations
 */

// Markup configuration based on contract period and selling margin
const MARKUP_TABLE = {
    otc: {
        '10': 1.1202,
        '15': 1.18624,
        '25': 1.3450
    },
    '6m': {
        otc: { '10': 1.1467, '15': 1.2164, '25': 1.3842 },
        recurrent: { '10': 1.1230, '15': 1.1910, '25': 1.3560 }
    },
    '12m': {
        otc: { '10': 1.1796, '15': 1.25343, '25': 1.4328 },
        recurrent: { '10': 1.1265, '15': 1.1968, '25': 1.3680 }
    },
    '24m': {
        otc: { '10': 1.2480, '15': 1.33052, '25': 1.5336 },
        recurrent: { '10': 1.1340, '15': 1.2089, '25': 1.3950 }
    },
    '36m': {
        otc: { '10': 1.3190, '15': 1.4117377, '25': 1.6434 },
        recurrent: { '10': 1.1415, '15': 1.2216, '25': 1.4220 }
    }
};

let otcRowCounter = 0;
let recurrentRowCounter = 0;

$(document).ready(function () {
    // Initialize with empty rows
    addOTCRow();
    addRecurrentRow();
});

/**
 * Add OTC item row
 */
function addOTCRow() {
    otcRowCounter++;
    const row = `
        <tr data-row-id="${otcRowCounter}" data-type="otc">
            <td>${otcRowCounter}</td>
            <td><input type="text" class="form-control form-control-sm" name="otc_desc[]" placeholder="Item description"></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm otc-unit-cost" name="otc_unit[]" placeholder="0.00" onchange="calculateRowTotal(this)"></td>
            <td><input type="number" min="0" class="form-control form-control-sm otc-volume" name="otc_vol[]" placeholder="1" value="1" onchange="calculateRowTotal(this)"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm otc-total bg-light" name="otc_total[]" readonly></td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Remove row"><i class="bi bi-trash"></i></button></td>
        </tr>
    `;
    $('#otcItemsBody').append(row);
}

/**
 * Add Recurrent item row
 */
function addRecurrentRow() {
    recurrentRowCounter++;
    const row = `
        <tr data-row-id="${recurrentRowCounter}" data-type="recurrent">
            <td>${recurrentRowCounter}</td>
            <td><input type="text" class="form-control form-control-sm" name="rec_desc[]" placeholder="Item description"></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm rec-unit-cost" name="rec_unit[]" placeholder="0.00" onchange="calculateRowTotal(this)"></td>
            <td><input type="number" min="0" class="form-control form-control-sm rec-volume" name="rec_vol[]" placeholder="1" value="1" onchange="calculateRowTotal(this)"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm rec-total bg-light" name="rec_total[]" readonly></td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Remove row"><i class="bi bi-trash"></i></button></td>
        </tr>
    `;
    $('#recurrentItemsBody').append(row);
}

/**
 * Calculate row total (unit cost × quantity)
 */
function calculateRowTotal(input) {
    const row = $(input).closest('tr');
    const type = row.data('type');

    let unitCost, volume;
    if (type === 'otc') {
        unitCost = parseFloat(row.find('.otc-unit-cost').val()) || 0;
        volume = parseFloat(row.find('.otc-volume').val()) || 0;
        const total = unitCost * volume;
        row.find('.otc-total').val(total.toFixed(2));
    } else {
        unitCost = parseFloat(row.find('.rec-unit-cost').val()) || 0;
        volume = parseFloat(row.find('.rec-volume').val()) || 0;
        const total = unitCost * volume;
        row.find('.rec-total').val(total.toFixed(2));
    }
}

/**
 * Remove row from table
 */
function removeRow(btn) {
    const row = $(btn).closest('tr');
    const tbody = row.closest('tbody');

    row.remove();

    // Renumber remaining rows
    tbody.find('tr').each(function (index) {
        $(this).find('td:first').text(index + 1);
    });
}

/**
 * Reset calculator - clear all inputs
 */
function resetCalculator() {
    if (!confirm('Reset all data? This cannot be undone.')) {
        return;
    }

    $('#otcItemsBody').empty();
    $('#recurrentItemsBody').empty();
    $('#resultsSection').hide();
    $('#lineItemResults').empty();
    $('#summaryResults').empty();

    otcRowCounter = 0;
    recurrentRowCounter = 0;

    addOTCRow();
    addRecurrentRow();

    showToast('Reset', 'Calculator cleared', 'info');
}

/**
 * Main calculation function
 * Collects all items and generates business cases
 */
function calculateAllBusinessCases() {
    // Collect OTC items
    const otcItems = [];
    $('#otcItemsBody tr').each(function () {
        const desc = $(this).find('[name="otc_desc[]"]').val().trim();
        const total = parseFloat($(this).find('.otc-total').val()) || 0;

        if (total > 0) {
            otcItems.push({
                description: desc || 'Unnamed OTC Item',
                cost: total
            });
        }
    });

    // Collect Recurrent items
    const recurrentItems = [];
    $('#recurrentItemsBody tr').each(function () {
        const desc = $(this).find('[name="rec_desc[]"]').val().trim();
        const total = parseFloat($(this).find('.rec-total').val()) || 0;

        if (total > 0) {
            recurrentItems.push({
                description: desc || 'Unnamed Recurrent Item',
                cost: total
            });
        }
    });

    // Validation
    if (otcItems.length === 0 && recurrentItems.length === 0) {
        showToast('Error', 'Please add at least one item with a cost', 'error');
        return;
    }

    // Generate results - SUMMARY FIRST, then line items
    displaySummaryTable(otcItems, recurrentItems);
    displayLineItemBusinessCases(otcItems, recurrentItems);

    $('#resultsSection').show();

    // Scroll to results
    $('html, body').animate({
        scrollTop: $('#resultsSection').offset().top - 100
    }, 500);

    showToast('Success', 'Business cases calculated', 'success');
}

/**
 * Display individual business case tables for each line item
 */
function displayLineItemBusinessCases(otcItems, recurrentItems) {
    const container = $('#lineItemResults');
    container.empty();

    // OTC Items
    otcItems.forEach((item, index) => {
        const card = createBusinessCaseCard(item, 'otc', index + 1);
        container.append(card);
    });

    // Recurrent Items
    recurrentItems.forEach((item, index) => {
        const card = createBusinessCaseCard(item, 'recurrent', index + 1);
        container.append(card);
    });
}

/**
 * Create business case card for single line item
 */
function createBusinessCaseCard(item, type, number) {
    const headerColor = type === 'otc' ? 'bg-primary' : 'bg-success';
    const icon = type === 'otc' ? 'bi-coin' : 'bi-arrow-repeat';

    let html = `
        <div class="card mb-3">
            <div class="card-header ${headerColor} text-white">
                <i class="bi ${icon}"></i> <strong>${type.toUpperCase()} Item ${number}:</strong> ${item.description}
                <span class="float-end">Base Cost: €${item.cost.toFixed(2)}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="align-middle">Period</th>
                                <th colspan="3" class="text-center border-end">10% Margin</th>
                                <th colspan="3" class="text-center border-end">15% Margin</th>
                                <th colspan="3" class="text-center">25% Margin</th>
                            </tr>
                            <tr>
                                <th class="text-center">OTP</th>
                                <th class="text-center">Monthly</th>
                                <th class="text-center border-end">TCV</th>
                                <th class="text-center">OTP</th>
                                <th class="text-center">Monthly</th>
                                <th class="text-center border-end">TCV</th>
                                <th class="text-center">OTP</th>
                                <th class="text-center">Monthly</th>
                                <th class="text-center">TCV</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    // ALL items show ALL periods (OTP, 6m, 12m, 24m, 36m)
    html += generatePeriodRow('OTP', item.cost, type, 'otc');
    html += generatePeriodRow('6 months', item.cost, type, '6m');
    html += generatePeriodRow('12 months', item.cost, type, '12m');
    html += generatePeriodRow('24 months', item.cost, type, '24m');
    html += generatePeriodRow('36 months', item.cost, type, '36m');

    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    return html;
}

/**
 * Generate table row for specific period
 * 
 * OTP (One-Time Payment):
 *   - OTC items: Pay once with OTC markup
 *   - Recurrent items: Pay 1 month upfront with OTC markup
 *   - No monthly component, total = OTC value
 * 
 * Contract Periods (6m, 12m, 24m, 36m):
 *   - OTC items: Amortized into monthly rate (OTC cost ÷ months × OTC markup)
 *   - Recurrent items: Added to monthly rate (monthly cost × recurrent markup)
 *   - Monthly rate covers everything
 *   - Total = Monthly rate × months
 */
function generatePeriodRow(periodLabel, baseCost, itemType, period) {
    const margins = ['10', '15', '25'];
    let row = `<tr><td><strong>${periodLabel}</strong></td>`;

    margins.forEach((margin, idx) => {
        let otcValue, monthlyValue, totalValue;

        if (period === 'otc') {
            // OTP: One-Time Payment scenario
            const markup = MARKUP_TABLE.otc[margin];
            otcValue = baseCost * markup;
            monthlyValue = 0;
            totalValue = otcValue;
        } else {
            // Contract scenario
            const otcMarkup = MARKUP_TABLE[period].otc[margin];
            const recMarkup = MARKUP_TABLE[period].recurrent[margin];
            const months = parseInt(period.replace('m', ''));

            if (itemType === 'otc') {
                // OTC item: Amortized into monthly payments
                // No upfront OTC payment in contracts
                otcValue = 0;
                monthlyValue = (baseCost / months) * otcMarkup;
                totalValue = monthlyValue * months;
            } else {
                // Recurrent item: Ongoing monthly cost
                otcValue = 0;
                monthlyValue = baseCost * recMarkup;
                totalValue = monthlyValue * months;
            }
        }

        const borderClass = idx === 2 ? '' : 'border-end';

        row += `
            <td class="text-end">${otcValue > 0 ? '€' + otcValue.toFixed(2) : '-'}</td>
            <td class="text-end">${monthlyValue > 0 ? '€' + monthlyValue.toFixed(2) : '-'}</td>
            <td class="text-end ${borderClass}"><strong>€${totalValue.toFixed(2)}</strong></td>
        `;
    });

    row += '</tr>';
    return row;
}

/**
 * Display summary table with grand totals
 */
function displaySummaryTable(otcItems, recurrentItems) {
    const container = $('#summaryResults');

    // Calculate grand totals
    const totals = calculateGrandTotals(otcItems, recurrentItems);

    // Calculate total base COSTS (without markup) for threshold checks
    const totalOtcCost = otcItems.reduce((sum, item) => sum + item.cost, 0);
    const totalRecurrentCost = recurrentItems.reduce((sum, item) => sum + item.cost, 0);

    let html = `
        <div class="row">
            <!-- Left Column: Summary Table -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Grand Total Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Period</th>
                                        <th>Margin</th>
                                        <th class="text-end">Monthly</th>
                                        <th class="text-end">TCV</th>
                                        <th class="text-end">AOV</th>
                                    </tr>
                                </thead>
                                <tbody>
    `;

    // OTP totals (AOV = TCV)
    // Check OTP cost threshold
    const otpCost = totalOtcCost + totalRecurrentCost; // OTP = OTC + 1 month recurrent
    const otpNeedsFinance = otpCost > 50000;
    const otpNeedsSCM = otpCost > 150000;

    ['10', '15', '25'].forEach((margin, idx) => {
        const tcv = totals.otc[margin];
        const aov = tcv; // OTP: AOV = TCV
        const tcvClass = (otpNeedsFinance || otpNeedsSCM) ? 'text-danger fw-bold' : '';

        html += `
            <tr class="table-primary">
                <td>${idx === 0 ? '<strong>OTP</strong>' : ''}</td>
                <td><strong>${margin}%</strong></td>
                <td class="text-end">-</td>
                <td class="text-end ${tcvClass}"><strong>€${tcv.toFixed(2)}</strong></td>
                <td class="text-end"><strong>€${aov.toFixed(2)}</strong></td>
            </tr>
        `;
    });

    // Contract period totals (show monthly rate, TCV, and AOV)
    [
        { period: '6m', label: '6m', color: 'table-success', months: 6, aovDivisor: 1 },
        { period: '12m', label: '12m', color: 'table-info', months: 12, aovDivisor: 1 },
        { period: '24m', label: '24m', color: 'table-warning', months: 24, aovDivisor: 2 },
        { period: '36m', label: '36m', color: 'table-danger', months: 36, aovDivisor: 3 }
    ].forEach(({ period, label, color, months, aovDivisor }) => {
        // Calculate total COST (base cost without markup) for this period
        const periodCost = totalOtcCost + (months * totalRecurrentCost);
        const needsFinance = periodCost > 50000;
        const needsSCM = periodCost > 150000;

        ['10', '15', '25'].forEach((margin, idx) => {
            const tcv = totals[period][margin];
            const monthlyRate = tcv / months;
            const aov = tcv / aovDivisor;

            const tcvClass = (needsFinance || needsSCM) ? 'text-danger fw-bold' : '';

            html += `
                <tr class="${color}">
                    <td>${idx === 0 ? `<strong>${label}</strong>` : ''}</td>
                    <td><strong>${margin}%</strong></td>
                    <td class="text-end">€${monthlyRate.toFixed(2)}</td>
                    <td class="text-end ${tcvClass}"><strong>€${tcv.toFixed(2)}</strong></td>
                    <td class="text-end"><strong>€${aov.toFixed(2)}</strong></td>
                </tr>
            `;
        });
    });

    html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Email Format -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-envelope"></i> Email-Ready Format</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyEmailFormat()">
                                <i class="bi bi-clipboard"></i> Copy to Clipboard
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="downloadEmailFormat()">
                                <i class="bi bi-download"></i> Download as Text
                            </button>
                        </div>
                        <textarea id="emailFormatText" class="form-control font-monospace flex-grow-1" style="min-height: 400px;" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.html(html);
    generateEmailFormat(totals, totalOtcCost, totalRecurrentCost);
}

/**
 * Calculate grand totals across all items
 * 
 * OTP Period:
 *   - OTC items: One-time payment with OTC markup
 *   - Recurrent items: One month payment with OTC markup
 * 
 * Contract Periods (6m, 12m, 24m, 36m):
 *   - OTC items: Amortized into monthly payments (no upfront OTC)
 *   - Recurrent items: Monthly payments with recurrent markup
 *   - Total = Monthly rate × months
 */
function calculateGrandTotals(otcItems, recurrentItems) {
    const totals = {
        otc: { '10': 0, '15': 0, '25': 0 },
        '6m': { '10': 0, '15': 0, '25': 0 },
        '12m': { '10': 0, '15': 0, '25': 0 },
        '24m': { '10': 0, '15': 0, '25': 0 },
        '36m': { '10': 0, '15': 0, '25': 0 }
    };

    // OTP: One-time payment scenario
    ['10', '15', '25'].forEach(margin => {
        // OTC items: Pay once
        otcItems.forEach(item => {
            totals.otc[margin] += item.cost * MARKUP_TABLE.otc[margin];
        });

        // Recurrent items: Pay 1 month upfront
        recurrentItems.forEach(item => {
            totals.otc[margin] += item.cost * MARKUP_TABLE.otc[margin];
        });
    });

    // Contract periods: Everything amortized into monthly payments
    ['6m', '12m', '24m', '36m'].forEach(period => {
        ['10', '15', '25'].forEach(margin => {
            const otcMarkup = MARKUP_TABLE[period].otc[margin];
            const recMarkup = MARKUP_TABLE[period].recurrent[margin];
            const months = parseInt(period.replace('m', ''));

            // OTC items: Amortized into monthly rate
            otcItems.forEach(item => {
                const monthlyValue = (item.cost / months) * otcMarkup;
                const totalValue = monthlyValue * months;
                totals[period][margin] += totalValue;
            });

            // Recurrent items: Monthly rate
            recurrentItems.forEach(item => {
                const monthlyValue = item.cost * recMarkup;
                const totalValue = monthlyValue * months;
                totals[period][margin] += totalValue;
            });
        });
    });

    return totals;
}

/**
 * Generate email-ready text format
 */
function generateEmailFormat(totals, totalOtcCost, totalRecurrentCost) {
    const date = new Date().toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });

    // Check OTP cost threshold
    const otpCost = totalOtcCost + totalRecurrentCost;
    const otpNeedsFinance = otpCost > 50000;
    const otpNeedsSCM = otpCost > 150000;

    let text = `PRICING SUMMARY - ${date}
${'='.repeat(50)}

OTP (ONE-TIME PAYMENT):
  10% margin:  €${totals.otc['10'].toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
  15% margin:  €${totals.otc['15'].toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
  25% margin:  €${totals.otc['25'].toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}

`;

    // Contract periods with cost threshold checks
    [
        { period: '6m', label: '6-MONTH CONTRACT', months: 6 },
        { period: '12m', label: '12-MONTH CONTRACT', months: 12 },
        { period: '24m', label: '24-MONTH CONTRACT', months: 24 },
        { period: '36m', label: '36-MONTH CONTRACT', months: 36 }
    ].forEach(({ period, label, months }) => {
        // Calculate total COST (without markup) for this period
        const periodCost = totalOtcCost + (months * totalRecurrentCost);
        const needsFinance = periodCost > 50000;
        const needsSCM = periodCost > 150000;

        text += `${label}:\n`;

        ['10', '15', '25'].forEach(margin => {
            const tcv = totals[period][margin];
            const monthlyRate = tcv / months;

            text += `  ${margin}% margin:  €${monthlyRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}/lună\n`;
        });

        text += '\n';
    });

    text += `${'='.repeat(50)}\n`;

    // Add warnings based on COST thresholds (not price)
    // Check all periods to determine highest cost
    let maxCost = otpCost; // OTP cost
    [6, 12, 24, 36].forEach(months => {
        const cost = totalOtcCost + (months * totalRecurrentCost);
        if (cost > maxCost) maxCost = cost;
    });

    if (maxCost > 150000) {
        text += '\n⚠️  ATENTIE:\n';
        text += 'Valoarea totala de cost necesita implicare Finance\n';
        text += 'Valoarea totala de cost necesita implicare SCM\n\n';
    } else if (maxCost > 50000) {
        text += '\n⚠️  ATENTIE:\n';
        text += 'Valoarea totala de cost necesita implicare Finance\n\n';
    }

    text += `Generated by CRM Pricing Calculator\n`;

    $('#emailFormatText').val(text);
}

/**
 * Copy email format to clipboard
 */
function copyEmailFormat() {
    const textarea = document.getElementById('emailFormatText');
    textarea.select();
    document.execCommand('copy');
    showToast('Success', 'Pricing summary copied to clipboard', 'success');
}

/**
 * Download email format as text file
 */
function downloadEmailFormat() {
    const text = $('#emailFormatText').val();
    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');

    const date = new Date().toISOString().split('T')[0];
    a.href = url;
    a.download = `pricing_summary_${date}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);

    showToast('Success', 'File downloaded', 'success');
}