// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import '@testing-library/jest-dom';

// Global test mocks
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(),
    removeListener: jest.fn(),
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

// ResizeObserver mock
global.ResizeObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn(),
}));

// IntersectionObserver mock
global.IntersectionObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn(),
}));

// Mock localStorage
const localStorageMock = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  clear: jest.fn(),
};
global.localStorage = localStorageMock;

// Mock sessionStorage
const sessionStorageMock = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  clear: jest.fn(),
};
global.sessionStorage = sessionStorageMock;

// Mock fetch
global.fetch = jest.fn();

// Mock URL.createObjectURL
global.URL.createObjectURL = jest.fn();

// Mock performance API
Object.defineProperty(global, 'performance', {
  writable: true,
  value: {
    now: jest.fn(() => Date.now()),
    mark: jest.fn(),
    measure: jest.fn(),
    getEntriesByName: jest.fn(() => []),
    getEntriesByType: jest.fn(() => []),
    clearMarks: jest.fn(),
    clearMeasures: jest.fn(),
    memory: {
      usedJSHeapSize: 1000000,
      totalJSHeapSize: 2000000,
      jsHeapSizeLimit: 4000000,
    },
  },
});

// Mock navigator
Object.defineProperty(global, 'navigator', {
  writable: true,
  value: {
    userAgent: 'test',
    language: 'en-US',
    languages: ['en-US', 'en'],
    onLine: true,
    serviceWorker: {
      register: jest.fn(() => Promise.resolve()),
      ready: Promise.resolve(),
    },
  },
});

// React-chartjs-2 mock
jest.mock('react-chartjs-2', () => ({
  Line: jest.fn(() => null),
  Bar: jest.fn(() => null),
  Doughnut: jest.fn(() => null),
  Pie: jest.fn(() => null),
  Radar: jest.fn(() => null),
  PolarArea: jest.fn(() => null),
}));

// Chart.js mock
jest.mock('chart.js', () => ({
  Chart: {
    register: jest.fn(),
  },
  CategoryScale: jest.fn(),
  LinearScale: jest.fn(),
  PointElement: jest.fn(),
  LineElement: jest.fn(),
  Title: jest.fn(),
  Tooltip: jest.fn(),
  Legend: jest.fn(),
  BarElement: jest.fn(),
  ArcElement: jest.fn(),
}));

// Recharts mock
jest.mock('recharts', () => ({
  LineChart: jest.fn(() => null),
  BarChart: jest.fn(() => null),
  PieChart: jest.fn(() => null),
  Line: jest.fn(() => null),
  Bar: jest.fn(() => null),
  XAxis: jest.fn(() => null),
  YAxis: jest.fn(() => null),
  CartesianGrid: jest.fn(() => null),
  Tooltip: jest.fn(() => null),
  Legend: jest.fn(() => null),
  ResponsiveContainer: jest.fn(() => null),
}));

// Lucide React mock
jest.mock('lucide-react', () => {
  return new Proxy({}, {
    get: () => jest.fn(() => null)
  });
});

// React Router mock
jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useNavigate: () => jest.fn(),
  useLocation: () => ({
    pathname: '/dashboard',
    search: '',
    hash: '',
    state: null,
  }),
  useParams: () => ({}),
}));

// Axios mock
jest.mock('axios', () => ({
  create: jest.fn(() => ({
    get: jest.fn(),
    post: jest.fn(),
    put: jest.fn(),
    delete: jest.fn(),
    patch: jest.fn(),
    interceptors: {
      request: { use: jest.fn() },
      response: { use: jest.fn() },
    },
  })),
  get: jest.fn(),
  post: jest.fn(),
  put: jest.fn(),
  delete: jest.fn(),
  patch: jest.fn(),
}));

// i18next mock
jest.mock('react-i18next', () => ({
  useTranslation: () => ({
    t: (key: string) => key,
    i18n: {
      changeLanguage: jest.fn(),
      language: 'en',
    },
  }),
  Trans: ({ children }: any) => children,
  initReactI18next: {
    type: '3rdParty',
    init: jest.fn(),
  },
}));

// Date-fns mock
jest.mock('date-fns', () => ({
  format: jest.fn((date, formatStr) => date.toISOString()),
  parseISO: jest.fn((dateStr) => new Date(dateStr)),
  isValid: jest.fn(() => true),
  addDays: jest.fn((date, days) => new Date(date.getTime() + days * 24 * 60 * 60 * 1000)),
  subDays: jest.fn((date, days) => new Date(date.getTime() - days * 24 * 60 * 60 * 1000)),
}));

// Lodash mock
jest.mock('lodash', () => ({
  debounce: jest.fn((fn) => fn),
  throttle: jest.fn((fn) => fn),
  cloneDeep: jest.fn((obj) => JSON.parse(JSON.stringify(obj))),
  merge: jest.fn((target, source) => ({ ...target, ...source })),
  get: jest.fn((obj, path, defaultValue) => {
    const keys = path.split('.');
    let result = obj;
    for (const key of keys) {
      result = result?.[key];
    }
    return result !== undefined ? result : defaultValue;
  }),
}));

// React Hot Toast mock
jest.mock('react-hot-toast', () => ({
  toast: {
    success: jest.fn(),
    error: jest.fn(),
    loading: jest.fn(),
    dismiss: jest.fn(),
  },
  Toaster: jest.fn(() => null),
}));

// Helper functions for tests
export const createMockUser = (overrides = {}) => ({
  id: 'user-123',
  username: 'testuser',
  email: 'test@example.com',
  role: 'admin',
  permissions: ['all'],
  ...overrides,
});

export const createMockApiResponse = (data: any, success = true) => ({
  success,
  data,
  message: success ? 'Success' : 'Error',
});

export const mockApiError = (message = 'API Error', code = 500) => ({
  response: {
    status: code,
    data: {
      success: false,
      error: {
        message,
        code: `ERROR_${code}`,
      },
    },
  },
});

// Test utilities
export const waitForAsync = () => new Promise(resolve => setTimeout(resolve, 0));

export const mockConsole = () => {
  const originalConsole = { ...console };
  beforeEach(() => {
    jest.spyOn(console, 'log').mockImplementation(() => {});
    jest.spyOn(console, 'warn').mockImplementation(() => {});
    jest.spyOn(console, 'error').mockImplementation(() => {});
  });
  
  afterEach(() => {
    Object.assign(console, originalConsole);
  });
};

// Mock WebSocket
global.WebSocket = jest.fn().mockImplementation(() => ({
  send: jest.fn(),
  close: jest.fn(),
  addEventListener: jest.fn(),
  removeEventListener: jest.fn(),
  readyState: 1,
}));

// Mock File and FileReader
global.File = jest.fn().mockImplementation((chunks, filename, options) => ({
  name: filename,
  size: chunks.reduce((acc: number, chunk: any) => acc + chunk.length, 0),
  type: options?.type || '',
}));

global.FileReader = jest.fn().mockImplementation(() => ({
  readAsDataURL: jest.fn(),
  readAsText: jest.fn(),
  result: '',
  addEventListener: jest.fn(),
  removeEventListener: jest.fn(),
}));

// Suppress console warnings in tests
const originalWarn = console.warn;
beforeAll(() => {
  console.warn = (...args: any[]) => {
    if (
      typeof args[0] === 'string' &&
      args[0].includes('Warning: ReactDOM.render is no longer supported')
    ) {
      return;
    }
    originalWarn.call(console, ...args);
  };
});

afterAll(() => {
  console.warn = originalWarn;
}); 