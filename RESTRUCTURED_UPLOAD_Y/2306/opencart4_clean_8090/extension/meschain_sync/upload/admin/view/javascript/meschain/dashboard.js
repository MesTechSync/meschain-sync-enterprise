/**
 * MesChain Dashboard JavaScript
 * A+++++ Level functionality
 */

(function ($) {
    'use strict';

    // Dashboard namespace
    window.MesChainDashboard = {
        // Configuration
        config: {
            refreshInterval: 30000, // 30 seconds
            apiEndpoint: 'index.php?route=extension/module/meschain_sync/',
            userToken: '',
            currentMarketplace: 'all'
        },

        // Initialize dashboard
        init: function (userToken) {
            this.config.userToken = userToken;
            this.bindEvents();
            this.loadDashboardData();
            this.startAutoRefresh();
            this.initCharts();
        },

        // Bind UI events
        bindEvents: function () {
            // Marketplace filter
            $('#marketplace-filter').on('change', function () {
                MesChainDashboard.config.currentMarketplace = $(this).val();
                MesChainDashboard.loadDashboardData();
            });

            // Sync buttons
            $('.btn-sync-marketplace').on('click', function () {
                var marketplace = $(this).data('marketplace');
                MesChainDashboard.syncMarketplace(marketplace);
            });

            // Refresh button
            $('#btn-refresh-dashboard').on('click', function () {
                MesChainDashboard.loadDashboardData();
            });
        },

        // Load dashboard data
        loadDashboardData: function () {
            $.ajax({
                url: this.config.apiEndpoint + 'getDashboardData&user_token=' + this.config.userToken,
                type: 'GET',
                data: {
                    marketplace: this.config.currentMarketplace
                },
                dataType: 'json',
                beforeSend: function () {
                    $('#dashboard-loading').show();
                },
                success: function (response) {
                    if (response.success) {
                        MesChainDashboard.updateDashboard(response.data);
                    } else {
                        MesChainDashboard.showError(response.error);
                    }
                },
                error: function () {
                    MesChainDashboard.showError('Failed to load dashboard data');
                },
                complete: function () {
                    $('#dashboard-loading').hide();
                }
            });
        },

        // Update dashboard UI
        updateDashboard: function (data) {
            // Update statistics
            $('#total-products').text(data.statistics.total_products);
            $('#synced-products').text(data.statistics.synced_products);
            $('#active-marketplaces').text(data.statistics.active_marketplaces);
            $('#sync-rate').text(data.statistics.sync_rate + '%');

            // Update marketplace cards
            if (data.marketplaces) {
                $.each(data.marketplaces, function (marketplace, stats) {
                    var card = $('#card-' + marketplace);
                    if (card.length) {
                        card.find('.products-count').text(stats.products);
                        card.find('.orders-count').text(stats.orders);
                        card.find('.sync-status').text(stats.status);
                        card.find('.last-sync').text(stats.last_sync);
                    }
                });
            }

            // Update charts
            if (data.charts) {
                this.updateCharts(data.charts);
            }

            // Update activity log
            if (data.activities) {
                this.updateActivityLog(data.activities);
            }
        },

        // Initialize charts
        initCharts: function () {
            // Sales chart
            var salesCtx = document.getElementById('sales-chart');
            if (salesCtx) {
                this.salesChart = new Chart(salesCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Sales',
                            data: [],
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Marketplace distribution chart
            var marketplaceCtx = document.getElementById('marketplace-chart');
            if (marketplaceCtx) {
                this.marketplaceChart = new Chart(marketplaceCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                '#FF6384',
                                '#36A2EB',
                                '#FFCE56',
                                '#4BC0C0',
                                '#9966FF',
                                '#FF9F40'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        },

        // Update charts
        updateCharts: function (chartData) {
            // Update sales chart
            if (this.salesChart && chartData.sales) {
                this.salesChart.data.labels = chartData.sales.labels;
                this.salesChart.data.datasets[0].data = chartData.sales.data;
                this.salesChart.update();
            }

            // Update marketplace chart
            if (this.marketplaceChart && chartData.marketplace) {
                this.marketplaceChart.data.labels = chartData.marketplace.labels;
                this.marketplaceChart.data.datasets[0].data = chartData.marketplace.data;
                this.marketplaceChart.update();
            }
        },

        // Update activity log
        updateActivityLog: function (activities) {
            var logContainer = $('#activity-log');
            logContainer.empty();

            $.each(activities, function (index, activity) {
                var logItem = $('<div class="activity-item">');
                logItem.append('<span class="activity-time">' + activity.time + '</span>');
                logItem.append('<span class="activity-type ' + activity.type + '">' + activity.type + '</span>');
                logItem.append('<span class="activity-message">' + activity.message + '</span>');
                logContainer.append(logItem);
            });
        },

        // Sync marketplace
        syncMarketplace: function (marketplace) {
            $.ajax({
                url: this.config.apiEndpoint + 'syncMarketplace&user_token=' + this.config.userToken,
                type: 'POST',
                data: {
                    marketplace: marketplace
                },
                dataType: 'json',
                beforeSend: function () {
                    $('#sync-' + marketplace).prop('disabled', true).text('Syncing...');
                },
                success: function (response) {
                    if (response.success) {
                        MesChainDashboard.showSuccess('Sync completed successfully');
                        MesChainDashboard.loadDashboardData();
                    } else {
                        MesChainDashboard.showError(response.error);
                    }
                },
                error: function () {
                    MesChainDashboard.showError('Sync failed');
                },
                complete: function () {
                    $('#sync-' + marketplace).prop('disabled', false).text('Sync');
                }
            });
        },

        // Auto refresh
        startAutoRefresh: function () {
            setInterval(function () {
                MesChainDashboard.loadDashboardData();
            }, this.config.refreshInterval);
        },

        // Show success message
        showSuccess: function (message) {
            $('#alert-container').html(
                '<div class="alert alert-success alert-dismissible">' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                '<i class="fa fa-check-circle"></i> ' + message +
                '</div>'
            );
        },

        // Show error message
        showError: function (message) {
            $('#alert-container').html(
                '<div class="alert alert-danger alert-dismissible">' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                '<i class="fa fa-exclamation-circle"></i> ' + message +
                '</div>'
            );
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        if (typeof userToken !== 'undefined') {
            MesChainDashboard.init(userToken);
        }
    });

})(jQuery);
