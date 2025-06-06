// 🏭 MesChain-Sync Enterprise - Mock Data Factories
// Comprehensive mock data generation for testing

import { faker } from '@faker-js/faker';

// ====================================
// 🏢 MARKETPLACE DATA MOCKS
// ====================================

export const createMockMarketplace = (overrides: any = {}) => ({
  id: faker.string.uuid(),
  name: faker.helpers.arrayElement(['Trendyol', 'N11', 'Amazon', 'eBay', 'Hepsiburada', 'Ozon']),
  slug: faker.lorem.slug(),
  logo: faker.image.avatar(),
  isActive: faker.datatype.boolean(),
  settings: {
    apiKey: faker.string.alphanumeric(32),
    apiSecret: faker.string.alphanumeric(64),
    webhookUrl: faker.internet.url(),
    syncEnabled: faker.datatype.boolean(),
  },
  stats: {
    totalProducts: faker.number.int({ min: 0, max: 10000 }),
    activeProducts: faker.number.int({ min: 0, max: 5000 }),
    totalOrders: faker.number.int({ min: 0, max: 1000 }),
    totalRevenue: parseFloat(faker.commerce.price({ min: 0, max: 100000 })),
  },
  createdAt: faker.date.past().toISOString(),
  updatedAt: faker.date.recent().toISOString(),
  ...overrides,
});

export const createMockMarketplaceList = (count = 5) => 
  Array.from({ length: count }, () => createMockMarketplace());

// ====================================
// 🛍️ PRODUCT DATA MOCKS
// ====================================

export const createMockProduct = (overrides: any = {}) => ({
  id: faker.string.uuid(),
  sku: faker.string.alphanumeric(10).toUpperCase(),
  title: faker.commerce.productName(),
  description: faker.commerce.productDescription(),
  price: parseFloat(faker.commerce.price({ min: 10, max: 1000 })),
  comparePrice: parseFloat(faker.commerce.price({ min: 15, max: 1200 })),
  currency: faker.helpers.arrayElement(['TRY', 'USD', 'EUR']),
  quantity: faker.number.int({ min: 0, max: 100 }),
  images: Array.from({ length: faker.number.int({ min: 1, max: 5 }) }, () => ({
    id: faker.string.uuid(),
    url: faker.image.url({ width: 800, height: 600 }),
    alt: faker.lorem.words(3),
    isPrimary: faker.datatype.boolean(),
  })),
  category: {
    id: faker.string.uuid(),
    name: faker.commerce.department(),
    path: faker.lorem.slug(),
  },
  brand: {
    id: faker.string.uuid(),
    name: faker.company.name(),
    logo: faker.image.avatar(),
  },
  attributes: {
    color: faker.color.human(),
    size: faker.helpers.arrayElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
    material: faker.helpers.arrayElement(['Cotton', 'Polyester', 'Wool', 'Silk']),
    weight: `${faker.number.float({ min: 0.1, max: 5, precision: 0.1 })}kg`,
  },
  seo: {
    title: faker.lorem.words(8),
    description: faker.lorem.paragraph(),
    keywords: faker.lorem.words(10).split(' '),
  },
  marketplace: {
    trendyol: {
      id: faker.string.numeric(10),
      url: faker.internet.url(),
      status: faker.helpers.arrayElement(['active', 'inactive', 'pending']),
      lastSync: faker.date.recent().toISOString(),
    },
    n11: {
      id: faker.string.numeric(8),
      url: faker.internet.url(),
      status: faker.helpers.arrayElement(['active', 'inactive', 'pending']),
      lastSync: faker.date.recent().toISOString(),
    },
  },
  status: faker.helpers.arrayElement(['draft', 'active', 'inactive', 'archived']),
  isVisible: faker.datatype.boolean(),
  createdAt: faker.date.past().toISOString(),
  updatedAt: faker.date.recent().toISOString(),
  ...overrides,
});

