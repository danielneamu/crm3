<?php
$tabConfig = $tabConfig ?? [];

$activeTab = $tabConfig['activeTab'] ?? 'table';
$tableId = $tabConfig['tableId'] ?? 'pipeline-table-tab';
$dashboardId = $tabConfig['dashboardId'] ?? 'pipeline-dashboard-tab';
?>

<ul class="nav nav-tabs mb-3" id="pipelineViewTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button
            class="nav-link <?= $activeTab === 'table' ? 'active' : '' ?>"
            id="<?= htmlspecialchars($tableId) ?>-button"
            data-bs-toggle="tab"
            data-bs-target="#<?= htmlspecialchars($tableId) ?>"
            type="button"
            role="tab"
            aria-controls="<?= htmlspecialchars($tableId) ?>"
            aria-selected="<?= $activeTab === 'table' ? 'true' : 'false' ?>">
            Table
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button
            class="nav-link <?= $activeTab === 'dashboard' ? 'active' : '' ?>"
            id="<?= htmlspecialchars($dashboardId) ?>-button"
            data-bs-toggle="tab"
            data-bs-target="#<?= htmlspecialchars($dashboardId) ?>"
            type="button"
            role="tab"
            aria-controls="<?= htmlspecialchars($dashboardId) ?>"
            aria-selected="<?= $activeTab === 'dashboard' ? 'true' : 'false' ?>">
            Dashboard
        </button>
    </li>
</ul>

<script>
    (function() {
        const tabs = document.querySelectorAll('#pipelineViewTabs button[data-bs-toggle="tab"]');

        tabs.forEach(function(tabButton) {
            tabButton.addEventListener('shown.bs.tab', function(event) {
                const targetSelector = event.target.getAttribute('data-bs-target') || '';
                const tabName = targetSelector.includes('dashboard') ? 'dashboard' : 'table';

                document.dispatchEvent(new CustomEvent('sfdcTabChanged', {
                    detail: {
                        tab: tabName,
                        target: targetSelector
                    }
                }));
            });
        });
    })();
</script>