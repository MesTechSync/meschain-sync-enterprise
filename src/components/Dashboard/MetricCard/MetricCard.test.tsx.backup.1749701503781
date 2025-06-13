// 🧪 MesChain-Sync Enterprise - MetricCard Component Tests
// Comprehensive testing for MetricCard component

import React from 'react';
import { screen, fireEvent, waitFor } from '@testing-library/react';
import { renderWithProviders } from '../../../tests/utils/testUtils';
import { MetricCard } from './MetricCard';
import { TrendingUp, TrendingDown, TrendingFlat } from '@mui/icons-material';

// ====================================
// 🎭 MOCK DATA
// ====================================

const mockMetricData = {
  title: 'Total Revenue',
  value: '₺125,450.00',
  previousValue: '₺110,230.00',
  change: 13.8,
  changeType: 'increase' as const,
  icon: TrendingUp,
  color: 'success' as const,
  loading: false,
  error: null,
};

const mockOnClick = jest.fn();

// ====================================
// 📋 COMPONENT TESTS
// ====================================

describe('MetricCard Component', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  // ====================================
  // 🎯 RENDERING TESTS
  // ====================================

  it('renders metric card with all data correctly', () => {
    renderWithProviders(<MetricCard {...mockMetricData} />);

    expect(screen.getByText('Total Revenue')).toBeInTheDocument();
    expect(screen.getByText('₺125,450.00')).toBeInTheDocument();
    expect(screen.getByText('13.8%')).toBeInTheDocument();
    expect(screen.getByTestId('metric-card')).toBeInTheDocument();
  });

  it('renders with increase trend indicator', () => {
    renderWithProviders(<MetricCard {...mockMetricData} />);

    const trendIcon = screen.getByTestId('trend-icon');
    expect(trendIcon).toBeInTheDocument();
    expect(screen.getByTestId('metric-card')).toHaveClass('trend-increase');
  });

  it('renders with decrease trend correctly', () => {
    const decreaseData = {
      ...mockMetricData,
      change: -5.2,
      changeType: 'decrease' as const,
      icon: TrendingDown,
      color: 'error' as const,
    };

    renderWithProviders(<MetricCard {...decreaseData} />);

    expect(screen.getByText('-5.2%')).toBeInTheDocument();
    expect(screen.getByTestId('metric-card')).toHaveClass('trend-decrease');
  });

  it('renders with neutral trend', () => {
    const neutralData = {
      ...mockMetricData,
      change: 0,
      changeType: 'neutral' as const,
      icon: TrendingFlat,
      color: 'info' as const,
    };

    renderWithProviders(<MetricCard {...neutralData} />);

    expect(screen.getByText('0.0%')).toBeInTheDocument();
    expect(screen.getByTestId('metric-card')).toHaveClass('trend-neutral');
  });

  // ====================================
  // 🔄 LOADING STATES
  // ====================================

  it('shows loading skeleton when loading', () => {
    const loadingData = { ...mockMetricData, loading: true };
    renderWithProviders(<MetricCard {...loadingData} />);

    expect(screen.getByTestId('metric-skeleton')).toBeInTheDocument();
    expect(screen.queryByText('Total Revenue')).not.toBeInTheDocument();
  });

  it('shows shimmer animation during loading', () => {
    const loadingData = { ...mockMetricData, loading: true };
    renderWithProviders(<MetricCard {...loadingData} />);

    const skeleton = screen.getByTestId('metric-skeleton');
    expect(skeleton).toHaveClass('shimmer-animation');
  });

  // ====================================
  // ❌ ERROR STATES
  // ====================================

  it('displays error message when error occurs', () => {
    const errorData = {
      ...mockMetricData,
      error: { message: 'Failed to load metric data' },
    };

    renderWithProviders(<MetricCard {...errorData} />);

    expect(screen.getByText('Failed to load metric data')).toBeInTheDocument();
    expect(screen.getByTestId('error-icon')).toBeInTheDocument();
  });

  it('shows retry button on error', () => {
    const onRetry = jest.fn();
    const errorData = {
      ...mockMetricData,
      error: { message: 'Network error' },
      onRetry,
    };

    renderWithProviders(<MetricCard {...errorData} />);

    const retryButton = screen.getByRole('button', { name: /retry/i });
    expect(retryButton).toBeInTheDocument();

    fireEvent.click(retryButton);
    expect(onRetry).toHaveBeenCalledTimes(1);
  });

  // ====================================
  // 🎪 INTERACTION TESTS
  // ====================================

  it('calls onClick when card is clicked', () => {
    renderWithProviders(
      <MetricCard {...mockMetricData} onClick={mockOnClick} />
    );

    const card = screen.getByTestId('metric-card');
    fireEvent.click(card);

    expect(mockOnClick).toHaveBeenCalledTimes(1);
  });

  it('shows hover effect on clickable card', () => {
    renderWithProviders(
      <MetricCard {...mockMetricData} onClick={mockOnClick} />
    );

    const card = screen.getByTestId('metric-card');
    expect(card).toHaveClass('clickable');

    fireEvent.mouseEnter(card);
    expect(card).toHaveClass('hover');
  });

  it('does not show hover effect when not clickable', () => {
    renderWithProviders(<MetricCard {...mockMetricData} />);

    const card = screen.getByTestId('metric-card');
    expect(card).not.toHaveClass('clickable');
  });

  // ====================================
  // 🌈 STYLING TESTS
  // ====================================

  it('applies correct color theme', () => {
    const { rerender } = renderWithProviders(
      <MetricCard {...mockMetricData} color="success" />
    );

    expect(screen.getByTestId('metric-card')).toHaveClass('color-success');

    rerender(<MetricCard {...mockMetricData} color="error" />);
    expect(screen.getByTestId('metric-card')).toHaveClass('color-error');

    rerender(<MetricCard {...mockMetricData} color="warning" />);
    expect(screen.getByTestId('metric-card')).toHaveClass('color-warning');
  });

  it('applies custom className', () => {
    renderWithProviders(
      <MetricCard {...mockMetricData} className="custom-metric" />
    );

    expect(screen.getByTestId('metric-card')).toHaveClass('custom-metric');
  });

  // ====================================
  // 📊 VALUE FORMATTING TESTS
  // ====================================

  it('formats large numbers correctly', () => {
    const largeValueData = {
      ...mockMetricData,
      value: '₺1,250,000.00',
      formatValue: (value: string) => '₺1.25M',
    };

    renderWithProviders(<MetricCard {...largeValueData} />);
    expect(screen.getByText('₺1.25M')).toBeInTheDocument();
  });

  it('handles percentage formatting', () => {
    const percentageData = {
      ...mockMetricData,
      title: 'Conversion Rate',
      value: '3.45%',
      change: 0.15,
    };

    renderWithProviders(<MetricCard {...percentageData} />);
    expect(screen.getByText('3.45%')).toBeInTheDocument();
  });

  // ====================================
  // 🔢 COMPARISON TESTS
  // ====================================

  it('shows previous value comparison', () => {
    renderWithProviders(<MetricCard {...mockMetricData} showComparison />);

    expect(screen.getByText('vs ₺110,230.00')).toBeInTheDocument();
    expect(screen.getByTestId('comparison-text')).toBeInTheDocument();
  });

  it('hides comparison when showComparison is false', () => {
    renderWithProviders(
      <MetricCard {...mockMetricData} showComparison={false} />
    );

    expect(screen.queryByText('vs ₺110,230.00')).not.toBeInTheDocument();
  });

  // ====================================
  // 📱 RESPONSIVE TESTS
  // ====================================

  it('adapts to mobile viewport', () => {
    // Mock mobile viewport
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 375,
    });

    renderWithProviders(<MetricCard {...mockMetricData} />);

    const card = screen.getByTestId('metric-card');
    expect(card).toHaveClass('mobile-layout');
  });

  // ====================================
  // ♿ ACCESSIBILITY TESTS
  // ====================================

  it('has proper accessibility attributes', () => {
    renderWithProviders(
      <MetricCard {...mockMetricData} onClick={mockOnClick} />
    );

    const card = screen.getByTestId('metric-card');
    expect(card).toHaveAttribute('role', 'button');
    expect(card).toHaveAttribute('tabIndex', '0');
    expect(card).toHaveAttribute('aria-label');
  });

  it('supports keyboard navigation', () => {
    renderWithProviders(
      <MetricCard {...mockMetricData} onClick={mockOnClick} />
    );

    const card = screen.getByTestId('metric-card');
    card.focus();

    fireEvent.keyDown(card, { key: 'Enter' });
    expect(mockOnClick).toHaveBeenCalledTimes(1);

    fireEvent.keyDown(card, { key: ' ' });
    expect(mockOnClick).toHaveBeenCalledTimes(2);
  });

  it('provides screen reader friendly content', () => {
    renderWithProviders(<MetricCard {...mockMetricData} />);

    const srText = screen.getByTestId('sr-only-content');
    expect(srText).toHaveTextContent(
      'Total Revenue: ₺125,450.00, increased by 13.8% from previous period'
    );
  });

  // ====================================
  // 🎨 ANIMATION TESTS
  // ====================================

  it('animates value changes', async () => {
    const { rerender } = renderWithProviders(
      <MetricCard {...mockMetricData} animated />
    );

    const valueElement = screen.getByTestId('metric-value');
    expect(valueElement).toHaveClass('animate-in');

    // Update value
    rerender(
      <MetricCard
        {...mockMetricData}
        value="₺135,600.00"
        animated
      />
    );

    await waitFor(() => {
      expect(screen.getByText('₺135,600.00')).toBeInTheDocument();
    });
  });

  // ====================================
  // 🔧 PERFORMANCE TESTS
  // ====================================

  it('memoizes expensive calculations', () => {
    const expensiveFormat = jest.fn((value: string) => value);
    
    const { rerender } = renderWithProviders(
      <MetricCard {...mockMetricData} formatValue={expensiveFormat} />
    );

    expect(expensiveFormat).toHaveBeenCalledTimes(1);

    // Re-render with same props - should not call formatValue again
    rerender(
      <MetricCard {...mockMetricData} formatValue={expensiveFormat} />
    );

    expect(expensiveFormat).toHaveBeenCalledTimes(1);
  });

  // ====================================
  // 🎯 EDGE CASES
  // ====================================

  it('handles missing icon gracefully', () => {
    const noIconData = { ...mockMetricData, icon: undefined };
    renderWithProviders(<MetricCard {...noIconData} />);

    expect(screen.queryByTestId('metric-icon')).not.toBeInTheDocument();
    expect(screen.getByTestId('metric-card')).toBeInTheDocument();
  });

  it('handles zero values correctly', () => {
    const zeroData = {
      ...mockMetricData,
      value: '₺0.00',
      change: 0,
      changeType: 'neutral' as const,
    };

    renderWithProviders(<MetricCard {...zeroData} />);

    expect(screen.getByText('₺0.00')).toBeInTheDocument();
    expect(screen.getByText('0.0%')).toBeInTheDocument();
  });

  it('handles extremely large values', () => {
    const largeData = {
      ...mockMetricData,
      value: '₺999,999,999,999.99',
    };

    renderWithProviders(<MetricCard {...largeData} />);

    expect(screen.getByTestId('metric-value')).toBeInTheDocument();
    // Should not cause overflow
    expect(screen.getByTestId('metric-card')).not.toHaveClass('overflow');
  });

  // ====================================
  // 🧪 SNAPSHOT TESTS
  // ====================================

  it('matches snapshot for default state', () => {
    const { container } = renderWithProviders(<MetricCard {...mockMetricData} />);
    expect(container.firstChild).toMatchSnapshot();
  });

  it('matches snapshot for loading state', () => {
    const loadingData = { ...mockMetricData, loading: true };
    const { container } = renderWithProviders(<MetricCard {...loadingData} />);
    expect(container.firstChild).toMatchSnapshot();
  });

  it('matches snapshot for error state', () => {
    const errorData = {
      ...mockMetricData,
      error: { message: 'Test error' },
    };
    const { container } = renderWithProviders(<MetricCard {...errorData} />);
    expect(container.firstChild).toMatchSnapshot();
  });
}); 