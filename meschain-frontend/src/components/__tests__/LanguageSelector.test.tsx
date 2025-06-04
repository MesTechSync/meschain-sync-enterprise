import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import LanguageSelector from '../LanguageSelector';
import * as i18n from '../../i18n';

// Mock i18n functions
jest.mock('../../i18n', () => ({
  ...jest.requireActual('../../i18n'),
  getCurrentLanguage: jest.fn(() => 'tr'),
  changeLanguage: jest.fn(() => Promise.resolve()),
  getLanguageDirection: jest.fn(() => 'ltr'),
  languages: {
    tr: { name: 'Türkçe', flag: '🇹🇷', dir: 'ltr', currency: 'TRY', currencySymbol: '₺', dateFormat: 'DD.MM.YYYY' },
    en: { name: 'English', flag: '🇺🇸', dir: 'ltr', currency: 'USD', currencySymbol: '$', dateFormat: 'MM/DD/YYYY' },
    ar: { name: 'العربية', flag: '🇸🇦', dir: 'rtl', currency: 'SAR', currencySymbol: 'ر.س', dateFormat: 'DD/MM/YYYY' },
    de: { name: 'Deutsch', flag: '🇩🇪', dir: 'ltr', currency: 'EUR', currencySymbol: '€', dateFormat: 'DD.MM.YYYY' }
  }
}));

const mockedI18n = i18n as jest.Mocked<typeof i18n>;

describe('LanguageSelector Component', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    mockedI18n.getCurrentLanguage.mockReturnValue('tr');
  });

  it('renders current language correctly', () => {
    render(<LanguageSelector />);
    
    expect(screen.getByText('🇹🇷 Türkçe')).toBeInTheDocument();
    expect(screen.getByRole('button')).toBeInTheDocument();
  });

  it('opens dropdown when clicked', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    expect(screen.getByText('Select Language')).toBeInTheDocument();
    expect(screen.getByText('English')).toBeInTheDocument();
    expect(screen.getByText('العربية')).toBeInTheDocument();
    expect(screen.getByText('Deutsch')).toBeInTheDocument();
  });

  it('closes dropdown when clicking outside', async () => {
    const user = userEvent.setup();
    render(
      <div>
        <LanguageSelector />
        <div data-testid="outside">Outside element</div>
      </div>
    );
    
    // Open dropdown
    const button = screen.getByRole('button');
    await user.click(button);
    expect(screen.getByText('Select Language')).toBeInTheDocument();
    
    // Click outside
    const outsideElement = screen.getByTestId('outside');
    await user.click(outsideElement);
    
    await waitFor(() => {
      expect(screen.queryByText('Select Language')).not.toBeInTheDocument();
    });
  });

  it('changes language when option is selected', async () => {
    const user = userEvent.setup();
    mockedI18n.changeLanguage.mockResolvedValueOnce(undefined);
    
    render(<LanguageSelector />);
    
    // Open dropdown
    const button = screen.getByRole('button');
    await user.click(button);
    
    // Select English
    const englishOption = screen.getByText('English');
    await user.click(englishOption);
    
    expect(mockedI18n.changeLanguage).toHaveBeenCalledWith('en');
  });

  it('shows loading state during language change', async () => {
    const user = userEvent.setup();
    let resolveLanguageChange: () => void;
    const languageChangePromise = new Promise<void>((resolve) => {
      resolveLanguageChange = resolve;
    });
    mockedI18n.changeLanguage.mockReturnValue(languageChangePromise);
    
    render(<LanguageSelector />);
    
    // Open dropdown and select language
    const button = screen.getByRole('button');
    await user.click(button);
    
    const englishOption = screen.getByText('English');
    await user.click(englishOption);
    
    // Check loading state
    expect(button).toHaveClass('opacity-50', 'cursor-not-allowed');
    
    // Resolve the promise
    resolveLanguageChange!();
    await waitFor(() => {
      expect(button).not.toHaveClass('opacity-50');
    });
  });

  it('displays success notification after language change', async () => {
    const user = userEvent.setup();
    mockedI18n.changeLanguage.mockResolvedValueOnce(undefined);
    
    // Mock document.createElement to capture notification
    const mockNotification = document.createElement('div');
    const originalCreateElement = document.createElement;
    document.createElement = jest.fn().mockReturnValue(mockNotification);
    
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    const englishOption = screen.getByText('English');
    await user.click(englishOption);
    
    await waitFor(() => {
      expect(mockNotification.textContent).toContain('Language changed to English');
    });
    
    // Restore original createElement
    document.createElement = originalCreateElement;
  });

  it('shows RTL indicator for Arabic', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    const arabicOption = screen.getByText('العربية');
    const arabicContainer = arabicOption.closest('button');
    
    expect(arabicContainer).toHaveAttribute('dir', 'rtl');
    expect(screen.getByText('RTL')).toBeInTheDocument();
  });

  it('displays currency information for each language', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    expect(screen.getByText('$ USD')).toBeInTheDocument();
    expect(screen.getByText('€ EUR')).toBeInTheDocument();
    expect(screen.getByText('ر.س SAR')).toBeInTheDocument();
  });

  it('shows current language with check mark', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    // Turkish should be selected (current language)
    const turkishOption = screen.getByText('Türkçe');
    const turkishContainer = turkishOption.closest('button');
    
    expect(turkishContainer).toHaveClass('bg-blue-50');
    expect(screen.getByTestId('lucide-icon')).toBeInTheDocument(); // Check icon
  });

  it('prevents language change when same language is selected', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    // Select Turkish (current language)
    const turkishOption = screen.getByText('Türkçe');
    await user.click(turkishOption);
    
    expect(mockedI18n.changeLanguage).not.toHaveBeenCalled();
  });

  it('handles keyboard navigation', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    
    // Focus and press Enter to open
    await user.tab();
    expect(button).toHaveFocus();
    
    await user.keyboard('{Enter}');
    expect(screen.getByText('Select Language')).toBeInTheDocument();
    
    // Navigate with arrow keys
    await user.keyboard('{ArrowDown}');
    await user.keyboard('{Enter}');
    
    // Should attempt to change language
    expect(mockedI18n.changeLanguage).toHaveBeenCalled();
  });

  it('handles language change error gracefully', async () => {
    const user = userEvent.setup();
    const consoleError = jest.spyOn(console, 'error').mockImplementation(() => {});
    mockedI18n.changeLanguage.mockRejectedValueOnce(new Error('Change failed'));
    
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    const englishOption = screen.getByText('English');
    await user.click(englishOption);
    
    await waitFor(() => {
      expect(consoleError).toHaveBeenCalledWith('Error changing language:', expect.any(Error));
    });
    
    consoleError.mockRestore();
  });

  it('displays date format information', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    expect(screen.getByText('DD.MM.YYYY')).toBeInTheDocument(); // Turkish format
    expect(screen.getByText('MM/DD/YYYY')).toBeInTheDocument(); // English format
  });

  it('supports dark mode styling', () => {
    // Add dark class to document
    document.documentElement.classList.add('dark');
    
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    expect(button).toHaveClass('dark:bg-gray-800');
    
    // Cleanup
    document.documentElement.classList.remove('dark');
  });
});

