// 🧪 MesChain-Sync Enterprise - Dashboard Integration Tests
// End-to-end integration testing for Dashboard functionality

import React from 'react';
import { screen, waitFor, fireEvent, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders, setupFetchMock, createMockApiResponse } from '../utils/testUtils';
import { createMockAnalyticsData, createMockUser, createMockProductList } from '../mocks/mockFactories';
import { Dashboard } from '../../pages/Dashboard/Dashboard';

// ====================================
// 🎭 MOCK DATA SETUP
// ====================================

const mockAnalytics = createMockAnalyticsData({
  metrics: {
    totalRevenue: 125450.00,
    totalOrders: 1234,
    averageOrderValue: 156.75,
    conversionRate: 3.45,
    customerRetention: 87.2,
    productsSold: 5678,
  },
});

const mockUser = createMockUser({
  role: 'admin',
  permissions: ['analytics.read', 'products.read', 'orders.read'],
});

const mockProducts = createMockProductList(5);

// ====================================
// 📡 API MOCK SETUP
// ====================================

const setupApiMocks = () => {
  setupFetchMock({
    '/api/analytics/dashboard': createMockApiResponse(mockAnalytics),
    '/api/auth/me': createMockApiResponse(mockUser),
    '/api/products': createMockApiResponse({
      items: mockProducts,
      pagination: { page: 1, pageSize: 10, total: 5, totalPages: 1 },
    }),
    '/api/orders/recent': createMockApiResponse([]),
    '/api/notifications/unread': createMockApiResponse([]),
  });
};

// ====================================
// 🧪 INTEGRATION TESTS
// ====================================

