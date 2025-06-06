// 🎭 MesChain-Sync Enterprise - Playwright E2E Test Configuration
// Comprehensive end-to-end testing setup

import { defineConfig, devices } from '@playwright/test';

/**
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  // Test directory
  testDir: './src/tests/e2e',
  
  // Global test timeout (30 seconds)
  timeout: 30000,
  
  // Test timeout for each assertion (5 seconds)
  expect: {
    timeout: 5000,
  },
  
  // Run tests in files in parallel
  fullyParallel: true,
  
  // Fail the build on CI if you accidentally left test.only in the source code
  forbidOnly: !!process.env.CI,
  
  // Retry on CI only
  retries: process.env.CI ? 2 : 0,
  
  // Opt out of parallel tests on CI
  workers: process.env.CI ? 1 : undefined,
  
  // Reporter configuration
  reporter: [
    // HTML reporter for detailed results
    ['html', { 
      outputFolder: 'playwright-report',
      open: 'never' 
    }],
    
    // JUnit reporter for CI integration
    ['junit', { 
      outputFile: 'test-results/junit.xml' 
    }],
    
    // JSON reporter for custom processing
    ['json', { 
      outputFile: 'test-results/results.json' 
    }],
    
    // List reporter for terminal output
    ['list'],
    
    // GitHub Actions reporter
    process.env.CI ? ['github'] : null,
  ].filter(Boolean),
  
  // Global setup and teardown
  globalSetup: require.resolve('./src/tests/e2e/global-setup.ts'),
  globalTeardown: require.resolve('./src/tests/e2e/global-teardown.ts'),
  
  // Shared settings for all projects
  use: {
    // Base URL for all tests
    baseURL: process.env.E2E_BASE_URL || 'http://localhost:3000',
    
    // Collect trace when retrying the failed test
    trace: 'on-first-retry',
    
    // Record video on failure
    video: 'retain-on-failure',
    
    // Take screenshot on failure
    screenshot: 'only-on-failure',
    
    // Browser context options
    ignoreHTTPSErrors: true,
    
    // Action timeout (10 seconds)
    actionTimeout: 10000,
    
    // Navigation timeout (30 seconds)
    navigationTimeout: 30000,
    
    // Locale for testing
    locale: 'en-US',
    
    // Timezone
    timezoneId: 'Europe/Istanbul',
    
    // Color scheme
    colorScheme: 'light',
    
    // Viewport size
    viewport: { width: 1280, height: 720 },
    
    // User agent
    userAgent: 'MesChain-Sync-E2E-Tests/1.0.0',
    
    // Extra HTTP headers
    extraHTTPHeaders: {
      'Accept-Language': 'en-US,en;q=0.9,tr;q=0.8',
      'X-Test-Runner': 'playwright',
    },
  },

  // Test projects for different browsers/scenarios
  projects: [
    // ====================================
    // 🌐 DESKTOP BROWSERS
    // ====================================
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },

    {
      name: 'chromium',
      dependencies: ['setup'],
      use: { 
        ...devices['Desktop Chrome'],
        // Channel for stable Chrome
        channel: 'chrome',
      },
    },

    {
      name: 'firefox',
      dependencies: ['setup'],
      use: { ...devices['Desktop Firefox'] },
    },

    {
      name: 'webkit',
      dependencies: ['setup'], 
      use: { ...devices['Desktop Safari'] },
    },

    // ====================================
    // 📱 MOBILE BROWSERS
    // ====================================
    {
      name: 'Mobile Chrome',
      dependencies: ['setup'],
      use: { ...devices['Pixel 5'] },
    },

    {
      name: 'Mobile Safari',
      dependencies: ['setup'],
      use: { ...devices['iPhone 12'] },
    },

    // ====================================
    // 🎨 THEME VARIATIONS
    // ====================================
    {
      name: 'dark-theme',
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        colorScheme: 'dark',
      },
    },

    // ====================================
    // 🌍 DIFFERENT LOCALES
    // ====================================
    {
      name: 'turkish-locale',
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        locale: 'tr-TR',
        timezoneId: 'Europe/Istanbul',
      },
    },

    // ====================================
    // 🔐 AUTHENTICATED TESTS
    // ====================================
    {
      name: 'authenticated',
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/user.json',
      },
      testMatch: /.*\.auth\.spec\.ts/,
    },

    // ====================================
    // 📊 PERFORMANCE TESTS
    // ====================================
    {
      name: 'performance',
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        // Slow network simulation
        launchOptions: {
          args: ['--disable-web-security', '--disable-features=VizDisplayCompositor'],
        },
      },
      testMatch: /.*\.perf\.spec\.ts/,
    },

    // ====================================
    // ♿ ACCESSIBILITY TESTS
    // ====================================
    {
      name: 'accessibility',
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        // Enable accessibility testing
        launchOptions: {
          args: ['--force-prefers-reduced-motion'],
        },
      },
      testMatch: /.*\.a11y\.spec\.ts/,
    },

    // ====================================
    // 🎯 API TESTING
    // ====================================
    {
      name: 'api',
      use: {
        baseURL: process.env.API_BASE_URL || 'http://localhost:3001/api',
        extraHTTPHeaders: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      },
      testMatch: /.*\.api\.spec\.ts/,
    },
  ],

  // ====================================
  // 🔧 ADVANCED CONFIGURATION
  // ====================================

  // Output directory for test artifacts
  outputDir: 'test-results',

  // Maximum time one test can run for (5 minutes)
  globalTimeout: 5 * 60 * 1000,

  // Directory for test fixtures
  testIgnore: [
    '**/node_modules/**',
    '**/dist/**',
    '**/build/**',
    '**/.next/**',
  ],

  // Web server configuration
  webServer: process.env.CI
    ? undefined
    : {
        command: 'npm run dev',
        url: 'http://localhost:3000',
        reuseExistingServer: !process.env.CI,
        timeout: 120 * 1000, // 2 minutes
        env: {
          NODE_ENV: 'test',
          PORT: '3000',
        },
      },

  // Test metadata
  metadata: {
    project: 'MesChain-Sync Enterprise',
    version: process.env.npm_package_version || '1.0.0',
    environment: process.env.NODE_ENV || 'test',
    testSuite: 'E2E Tests',
  },
}); 