// 🧪 MesChain-Sync Enterprise - Test Setup Configuration
// Global test environment setup and utilities

import '@testing-library/jest-dom';
import 'jest-localstorage-mock';
import { configure } from '@testing-library/react';
import { TextEncoder, TextDecoder } from 'util';

// ====================================
// 🌍 GLOBAL SETUP
// ====================================

// Configure React Testing Library
configure({
  testIdAttribute: 'data-testid',
  asyncUtilTimeout: 5000,
});

// ====================================
// 🔧 POLYFILLS & GLOBAL MOCKS
// ====================================

// TextEncoder/TextDecoder polyfill for Node.js
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder as typeof global.TextDecoder;

// IntersectionObserver mock
global.IntersectionObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn(),
  root: null,
  rootMargin: '',
  thresholds: [],
}));

// ResizeObserver mock
global.ResizeObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn(),
}));

// MutationObserver mock
global.MutationObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  disconnect: jest.fn(),
  takeRecords: jest.fn(),
}));

// ====================================
// 🌐 WEB API MOCKS
// ====================================

// Fetch API mock
global.fetch = jest.fn();

// matchMedia mock
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation((query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(), // deprecated
    removeListener: jest.fn(), // deprecated
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

// scrollTo mock
Object.defineProperty(window, 'scrollTo', {
  writable: true,
  value: jest.fn(),
});

// scrollIntoView mock
Object.defineProperty(Element.prototype, 'scrollIntoView', {
  writable: true,
  value: jest.fn(),
});

// getComputedStyle mock
Object.defineProperty(window, 'getComputedStyle', {
  writable: true,
  value: jest.fn().mockImplementation(() => ({
    getPropertyValue: jest.fn(),
    display: 'block',
    visibility: 'visible',
    opacity: '1',
  })),
});

// ====================================
// 📱 DEVICE & BROWSER MOCKS
// ====================================

// navigator mock
Object.defineProperty(navigator, 'userAgent', {
  writable: true,
  value: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
});

Object.defineProperty(navigator, 'language', {
  writable: true,
  value: 'en-US',
});

Object.defineProperty(navigator, 'languages', {
  writable: true,
  value: ['en-US', 'en'],
});

// clipboard mock
Object.defineProperty(navigator, 'clipboard', {
  writable: true,
  value: {
    writeText: jest.fn().mockResolvedValue(undefined),
    readText: jest.fn().mockResolvedValue(''),
  },
});

// ====================================
// 🔊 MEDIA MOCKS
// ====================================

// HTMLMediaElement mock
Object.defineProperty(HTMLMediaElement.prototype, 'muted', {
  writable: true,
  value: false,
});

Object.defineProperty(HTMLMediaElement.prototype, 'play', {
  writable: true,
  value: jest.fn().mockResolvedValue(undefined),
});

Object.defineProperty(HTMLMediaElement.prototype, 'pause', {
  writable: true,
  value: jest.fn(),
});

Object.defineProperty(HTMLMediaElement.prototype, 'load', {
  writable: true,
  value: jest.fn(),
});

// ====================================
// 🎨 CANVAS & WEBGL MOCKS
// ====================================

// HTMLCanvasElement mock
Object.defineProperty(HTMLCanvasElement.prototype, 'getContext', {
  writable: true,
  value: jest.fn().mockReturnValue({
    fillRect: jest.fn(),
    clearRect: jest.fn(),
    getImageData: jest.fn().mockReturnValue({
      data: new Uint8ClampedArray(4),
    }),
    putImageData: jest.fn(),
    createImageData: jest.fn().mockReturnValue({}),
    setTransform: jest.fn(),
    drawImage: jest.fn(),
    save: jest.fn(),
    fillText: jest.fn(),
    restore: jest.fn(),
    beginPath: jest.fn(),
    moveTo: jest.fn(),
    lineTo: jest.fn(),
    closePath: jest.fn(),
    stroke: jest.fn(),
    translate: jest.fn(),
    scale: jest.fn(),
    rotate: jest.fn(),
    arc: jest.fn(),
    fill: jest.fn(),
    measureText: jest.fn().mockReturnValue({ width: 0 }),
    transform: jest.fn(),
    rect: jest.fn(),
    clip: jest.fn(),
  }),
});

// ====================================
// 🗄️ STORAGE MOCKS
// ====================================

// localStorage is already mocked by jest-localstorage-mock

// sessionStorage mock
const sessionStorageMock = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  clear: jest.fn(),
  length: 0,
  key: jest.fn(),
};

Object.defineProperty(window, 'sessionStorage', {
  writable: true,
  value: sessionStorageMock,
});

// ====================================
// 🔔 NOTIFICATION MOCKS
// ====================================

// Notification API mock
Object.defineProperty(window, 'Notification', {
  writable: true,
  value: jest.fn().mockImplementation(() => ({
    close: jest.fn(),
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

Object.defineProperty(Notification, 'permission', {
  writable: true,
  value: 'granted',
});

Object.defineProperty(Notification, 'requestPermission', {
  writable: true,
  value: jest.fn().mockResolvedValue('granted'),
});

// ====================================
// 🌐 LOCATION & HISTORY MOCKS
// ====================================

// Location mock enhancement
Object.defineProperty(window, 'location', {
  writable: true,
  value: {
    href: 'http://localhost:3000/',
    origin: 'http://localhost:3000',
    protocol: 'http:',
    host: 'localhost:3000',
    hostname: 'localhost',
    port: '3000',
    pathname: '/',
    search: '',
    hash: '',
    assign: jest.fn(),
    replace: jest.fn(),
    reload: jest.fn(),
  },
});

// ====================================
// 🧪 TESTING UTILITIES
// ====================================

// Custom error handler for unhandled promise rejections
process.on('unhandledRejection', (reason) => {
  console.error('Unhandled Rejection:', reason);
});

// Mock console methods in tests to reduce noise
const originalError = console.error;
beforeAll(() => {
  console.error = (...args: any[]) => {
    if (
      typeof args[0] === 'string' &&
      (args[0].includes('Warning: ReactDOM.render is deprecated') ||
        args[0].includes('Warning: componentWillReceiveProps has been renamed'))
    ) {
      return;
    }
    originalError.call(console, ...args);
  };
});

afterAll(() => {
  console.error = originalError;
});

// ====================================
// 🔄 TEST LIFECYCLE HOOKS
// ====================================

// Clear all mocks before each test
beforeEach(() => {
  jest.clearAllMocks();
  
  // Reset localStorage
  localStorage.clear();
  
  // Reset sessionStorage
  sessionStorageMock.getItem.mockClear();
  sessionStorageMock.setItem.mockClear();
  sessionStorageMock.removeItem.mockClear();
  sessionStorageMock.clear.mockClear();
  
  // Reset fetch mock
  (global.fetch as jest.Mock).mockClear();
});

// ====================================
// 📊 PERFORMANCE MONITORING
// ====================================

// Mock performance API
Object.defineProperty(window, 'performance', {
  writable: true,
  value: {
    now: jest.fn().mockReturnValue(Date.now()),
    mark: jest.fn(),
    measure: jest.fn(),
    getEntriesByName: jest.fn().mockReturnValue([]),
    getEntriesByType: jest.fn().mockReturnValue([]),
    clearMarks: jest.fn(),
    clearMeasures: jest.fn(),
    navigation: {
      type: 1,
    },
    timing: {
      navigationStart: Date.now(),
      loadEventEnd: Date.now() + 1000,
    },
  },
});

// ====================================
// 🌍 INTERNATIONALIZATION MOCKS
// ====================================

// Intl API enhancements
Object.defineProperty(Intl, 'DateTimeFormat', {
  writable: true,
  value: jest.fn().mockImplementation(() => ({
    format: jest.fn().mockReturnValue('1/1/2025'),
    formatToParts: jest.fn().mockReturnValue([]),
  })),
});

Object.defineProperty(Intl, 'NumberFormat', {
  writable: true,
  value: jest.fn().mockImplementation(() => ({
    format: jest.fn().mockReturnValue('1,000'),
    formatToParts: jest.fn().mockReturnValue([]),
  })),
});

// ====================================
// 🎯 CUSTOM MATCHERS
// ====================================

// Add custom Jest matchers
expect.extend({
  toBeInTheDocument: require('@testing-library/jest-dom/matchers').toBeInTheDocument,
  toHaveClass: require('@testing-library/jest-dom/matchers').toHaveClass,
  toHaveStyle: require('@testing-library/jest-dom/matchers').toHaveStyle,
  toBeVisible: require('@testing-library/jest-dom/matchers').toBeVisible,
  toBeDisabled: require('@testing-library/jest-dom/matchers').toBeDisabled,
  toHaveAttribute: require('@testing-library/jest-dom/matchers').toHaveAttribute,
  toHaveTextContent: require('@testing-library/jest-dom/matchers').toHaveTextContent,
});

console.log('🧪 Test environment setup completed successfully!'); 