export const createMockProductList = (count = 10) => 
  Array.from({ length: count }, () => createMockProduct());

// ====================================
// 🛒 ORDER DATA MOCKS
// ====================================

export const createMockOrderItem = (overrides: any = {}) => ({
  id: faker.string.uuid(),
  productId: faker.string.uuid(),
  sku: faker.string.alphanumeric(10).toUpperCase(),
  title: faker.commerce.productName(),
  quantity: faker.number.int({ min: 1, max: 5 }),
  price: parseFloat(faker.commerce.price({ min: 10, max: 500 })),
  total: 0, // Will be calculated
  image: faker.image.url({ width: 200, height: 200 }),
  ...overrides,
});

export const createMockOrder = (overrides: any = {}) => {
  const items = Array.from({ length: faker.number.int({ min: 1, max: 4 }) }, () => 
    createMockOrderItem()
  );
  
  // Calculate totals
  const subtotal = items.reduce((sum, item) => {
    item.total = item.price * item.quantity;
    return sum + item.total;
  }, 0);
  
  const tax = subtotal * 0.18; // 18% VAT
  const shipping = parseFloat(faker.commerce.price({ min: 0, max: 50 }));
  const total = subtotal + tax + shipping;

  return {
    id: faker.string.uuid(),
    orderNumber: `MSE-${faker.string.numeric(8)}`,
    marketplace: faker.helpers.arrayElement(['trendyol', 'n11', 'amazon', 'hepsiburada']),
    customer: {
      id: faker.string.uuid(),
      name: faker.person.fullName(),
      email: faker.internet.email(),
      phone: faker.phone.number(),
    },
    billing: {
      name: faker.person.fullName(),
      company: faker.company.name(),
      address: faker.location.streetAddress(),
      city: faker.location.city(),
      state: faker.location.state(),
      country: faker.location.country(),
      postalCode: faker.location.zipCode(),
    },
    shipping: {
      name: faker.person.fullName(),
      address: faker.location.streetAddress(),
      city: faker.location.city(),
      state: faker.location.state(),
      country: faker.location.country(),
      postalCode: faker.location.zipCode(),
      method: faker.helpers.arrayElement(['Standard', 'Express', 'Overnight']),
      trackingNumber: faker.string.alphanumeric(12).toUpperCase(),
    },
    items,
    pricing: {
      subtotal: parseFloat(subtotal.toFixed(2)),
      tax: parseFloat(tax.toFixed(2)),
      shipping: parseFloat(shipping.toFixed(2)),
      discount: 0,
      total: parseFloat(total.toFixed(2)),
      currency: 'TRY',
    },
    status: faker.helpers.arrayElement([
      'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'
    ]),
    paymentStatus: faker.helpers.arrayElement(['pending', 'paid', 'failed', 'refunded']),
    fulfillmentStatus: faker.helpers.arrayElement([
      'unfulfilled', 'partial', 'fulfilled', 'shipped', 'delivered'
    ]),
    notes: faker.lorem.paragraph(),
    tags: faker.lorem.words(3).split(' '),
    createdAt: faker.date.past().toISOString(),
    updatedAt: faker.date.recent().toISOString(),
    ...overrides,
  };
};

export const createMockOrderList = (count = 20) => 
  Array.from({ length: count }, () => createMockOrder());

// ====================================
// 👤 USER DATA MOCKS
// ====================================

