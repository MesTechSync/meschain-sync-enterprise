// 🧪 MesChain-Sync Enterprise - Test Utilities
// Comprehensive testing utilities and helpers

import React, { ReactElement, ReactNode } from 'react';
import { render, RenderOptions, RenderResult } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { Provider } from 'react-redux';
import { ThemeProvider } from '@mui/material/styles';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { I18nextProvider } from 'react-i18next';
import { configureStore, Store } from '@reduxjs/toolkit';
import { theme } from '../../theme/theme';
import i18n from '../../i18n/i18n';

// ====================================
// 🏪 STORE UTILITIES
// ====================================

/**
 * Create a test store with optional initial state
 */
export const createTestStore = (preloadedState?: any): Store => {
  return configureStore({
    reducer: {
      // Add your reducers here
      auth: (state = { user: null, isAuthenticated: false }, action) => state,
      ui: (state = { theme: 'light', language: 'en' }, action) => state,
      dashboard: (state = { data: [], loading: false }, action) => state,
    },
    preloadedState,
    middleware: (getDefaultMiddleware) =>
      getDefaultMiddleware({
        serializableCheck: false,
      }),
  });
};

// ====================================
// 🎭 WRAPPER COMPONENTS
// ====================================

interface AllProvidersProps {
  children: ReactNode;
  store?: Store;
  queryClient?: QueryClient;
  initialRoute?: string;
}

/**
 * All providers wrapper for testing
 */
const AllProviders: React.FC<AllProvidersProps> = ({
  children,
  store = createTestStore(),
  queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
        staleTime: 0,
        gcTime: 0,
      },
    },
  }),
  initialRoute = '/',
}) => {
  // Set initial route
  if (initialRoute !== '/') {
    window.history.pushState({}, 'Test page', initialRoute);
  }

  return (
    <Provider store={store}>
      <QueryClientProvider client={queryClient}>
        <I18nextProvider i18n={i18n}>
          <ThemeProvider theme={theme}>
            <BrowserRouter>
              {children}
            </BrowserRouter>
          </ThemeProvider>
        </I18nextProvider>
      </QueryClientProvider>
    </Provider>
  );
};

// ====================================
// 🧪 CUSTOM RENDER FUNCTIONS
// ====================================

interface CustomRenderOptions extends Omit<RenderOptions, 'wrapper'> {
  store?: Store;
  queryClient?: QueryClient;
  initialRoute?: string;
  preloadedState?: any;
}

/**
 * Custom render function with all providers
 */
export const renderWithProviders = (
  ui: ReactElement,
  options: CustomRenderOptions = {}
): RenderResult & {
  store: Store;
  queryClient: QueryClient;
} => {
  const {
    store = createTestStore(options.preloadedState),
    queryClient = new QueryClient({
      defaultOptions: {
        queries: {
          retry: false,
          staleTime: 0,
          gcTime: 0,
        },
      },
    }),
    initialRoute = '/',
    ...renderOptions
  } = options;

  const Wrapper = ({ children }: { children: ReactNode }) => (
    <AllProviders
      store={store}
      queryClient={queryClient}
      initialRoute={initialRoute}
    >
      {children}
    </AllProviders>
  );

  const result = render(ui, { wrapper: Wrapper, ...renderOptions });

  return {
    ...result,
    store,
    queryClient,
  };
};

/**
 * Render component with Redux only
 */
export const renderWithRedux = (
  ui: ReactElement,
  options: { store?: Store; preloadedState?: any } = {}
): RenderResult & { store: Store } => {
  const store = options.store || createTestStore(options.preloadedState);

  const Wrapper = ({ children }: { children: ReactNode }) => (
    <Provider store={store}>{children}</Provider>
  );

  const result = render(ui, { wrapper: Wrapper });

  return {
    ...result,
    store,
  };
};

/**
 * Render component with Router only
 */
export const renderWithRouter = (
  ui: ReactElement,
  options: { initialRoute?: string } = {}
): RenderResult => {
  const { initialRoute = '/' } = options;

  if (initialRoute !== '/') {
    window.history.pushState({}, 'Test page', initialRoute);
  }

  const Wrapper = ({ children }: { children: ReactNode }) => (
    <BrowserRouter>{children}</BrowserRouter>
  );

  return render(ui, { wrapper: Wrapper });
};

/**
 * Render component with Theme only
 */
export const renderWithTheme = (ui: ReactElement): RenderResult => {
  const Wrapper = ({ children }: { children: ReactNode }) => (
    <ThemeProvider theme={theme}>{children}</ThemeProvider>
  );

  return render(ui, { wrapper: Wrapper });
};

// ====================================
// 🎯 CUSTOM ASSERTIONS
// ====================================

/**
 * Wait for element to be removed with timeout
 */
export const waitForElementToBeRemoved = async (
  element: HTMLElement,
  timeout = 5000
): Promise<void> => {
  return new Promise((resolve, reject) => {
    const startTime = Date.now();
    
    const checkElement = () => {
      if (!document.contains(element)) {
        resolve();
        return;
      }
      
      if (Date.now() - startTime > timeout) {
        reject(new Error(`Element was not removed within ${timeout}ms`));
        return;
      }
      
      setTimeout(checkElement, 50);
    };
    
    checkElement();
  });
};

/**
 * Wait for condition to be true
 */
export const waitForCondition = async (
  condition: () => boolean,
  timeout = 5000,
  interval = 50
): Promise<void> => {
  return new Promise((resolve, reject) => {
    const startTime = Date.now();
    
    const checkCondition = () => {
      if (condition()) {
        resolve();
        return;
      }
      
      if (Date.now() - startTime > timeout) {
        reject(new Error(`Condition was not met within ${timeout}ms`));
        return;
      }
      
      setTimeout(checkCondition, interval);
    };
    
    checkCondition();
  });
};

