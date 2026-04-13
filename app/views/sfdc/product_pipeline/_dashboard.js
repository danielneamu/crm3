/**
 * Product Pipeline Dashboard JavaScript
 * Handles: data loading, KPI population, state management
 */

(function (window, document, $) {
    'use strict';

    const Dashboard = {
        config: {
            endpoint: '../api/sfdc_product_pipeline.php?action=get_dashboard_data',
            currentData: null,
            initialized: false,
            isLoading: false
        },

        init: function () {
            if (this.config.initialized) {
                return;
            }

            const dashboardTab = document.getElementById('product-pipeline-dashboard-tab');
            if (!dashboardTab) {
                console.warn('Dashboard tab element not found');
                return;
            }

            this.setupEventHandlers();
            this.config.initialized = true;

            console.log('Dashboard initialized successfully');
        },

        setupEventHandlers: function () {
            const self = this;

            // Fiscal year selector
            const yearDropdown = document.getElementById('dashboardFiscalYearProduct');
            if (yearDropdown) {
                $(yearDropdown).on('change', function () {
                    self.fetchData(this.value);
                });
            }

            // Refresh button
            const refreshBtn = document.getElementById('dashboardRefreshProduct');
            if (refreshBtn) {
                $(refreshBtn).on('click', function () {
                    const fy = yearDropdown ? yearDropdown.value : self.getCurrentFiscalYear();
                    self.fetchData(fy);
                });
            }

            // Listen for tab changes
            document.addEventListener('sfdcTabChanged', function (event) {
                if (!event.detail || event.detail.tab !== 'dashboard') {
                    return;
                }

                const fy = yearDropdown ? yearDropdown.value : self.getCurrentFiscalYear();

                // Only fetch if not already loaded
                if (!self.config.currentData) {
                    self.fetchData(fy);
                }
            });

            // Auto-load if dashboard is visible on page load
            const dashboardTab = document.getElementById('product-pipeline-dashboard-tab');
            if (dashboardTab && dashboardTab.classList.contains('active')) {
                const fy = yearDropdown ? yearDropdown.value : this.getCurrentFiscalYear();
                this.fetchData(fy);
            }
        },

        fetchData: function (fiscalYear) {
            if (this.config.isLoading) {
                console.warn('Dashboard data load already in progress');
                return;
            }

            this.config.isLoading = true;
            this.showState('loading');

            const url = this.config.endpoint + '&fiscal_year=' + encodeURIComponent(fiscalYear);
            const self = this;

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                timeout: 10000,
                success: function (result) {
                    if (result && result.success && result.data) {
                        self.config.currentData = result.data;
                        self.updateKpiDisplays(result.data);
                        self.showState('content');
                        console.log('Dashboard data loaded successfully');
                    } else {
                        self.showState('error', 'Invalid response format');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    let errorMsg = 'Failed to load dashboard data';

                    if (textStatus === 'timeout') {
                        errorMsg = 'Request timeout - server took too long to respond';
                    } else if (jqXHR.status === 404) {
                        errorMsg = 'API endpoint not found (404)';
                    } else if (jqXHR.status === 403) {
                        errorMsg = 'Access denied (403) - check permissions';
                    } else if (jqXHR.status === 500) {
                        errorMsg = 'Server error (500) - check server logs';
                    } else if (textStatus === 'error') {
                        errorMsg = 'Network error: ' + errorThrown;
                    }

                    console.error('Dashboard fetch error:', errorMsg, jqXHR);
                    self.showState('error', errorMsg);
                },
                complete: function () {
                    self.config.isLoading = false;
                }
            });
        },

        updateKpiDisplays: function (data) {
            if (!data || !data.data) {
                console.warn('No aggregated data available for KPI display');
                return;
            }

            const allKpi = (data.data.All && data.data.All.kpi) ? data.data.All.kpi : {};

            // All products metrics
            this.setText('kpiAllArrovTotal',
                allKpi.total_arrov ? '€' + this.formatCurrency(allKpi.total_arrov) : '—'
            );

            this.setText('kpiAllArrovAvg',
                allKpi.avg_arrov ? '€' + this.formatCurrency(allKpi.avg_arrov) : '—'
            );

            this.setText('kpiAllArrovDeals',
                allKpi.deal_count || '—'
            );

            // Family and team counts
            const families = (data.families || []).length;
            const teams = (data.teams || []).length;

            this.setText('kpiProductFamilyCount', families || '—');
            this.setText('kpiTeamCount', teams || '—');

            // Totals (same for all breakdowns in wireframe)
            const totalDisplay = allKpi.total_arrov ? '€' + this.formatCurrency(allKpi.total_arrov) : '—';

            this.setText('kpiProductFamilyTotal', totalDisplay);
            this.setText('kpiTeamTotal', totalDisplay);
            this.setText('kpiStageTotal', totalDisplay);

            // Count unique stages if available
            if (data.data && Object.keys(data.data).length > 0) {
                // For now, we don't have stage count in aggregation
                // This would need to be calculated from the raw data
                this.setText('kpiStageCount', '—');
            }

            console.log('KPI displays updated successfully');
        },

        setText: function (elementId, text) {
            const el = document.getElementById(elementId);
            if (el) {
                el.textContent = text;
            }
        },

        showState: function (state, errorMessage) {
            const loadingEl = document.getElementById('dashboardLoadingProduct');
            const contentEl = document.getElementById('dashboardContentProduct');
            const errorEl = document.getElementById('dashboardErrorProduct');
            const errorMsgEl = document.getElementById('dashboardErrorMessageProduct');

            if (loadingEl) {
                loadingEl.style.display = state === 'loading' ? 'block' : 'none';
            }

            if (contentEl) {
                contentEl.style.display = state === 'content' ? 'block' : 'none';
            }

            if (errorEl) {
                errorEl.style.display = state === 'error' ? 'block' : 'none';
            }

            if (state === 'error' && errorMsgEl) {
                errorMsgEl.textContent = errorMessage || 'Unknown error occurred';
            }
        },

        getCurrentFiscalYear: function () {
            const now = new Date();
            const month = now.getMonth() + 1;
            const year = now.getFullYear();
            return month >= 4 ? year + 1 : year;
        },

        formatCurrency: function (value) {
            if (!value) {
                return '0.00';
            }

            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }
    };

    // Initialize on DOM ready
    $(document).ready(function () {
        Dashboard.init();
    });

    // Expose to window for external access
    window.ProductPipelineDashboard = Dashboard;

})(window, document, jQuery);