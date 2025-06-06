/**
 * Currency Conversion Service - Global currency management and conversion
 * Handles real-time rates, historical data, tax calculations, and multi-provider aggregation
 * 
 * @author MesChain Team
 * @version 3.0.0
 * @since 2025-01-15
 */

import { EventEmitter } from 'events';
import axios, { AxiosInstance } from 'axios';
import { performance } from 'perf_hooks';

// Types and Interfaces
export interface Currency {
  code: string;
  name: string;
  symbol: string;
  decimals: number;
  region: string;
  countries: string[];
  type: 'FIAT' | 'CRYPTO' | 'COMMODITY';
  status: 'ACTIVE' | 'DEPRECATED' | 'UNSTABLE';
  lastUpdated: Date;
}

export interface ExchangeRate {
  from: string;
  to: string;
  rate: number;
  inverse: number;
  timestamp: Date;
  source: string;
  bid: number;
  ask: number;
  spread: number;
  change24h: number;
  change7d: number;
  volatility: number;
  confidence: number;
}

export interface ConversionResult {
  originalAmount: number;
  convertedAmount: number;
  fromCurrency: string;
  toCurrency: string;
  rate: number;
  fees: {
    conversionFee: number;
    providerFee: number;
    totalFee: number;
  };
  taxes: {
    vatRate: number;
    vatAmount: number;
    totalTax: number;
  };
  finalAmount: number;
  timestamp: Date;
  source: string;
  metadata: {
    rateAge: number;
    confidence: number;
    route: string[];
  };
}

export interface RateProvider {
  id: string;
  name: string;
  url: string;
  apiKey?: string;
  priority: number;
  weight: number;
  reliability: number;
  latency: number;
  supportedCurrencies: string[];
  features: {
    realTime: boolean;
    historical: boolean;
    crypto: boolean;
    commodities: boolean;
    freeQuota: number;
    paidQuota: number;
  };
  status: 'ACTIVE' | 'INACTIVE' | 'ERROR';
  lastUpdate: Date;
  errorCount: number;
}

export interface HistoricalRate {
  currency: string;
  baseCurrency: string;
  date: Date;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
  source: string;
}

export interface TaxRule {
  country: string;
  region: string;
  type: 'VAT' | 'GST' | 'SALES_TAX' | 'EXCISE' | 'CUSTOMS';
  rate: number;
  threshold: number;
  currency: string;
  applicableCategories: string[];
  exemptions: string[];
  effectiveDate: Date;
  endDate?: Date;
}

export interface ConversionCache {
  key: string;
  result: ConversionResult;
  timestamp: Date;
  ttl: number;
  hits: number;
}

export class CurrencyConversionService extends EventEmitter {
  private currencies: Map<string, Currency> = new Map();
  private rates: Map<string, ExchangeRate> = new Map();
  private providers: Map<string, RateProvider> = new Map();
  private historicalRates: Map<string, HistoricalRate[]> = new Map();
  private taxRules: Map<string, TaxRule[]> = new Map();
  private conversionCache: Map<string, ConversionCache> = new Map();
  private updateInterval: NodeJS.Timeout | null = null;
  private cacheCleanupInterval: NodeJS.Timeout | null = null;

  constructor() {
    super();
    this.initializeCurrencies();
    this.initializeProviders();
    this.initializeTaxRules();
    this.startRateUpdates();
    this.startCacheCleanup();
  }

