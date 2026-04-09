<?php
$filterConfig = $filterConfig ?? [];

$showTeam         = $filterConfig['showTeam'] ?? true;
$showAgent        = $filterConfig['showAgent'] ?? true;
$showMonth        = $filterConfig['showMonth'] ?? true;
$showQuarter      = $filterConfig['showQuarter'] ?? true;
$showFiscalPeriod = $filterConfig['showFiscalPeriod'] ?? false;
$showYear         = $filterConfig['showYear'] ?? true;
$showRealFlag     = $filterConfig['showRealFlag'] ?? false;

$teams         = $filterConfig['teams'] ?? [];
$agents        = $filterConfig['agents'] ?? [];
$fiscalPeriods = $filterConfig['fiscalPeriods'] ?? [];
$selected      = $filterConfig['selected'] ?? [];

function sfdcFilterSelected($key, $selected, $default = '')
{
    return isset($selected[$key]) ? (string)$selected[$key] : (string)$default;
}
?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <form id="sfdcFiltersForm" class="row g-2 align-items-end">


            <div class="col-md-1 filter-divider" id="globalSearchWrap">
                <label for="globalSearch" class="form-label fw-normal mb-1">Search</label>
                <input type="text" id="globalSearch" class="form-control form-control-sm" placeholder="Search...">
            </div>

            <div class="col-md-1 filter-divider">
                <div class="mb-0">
                    <label for="wonGroupingMode" class="form-label fw-normal mb-1">Row grouping</label>
                    <select id="wonGroupingMode" class="form-select form-select-sm">
                        <option value="none" selected>None</option>
                        <option value="month">Month</option>
                        <option value="team">Team</option>
                        <option value="month_team">Month + Team</option>
                    </select>
                </div>
            </div>

            <div class="col-md-1 filter-divider">
                <div class=" mb-0">
                    <label for="wonTypeFilter" class="form-label fw-normal mb-1">Type</label>
                    <select id="wonTypeFilter" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="Fixed">Fixed</option>
                        <option value="ICT">ICT</option>
                        <option value="Other">Other</option>
                        <option value="__EMPTY__">Empty</option>
                    </select>
                </div>
            </div>


            <?php if ($showTeam): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_team" class="form-label mb-1">Team</label>
                    <select id="filter_team" name="team" class="form-select form-select-sm">
                        <option value="">All Teams</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= htmlspecialchars($team) ?>"
                                <?= sfdcFilterSelected('team', $selected) === (string)$team ? 'selected' : '' ?>>
                                <?= htmlspecialchars($team) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showAgent): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_agent" class="form-label mb-1">Agent</label>
                    <select id="filter_agent" name="agent" class="form-select form-select-sm">
                        <option value="">All Agents</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= htmlspecialchars($agent) ?>"
                                <?= sfdcFilterSelected('agent', $selected) === (string)$agent ? 'selected' : '' ?>>
                                <?= htmlspecialchars($agent) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showMonth): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_month" class="form-label mb-1">Month</label>
                    <select id="filter_month" name="month" class="form-select form-select-sm">
                        <option value="">All Months</option>
                        <?php
                        $months = [
                            1 => 'January',
                            2 => 'February',
                            3 => 'March',
                            4 => 'April',
                            5 => 'May',
                            6 => 'June',
                            7 => 'July',
                            8 => 'August',
                            9 => 'September',
                            10 => 'October',
                            11 => 'November',
                            12 => 'December'
                        ];
                        foreach ($months as $value => $label):
                        ?>
                            <option value="<?= $value ?>"
                                <?= sfdcFilterSelected('month', $selected) === (string)$value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showQuarter): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_quarter" class="form-label mb-1">Quarter</label>
                    <select id="filter_quarter" name="quarter" class="form-select form-select-sm">
                        <option value="">All Quarters</option>
                        <?php for ($q = 1; $q <= 4; $q++): ?>
                            <option value="<?= $q ?>"
                                <?= sfdcFilterSelected('quarter', $selected) === (string)$q ? 'selected' : '' ?>>
                                Q<?= $q ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showFiscalPeriod): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_fiscal_period" class="form-label mb-1">Fiscal Period</label>
                    <select id="filter_fiscal_period" name="fiscal_period" class="form-select form-select-sm">
                        <option value="">All Periods</option>
                        <?php foreach ($fiscalPeriods as $period): ?>
                            <option value="<?= htmlspecialchars($period) ?>"
                                <?= sfdcFilterSelected('fiscal_period', $selected) === (string)$period ? 'selected' : '' ?>>
                                <?= htmlspecialchars($period) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showYear): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_year" class="form-label mb-1">Year</label>
                    <input
                        type="number"
                        min="2020"
                        max="2099"
                        step="1"
                        id="filter_year"
                        name="year"
                        class="form-control form-control-sm"
                        value="<?= htmlspecialchars(sfdcFilterSelected('year', $selected)) ?>"
                        placeholder="2026">
                </div>
            <?php endif; ?>

            <?php if ($showRealFlag): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_real_flag" class="form-label mb-1">Real</label>
                    <select id="filter_real_flag" name="real_flag" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" <?= sfdcFilterSelected('real_flag', $selected) === '1' ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= sfdcFilterSelected('real_flag', $selected) === '0' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-auto col-sm-12">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    Apply
                </button>
                <button type="button" id="resetSfdcFilters" class="btn btn-outline-secondary btn-sm">
                    Reset
                </button>
            </div>


            <div class="col-md-1 col-sm-6 filter-divider-left">
                <label class="form-label fw-normal mb-1 d-block">Export</label>
                <button type="button" id="exportCsvBtn" class="btn btn-sm btn-outline-secondary w-100">
                    CSV
                </button>
            </div>

        </form>
    </div>
    <!-- </div>-->

    <script>
        (function() {
            const form = document.getElementById('sfdcFiltersForm');
            const resetButton = document.getElementById('resetSfdcFilters');

            if (!form) {
                return;
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const params = new URLSearchParams(new FormData(form));
                const cleaned = new URLSearchParams();

                for (const [key, value] of params.entries()) {
                    if (value !== '') {
                        cleaned.append(key, value);
                    }
                }

                const event = new CustomEvent('sfdcFiltersChanged', {
                    detail: {
                        queryString: cleaned.toString(),
                        params: Object.fromEntries(cleaned.entries())
                    }
                });

                document.dispatchEvent(event);
            });

            if (resetButton) {
                resetButton.addEventListener('click', function() {
                    form.reset();

                    const event = new CustomEvent('sfdcFiltersChanged', {
                        detail: {
                            queryString: '',
                            params: {}
                        }
                    });

                    document.dispatchEvent(event);
                });
            }
        })();
    </script>