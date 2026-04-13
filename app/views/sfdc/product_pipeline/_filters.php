<?php
$filterConfig = $filterConfig ?? [];

$showTeam         = $filterConfig['showTeam'] ?? true;
$showAgent        = $filterConfig['showAgent'] ?? true;
$showMonth        = $filterConfig['showMonth'] ?? true;
$showQuarter      = $filterConfig['showQuarter'] ?? false;  // REMOVED: Quarter is redundant with Fiscal Period
$showFiscalPeriod = $filterConfig['showFiscalPeriod'] ?? true;
$showYear         = $filterConfig['showYear'] ?? true;
$showProductFamily = $filterConfig['showProductFamily'] ?? true;
$showStage        = $filterConfig['showStage'] ?? true;

$teams         = $filterConfig['teams'] ?? [];
$agents        = $filterConfig['agents'] ?? [];
$fiscalPeriods = $filterConfig['fiscalPeriods'] ?? [];
$productFamilies = $filterConfig['productFamilies'] ?? [];
$stages        = $filterConfig['stages'] ?? [];
$selected      = $filterConfig['selected'] ?? [];

function productFilterSelected($key, $selected, $default = '')
{
    return isset($selected[$key]) ? (string)$selected[$key] : (string)$default;
}
?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <form id="productFiltersForm" class="row g-2 align-items-end">

            <div class="col-md-1 filter-divider" id="globalSearchWrap">
                <label for="globalSearchProduct" class="form-label fw-normal mb-1">Search</label>
                <input type="text" id="globalSearchProduct" class="form-control form-control-sm" placeholder="Search...">
            </div>

            <div class="col-md-1 filter-divider">
                <div class="mb-0">
                    <label for="productGroupingMode" class="form-label fw-normal mb-1">Row grouping</label>
                    <select id="productGroupingMode" class="form-select form-select-sm">
                        <option value="none" selected>None</option>
                        <option value="month">Month</option>
                        <option value="team">Team</option>
                        <option value="family">Product Family</option>
                        <option value="month_team">Month + Team</option>
                        <option value="month_family">Month + Family</option>
                    </select>
                </div>
            </div>

            <?php if ($showTeam): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_team_product" class="form-label mb-1">Team</label>
                    <select id="filter_team_product" name="team" class="form-select form-select-sm">
                        <option value="">All Teams</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= htmlspecialchars($team) ?>"
                                <?= productFilterSelected('team', $selected) === (string)$team ? 'selected' : '' ?>>
                                <?= htmlspecialchars($team) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showAgent): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_agent_product" class="form-label mb-1">Agent</label>
                    <select id="filter_agent_product" name="agent" class="form-select form-select-sm">
                        <option value="">All Agents</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= htmlspecialchars($agent) ?>"
                                <?= productFilterSelected('agent', $selected) === (string)$agent ? 'selected' : '' ?>>
                                <?= htmlspecialchars($agent) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showProductFamily): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_product_family" class="form-label mb-1">Product Family</label>
                    <select id="filter_product_family" name="product_family" class="form-select form-select-sm">
                        <option value="">All Families</option>
                        <?php foreach ($productFamilies as $family): ?>
                            <option value="<?= htmlspecialchars($family) ?>"
                                <?= productFilterSelected('product_family', $selected) === (string)$family ? 'selected' : '' ?>>
                                <?= htmlspecialchars($family) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showStage): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_stage_product" class="form-label mb-1">Stage</label>
                    <select id="filter_stage_product" name="stage" class="form-select form-select-sm">
                        <option value="">All Stages</option>
                        <?php foreach ($stages as $stage): ?>
                            <option value="<?= htmlspecialchars($stage) ?>"
                                <?= productFilterSelected('stage', $selected) === (string)$stage ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stage) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showMonth): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_month_product" class="form-label mb-1">Month</label>
                    <select id="filter_month_product" name="month" class="form-select form-select-sm">
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
                                <?= productFilterSelected('month', $selected) === (string)$value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showQuarter): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_quarter_product" class="form-label mb-1">Quarter</label>
                    <select id="filter_quarter_product" name="quarter" class="form-select form-select-sm">
                        <option value="">All Quarters</option>
                        <?php for ($q = 1; $q <= 4; $q++): ?>
                            <option value="<?= $q ?>"
                                <?= productFilterSelected('quarter', $selected) === (string)$q ? 'selected' : '' ?>>
                                Q<?= $q ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showFiscalPeriod): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_fiscal_period_product" class="form-label mb-1">Fiscal Period</label>
                    <select id="filter_fiscal_period_product" name="fiscal_period" class="form-select form-select-sm">
                        <option value="">All Periods</option>
                        <?php foreach ($fiscalPeriods as $period): ?>
                            <option value="<?= htmlspecialchars($period) ?>"
                                <?= productFilterSelected('fiscal_period', $selected) === (string)$period ? 'selected' : '' ?>>
                                <?= htmlspecialchars($period) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($showYear): ?>
                <div class="col-md-1 col-sm-6">
                    <label for="filter_year_product" class="form-label mb-1">Year</label>
                    <input
                        type="number"
                        min="2020"
                        max="2099"
                        step="1"
                        id="filter_year_product"
                        name="year"
                        class="form-control form-control-sm"
                        value="<?= htmlspecialchars(productFilterSelected('year', $selected)) ?>"
                        placeholder="2026">
                </div>
            <?php endif; ?>

            <div class="col-md-auto col-sm-12">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    Apply
                </button>
                <button type="button" id="resetProductFilters" class="btn btn-outline-secondary btn-sm">
                    Reset
                </button>
            </div>

            <div class="col-md-1 col-sm-6 filter-divider-left">
                <label class="form-label fw-normal mb-1 d-block">Export</label>
                <button type="button" id="exportCsvBtnProduct" class="btn btn-sm btn-outline-secondary w-100">
                    CSV
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('productFiltersForm');
        const resetButton = document.getElementById('resetProductFilters');

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

            const event = new CustomEvent('productFiltersChanged', {
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

                const event = new CustomEvent('productFiltersChanged', {
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