export const createMockUser = (overrides: any = {}) => ({
  id: faker.string.uuid(),
  email: faker.internet.email(),
  username: faker.internet.userName(),
  firstName: faker.person.firstName(),
  lastName: faker.person.lastName(),
  fullName: faker.person.fullName(),
  avatar: faker.image.avatar(),
  role: faker.helpers.arrayElement(['admin', 'manager', 'user', 'viewer']),
  permissions: faker.helpers.arrayElements([
    'products.read', 'products.write', 'orders.read', 'orders.write',
    'analytics.read', 'settings.write', 'users.manage'
  ], { min: 2, max: 6 }),
  preferences: {
    language: faker.helpers.arrayElement(['en', 'tr', 'de', 'fr']),
    timezone: faker.location.timeZone(),
    theme: faker.helpers.arrayElement(['light', 'dark', 'auto']),
    notifications: {
      email: faker.datatype.boolean(),
      push: faker.datatype.boolean(),
      sms: faker.datatype.boolean(),
    },
  },
  profile: {
    company: faker.company.name(),
    department: faker.commerce.department(),
    title: faker.person.jobTitle(),
    phone: faker.phone.number(),
    address: {
      street: faker.location.streetAddress(),
      city: faker.location.city(),
      state: faker.location.state(),
      country: faker.location.country(),
      postalCode: faker.location.zipCode(),
    },
  },
  stats: {
    loginCount: faker.number.int({ min: 0, max: 1000 }),
    lastLogin: faker.date.recent().toISOString(),
    productsManaged: faker.number.int({ min: 0, max: 500 }),
    ordersProcessed: faker.number.int({ min: 0, max: 200 }),
  },
  isActive: faker.datatype.boolean({ probability: 0.9 }),
  isVerified: faker.datatype.boolean({ probability: 0.8 }),
  createdAt: faker.date.past().toISOString(),
  updatedAt: faker.date.recent().toISOString(),
  ...overrides,
});

export const createMockUserList = (count = 15) => 
  Array.from({ length: count }, () => createMockUser());

// ====================================
// 📊 ANALYTICS DATA MOCKS
// ====================================

export const createMockAnalyticsData = (overrides: any = {}) => ({
  period: faker.helpers.arrayElement(['today', 'week', 'month', 'quarter', 'year']),
  metrics: {
    totalRevenue: parseFloat(faker.commerce.price({ min: 10000, max: 500000 })),
    totalOrders: faker.number.int({ min: 100, max: 5000 }),
    averageOrderValue: parseFloat(faker.commerce.price({ min: 50, max: 300 })),
    conversionRate: parseFloat((faker.number.float({ min: 1, max: 15, precision: 0.01 })).toFixed(2)),
    customerRetention: parseFloat((faker.number.float({ min: 60, max: 95, precision: 0.01 })).toFixed(2)),
    productsSold: faker.number.int({ min: 500, max: 10000 }),
  },
  trends: {
    revenue: Array.from({ length: 30 }, (_, i) => ({
      date: faker.date.recent({ days: 30 - i }).toISOString().split('T')[0],
      value: parseFloat(faker.commerce.price({ min: 1000, max: 10000 })),
    })),
    orders: Array.from({ length: 30 }, (_, i) => ({
      date: faker.date.recent({ days: 30 - i }).toISOString().split('T')[0],
      value: faker.number.int({ min: 10, max: 100 }),
    })),
  },
  topProducts: Array.from({ length: 10 }, () => ({
    id: faker.string.uuid(),
    name: faker.commerce.productName(),
    sold: faker.number.int({ min: 10, max: 500 }),
    revenue: parseFloat(faker.commerce.price({ min: 500, max: 25000 })),
  })),
  topMarketplaces: Array.from({ length: 5 }, () => ({
    name: faker.helpers.arrayElement(['Trendyol', 'N11', 'Amazon', 'Hepsiburada']),
    orders: faker.number.int({ min: 50, max: 1000 }),
    revenue: parseFloat(faker.commerce.price({ min: 5000, max: 100000 })),
    percentage: parseFloat((faker.number.float({ min: 10, max: 40, precision: 0.1 })).toFixed(1)),
  })),
  ...overrides,
});

// ====================================
// 🔔 NOTIFICATION DATA MOCKS
// ====================================

