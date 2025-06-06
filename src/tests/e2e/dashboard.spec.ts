// 🎭 MesChain-Sync Enterprise - Dashboard E2E Tests
// End-to-end testing for Dashboard user flows

import { test, expect, Page } from '@playwright/test';

// ====================================
// 🎯 PAGE OBJECT MODEL
// ====================================

class DashboardPage {
  constructor(private page: Page) {}

  // Navigation
  async goto() {
    await this.page.goto('/dashboard');
  }

  // Locators
  get loadingSpinner() {
    return this.page.getByTestId('dashboard-loading');
  }

  get metricsOverview() {
    return this.page.getByTestId('metrics-overview');
  }

  get revenueMetric() {
    return this.page.getByTestId('total-revenue-metric');
  }

  get ordersMetric() {
    return this.page.getByTestId('total-orders-metric');
  }

  get revenueChart() {
    return this.page.getByTestId('revenue-chart');
  }

  get refreshButton() {
    return this.page.getByTestId('refresh-dashboard');
  }

  get dateRangePicker() {
    return this.page.getByTestId('date-range-picker');
  }

  get marketplaceFilter() {
    return this.page.getByTestId('marketplace-filter');
  }

  get themeToggle() {
    return this.page.getByTestId('theme-toggle');
  }

  // Actions
  async waitForLoad() {
    await expect(this.loadingSpinner).toBeVisible();
    await expect(this.loadingSpinner).toBeHidden();
    await expect(this.metricsOverview).toBeVisible();
  }

  async refreshDashboard() {
    await this.refreshButton.click();
    await this.waitForLoad();
  }

  async selectDateRange(range: string) {
    await this.dateRangePicker.click();
    await this.page.getByText(range).click();
  }

  async selectMarketplace(marketplace: string) {
    await this.marketplaceFilter.click();
    await this.page.getByText(marketplace).click();
  }

  async toggleTheme() {
    await this.themeToggle.click();
  }

  // Assertions
  async expectMetricValue(metric: string, value: string) {
    await expect(this.page.getByTestId(`${metric}-value`)).toContainText(value);
  }

  async expectChartVisible() {
    await expect(this.revenueChart).toBeVisible();
  }
}

// ====================================
// 🧪 TEST SUITE
// ====================================

