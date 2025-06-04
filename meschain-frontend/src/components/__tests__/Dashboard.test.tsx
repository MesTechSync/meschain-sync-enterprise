import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import Dashboard from '../Dashboard';
import { createMockUser, createMockApiResponse } from '../../setupTests';
import axios from 'axios';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const MockedDashboard = ({ user = createMockUser() }) => (
  <MemoryRouter>
    <Dashboard user={user} />
  </MemoryRouter>
);

describe('Dashboard Component', () => {
  beforeEach(() => {
    mockedAxios.get.mockClear();
  });

  it('renders dashboard with all main sections', () => {
    render(<MockedDashboard />);
    
    expect(screen.getByText('navigation.dashboard')).toBeInTheDocument();
    expect(screen.getByTestId('dashboard-stats')).toBeInTheDocument();
    expect(screen.getByTestId('dashboard-charts')).toBeInTheDocument();
  });

  it('displays loading state initially', () => {
    render(<MockedDashboard />);
    
    expect(screen.getByText('common.loading')).toBeInTheDocument();
  });

  it('loads and displays dashboard data', async () => {
    const mockData = {
      stats: {
        totalSales: 150000,
        totalOrders: 1250,
        totalProducts: 450,
        activeMarketplaces: 5
      },
      recentOrders: [
        {
          id: 'ORD-001',
          customerName: 'John Doe',
          amount: 299.99,
          status: 'completed',
          date: '2024-06-03'
        }
      ],
      topProducts: [
        {
          id: 'PROD-001',
          name: 'Test Product',
          sales: 50,
          revenue: 1500
        }
      ]
    };

    mockedAxios.get.mockResolvedValueOnce(createMockApiResponse(mockData));
    
    render(<MockedDashboard />);
    
    await waitFor(() => {
      expect(screen.getByText('₺150,000')).toBeInTheDocument();
      expect(screen.getByText('1,250')).toBeInTheDocument();
      expect(screen.getByText('450')).toBeInTheDocument();
      expect(screen.getByText('5')).toBeInTheDocument();
    });
  });

  it('handles API error gracefully', async () => {
    mockedAxios.get.mockRejectedValueOnce(new Error('API Error'));
    
    render(<MockedDashboard />);
    
    await waitFor(() => {
      expect(screen.getByText(/messages.error/)).toBeInTheDocument();
    });
  });

  it('refreshes data when refresh button is clicked', async () => {
    mockedAxios.get.mockResolvedValue(createMockApiResponse({ stats: {} }));
    
    render(<MockedDashboard />);
    
    const refreshButton = screen.getByTestId('refresh-button');
    fireEvent.click(refreshButton);
    
    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });
  });

  it('filters data by date range', async () => {
    mockedAxios.get.mockResolvedValue(createMockApiResponse({ stats: {} }));
    
    render(<MockedDashboard />);
    
    const dateFilter = screen.getByTestId('date-filter');
    fireEvent.change(dateFilter, { target: { value: 'last7days' } });
    
    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        expect.stringContaining('dateRange=last7days')
      );
    });
  });

  it('shows different content for different user roles', () => {
    const superAdminUser = createMockUser('super_admin');
    render(<MockedDashboard user={superAdminUser} />);
    
    expect(screen.getByTestId('admin-controls')).toBeInTheDocument();
  });

  it('displays real-time updates when WebSocket data is received', async () => {
    render(<MockedDashboard />);
    
    // Simulate WebSocket message
    const wsMessage = {
      type: 'STATS_UPDATE',
      data: { totalSales: 155000 }
    };
    
    // Trigger WebSocket message event
    window.dispatchEvent(new CustomEvent('websocket-message', { 
      detail: wsMessage 
    }));
    
    await waitFor(() => {
      expect(screen.getByText('₺155,000')).toBeInTheDocument();
    });
  });

  it('navigates to marketplace when marketplace card is clicked', () => {
    const mockNavigate = jest.fn();
    jest.spyOn(require('react-router-dom'), 'useNavigate')
      .mockReturnValue(mockNavigate);
    
    render(<MockedDashboard />);
    
    const trendyolCard = screen.getByTestId('marketplace-trendyol');
    fireEvent.click(trendyolCard);
    
    expect(mockNavigate).toHaveBeenCalledWith('/marketplace/trendyol');
  });

  it('handles keyboard navigation', () => {
    render(<MockedDashboard />);
    
    const firstCard = screen.getByTestId('stat-card-sales');
    firstCard.focus();
    
    fireEvent.keyDown(firstCard, { key: 'Enter' });
    
    expect(firstCard).toHaveFocus();
  });
});

// Integration tests
describe('Dashboard Integration Tests', () => {
  it('loads complete dashboard workflow', async () => {
    const mockStats = createMockApiResponse({
      stats: { totalSales: 100000, totalOrders: 500 }
    });
    const mockOrders = createMockApiResponse([
      { id: '1', customer: 'Test Customer', amount: 100 }
    ]);
    
    mockedAxios.get
      .mockResolvedValueOnce(mockStats)
      .mockResolvedValueOnce(mockOrders);
    
    render(<MockedDashboard />);
    
    await waitFor(() => {
      expect(screen.getByText('₺100,000')).toBeInTheDocument();
      expect(screen.getByText('500')).toBeInTheDocument();
      expect(screen.getByText('Test Customer')).toBeInTheDocument();
    });
  });

  it('handles concurrent API requests', async () => {
    mockedAxios.get.mockImplementation((url) => {
      if (url.includes('/stats')) {
        return Promise.resolve(createMockApiResponse({ totalSales: 50000 }));
      }
      if (url.includes('/orders')) {
        return Promise.resolve(createMockApiResponse([]));
      }
      return Promise.resolve(createMockApiResponse({}));
    });
    
    render(<MockedDashboard />);
    
    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(3);
    });
  });
});

// Performance tests
describe('Dashboard Performance Tests', () => {
  it('renders within acceptable time limit', async () => {
    const startTime = performance.now();
    
    render(<MockedDashboard />);
    
    const endTime = performance.now();
    const renderTime = endTime - startTime;
    
    expect(renderTime).toBeLessThan(100); // Should render in less than 100ms
  });

  it('handles large datasets efficiently', async () => {
    const largeDataset = Array.from({ length: 1000 }, (_, i) => ({
      id: `order-${i}`,
      customer: `Customer ${i}`,
      amount: Math.random() * 1000
    }));
    
    mockedAxios.get.mockResolvedValue(createMockApiResponse(largeDataset));
    
    const startTime = performance.now();
    render(<MockedDashboard />);
    
    await waitFor(() => {
      expect(screen.getByTestId('dashboard-stats')).toBeInTheDocument();
    });
    
    const endTime = performance.now();
    expect(endTime - startTime).toBeLessThan(500);
  });
});

// Accessibility tests
describe('Dashboard Accessibility Tests', () => {
  it('has proper ARIA labels and roles', () => {
    render(<MockedDashboard />);
    
    expect(screen.getByRole('main')).toBeInTheDocument();
    expect(screen.getByLabelText(/dashboard overview/i)).toBeInTheDocument();
  });

  it('supports keyboard navigation', () => {
    render(<MockedDashboard />);
    
    const interactiveElements = screen.getAllByRole('button');
    interactiveElements.forEach(element => {
      expect(element).toHaveAttribute('tabIndex');
    });
  });

  it('provides screen reader friendly content', () => {
    render(<MockedDashboard />);
    
    expect(screen.getByText(/dashboard statistics/i)).toBeInTheDocument();
    expect(screen.getByRole('region', { name: /charts/i })).toBeInTheDocument();
  });
}); 