// Integration tests with i18n
describe('LanguageSelector Integration Tests', () => {
  it('integrates properly with i18n system', async () => {
    const user = userEvent.setup();
    mockedI18n.changeLanguage.mockImplementation(async (lng) => {
      mockedI18n.getCurrentLanguage.mockReturnValue(lng);
      return Promise.resolve();
    });
    
    render(<LanguageSelector />);
    
    // Change to English
    const button = screen.getByRole('button');
    await user.click(button);
    
    const englishOption = screen.getByText('English');
    await user.click(englishOption);
    
    await waitFor(() => {
      expect(mockedI18n.getCurrentLanguage()).toBe('en');
    });
  });

  it('handles RTL language switching correctly', async () => {
    const user = userEvent.setup();
    mockedI18n.getLanguageDirection.mockReturnValue('rtl');
    
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    const arabicOption = screen.getByText('العربية');
    await user.click(arabicOption);
    
    expect(mockedI18n.changeLanguage).toHaveBeenCalledWith('ar');
  });
});

// Accessibility tests
describe('LanguageSelector Accessibility Tests', () => {
  it('has proper ARIA attributes', () => {
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    expect(button).toHaveAttribute('aria-expanded', 'false');
  });

  it('updates ARIA attributes when dropdown opens', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    expect(button).toHaveAttribute('aria-expanded', 'true');
  });

  it('supports screen readers with proper labels', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    const options = screen.getAllByRole('button');
    options.forEach(option => {
      expect(option).toHaveAttribute('aria-label');
    });
  });

  it('maintains focus management', async () => {
    const user = userEvent.setup();
    render(<LanguageSelector />);
    
    const button = screen.getByRole('button');
    await user.click(button);
    
    // Focus should remain on the main button after opening
    expect(button).toHaveFocus();
  });
}); 