test.describe('Dashboard E2E Tests', () => {
  let dashboardPage: DashboardPage;

  test.beforeEach(async ({ page }) => {
    dashboardPage = new DashboardPage(page);
    
    // Mock API responses for consistent testing
    await page.route('/api/analytics/dashboard', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          data: {
            metrics: {
              totalRevenue: 125450.00,
              totalOrders: 1234,
              averageOrderValue: 156.75,
              conversionRate: 3.45,
            },
            trends: {
              revenue: Array.from({ length: 30 }, (_, i) => ({
                date: new Date(Date.now() - (29 - i) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                value: 1000 + Math.random() * 2000,
              })),
            },
          },
        }),
      });
    });
  });

  // ====================================
  // 🚀 BASIC FUNCTIONALITY
  // ====================================

  test('should load dashboard successfully', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Verify main sections are visible
    await expect(dashboardPage.metricsOverview).toBeVisible();
    await expect(dashboardPage.revenueChart).toBeVisible();
    
    // Check that metrics have loaded
    await dashboardPage.expectMetricValue('total-revenue', '₺125,450');
    await dashboardPage.expectMetricValue('total-orders', '1,234');
  });

  test('should display correct page title', async ({ page }) => {
    await dashboardPage.goto();
    await expect(page).toHaveTitle(/Dashboard.*MesChain-Sync/);
  });

  test('should have responsive navigation', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Check navigation elements
    await expect(page.getByTestId('main-navigation')).toBeVisible();
    await expect(page.getByTestId('user-menu')).toBeVisible();
  });

  // ====================================
  // 📊 CHART INTERACTIONS
  // ====================================

  test('should interact with revenue chart', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Chart should be visible
    await dashboardPage.expectChartVisible();

    // Hover over chart to show tooltip
    await dashboardPage.revenueChart.hover();
    await expect(page.getByTestId('chart-tooltip')).toBeVisible();

    // Test chart period selection
    await page.getByTestId('chart-period-selector').click();
    await page.getByText('Last 7 days').click();
    
    // Chart should update
    await expect(dashboardPage.revenueChart).toHaveAttribute('data-period', 'week');
  });

  test('should handle chart zoom functionality', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Double-click to zoom
    await dashboardPage.revenueChart.dblclick();
    await expect(page.getByTestId('chart-zoom-controls')).toBeVisible();

    // Reset zoom
    await page.getByTestId('reset-zoom-button').click();
    await expect(page.getByTestId('chart-zoom-controls')).toBeHidden();
  });

  // ====================================
  // 🔍 FILTERING & SEARCH
  // ====================================

  test('should filter by date range', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Select different date range
    await dashboardPage.selectDateRange('Last 30 days');
    
    // Wait for data to update
    await page.waitForResponse('/api/analytics/dashboard?period=month');
    
    // Verify URL contains filter parameter
    await expect(page).toHaveURL(/period=month/);
  });

  test('should filter by marketplace', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Select Trendyol marketplace
    await dashboardPage.selectMarketplace('Trendyol');
    
    // Wait for filtered data
    await page.waitForResponse(/marketplace=trendyol/);
    
    // Verify filter is applied
    await expect(page.getByTestId('active-filters')).toContainText('Trendyol');
  });

  test('should clear all filters', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Apply some filters
    await dashboardPage.selectDateRange('Last 7 days');
    await dashboardPage.selectMarketplace('Trendyol');

    // Clear all filters
    await page.getByTestId('clear-filters-button').click();
    
    // Verify filters are cleared
    await expect(page.getByTestId('active-filters')).toBeEmpty();
    await expect(page).toHaveURL('/dashboard');
  });

  // ====================================
  // 🎯 METRIC INTERACTIONS
  // ====================================

  test('should drill down into revenue metrics', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Click on revenue metric
    await dashboardPage.revenueMetric.click();
    
    // Should open detailed modal
    await expect(page.getByTestId('revenue-details-modal')).toBeVisible();
    
    // Modal should contain detailed breakdown
    await expect(page.getByTestId('revenue-breakdown-chart')).toBeVisible();
    
    // Close modal
    await page.getByTestId('close-modal-button').click();
    await expect(page.getByTestId('revenue-details-modal')).toBeHidden();
  });

  test('should compare metrics across time periods', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Enable comparison mode
    await page.getByTestId('comparison-toggle').click();
    await expect(page.getByTestId('comparison-selector')).toBeVisible();

    // Select comparison period
    await page.getByTestId('comparison-selector').click();
    await page.getByText('Previous month').click();

    // Verify comparison data is shown
    await expect(page.getByTestId('comparison-indicators')).toBeVisible();
    await expect(page.getByTestId('change-percentage')).toBeVisible();
  });

  // ====================================
  // 📱 RESPONSIVE DESIGN
  // ====================================

  test('should work on mobile viewport', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Check mobile-specific layout
    await expect(page.getByTestId('mobile-metrics-carousel')).toBeVisible();
    await expect(page.getByTestId('mobile-menu-button')).toBeVisible();

    // Test mobile navigation
    await page.getByTestId('mobile-menu-button').click();
    await expect(page.getByTestId('mobile-navigation')).toBeVisible();
  });

  test('should adapt charts for small screens', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Charts should be stacked vertically
    const chartContainer = page.getByTestId('charts-container');
    await expect(chartContainer).toHaveClass(/flex-col/);
  });

  // ====================================
  // 🎨 THEME FUNCTIONALITY
  // ====================================

  test('should toggle between light and dark themes', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Initially light theme
    await expect(page.locator('html')).toHaveClass(/theme-light/);

    // Toggle to dark theme
    await dashboardPage.toggleTheme();
    await expect(page.locator('html')).toHaveClass(/theme-dark/);

    // Toggle back to light
    await dashboardPage.toggleTheme();
    await expect(page.locator('html')).toHaveClass(/theme-light/);
  });

  test('should persist theme preference', async ({ page, context }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Switch to dark theme
    await dashboardPage.toggleTheme();
    await expect(page.locator('html')).toHaveClass(/theme-dark/);

    // Create new page in same context
    const newPage = await context.newPage();
    const newDashboard = new DashboardPage(newPage);
    await newDashboard.goto();
    
    // Theme should be persisted
    await expect(newPage.locator('html')).toHaveClass(/theme-dark/);
  });

  // ====================================
  // ⚡ REAL-TIME UPDATES
  // ====================================

  test('should handle real-time metric updates', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Mock WebSocket connection
    await page.evaluate(() => {
      // Simulate real-time update
      window.dispatchEvent(new CustomEvent('metrics-update', {
        detail: {
          totalRevenue: 135600.00,
          totalOrders: 1456,
        }
      }));
    });

    // Values should update
    await dashboardPage.expectMetricValue('total-revenue', '₺135,600');
    await dashboardPage.expectMetricValue('total-orders', '1,456');
  });

  // ====================================
  // 🔄 REFRESH FUNCTIONALITY
  // ====================================

  test('should refresh dashboard data', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Mock updated data for refresh
    await page.route('/api/analytics/dashboard', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          data: {
            metrics: {
              totalRevenue: 145600.00,
              totalOrders: 1456,
              averageOrderValue: 168.25,
              conversionRate: 3.87,
            },
          },
        }),
      });
    });

    // Trigger refresh
    await dashboardPage.refreshDashboard();

    // Verify updated values
    await dashboardPage.expectMetricValue('total-revenue', '₺145,600');
    await dashboardPage.expectMetricValue('total-orders', '1,456');
  });

  // ====================================
  // ⚠️ ERROR HANDLING
  // ====================================

  test('should handle API errors gracefully', async ({ page }) => {
    // Mock API error
    await page.route('/api/analytics/dashboard', async (route) => {
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({
          success: false,
          error: { message: 'Internal server error' },
        }),
      });
    });

    await dashboardPage.goto();

    // Should show error state
    await expect(page.getByTestId('dashboard-error')).toBeVisible();
    await expect(page.getByText(/failed to load/i)).toBeVisible();
    
    // Should have retry button
    await expect(page.getByTestId('retry-button')).toBeVisible();
  });

  test('should retry failed requests', async ({ page }) => {
    let requestCount = 0;
    
    await page.route('/api/analytics/dashboard', async (route) => {
      requestCount++;
      
      if (requestCount === 1) {
        // First request fails
        await route.fulfill({
          status: 500,
          body: JSON.stringify({ success: false, error: { message: 'Server error' } }),
        });
      } else {
        // Second request succeeds
        await route.fulfill({
          status: 200,
          body: JSON.stringify({
            success: true,
            data: { metrics: { totalRevenue: 125450.00 } },
          }),
        });
      }
    });

    await dashboardPage.goto();

    // Should show error initially
    await expect(page.getByTestId('dashboard-error')).toBeVisible();
    
    // Click retry
    await page.getByTestId('retry-button').click();
    
    // Should load successfully
    await dashboardPage.waitForLoad();
    await expect(page.getByTestId('dashboard-error')).toBeHidden();
  });

  // ====================================
  // ♿ ACCESSIBILITY
  // ====================================

  test('should be keyboard navigable', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Tab through interactive elements
    await page.keyboard.press('Tab');
    await expect(page.getByTestId('refresh-dashboard')).toBeFocused();

    await page.keyboard.press('Tab');
    await expect(page.getByTestId('date-range-picker')).toBeFocused();

    await page.keyboard.press('Tab');
    await expect(page.getByTestId('marketplace-filter')).toBeFocused();
  });

  test('should have proper ARIA labels', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Check ARIA labels
    await expect(dashboardPage.revenueMetric).toHaveAttribute('aria-label');
    await expect(dashboardPage.ordersMetric).toHaveAttribute('aria-label');
    await expect(dashboardPage.revenueChart).toHaveAttribute('aria-label');
  });

  // ====================================
  // 📊 PERFORMANCE
  // ====================================

  test('should load within performance budget', async ({ page }) => {
    const startTime = Date.now();
    
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();
    
    const loadTime = Date.now() - startTime;
    
    // Should load within 3 seconds
    expect(loadTime).toBeLessThan(3000);
  });

  test('should not have memory leaks', async ({ page }) => {
    await dashboardPage.goto();
    await dashboardPage.waitForLoad();

    // Force garbage collection if available
    await page.evaluate(() => {
      if (window.gc) {
        window.gc();
      }
    });

    // Monitor memory usage during interaction
    const initialHeap = await page.evaluate(() => 
      (performance as any).memory?.usedJSHeapSize || 0
    );

    // Perform multiple interactions
    for (let i = 0; i < 10; i++) {
      await dashboardPage.refreshDashboard();
      await page.waitForTimeout(100);
    }

    const finalHeap = await page.evaluate(() => 
      (performance as any).memory?.usedJSHeapSize || 0
    );

    // Memory growth should be reasonable (< 10MB)
    const memoryGrowth = finalHeap - initialHeap;
    expect(memoryGrowth).toBeLessThan(10 * 1024 * 1024);
  });
}); 