export const createMockNotification = (overrides: any = {}) => ({
  id: faker.string.uuid(),
  type: faker.helpers.arrayElement(['info', 'success', 'warning', 'error']),
  title: faker.lorem.words(4),
  message: faker.lorem.sentence(),
  data: {
    userId: faker.string.uuid(),
    orderId: faker.string.uuid(),
    productId: faker.string.uuid(),
  },
  isRead: faker.datatype.boolean({ probability: 0.3 }),
  priority: faker.helpers.arrayElement(['low', 'medium', 'high', 'urgent']),
  category: faker.helpers.arrayElement([
    'order', 'product', 'system', 'security', 'billing', 'marketplace'
  ]),
  actions: [
    {
      label: 'View Details',
      url: faker.internet.url(),
      type: 'primary',
    },
    {
      label: 'Dismiss',
      action: 'dismiss',
      type: 'secondary',
    },
  ],
  createdAt: faker.date.recent().toISOString(),
  readAt: faker.datatype.boolean() ? faker.date.recent().toISOString() : null,
  expiresAt: faker.date.future().toISOString(),
  ...overrides,
});

export const createMockNotificationList = (count = 25) => 
  Array.from({ length: count }, () => createMockNotification());

// ====================================
// ⚙️ SETTINGS DATA MOCKS
// ====================================

export const createMockSettings = (overrides: any = {}) => ({
  general: {
    siteName: 'MesChain-Sync Enterprise',
    siteUrl: faker.internet.url(),
    supportEmail: faker.internet.email(),
    timezone: faker.location.timeZone(),
    currency: faker.helpers.arrayElement(['TRY', 'USD', 'EUR']),
    language: faker.helpers.arrayElement(['en', 'tr', 'de', 'fr']),
  },
  marketplace: {
    autoSync: faker.datatype.boolean(),
    syncInterval: faker.helpers.arrayElement([5, 15, 30, 60]),
    errorRetries: faker.number.int({ min: 1, max: 5 }),
    batchSize: faker.helpers.arrayElement([10, 25, 50, 100]),
  },
  notifications: {
    email: {
      enabled: faker.datatype.boolean(),
      host: 'smtp.gmail.com',
      port: 587,
      username: faker.internet.email(),
      password: faker.internet.password(),
    },
    slack: {
      enabled: faker.datatype.boolean(),
      webhookUrl: faker.internet.url(),
      channel: '#notifications',
    },
    webhook: {
      enabled: faker.datatype.boolean(),
      url: faker.internet.url(),
      secret: faker.string.alphanumeric(32),
    },
  },
  security: {
    twoFactorAuth: faker.datatype.boolean(),
    sessionTimeout: faker.helpers.arrayElement([30, 60, 120, 240]),
    passwordPolicy: {
      minLength: faker.number.int({ min: 8, max: 16 }),
      requireUppercase: faker.datatype.boolean(),
      requireNumbers: faker.datatype.boolean(),
      requireSymbols: faker.datatype.boolean(),
    },
  },
  integrations: {
    analytics: {
      googleAnalytics: {
        enabled: faker.datatype.boolean(),
        trackingId: `GA-${faker.string.numeric(9)}`,
      },
      mixpanel: {
        enabled: faker.datatype.boolean(),
        projectToken: faker.string.alphanumeric(32),
      },
    },
    payment: {
      stripe: {
        enabled: faker.datatype.boolean(),
        publicKey: faker.string.alphanumeric(64),
        secretKey: faker.string.alphanumeric(64),
      },
      paypal: {
        enabled: faker.datatype.boolean(),
        clientId: faker.string.alphanumeric(32),
        clientSecret: faker.string.alphanumeric(64),
      },
    },
  },
  ...overrides,
});

// ====================================
// 🎯 API RESPONSE MOCKS
// ====================================

export const createMockApiResponse = <T>(data: T, overrides: any = {}) => ({
  success: true,
  data,
  message: 'Request successful',
  timestamp: new Date().toISOString(),
  requestId: faker.string.uuid(),
  version: '1.0.0',
  ...overrides,
});