// ====================================
// 🎭 MOCK GENERATORS
// ====================================

/**
 * Generate mock user data
 */
export const createMockUser = (overrides: any = {}) => ({
  id: '1',
  name: 'Test User',
  email: 'test@example.com',
  role: 'admin',
  avatar: 'https://example.com/avatar.jpg',
  isActive: true,
  createdAt: new Date().toISOString(),
  updatedAt: new Date().toISOString(),
  ...overrides,
});

/**
 * Generate mock API response
 */
export const createMockApiResponse = <T>(data: T, overrides: any = {}) => ({
  data,
  status: 200,
  message: 'Success',
  timestamp: new Date().toISOString(),
  ...overrides,
});

/**
 * Generate mock error response
 */
export const createMockErrorResponse = (message = 'An error occurred', code = 500) => ({
  error: {
    message,
    code,
    timestamp: new Date().toISOString(),
  },
});

/**
 * Generate mock pagination data
 */
export const createMockPagination = (overrides: any = {}) => ({
  page: 1,
  pageSize: 10,
  total: 100,
  totalPages: 10,
  hasNext: true,
  hasPrev: false,
  ...overrides,
});

// ====================================
// 🌐 API MOCK UTILITIES
// ====================================

/**
 * Mock fetch response
 */
export const mockFetchResponse = (data: any, options: any = {}) => {
  const { status = 200, ok = true, statusText = 'OK' } = options;
  
  return Promise.resolve({
    ok,
    status,
    statusText,
    json: () => Promise.resolve(data),
    text: () => Promise.resolve(JSON.stringify(data)),
    headers: new Headers(),
    redirected: false,
    type: 'basic' as ResponseType,
    url: '',
    clone: jest.fn(),
    body: null,
    bodyUsed: false,
    arrayBuffer: () => Promise.resolve(new ArrayBuffer(0)),
    blob: () => Promise.resolve(new Blob()),
    formData: () => Promise.resolve(new FormData()),
  } as Response);
};

/**
 * Mock fetch error
 */
export const mockFetchError = (message = 'Network error') => {
  return Promise.reject(new Error(message));
};

/**
 * Setup fetch mock with responses
 */
export const setupFetchMock = (responses: Record<string, any>) => {
  (global.fetch as jest.Mock).mockImplementation((url: string) => {
    if (responses[url]) {
      return mockFetchResponse(responses[url]);
    }
    return mockFetchError(`No mock response for ${url}`);
  });
};

// ====================================
// 🎪 EVENT UTILITIES
// ====================================

/**
 * Create mock event
 */
export const createMockEvent = (type: string, options: any = {}) => {
  const event = new Event(type, { bubbles: true, cancelable: true });
  Object.assign(event, options);
  return event;
};

/**
 * Create mock keyboard event
 */
export const createMockKeyboardEvent = (key: string, options: any = {}) => {
  return new KeyboardEvent('keydown', {
    key,
    bubbles: true,
    cancelable: true,
    ...options,
  });
};

/**
 * Create mock mouse event
 */
export const createMockMouseEvent = (type: string, options: any = {}) => {
  return new MouseEvent(type, {
    bubbles: true,
    cancelable: true,
    ...options,
  });
};

// ====================================
// 📊 PERFORMANCE TESTING
// ====================================

/**
 * Measure component render time
 */
export const measureRenderTime = async (renderFn: () => void): Promise<number> => {
  const start = performance.now();
  renderFn();
  const end = performance.now();
  return end - start;
};

/**
 * Test component performance
 */
export const testComponentPerformance = async (
  component: ReactElement,
  maxRenderTime = 100
): Promise<{ renderTime: number; passed: boolean }> => {
  const renderTime = await measureRenderTime(() => {
    render(component);
  });
  
  return {
    renderTime,
    passed: renderTime <= maxRenderTime,
  };
};

// ====================================
// 🔧 DEBUGGING UTILITIES
// ====================================

/**
 * Debug render result
 */
export const debugRender = (result: RenderResult): void => {
  console.log('🐛 Debug Render:');
  console.log(result.container.innerHTML);
  result.debug();
};

/**
 * Log component props for debugging
 */
export const logProps = (component: any): void => {
  console.log('🔍 Component Props:', component.props);
};

/**
 * Capture console output during test
 */
export const captureConsole = () => {
  const logs: string[] = [];
  const originalLog = console.log;
  const originalError = console.error;
  const originalWarn = console.warn;
  
  console.log = (...args) => logs.push(`LOG: ${args.join(' ')}`);
  console.error = (...args) => logs.push(`ERROR: ${args.join(' ')}`);
  console.warn = (...args) => logs.push(`WARN: ${args.join(' ')}`);
  
  return {
    getLogs: () => logs,
    restore: () => {
      console.log = originalLog;
      console.error = originalError;
      console.warn = originalWarn;
    },
  };
};

// ====================================
// 📋 ACCESSIBILITY TESTING
// ====================================

/**
 * Test component accessibility
 */
export const testAccessibility = async (element: HTMLElement): Promise<any> => {
  const axe = await import('axe-core');
  return axe.run(element);
};

/**
 * Check ARIA attributes
 */
export const checkAriaAttributes = (element: HTMLElement): Record<string, string> => {
  const ariaAttributes: Record<string, string> = {};
  
  for (const attr of element.attributes) {
    if (attr.name.startsWith('aria-')) {
      ariaAttributes[attr.name] = attr.value;
    }
  }
  
  return ariaAttributes;
};

// Export all utilities
export * from '@testing-library/react';
export * from '@testing-library/user-event';
export { renderWithProviders as render }; 