  /**
   * Initialize supported currencies
   */
  private initializeCurrencies(): void {
    const currencies: Currency[] = [
      // Major Fiat Currencies
      {
        code: 'USD',
        name: 'US Dollar',
        symbol: '$',
        decimals: 2,
        region: 'AMERICAS',
        countries: ['US', 'EC', 'SV', 'ZW'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'EUR',
        name: 'Euro',
        symbol: '€',
        decimals: 2,
        region: 'EUROPE',
        countries: ['DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'PT', 'IE', 'GR', 'FI', 'LU', 'SI', 'CY', 'MT', 'SK', 'EE', 'LV', 'LT'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'GBP',
        name: 'British Pound Sterling',
        symbol: '£',
        decimals: 2,
        region: 'EUROPE',
        countries: ['GB', 'JE', 'GG', 'IM'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'JPY',
        name: 'Japanese Yen',
        symbol: '¥',
        decimals: 0,
        region: 'ASIA',
        countries: ['JP'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'CNY',
        name: 'Chinese Yuan',
        symbol: '¥',
        decimals: 2,
        region: 'ASIA',
        countries: ['CN'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'TRY',
        name: 'Turkish Lira',
        symbol: '₺',
        decimals: 2,
        region: 'EUROPE',
        countries: ['TR'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      // Regional Currencies
      {
        code: 'SGD',
        name: 'Singapore Dollar',
        symbol: 'S$',
        decimals: 2,
        region: 'ASIA',
        countries: ['SG'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'PLN',
        name: 'Polish Zloty',
        symbol: 'zł',
        decimals: 2,
        region: 'EUROPE',
        countries: ['PL'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'BRL',
        name: 'Brazilian Real',
        symbol: 'R$',
        decimals: 2,
        region: 'AMERICAS',
        countries: ['BR'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'INR',
        name: 'Indian Rupee',
        symbol: '₹',
        decimals: 2,
        region: 'ASIA',
        countries: ['IN', 'BT'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'AUD',
        name: 'Australian Dollar',
        symbol: 'A$',
        decimals: 2,
        region: 'OCEANIA',
        countries: ['AU', 'CX', 'CC', 'HM', 'KI', 'NR', 'NF', 'TV'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'CAD',
        name: 'Canadian Dollar',
        symbol: 'C$',
        decimals: 2,
        region: 'AMERICAS',
        countries: ['CA'],
        type: 'FIAT',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      // Cryptocurrencies
      {
        code: 'BTC',
        name: 'Bitcoin',
        symbol: '₿',
        decimals: 8,
        region: 'GLOBAL',
        countries: [],
        type: 'CRYPTO',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'ETH',
        name: 'Ethereum',
        symbol: 'Ξ',
        decimals: 18,
        region: 'GLOBAL',
        countries: [],
        type: 'CRYPTO',
        status: 'ACTIVE',
        lastUpdated: new Date()
      },
      {
        code: 'USDT',
        name: 'Tether',
        symbol: '₮',
        decimals: 6,
        region: 'GLOBAL',
        countries: [],
        type: 'CRYPTO',
        status: 'ACTIVE',
        lastUpdated: new Date()
      }
    ];

    currencies.forEach(currency => {
      this.currencies.set(currency.code, currency);
    });

    console.log(`✅ Initialized ${currencies.length} currencies`);
  }

  /**
   * Initialize rate providers
   */
  private initializeProviders(): void {
    const providers: RateProvider[] = [
      {
        id: 'exchangerate_api',
        name: 'ExchangeRate-API',
        url: 'https://api.exchangerate-api.com/v4/latest',
        priority: 1,
        weight: 30,
        reliability: 95,
        latency: 150,
        supportedCurrencies: ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'TRY', 'SGD', 'PLN', 'BRL', 'INR', 'AUD', 'CAD'],
        features: {
          realTime: true,
          historical: true,
          crypto: false,
          commodities: false,
          freeQuota: 1500,
          paidQuota: 100000
        },
        status: 'ACTIVE',
        lastUpdate: new Date(),
        errorCount: 0
      },
      {
        id: 'fixer_io',
        name: 'Fixer.io',
        url: 'https://api.fixer.io/latest',
        priority: 2,
        weight: 25,
        reliability: 92,
        latency: 200,
        supportedCurrencies: ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'TRY', 'SGD', 'PLN', 'BRL', 'INR', 'AUD', 'CAD'],
        features: {
          realTime: true,
          historical: true,
          crypto: false,
          commodities: false,
          freeQuota: 100,
          paidQuota: 50000
        },
        status: 'ACTIVE',
        lastUpdate: new Date(),
        errorCount: 0
      },
      {
        id: 'currencylayer',
        name: 'CurrencyLayer',
        url: 'https://api.currencylayer.com/live',
        priority: 3,
        weight: 20,
        reliability: 88,
        latency: 250,
        supportedCurrencies: ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'TRY', 'SGD', 'PLN', 'BRL', 'INR', 'AUD', 'CAD'],
        features: {
          realTime: true,
          historical: true,
          crypto: false,
          commodities: false,
          freeQuota: 1000,
          paidQuota: 1000000
        },
        status: 'ACTIVE',
        lastUpdate: new Date(),
        errorCount: 0
      },
      {
        id: 'coinapi',
        name: 'CoinAPI',
        url: 'https://rest.coinapi.io/v1/exchangerate',
        priority: 4,
        weight: 15,
        reliability: 90,
        latency: 180,
        supportedCurrencies: ['BTC', 'ETH', 'USDT', 'USD', 'EUR'],
        features: {
          realTime: true,
          historical: true,
          crypto: true,
          commodities: false,
          freeQuota: 100,
          paidQuota: 100000
        },
        status: 'ACTIVE',
        lastUpdate: new Date(),
        errorCount: 0
      },
      {
        id: 'central_bank_ecb',
        name: 'European Central Bank',
        url: 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml',
        priority: 5,
        weight: 10,
        reliability: 99,
        latency: 500,
        supportedCurrencies: ['EUR', 'USD', 'GBP', 'JPY', 'CNY', 'TRY', 'PLN'],
        features: {
          realTime: false,
          historical: true,
          crypto: false,
          commodities: false,
          freeQuota: Number.MAX_VALUE,
          paidQuota: Number.MAX_VALUE
        },
        status: 'ACTIVE',
        lastUpdate: new Date(),
        errorCount: 0
      }
    ];

    providers.forEach(provider => {
      this.providers.set(provider.id, provider);
    });

    console.log(`✅ Initialized ${providers.length} rate providers`);
  }

  /**
   * Initialize tax rules
   */
  private initializeTaxRules(): void {
    const taxRules: TaxRule[] = [
      // European Union VAT
      {
        country: 'DE',
        region: 'EU',
        type: 'VAT',
        rate: 19,
        threshold: 0,
        currency: 'EUR',
        applicableCategories: ['goods', 'services'],
        exemptions: ['food', 'medicine', 'books'],
        effectiveDate: new Date('2007-01-01')
      },
      {
        country: 'FR',
        region: 'EU',
        type: 'VAT',
        rate: 20,
        threshold: 0,
        currency: 'EUR',
        applicableCategories: ['goods', 'services'],
        exemptions: ['food', 'medicine', 'books'],
        effectiveDate: new Date('2014-01-01')
      },
      {
        country: 'TR',
        region: 'EU',
        type: 'VAT',
        rate: 18,
        threshold: 0,
        currency: 'TRY',
        applicableCategories: ['goods', 'services'],
        exemptions: ['food', 'medicine', 'education'],
        effectiveDate: new Date('2023-01-01')
      },
      // United States Sales Tax
      {
        country: 'US',
        region: 'AMERICAS',
        type: 'SALES_TAX',
        rate: 8.5,
        threshold: 0,
        currency: 'USD',
        applicableCategories: ['goods'],
        exemptions: ['food', 'medicine'],
        effectiveDate: new Date('2020-01-01')
      },
      // United Kingdom VAT
      {
        country: 'GB',
        region: 'EUROPE',
        type: 'VAT',
        rate: 20,
        threshold: 85000,
        currency: 'GBP',
        applicableCategories: ['goods', 'services'],
        exemptions: ['food', 'medicine', 'books', 'children_clothes'],
        effectiveDate: new Date('2011-01-04')
      },
      // Singapore GST
      {
        country: 'SG',
        region: 'ASIA',
        type: 'GST',
        rate: 8,
        threshold: 1000000,
        currency: 'SGD',
        applicableCategories: ['goods', 'services'],
        exemptions: ['residential_property', 'financial_services'],
        effectiveDate: new Date('2023-01-01')
      },
      // Brazil Tax
      {
        country: 'BR',
        region: 'AMERICAS',
        type: 'VAT',
        rate: 17,
        threshold: 0,
        currency: 'BRL',
        applicableCategories: ['goods', 'services'],
        exemptions: ['medicine', 'education'],
        effectiveDate: new Date('2020-01-01')
      },
      // Australia GST
      {
        country: 'AU',
        region: 'OCEANIA',
        type: 'GST',
        rate: 10,
        threshold: 75000,
        currency: 'AUD',
        applicableCategories: ['goods', 'services'],
        exemptions: ['food', 'health', 'education'],
        effectiveDate: new Date('2000-07-01')
      }
    ];

    // Group tax rules by country
    taxRules.forEach(rule => {
      if (!this.taxRules.has(rule.country)) {
        this.taxRules.set(rule.country, []);
      }
      this.taxRules.get(rule.country)!.push(rule);
    });

    console.log(`✅ Initialized tax rules for ${this.taxRules.size} countries`);
  }

  /**
   * Start rate updates
   */
  private startRateUpdates(): void {
    this.updateRates();
    
    // Update rates every 5 minutes
    this.updateInterval = setInterval(() => {
      this.updateRates();
    }, 5 * 60 * 1000);

    console.log('✅ Started rate update scheduler');
  }

  /**
   * Start cache cleanup
   */
  private startCacheCleanup(): void {
    // Clean cache every hour
    this.cacheCleanupInterval = setInterval(() => {
      this.cleanCache();
    }, 60 * 60 * 1000);

    console.log('✅ Started cache cleanup scheduler');
  }

  /**
   * Update exchange rates from providers
   */
  private async updateRates(): Promise<void> {
    const startTime = performance.now();
    let successCount = 0;
    let errorCount = 0;

    for (const [providerId, provider] of this.providers) {
      if (provider.status !== 'ACTIVE') continue;

      try {
        const rates = await this.fetchRatesFromProvider(provider);
        
        for (const rate of rates) {
          const key = `${rate.from}_${rate.to}`;
          const existing = this.rates.get(key);
          
          // Calculate 24h change
          if (existing) {
            rate.change24h = ((rate.rate - existing.rate) / existing.rate) * 100;
          }

          this.rates.set(key, rate);
        }

        provider.lastUpdate = new Date();
        provider.errorCount = Math.max(0, provider.errorCount - 1);
        successCount++;

      } catch (error) {
        provider.errorCount++;
        errorCount++;
        
        if (provider.errorCount >= 5) {
          provider.status = 'ERROR';
          console.warn(`⚠️ Provider ${providerId} marked as ERROR due to repeated failures`);
        }
        
        console.error(`❌ Failed to update rates from ${providerId}:`, error.message);
      }
    }

    const duration = performance.now() - startTime;
    console.log(`💱 Rate update completed: ${successCount} success, ${errorCount} errors (${duration.toFixed(0)}ms)`);
    
    this.emit('rates:updated', {
      totalRates: this.rates.size,
      successfulProviders: successCount,
      failedProviders: errorCount,
      duration
    });
  }

  /**
   * Fetch rates from a specific provider
   */
  private async fetchRatesFromProvider(provider: RateProvider): Promise<ExchangeRate[]> {
    const rates: ExchangeRate[] = [];

    // Mock implementation - in production, implement actual API calls
    switch (provider.id) {
      case 'exchangerate_api':
        return this.fetchFromExchangeRateAPI(provider);
      case 'fixer_io':
        return this.fetchFromFixerIO(provider);
      case 'currencylayer':
        return this.fetchFromCurrencyLayer(provider);
      case 'coinapi':
        return this.fetchFromCoinAPI(provider);
      default:
        return this.generateMockRates(provider);
    }
  }

  /**
   * Generate mock rates for demo purposes
   */
  private generateMockRates(provider: RateProvider): ExchangeRate[] {
    const rates: ExchangeRate[] = [];
    const baseCurrency = 'USD';
    
    const mockRates: Record<string, number> = {
      'USD_EUR': 0.85 + (Math.random() - 0.5) * 0.02,
      'USD_GBP': 0.73 + (Math.random() - 0.5) * 0.02,
      'USD_JPY': 110 + (Math.random() - 0.5) * 2,
      'USD_CNY': 7.2 + (Math.random() - 0.5) * 0.1,
      'USD_TRY': 27.5 + (Math.random() - 0.5) * 1.0,
      'USD_SGD': 1.35 + (Math.random() - 0.5) * 0.02,
      'USD_PLN': 4.2 + (Math.random() - 0.5) * 0.1,
      'USD_BRL': 5.8 + (Math.random() - 0.5) * 0.2,
      'USD_INR': 83.2 + (Math.random() - 0.5) * 1.0,
      'USD_AUD': 1.48 + (Math.random() - 0.5) * 0.02,
      'USD_CAD': 1.33 + (Math.random() - 0.5) * 0.02
    };

    // Add crypto rates if supported
    if (provider.features.crypto) {
      mockRates['USD_BTC'] = 0.000023 + (Math.random() - 0.5) * 0.000002;
      mockRates['USD_ETH'] = 0.00041 + (Math.random() - 0.5) * 0.00004;
      mockRates['USD_USDT'] = 1.0 + (Math.random() - 0.5) * 0.001;
    }

    for (const [pair, rate] of Object.entries(mockRates)) {
      const [from, to] = pair.split('_');
      
      if (provider.supportedCurrencies.includes(from) && provider.supportedCurrencies.includes(to)) {
        const spread = 0.001; // 0.1% spread
        const bid = rate * (1 - spread);
        const ask = rate * (1 + spread);

        rates.push({
          from,
          to,
          rate,
          inverse: 1 / rate,
          timestamp: new Date(),
          source: provider.id,
          bid,
          ask,
          spread: (ask - bid) / rate * 100,
          change24h: (Math.random() - 0.5) * 5, // -2.5% to +2.5%
          change7d: (Math.random() - 0.5) * 15, // -7.5% to +7.5%
          volatility: Math.random() * 10, // 0-10%
          confidence: 95 + Math.random() * 5 // 95-100%
        });

        // Add reverse rate
        rates.push({
          from: to,
          to: from,
          rate: 1 / rate,
          inverse: rate,
          timestamp: new Date(),
          source: provider.id,
          bid: 1 / ask,
          ask: 1 / bid,
          spread: (ask - bid) / rate * 100,
          change24h: -(Math.random() - 0.5) * 5,
          change7d: -(Math.random() - 0.5) * 15,
          volatility: Math.random() * 10,
          confidence: 95 + Math.random() * 5
        });
      }
    }

    return rates;
  }

  /**
   * Mock API implementations
   */
  private async fetchFromExchangeRateAPI(provider: RateProvider): Promise<ExchangeRate[]> {
    // Mock implementation - in production, implement actual API call
    return this.generateMockRates(provider);
  }

  private async fetchFromFixerIO(provider: RateProvider): Promise<ExchangeRate[]> {
    // Mock implementation - in production, implement actual API call
    return this.generateMockRates(provider);
  }

  private async fetchFromCurrencyLayer(provider: RateProvider): Promise<ExchangeRate[]> {
    // Mock implementation - in production, implement actual API call
    return this.generateMockRates(provider);
  }

  private async fetchFromCoinAPI(provider: RateProvider): Promise<ExchangeRate[]> {
    // Mock implementation - in production, implement actual API call
    return this.generateMockRates(provider);
  }

  /**
   * Convert currency with all calculations
   */
  public async convert(
    amount: number,
    fromCurrency: string,
    toCurrency: string,
    options: {
      country?: string;
      category?: string;
      includeTax?: boolean;
      includeFeatures?: boolean;
      preferredProvider?: string;
    } = {}
  ): Promise<ConversionResult> {
    const cacheKey = `${amount}_${fromCurrency}_${toCurrency}_${JSON.stringify(options)}`;
    
    // Check cache first
    const cached = this.conversionCache.get(cacheKey);
    if (cached && (Date.now() - cached.timestamp.getTime()) < cached.ttl) {
      cached.hits++;
      return cached.result;
    }

    if (fromCurrency === toCurrency) {
      const result: ConversionResult = {
        originalAmount: amount,
        convertedAmount: amount,
        fromCurrency,
        toCurrency,
        rate: 1,
        fees: {
          conversionFee: 0,
          providerFee: 0,
          totalFee: 0
        },
        taxes: {
          vatRate: 0,
          vatAmount: 0,
          totalTax: 0
        },
        finalAmount: amount,
        timestamp: new Date(),
        source: 'direct',
        metadata: {
          rateAge: 0,
          confidence: 100,
          route: [fromCurrency, toCurrency]
        }
      };
      return result;
    }

    // Get exchange rate
    const rate = await this.getExchangeRate(fromCurrency, toCurrency, options.preferredProvider);
    const convertedAmount = amount * rate.rate;

    // Calculate fees
    const fees = this.calculateFees(convertedAmount, fromCurrency, toCurrency);

    // Calculate taxes
    const taxes = options.includeTax && options.country 
      ? this.calculateTaxes(convertedAmount, options.country, options.category || 'goods')
      : { vatRate: 0, vatAmount: 0, totalTax: 0 };

    const finalAmount = convertedAmount + fees.totalFee + taxes.totalTax;

    const result: ConversionResult = {
      originalAmount: amount,
      convertedAmount,
      fromCurrency,
      toCurrency,
      rate: rate.rate,
      fees,
      taxes,
      finalAmount,
      timestamp: new Date(),
      source: rate.source,
      metadata: {
        rateAge: Date.now() - rate.timestamp.getTime(),
        confidence: rate.confidence,
        route: [fromCurrency, toCurrency]
      }
    };

    // Cache the result
    this.conversionCache.set(cacheKey, {
      key: cacheKey,
      result,
      timestamp: new Date(),
      ttl: 5 * 60 * 1000, // 5 minutes
      hits: 1
    });

    return result;
  }

  /**
   * Get exchange rate between two currencies
   */
  private async getExchangeRate(
    fromCurrency: string,
    toCurrency: string,
    preferredProvider?: string
  ): Promise<ExchangeRate> {
    const directKey = `${fromCurrency}_${toCurrency}`;
    const reverseKey = `${toCurrency}_${fromCurrency}`;

    // Try direct rate first
    let rate = this.rates.get(directKey);
    if (rate) {
      return rate;
    }

    // Try reverse rate
    rate = this.rates.get(reverseKey);
    if (rate) {
      return {
        ...rate,
        from: fromCurrency,
        to: toCurrency,
        rate: rate.inverse,
        inverse: rate.rate,
        bid: 1 / rate.ask,
        ask: 1 / rate.bid
      };
    }

    // Try triangular arbitrage through USD
    if (fromCurrency !== 'USD' && toCurrency !== 'USD') {
      const fromUsdRate = this.rates.get(`${fromCurrency}_USD`);
      const toUsdRate = this.rates.get(`USD_${toCurrency}`);
      
      if (fromUsdRate && toUsdRate) {
        const triangularRate = fromUsdRate.rate * toUsdRate.rate;
        return {
          from: fromCurrency,
          to: toCurrency,
          rate: triangularRate,
          inverse: 1 / triangularRate,
          timestamp: new Date(Math.min(fromUsdRate.timestamp.getTime(), toUsdRate.timestamp.getTime())),
          source: 'triangular',
          bid: triangularRate * 0.999,
          ask: triangularRate * 1.001,
          spread: 0.2,
          change24h: (fromUsdRate.change24h + toUsdRate.change24h) / 2,
          change7d: (fromUsdRate.change7d + toUsdRate.change7d) / 2,
          volatility: Math.max(fromUsdRate.volatility, toUsdRate.volatility),
          confidence: Math.min(fromUsdRate.confidence, toUsdRate.confidence)
        };
      }
    }

    throw new Error(`Exchange rate not available: ${fromCurrency} -> ${toCurrency}`);
  }

  /**
   * Calculate conversion fees
   */
  private calculateFees(amount: number, fromCurrency: string, toCurrency: string): {
    conversionFee: number;
    providerFee: number;
    totalFee: number;
  } {
    // Base conversion fee: 0.5%
    const conversionFee = amount * 0.005;
    
    // Provider fee varies by currency pair and amount
    let providerFeeRate = 0.001; // 0.1%
    
    // Higher fees for exotic currencies
    const exoticCurrencies = ['TRY', 'BRL', 'INR'];
    if (exoticCurrencies.includes(fromCurrency) || exoticCurrencies.includes(toCurrency)) {
      providerFeeRate += 0.002; // +0.2%
    }

    // Higher fees for crypto
    const cryptoCurrencies = ['BTC', 'ETH', 'USDT'];
    if (cryptoCurrencies.includes(fromCurrency) || cryptoCurrencies.includes(toCurrency)) {
      providerFeeRate += 0.005; // +0.5%
    }

    // Volume discounts
    if (amount > 10000) {
      providerFeeRate *= 0.8; // 20% discount for large amounts
    } else if (amount > 1000) {
      providerFeeRate *= 0.9; // 10% discount for medium amounts
    }

    const providerFee = amount * providerFeeRate;
    const totalFee = conversionFee + providerFee;

    return {
      conversionFee,
      providerFee,
      totalFee
    };
  }

  /**
   * Calculate taxes based on country and category
   */
  private calculateTaxes(amount: number, country: string, category: string): {
    vatRate: number;
    vatAmount: number;
    totalTax: number;
  } {
    const countryRules = this.taxRules.get(country.toUpperCase());
    if (!countryRules) {
      return { vatRate: 0, vatAmount: 0, totalTax: 0 };
    }

    // Find applicable tax rule
    const applicableRule = countryRules.find(rule => 
      rule.applicableCategories.includes(category) &&
      !rule.exemptions.includes(category) &&
      rule.effectiveDate <= new Date() &&
      (!rule.endDate || rule.endDate > new Date())
    );

    if (!applicableRule) {
      return { vatRate: 0, vatAmount: 0, totalTax: 0 };
    }

    // Check threshold
    if (amount < applicableRule.threshold) {
      return { vatRate: 0, vatAmount: 0, totalTax: 0 };
    }

    const vatRate = applicableRule.rate;
    const vatAmount = amount * (vatRate / 100);
    const totalTax = vatAmount;

    return {
      vatRate,
      vatAmount,
      totalTax
    };
  }

  /**
   * Get historical rates
   */
  public getHistoricalRates(
    currency: string,
    baseCurrency: string = 'USD',
    days: number = 30
  ): HistoricalRate[] {
    const key = `${currency}_${baseCurrency}`;
    const rates = this.historicalRates.get(key) || [];
    
    const endDate = new Date();
    const startDate = new Date(endDate.getTime() - (days * 24 * 60 * 60 * 1000));
    
    return rates.filter(rate => 
      rate.date >= startDate && rate.date <= endDate
    ).sort((a, b) => a.date.getTime() - b.date.getTime());
  }

  /**
   * Get supported currencies
   */
  public getSupportedCurrencies(type?: 'FIAT' | 'CRYPTO' | 'COMMODITY'): Currency[] {
    let currencies = Array.from(this.currencies.values());
    
    if (type) {
      currencies = currencies.filter(c => c.type === type);
    }
    
    return currencies.filter(c => c.status === 'ACTIVE');
  }

  /**
   * Get currency by code
   */
  public getCurrency(code: string): Currency | null {
    return this.currencies.get(code.toUpperCase()) || null;
  }

  /**
   * Get current rates summary
   */
  public getRatesSummary(): {
    totalRates: number;
    lastUpdate: Date;
    providers: {
      active: number;
      inactive: number;
      error: number;
    };
    currencies: {
      fiat: number;
      crypto: number;
      total: number;
    };
    averageSpread: number;
  } {
    const rates = Array.from(this.rates.values());
    const providers = Array.from(this.providers.values());
    const currencies = Array.from(this.currencies.values());

    const activeProviders = providers.filter(p => p.status === 'ACTIVE').length;
    const inactiveProviders = providers.filter(p => p.status === 'INACTIVE').length;
    const errorProviders = providers.filter(p => p.status === 'ERROR').length;

    const fiatCurrencies = currencies.filter(c => c.type === 'FIAT' && c.status === 'ACTIVE').length;
    const cryptoCurrencies = currencies.filter(c => c.type === 'CRYPTO' && c.status === 'ACTIVE').length;

    const averageSpread = rates.length > 0 
      ? rates.reduce((sum, rate) => sum + rate.spread, 0) / rates.length 
      : 0;

    const lastUpdate = rates.length > 0 
      ? new Date(Math.max(...rates.map(r => r.timestamp.getTime())))
      : new Date();

    return {
      totalRates: rates.length,
      lastUpdate,
      providers: {
        active: activeProviders,
        inactive: inactiveProviders,
        error: errorProviders
      },
      currencies: {
        fiat: fiatCurrencies,
        crypto: cryptoCurrencies,
        total: fiatCurrencies + cryptoCurrencies
      },
      averageSpread
    };
  }

  /**
   * Clean expired cache entries
   */
  private cleanCache(): void {
    const now = Date.now();
    let cleanedCount = 0;

    for (const [key, cache] of this.conversionCache) {
      if ((now - cache.timestamp.getTime()) > cache.ttl) {
        this.conversionCache.delete(key);
        cleanedCount++;
      }
    }

    if (cleanedCount > 0) {
      console.log(`🧹 Cleaned ${cleanedCount} expired cache entries`);
    }
  }

  /**
   * Get cache statistics
   */
  public getCacheStats(): {
    totalEntries: number;
    totalHits: number;
    hitRate: number;
    oldestEntry: Date | null;
    newestEntry: Date | null;
  } {
    const entries = Array.from(this.conversionCache.values());
    const totalHits = entries.reduce((sum, entry) => sum + entry.hits, 0);
    const totalRequests = entries.reduce((sum, entry) => sum + entry.hits, 0) + entries.length;
    
    return {
      totalEntries: entries.length,
      totalHits,
      hitRate: totalRequests > 0 ? (totalHits / totalRequests) * 100 : 0,
      oldestEntry: entries.length > 0 
        ? new Date(Math.min(...entries.map(e => e.timestamp.getTime())))
        : null,
      newestEntry: entries.length > 0 
        ? new Date(Math.max(...entries.map(e => e.timestamp.getTime())))
        : null
    };
  }

  /**
   * Format currency amount
   */
  public formatCurrency(amount: number, currencyCode: string): string {
    const currency = this.getCurrency(currencyCode);
    if (!currency) {
      return `${amount} ${currencyCode}`;
    }

    const formattedAmount = amount.toFixed(currency.decimals);
    return `${currency.symbol}${formattedAmount}`;
  }

  /**
   * Shutdown service
   */
  public async shutdown(): Promise<void> {
    if (this.updateInterval) {
      clearInterval(this.updateInterval);
    }
    
    if (this.cacheCleanupInterval) {
      clearInterval(this.cacheCleanupInterval);
    }

    this.removeAllListeners();
    console.log('✅ Currency Conversion Service shutdown complete');
  }
}

export default CurrencyConversionService;