export const createMockApiError = (overrides: any = {}) => ({
  success: false,
  error: {
    code: faker.helpers.arrayElement(['VALIDATION_ERROR', 'NOT_FOUND', 'UNAUTHORIZED', 'SERVER_ERROR']),
    message: faker.lorem.sentence(),
    details: faker.lorem.paragraph(),
    field: faker.helpers.maybe(() => faker.lorem.word()),
  },
  timestamp: new Date().toISOString(),
  requestId: faker.string.uuid(),
  version: '1.0.0',
  ...overrides,
});

export const createMockPaginatedResponse = <T>(
  items: T[],
  page = 1,
  pageSize = 10,
  overrides: any = {}
) => {
  const total = items.length + faker.number.int({ min: 0, max: 100 });
  const totalPages = Math.ceil(total / pageSize);
  
  return createMockApiResponse({
    items: items.slice((page - 1) * pageSize, page * pageSize),
    pagination: {
      page,
      pageSize,
      total,
      totalPages,
      hasNext: page < totalPages,
      hasPrev: page > 1,
    },
  }, overrides);
};

// ====================================
// 🔄 SYNC STATUS MOCKS
// ====================================

export const createMockSyncStatus = (overrides: any = {}) => ({
  id: faker.string.uuid(),
  marketplace: faker.helpers.arrayElement(['trendyol', 'n11', 'amazon', 'hepsiburada']),
  type: faker.helpers.arrayElement(['product', 'order', 'inventory', 'pricing']),
  status: faker.helpers.arrayElement(['pending', 'running', 'completed', 'failed']),
  progress: faker.number.int({ min: 0, max: 100 }),
  totalItems: faker.number.int({ min: 10, max: 1000 }),
  processedItems: faker.number.int({ min: 0, max: 1000 }),
  successCount: faker.number.int({ min: 0, max: 1000 }),
  errorCount: faker.number.int({ min: 0, max: 50 }),
  errors: Array.from({ length: faker.number.int({ min: 0, max: 5 }) }, () => ({
    item: faker.lorem.word(),
    error: faker.lorem.sentence(),
    code: faker.string.alphanumeric(8),
  })),
  startedAt: faker.date.recent().toISOString(),
  completedAt: faker.helpers.maybe(() => faker.date.recent().toISOString()),
  duration: faker.number.int({ min: 10, max: 3600 }), // seconds
  ...overrides,
});

export const createMockSyncStatusList = (count = 10) => 
  Array.from({ length: count }, () => createMockSyncStatus());

// ====================================
// 🏪 UTILITY FUNCTIONS
// ====================================

/**
 * Create mock data based on type
 */
export const createMockData = (type: string, count = 1, overrides: any = {}) => {
  const factories: Record<string, Function> = {
    marketplace: createMockMarketplace,
    product: createMockProduct,
    order: createMockOrder,
    user: createMockUser,
    notification: createMockNotification,
    syncStatus: createMockSyncStatus,
    analytics: createMockAnalyticsData,
    settings: createMockSettings,
  };

  const factory = factories[type];
  if (!factory) {
    throw new Error(`Unknown mock type: ${type}`);
  }

  if (count === 1) {
    return factory(overrides);
  }

  return Array.from({ length: count }, () => factory(overrides));
};

/**
 * Reset faker seed for consistent testing
 */
export const resetMockSeed = (seed = 12345) => {
  faker.seed(seed);
};

/**
 * Generate test scenarios
 */
export const createTestScenarios = () => ({
  emptyState: {
    products: [],
    orders: [],
    notifications: [],
  },
  loadingState: {
    isLoading: true,
    data: null,
  },
  errorState: {
    error: createMockApiError(),
    data: null,
  },
  successState: {
    data: createMockProductList(5),
    error: null,
  },
}); 