describe('Dashboard Integration', () => {
  const user = userEvent.setup();
  
  beforeEach(() => {
    setupApiMocks();
    jest.clearAllMocks();
  });

  // ====================================
  // 🚀 INITIAL LOAD TESTS
  // ====================================

  it('loads dashboard with all components successfully', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    // Check loading state first
    expect(screen.getByTestId('dashboard-loading')).toBeInTheDocument();

    // Wait for content to load
    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toBeInTheDocument();
    });

    // Verify main sections are rendered
    expect(screen.getByTestId('metrics-overview')).toBeInTheDocument();
    expect(screen.getByTestId('charts-section')).toBeInTheDocument();
    expect(screen.getByTestId('recent-activities')).toBeInTheDocument();
  });

  it('displays correct metric values from API', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByText('₺125,450.00')).toBeInTheDocument();
      expect(screen.getByText('1,234')).toBeInTheDocument();
      expect(screen.getByText('₺156.75')).toBeInTheDocument();
      expect(screen.getByText('3.45%')).toBeInTheDocument();
    });
  });

  // ====================================
  // 🔄 REAL-TIME UPDATES
  // ====================================

  it('updates metrics when refreshed', async () => {
    const { store } = renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByText('₺125,450.00')).toBeInTheDocument();
    });

    // Setup new mock data for refresh
    const updatedAnalytics = createMockAnalyticsData({
      metrics: { ...mockAnalytics.metrics, totalRevenue: 145600.00 },
    });
    
    setupFetchMock({
      '/api/analytics/dashboard': createMockApiResponse(updatedAnalytics),
    });

    // Trigger refresh
    const refreshButton = screen.getByTestId('refresh-dashboard');
    await user.click(refreshButton);

    await waitFor(() => {
      expect(screen.getByText('₺145,600.00')).toBeInTheDocument();
    });
  });

  it('handles WebSocket real-time updates', async () => {
    const mockWebSocket = {
      send: jest.fn(),
      close: jest.fn(),
      addEventListener: jest.fn(),
      removeEventListener: jest.fn(),
    };

    // Mock WebSocket
    (global as any).WebSocket = jest.fn(() => mockWebSocket);

    renderWithProviders(<Dashboard realTimeUpdates />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toBeInTheDocument();
    });

    // Simulate WebSocket message
    const wsMessageEvent = new MessageEvent('message', {
      data: JSON.stringify({
        type: 'metrics_update',
        data: { totalRevenue: 155750.00 },
      }),
    });

    // Trigger WebSocket message handler
    mockWebSocket.addEventListener.mock.calls
      .find(([event]) => event === 'message')[1](wsMessageEvent);

    await waitFor(() => {
      expect(screen.getByText('₺155,750.00')).toBeInTheDocument();
    });
  });

  // ====================================
  // 📊 CHART INTERACTIONS
  // ====================================

  it('displays and interacts with revenue chart', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('revenue-chart')).toBeInTheDocument();
    });

    // Test chart period selection
    const periodSelector = screen.getByTestId('chart-period-selector');
    await user.click(within(periodSelector).getByText('Month'));

    await waitFor(() => {
      expect(screen.getByTestId('revenue-chart')).toHaveAttribute(
        'data-period',
        'month'
      );
    });
  });

  it('shows chart tooltips on hover', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('revenue-chart')).toBeInTheDocument();
    });

    // Simulate chart hover
    const chartElement = screen.getByTestId('revenue-chart');
    fireEvent.mouseMove(chartElement, { clientX: 100, clientY: 100 });

    await waitFor(() => {
      expect(screen.getByTestId('chart-tooltip')).toBeInTheDocument();
    });
  });

  // ====================================
  // 🔍 FILTER & SEARCH FUNCTIONALITY
  // ====================================

  it('filters dashboard data by date range', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toBeInTheDocument();
    });

    // Open date range picker
    const dateRangePicker = screen.getByTestId('date-range-picker');
    await user.click(dateRangePicker);

    // Select last 7 days
    const lastWeekOption = screen.getByText('Last 7 days');
    await user.click(lastWeekOption);

    // Verify API call with new date range
    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/analytics/dashboard?period=week'),
        expect.any(Object)
      );
    });
  });

  it('filters by marketplace selection', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('marketplace-filter')).toBeInTheDocument();
    });

    // Select Trendyol marketplace
    const marketplaceFilter = screen.getByTestId('marketplace-filter');
    await user.click(marketplaceFilter);
    
    const trendyolOption = screen.getByText('Trendyol');
    await user.click(trendyolOption);

    // Verify filtered data request
    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('marketplace=trendyol'),
        expect.any(Object)
      );
    });
  });

  // ====================================
  // 🎯 DRILL-DOWN FUNCTIONALITY
  // ====================================

  it('navigates to detailed view on metric click', async () => {
    const mockNavigate = jest.fn();
    
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('total-revenue-metric')).toBeInTheDocument();
    });

    // Click on revenue metric
    const revenueMetric = screen.getByTestId('total-revenue-metric');
    await user.click(revenueMetric);

    // Should show detailed modal or navigate
    await waitFor(() => {
      expect(screen.getByTestId('revenue-details-modal')).toBeInTheDocument();
    });
  });

  it('opens product details from top products list', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('top-products-list')).toBeInTheDocument();
    });

    // Click on first product
    const firstProduct = screen.getByTestId('product-item-0');
    await user.click(firstProduct);

    await waitFor(() => {
      expect(screen.getByTestId('product-details-drawer')).toBeInTheDocument();
    });
  });

  // ====================================
  // 📱 RESPONSIVE BEHAVIOR
  // ====================================

  it('adapts layout for mobile viewport', async () => {
    // Mock mobile viewport
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 375,
    });

    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toHaveClass('mobile-layout');
    });

    // Check mobile-specific components
    expect(screen.getByTestId('mobile-metrics-carousel')).toBeInTheDocument();
    expect(screen.queryByTestId('desktop-sidebar')).not.toBeInTheDocument();
  });

  it('collapses charts on small screens', async () => {
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 768,
    });

    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('charts-section')).toHaveClass('collapsed');
    });

    // Expand charts
    const expandButton = screen.getByTestId('expand-charts-button');
    await user.click(expandButton);

    await waitFor(() => {
      expect(screen.getByTestId('charts-section')).not.toHaveClass('collapsed');
    });
  });

  // ====================================
  // ⚠️ ERROR HANDLING
  // ====================================

  it('handles API errors gracefully', async () => {
    // Setup failing API mock
    setupFetchMock({
      '/api/analytics/dashboard': Promise.reject(new Error('Network error')),
    });

    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-error')).toBeInTheDocument();
      expect(screen.getByText(/failed to load dashboard/i)).toBeInTheDocument();
    });

    // Test retry functionality
    const retryButton = screen.getByTestId('retry-button');
    
    // Setup successful retry
    setupFetchMock({
      '/api/analytics/dashboard': createMockApiResponse(mockAnalytics),
    });

    await user.click(retryButton);

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toBeInTheDocument();
      expect(screen.queryByTestId('dashboard-error')).not.toBeInTheDocument();
    });
  });

  it('shows partial data when some APIs fail', async () => {
    setupFetchMock({
      '/api/analytics/dashboard': createMockApiResponse(mockAnalytics),
      '/api/products': Promise.reject(new Error('Products API failed')),
      '/api/orders/recent': createMockApiResponse([]),
    });

    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      // Main metrics should load
      expect(screen.getByText('₺125,450.00')).toBeInTheDocument();
      
      // Products section should show error
      expect(screen.getByTestId('products-section-error')).toBeInTheDocument();
    });
  });

  // ====================================
  // 🔐 PERMISSION HANDLING
  // ====================================

  it('hides restricted sections based on user permissions', async () => {
    const limitedUser = createMockUser({
      role: 'viewer',
      permissions: ['analytics.read'], // No products or orders access
    });

    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: limitedUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      // Should show analytics
      expect(screen.getByTestId('metrics-overview')).toBeInTheDocument();
      
      // Should hide restricted sections
      expect(screen.queryByTestId('products-section')).not.toBeInTheDocument();
      expect(screen.queryByTestId('orders-section')).not.toBeInTheDocument();
    });
  });

  // ====================================
  // 🎨 THEME & ACCESSIBILITY
  // ====================================

  it('supports dark theme toggle', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
        ui: { theme: 'light' },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toHaveClass('theme-light');
    });

    // Toggle to dark theme
    const themeToggle = screen.getByTestId('theme-toggle');
    await user.click(themeToggle);

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toHaveClass('theme-dark');
    });
  });

  it('provides keyboard navigation support', async () => {
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toBeInTheDocument();
    });

    // Test tab navigation
    const firstMetric = screen.getByTestId('total-revenue-metric');
    firstMetric.focus();

    // Press Tab to navigate
    await user.tab();
    
    const secondMetric = screen.getByTestId('total-orders-metric');
    expect(secondMetric).toHaveFocus();
  });

  // ====================================
  // 📊 PERFORMANCE TESTS
  // ====================================

  it('renders within acceptable time limits', async () => {
    const startTime = performance.now();
    
    renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toBeInTheDocument();
    });

    const endTime = performance.now();
    const renderTime = endTime - startTime;

    // Should render within 2 seconds
    expect(renderTime).toBeLessThan(2000);
  });

  // ====================================
  // 🔄 CLEANUP TESTS
  // ====================================

  it('cleans up resources on unmount', async () => {
    const { unmount } = renderWithProviders(<Dashboard />, {
      preloadedState: {
        auth: { user: mockUser, isAuthenticated: true },
      },
    });

    await waitFor(() => {
      expect(screen.getByTestId('dashboard-container')).toBeInTheDocument();
    });

    // Unmount component
    unmount();

    // Check that intervals and listeners are cleaned up
    expect(clearInterval).toHaveBeenCalled();
    expect(window.removeEventListener).toHaveBeenCalled();
  });
}); 