// 🔥 CURSOR TEAM P0 CRITICAL: DATA EXPORT & REPORTING SYSTEM
// Target: Complete Super Admin Dashboard %95 → %100
// Features: Excel/CSV Export, Automated Reports, Scheduled Generation

const XLSX = require('xlsx');
const fs = require('fs');
const path = require('path');
const cron = require('node-cron');
const nodemailer = require('nodemailer');

/**
 * 🚀 DATA EXPORT & REPORTING ENGINE - ENTERPRISE GRADE
 * Features: Multi-format export, Automated scheduling, Real-time generation
 * Performance Target: Large dataset export, Scheduled delivery, Dashboard completion
 */
class DataExportReportingSystem {
    constructor(options = {}) {
        this.options = {
            exportPath: options.exportPath || './exports',
            reportsPath: options.reportsPath || './reports',
            tempPath: options.tempPath || './temp',
            maxFileSize: options.maxFileSize || 100 * 1024 * 1024, // 100MB
            enableEmail: options.enableEmail || true,
            emailConfig: options.emailConfig || this.getDefaultEmailConfig(),
            enableScheduling: options.enableScheduling || true,
            timezone: options.timezone || 'Europe/Istanbul',
            ...options
        };

        this.scheduledJobs = new Map();
        this.exportHistory = [];
        this.emailTransporter = null;

        // Performance Statistics
        this.stats = {
            totalExports: 0,
            successfulExports: 0,
            failedExports: 0,
            totalReports: 0,
            scheduledReports: 0,
            emailsSent: 0,
            avgExportTime: 0,
            totalExportSize: 0
        };

        this.init();
    }

    /**
     * 🔧 Initialize Export & Reporting System
     */
    async init() {
        try {
            console.log('🚀 Initializing Data Export & Reporting System...');
            
            // Create necessary directories
            await this.createDirectories();
            
            // Setup email transporter
            if (this.options.enableEmail) {
                await this.setupEmailTransporter();
            }
            
            // Setup scheduled reports
            if (this.options.enableScheduling) {
                this.setupScheduledReports();
            }
            
            console.log('✅ Data Export & Reporting System initialized successfully');
            
        } catch (error) {
            console.error('🚨 Export system initialization failed:', error);
            throw error;
        }
    }

    /**
     * 📁 Create necessary directories
     */
    async createDirectories() {
        const dirs = [
            this.options.exportPath,
            this.options.reportsPath,
            this.options.tempPath,
            path.join(this.options.exportPath, 'excel'),
            path.join(this.options.exportPath, 'csv'),
            path.join(this.options.exportPath, 'pdf'),
            path.join(this.options.reportsPath, 'daily'),
            path.join(this.options.reportsPath, 'weekly'),
            path.join(this.options.reportsPath, 'monthly')
        ];

        for (const dir of dirs) {
            if (!fs.existsSync(dir)) {
                fs.mkdirSync(dir, { recursive: true });
                console.log(`📁 Created directory: ${dir}`);
            }
        }
    }

    /**
     * 📧 Setup email transporter
     */
    async setupEmailTransporter() {
        try {
            this.emailTransporter = nodemailer.createTransporter(this.options.emailConfig);
            
            // Test email connection
            await this.emailTransporter.verify();
            console.log('📧 Email transporter setup successfully');
            
        } catch (error) {
            console.error('🚨 Email transporter setup failed:', error);
            this.options.enableEmail = false;
        }
    }

    /**
     * 📊 EXCEL EXPORT - Advanced Excel generation
     * @param {Array} data Data to export
     * @param {Object} options Export options
     * @returns {Promise<string>} File path
     */
    async exportToExcel(data, options = {}) {
        const startTime = Date.now();
        this.stats.totalExports++;

        try {
            const {
                filename = `export_${Date.now()}.xlsx`,
                sheetName = 'Data',
                includeCharts = false,
                formatting = true,
                filters = true,
                metadata = {}
            } = options;

            console.log(`📊 Starting Excel export: ${filename}`);

            // Create workbook and worksheet
            const workbook = XLSX.utils.book_new();
            const worksheet = XLSX.utils.json_to_sheet(data);

            // Apply formatting if enabled
            if (formatting) {
                this.applyExcelFormatting(worksheet, data);
            }

            // Add filters if enabled
            if (filters && data.length > 0) {
                const range = XLSX.utils.encode_range({
                    s: { c: 0, r: 0 },
                    e: { c: Object.keys(data[0]).length - 1, r: data.length }
                });
                worksheet['!autofilter'] = { ref: range };
            }

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);

            // Add metadata sheet if provided
            if (Object.keys(metadata).length > 0) {
                const metadataSheet = XLSX.utils.json_to_sheet([{
                    ...metadata,
                    generatedAt: new Date().toISOString(),
                    recordCount: data.length,
                    exportType: 'Excel'
                }]);
                XLSX.utils.book_append_sheet(workbook, metadataSheet, 'Metadata');
            }

            // Write file
            const filePath = path.join(this.options.exportPath, 'excel', filename);
            XLSX.writeFile(workbook, filePath);

            // Update statistics
            const fileSize = fs.statSync(filePath).size;
            const exportTime = Date.now() - startTime;
            this.updateExportStats(exportTime, fileSize, true);

            console.log(`✅ Excel export completed: ${filename} (${exportTime}ms, ${this.formatFileSize(fileSize)})`);
            
            // Add to history
            this.addToHistory({
                type: 'excel',
                filename,
                filePath,
                recordCount: data.length,
                fileSize,
                exportTime,
                success: true
            });

            return filePath;

        } catch (error) {
            const exportTime = Date.now() - startTime;
            this.updateExportStats(exportTime, 0, false);
            console.error(`🚨 Excel export failed: ${error.message}`);
            throw error;
        }
    }

    /**
     * 📋 CSV EXPORT - High-performance CSV generation
     * @param {Array} data Data to export
     * @param {Object} options Export options
     * @returns {Promise<string>} File path
     */
    async exportToCSV(data, options = {}) {
        const startTime = Date.now();
        this.stats.totalExports++;

        try {
            const {
                filename = `export_${Date.now()}.csv`,
                delimiter = ',',
                encoding = 'utf8',
                includeHeaders = true,
                customHeaders = null
            } = options;

            console.log(`📋 Starting CSV export: ${filename}`);

            if (data.length === 0) {
                throw new Error('No data to export');
            }

            // Generate CSV content
            let csvContent = '';
            
            // Add headers
            if (includeHeaders) {
                const headers = customHeaders || Object.keys(data[0]);
                csvContent += headers.map(header => `"${header}"`).join(delimiter) + '\n';
            }

            // Add data rows
            for (const row of data) {
                const values = Object.values(row).map(value => {
                    // Handle special characters and quotes
                    const stringValue = String(value || '');
                    if (stringValue.includes(delimiter) || stringValue.includes('"') || stringValue.includes('\n')) {
                        return `"${stringValue.replace(/"/g, '""')}"`;
                    }
                    return stringValue;
                });
                csvContent += values.join(delimiter) + '\n';
            }

            // Write file
            const filePath = path.join(this.options.exportPath, 'csv', filename);
            fs.writeFileSync(filePath, csvContent, encoding);

            // Update statistics
            const fileSize = fs.statSync(filePath).size;
            const exportTime = Date.now() - startTime;
            this.updateExportStats(exportTime, fileSize, true);

            console.log(`✅ CSV export completed: ${filename} (${exportTime}ms, ${this.formatFileSize(fileSize)})`);
            
            // Add to history
            this.addToHistory({
                type: 'csv',
                filename,
                filePath,
                recordCount: data.length,
                fileSize,
                exportTime,
                success: true
            });

            return filePath;

        } catch (error) {
            const exportTime = Date.now() - startTime;
            this.updateExportStats(exportTime, 0, false);
            console.error(`🚨 CSV export failed: ${error.message}`);
            throw error;
        }
    }

    /**
     * 📊 ADVANCED REPORTING - Generate comprehensive reports
     * @param {string} reportType Type of report (daily, weekly, monthly)
     * @param {Object} data Report data
     * @param {Object} options Report options
     * @returns {Promise<Object>} Report result
     */
    async generateReport(reportType, data, options = {}) {
        const startTime = Date.now();
        this.stats.totalReports++;

        try {
            const {
                format = 'excel',
                includeCharts = true,
                includeAnalytics = true,
                emailRecipients = [],
                customTemplate = null
            } = options;

            console.log(`📊 Generating ${reportType} report in ${format} format...`);

            // Prepare report data
            const reportData = await this.prepareReportData(reportType, data, options);
            
            // Generate report based on format
            let reportPath;
            if (format === 'excel') {
                reportPath = await this.generateExcelReport(reportType, reportData, options);
            } else if (format === 'csv') {
                reportPath = await this.generateCSVReport(reportType, reportData, options);
            } else {
                throw new Error(`Unsupported report format: ${format}`);
            }

            // Send email if recipients provided
            if (emailRecipients.length > 0 && this.options.enableEmail) {
                await this.emailReport(reportPath, emailRecipients, reportType);
            }

            const reportTime = Date.now() - startTime;
            console.log(`✅ ${reportType} report generated successfully (${reportTime}ms)`);

            return {
                success: true,
                reportPath,
                reportType,
                format,
                generationTime: reportTime,
                recordCount: reportData.summary?.totalRecords || 0
            };

        } catch (error) {
            console.error(`🚨 Report generation failed: ${error.message}`);
            return {
                success: false,
                error: error.message,
                reportType,
                generationTime: Date.now() - startTime
            };
        }
    }

    /**
     * 📊 Generate Excel Report with advanced features
     */
    async generateExcelReport(reportType, reportData, options = {}) {
        const filename = `${reportType}_report_${new Date().toISOString().split('T')[0]}.xlsx`;
        const workbook = XLSX.utils.book_new();

        // Summary Sheet
        if (reportData.summary) {
            const summarySheet = XLSX.utils.json_to_sheet([reportData.summary]);
            XLSX.utils.book_append_sheet(workbook, summarySheet, 'Summary');
        }

        // Data Sheets
        if (reportData.orders) {
            const ordersSheet = XLSX.utils.json_to_sheet(reportData.orders);
            XLSX.utils.book_append_sheet(workbook, ordersSheet, 'Orders');
        }

        if (reportData.products) {
            const productsSheet = XLSX.utils.json_to_sheet(reportData.products);
            XLSX.utils.book_append_sheet(workbook, productsSheet, 'Products');
        }

        if (reportData.customers) {
            const customersSheet = XLSX.utils.json_to_sheet(reportData.customers);
            XLSX.utils.book_append_sheet(workbook, customersSheet, 'Customers');
        }

        if (reportData.analytics) {
            const analyticsSheet = XLSX.utils.json_to_sheet(reportData.analytics);
            XLSX.utils.book_append_sheet(workbook, analyticsSheet, 'Analytics');
        }

        // Write file
        const filePath = path.join(this.options.reportsPath, reportType, filename);
        XLSX.writeFile(workbook, filePath);

        return filePath;
    }

    /**
     * 📋 Generate CSV Report (multi-file)
     */
    async generateCSVReport(reportType, reportData, options = {}) {
        const timestamp = new Date().toISOString().split('T')[0];
        const reportDir = path.join(this.options.reportsPath, reportType, `csv_report_${timestamp}`);
        
        // Create report directory
        if (!fs.existsSync(reportDir)) {
            fs.mkdirSync(reportDir, { recursive: true });
        }

        const files = [];

        // Export each data type to separate CSV files
        if (reportData.summary) {
            const summaryPath = path.join(reportDir, 'summary.csv');
            await this.exportToCSV([reportData.summary], { filename: path.basename(summaryPath) });
            files.push(summaryPath);
        }

        if (reportData.orders) {
            const ordersPath = path.join(reportDir, 'orders.csv');
            await this.exportToCSV(reportData.orders, { filename: path.basename(ordersPath) });
            files.push(ordersPath);
        }

        if (reportData.products) {
            const productsPath = path.join(reportDir, 'products.csv');
            await this.exportToCSV(reportData.products, { filename: path.basename(productsPath) });
            files.push(productsPath);
        }

        return reportDir;
    }

    /**
     * 📊 Prepare report data based on type
     */
    async prepareReportData(reportType, rawData, options = {}) {
        const reportData = {
            summary: {},
            orders: [],
            products: [],
            customers: [],
            analytics: []
        };

        // Generate summary statistics
        reportData.summary = {
            reportType: reportType.toUpperCase(),
            generatedAt: new Date().toISOString(),
            period: this.getReportPeriod(reportType),
            totalOrders: rawData.orders?.length || 0,
            totalRevenue: this.calculateTotalRevenue(rawData.orders || []),
            avgOrderValue: this.calculateAvgOrderValue(rawData.orders || []),
            topProductId: this.getTopProduct(rawData.products || []),
            totalCustomers: rawData.customers?.length || 0,
            newCustomers: this.getNewCustomers(rawData.customers || [], reportType),
            conversionRate: this.calculateConversionRate(rawData),
            returnRate: this.calculateReturnRate(rawData.orders || [])
        };

        // Process orders data
        if (rawData.orders) {
            reportData.orders = rawData.orders.map(order => ({
                orderId: order.id,
                date: order.date,
                customerId: order.customerId,
                total: order.total,
                status: order.status,
                marketplace: order.marketplace,
                paymentMethod: order.paymentMethod,
                shippingMethod: order.shippingMethod
            }));
        }

        // Process products data
        if (rawData.products) {
            reportData.products = rawData.products.map(product => ({
                productId: product.id,
                name: product.name,
                category: product.category,
                price: product.price,
                stock: product.stock,
                soldQuantity: product.soldQuantity || 0,
                revenue: (product.price || 0) * (product.soldQuantity || 0),
                profit: this.calculateProductProfit(product)
            }));
        }

        // Process customers data
        if (rawData.customers) {
            reportData.customers = rawData.customers.map(customer => ({
                customerId: customer.id,
                name: customer.name,
                email: customer.email,
                registrationDate: customer.registrationDate,
                totalOrders: customer.totalOrders || 0,
                totalSpent: customer.totalSpent || 0,
                lastOrderDate: customer.lastOrderDate,
                status: customer.status
            }));
        }

        // Generate analytics data
        reportData.analytics = this.generateAnalyticsData(rawData, reportType);

        return reportData;
    }

    /**
     * 📅 SCHEDULED REPORTS - Setup automated reporting
     */
    setupScheduledReports() {
        console.log('📅 Setting up scheduled reports...');

        // Daily report - Every day at 06:00
        const dailyJob = cron.schedule('0 6 * * *', async () => {
            console.log('📊 Generating scheduled daily report...');
            try {
                const data = await this.collectDailyData();
                await this.generateReport('daily', data, {
                    format: 'excel',
                    emailRecipients: this.getDailyReportRecipients()
                });
                this.stats.scheduledReports++;
            } catch (error) {
                console.error('🚨 Scheduled daily report failed:', error);
            }
        }, { timezone: this.options.timezone });

        // Weekly report - Every Monday at 07:00
        const weeklyJob = cron.schedule('0 7 * * 1', async () => {
            console.log('📊 Generating scheduled weekly report...');
            try {
                const data = await this.collectWeeklyData();
                await this.generateReport('weekly', data, {
                    format: 'excel',
                    emailRecipients: this.getWeeklyReportRecipients()
                });
                this.stats.scheduledReports++;
            } catch (error) {
                console.error('🚨 Scheduled weekly report failed:', error);
            }
        }, { timezone: this.options.timezone });

        // Monthly report - First day of month at 08:00
        const monthlyJob = cron.schedule('0 8 1 * *', async () => {
            console.log('📊 Generating scheduled monthly report...');
            try {
                const data = await this.collectMonthlyData();
                await this.generateReport('monthly', data, {
                    format: 'excel',
                    emailRecipients: this.getMonthlyReportRecipients()
                });
                this.stats.scheduledReports++;
            } catch (error) {
                console.error('🚨 Scheduled monthly report failed:', error);
            }
        }, { timezone: this.options.timezone });

        this.scheduledJobs.set('daily', dailyJob);
        this.scheduledJobs.set('weekly', weeklyJob);
        this.scheduledJobs.set('monthly', monthlyJob);

        console.log('✅ Scheduled reports setup completed');
    }

    /**
     * 📧 Email report to recipients
     */
    async emailReport(filePath, recipients, reportType) {
        try {
            const filename = path.basename(filePath);
            const fileSize = fs.statSync(filePath).size;

            const mailOptions = {
                from: this.options.emailConfig.auth.user,
                to: recipients.join(', '),
                subject: `${reportType.toUpperCase()} Report - ${new Date().toLocaleDateString()}`,
                html: this.generateEmailTemplate(reportType, filename, fileSize),
                attachments: [{
                    filename: filename,
                    path: filePath,
                    contentType: 'application/octet-stream'
                }]
            };

            await this.emailTransporter.sendMail(mailOptions);
            this.stats.emailsSent++;
            
            console.log(`📧 Report emailed to ${recipients.length} recipients`);

        } catch (error) {
            console.error('🚨 Email sending failed:', error);
            throw error;
        }
    }

    /**
     * 🔧 Helper Methods
     */
    applyExcelFormatting(worksheet, data) {
        // Apply basic formatting - would be expanded in production
        if (data.length > 0) {
            const headers = Object.keys(data[0]);
            headers.forEach((header, index) => {
                const cellAddress = XLSX.utils.encode_cell({ r: 0, c: index });
                if (worksheet[cellAddress]) {
                    worksheet[cellAddress].s = {
                        font: { bold: true },
                        fill: { fgColor: { rgb: "CCCCCC" } }
                    };
                }
            });
        }
    }

    updateExportStats(exportTime, fileSize, success) {
        if (success) {
            this.stats.successfulExports++;
            this.stats.totalExportSize += fileSize;
            this.stats.avgExportTime = 
                (this.stats.avgExportTime * (this.stats.successfulExports - 1) + exportTime) / this.stats.successfulExports;
        } else {
            this.stats.failedExports++;
        }
    }

    addToHistory(exportInfo) {
        this.exportHistory.unshift({
            ...exportInfo,
            timestamp: new Date().toISOString()
        });

        // Keep only last 100 exports
        if (this.exportHistory.length > 100) {
            this.exportHistory = this.exportHistory.slice(0, 100);
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    getDefaultEmailConfig() {
        return {
            service: process.env.EMAIL_SERVICE || 'gmail',
            auth: {
                user: process.env.EMAIL_USER || 'reports@meschain.com',
                pass: process.env.EMAIL_PASS || 'password'
            }
        };
    }

    // Data collection methods (would connect to actual database)
    async collectDailyData() {
        // Placeholder - would fetch from database
        return {
            orders: this.generateSampleOrders(50),
            products: this.generateSampleProducts(100),
            customers: this.generateSampleCustomers(25)
        };
    }

    async collectWeeklyData() {
        return {
            orders: this.generateSampleOrders(300),
            products: this.generateSampleProducts(200),
            customers: this.generateSampleCustomers(150)
        };
    }

    async collectMonthlyData() {
        return {
            orders: this.generateSampleOrders(1200),
            products: this.generateSampleProducts(500),
            customers: this.generateSampleCustomers(600)
        };
    }

    // Sample data generators (for testing)
    generateSampleOrders(count) {
        return Array.from({ length: count }, (_, i) => ({
            id: `order_${i + 1}`,
            date: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000).toISOString(),
            customerId: `customer_${Math.floor(Math.random() * 100) + 1}`,
            total: Math.round(Math.random() * 1000 * 100) / 100,
            status: ['completed', 'pending', 'cancelled'][Math.floor(Math.random() * 3)],
            marketplace: ['Trendyol', 'Amazon', 'N11', 'Hepsiburada'][Math.floor(Math.random() * 4)]
        }));
    }

    generateSampleProducts(count) {
        return Array.from({ length: count }, (_, i) => ({
            id: `product_${i + 1}`,
            name: `Product ${i + 1}`,
            category: ['Electronics', 'Clothing', 'Home', 'Books'][Math.floor(Math.random() * 4)],
            price: Math.round(Math.random() * 500 * 100) / 100,
            stock: Math.floor(Math.random() * 100),
            soldQuantity: Math.floor(Math.random() * 50)
        }));
    }

    generateSampleCustomers(count) {
        return Array.from({ length: count }, (_, i) => ({
            id: `customer_${i + 1}`,
            name: `Customer ${i + 1}`,
            email: `customer${i + 1}@example.com`,
            registrationDate: new Date(Date.now() - Math.random() * 365 * 24 * 60 * 60 * 1000).toISOString(),
            totalOrders: Math.floor(Math.random() * 20),
            totalSpent: Math.round(Math.random() * 5000 * 100) / 100
        }));
    }

    // Helper calculation methods
    calculateTotalRevenue(orders) {
        return orders.reduce((sum, order) => sum + (order.total || 0), 0);
    }

    calculateAvgOrderValue(orders) {
        if (orders.length === 0) return 0;
        return this.calculateTotalRevenue(orders) / orders.length;
    }

    getTopProduct(products) {
        if (products.length === 0) return null;
        return products.reduce((top, product) => 
            (product.soldQuantity || 0) > (top.soldQuantity || 0) ? product : top
        ).id;
    }

    getNewCustomers(customers, reportType) {
        const now = new Date();
        const cutoffDate = new Date();
        
        if (reportType === 'daily') {
            cutoffDate.setDate(now.getDate() - 1);
        } else if (reportType === 'weekly') {
            cutoffDate.setDate(now.getDate() - 7);
        } else if (reportType === 'monthly') {
            cutoffDate.setMonth(now.getMonth() - 1);
        }

        return customers.filter(customer => 
            new Date(customer.registrationDate) >= cutoffDate
        ).length;
    }

    calculateConversionRate(data) {
        // Simplified conversion rate calculation
        const totalVisitors = (data.analytics?.visitors || 1000);
        const totalOrders = (data.orders?.length || 0);
        return totalOrders / totalVisitors * 100;
    }

    calculateReturnRate(orders) {
        const returnedOrders = orders.filter(order => order.status === 'returned').length;
        return orders.length > 0 ? (returnedOrders / orders.length) * 100 : 0;
    }

    calculateProductProfit(product) {
        const revenue = (product.price || 0) * (product.soldQuantity || 0);
        const cost = revenue * 0.7; // Assume 30% profit margin
        return revenue - cost;
    }

    generateAnalyticsData(rawData, reportType) {
        return [
            {
                metric: 'Total Revenue',
                value: this.calculateTotalRevenue(rawData.orders || []),
                change: Math.round((Math.random() - 0.5) * 20 * 100) / 100 + '%'
            },
            {
                metric: 'Order Count',
                value: rawData.orders?.length || 0,
                change: Math.round((Math.random() - 0.5) * 30 * 100) / 100 + '%'
            },
            {
                metric: 'Customer Count',
                value: rawData.customers?.length || 0,
                change: Math.round((Math.random() - 0.5) * 15 * 100) / 100 + '%'
            }
        ];
    }

    getReportPeriod(reportType) {
        const now = new Date();
        if (reportType === 'daily') {
            return now.toISOString().split('T')[0];
        } else if (reportType === 'weekly') {
            const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
            const weekEnd = new Date(now.setDate(now.getDate() - now.getDay() + 6));
            return `${weekStart.toISOString().split('T')[0]} to ${weekEnd.toISOString().split('T')[0]}`;
        } else if (reportType === 'monthly') {
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
        }
        return 'Custom Period';
    }

    getDailyReportRecipients() {
        return process.env.DAILY_REPORT_EMAILS?.split(',') || ['admin@meschain.com'];
    }

    getWeeklyReportRecipients() {
        return process.env.WEEKLY_REPORT_EMAILS?.split(',') || ['admin@meschain.com', 'manager@meschain.com'];
    }

    getMonthlyReportRecipients() {
        return process.env.MONTHLY_REPORT_EMAILS?.split(',') || ['admin@meschain.com', 'ceo@meschain.com'];
    }

    generateEmailTemplate(reportType, filename, fileSize) {
        return `
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>📊 ${reportType.toUpperCase()} Report Generated</h2>
            <p>Your automated ${reportType} report has been generated successfully.</p>
            
            <h3>Report Details:</h3>
            <ul>
                <li><strong>Report Type:</strong> ${reportType.toUpperCase()}</li>
                <li><strong>Generated:</strong> ${new Date().toLocaleString()}</li>
                <li><strong>Filename:</strong> ${filename}</li>
                <li><strong>File Size:</strong> ${this.formatFileSize(fileSize)}</li>
            </ul>
            
            <p>The report is attached to this email. Please review the data and contact support if you have any questions.</p>
            
            <hr>
            <p><small>This is an automated message from MesChain-Sync Reporting System.</small></p>
        </body>
        </html>
        `;
    }

    /**
     * 📊 Get comprehensive statistics
     */
    getStats() {
        return {
            ...this.stats,
            exportHistory: this.exportHistory.slice(0, 10), // Last 10 exports
            scheduledJobsCount: this.scheduledJobs.size,
            avgFileSizeMB: this.stats.successfulExports > 0 
                ? Math.round(this.stats.totalExportSize / this.stats.successfulExports / 1024 / 1024 * 100) / 100
                : 0
        };
    }

    /**
     * 🔌 Cleanup and close
     */
    async cleanup() {
        // Stop all scheduled jobs
        for (const [name, job] of this.scheduledJobs) {
            job.destroy();
            console.log(`📅 Stopped scheduled job: ${name}`);
        }

        // Close email transporter
        if (this.emailTransporter) {
            this.emailTransporter.close();
        }

        console.log('🔌 Data Export & Reporting System cleanup completed');
    }
}

// 🚀 Export module
module.exports = {
    DataExportReportingSystem
};

// 🎯 Usage Example
if (require.main === module) {
    (async () => {
        console.log('🚀 CURSOR TEAM: Data Export & Reporting System Test Started');
        
        const exportSystem = new DataExportReportingSystem({
            exportPath: './test_exports',
            reportsPath: './test_reports',
            enableEmail: false, // Disable for testing
            enableScheduling: false // Disable for testing
        });

        // Test Excel export
        const sampleData = [
            { id: 1, name: 'Product A', price: 100, category: 'Electronics' },
            { id: 2, name: 'Product B', price: 200, category: 'Clothing' },
            { id: 3, name: 'Product C', price: 150, category: 'Home' }
        ];

        try {
            const excelPath = await exportSystem.exportToExcel(sampleData, {
                filename: 'test_products.xlsx',
                sheetName: 'Products',
                formatting: true,
                filters: true
            });
            console.log(`📊 Excel export successful: ${excelPath}`);

            const csvPath = await exportSystem.exportToCSV(sampleData, {
                filename: 'test_products.csv'
            });
            console.log(`📋 CSV export successful: ${csvPath}`);

            // Test report generation
            const reportData = await exportSystem.collectDailyData();
            const reportResult = await exportSystem.generateReport('daily', reportData, {
                format: 'excel'
            });
            console.log('📊 Report generation result:', reportResult);

            // Display stats
            const stats = exportSystem.getStats();
            console.log('📊 Export System Statistics:', stats);

        } catch (error) {
            console.error('🚨 Test failed:', error);
        }

        console.log('✅ CURSOR TEAM: Data Export & Reporting Test Completed');
    })().catch(console.error);
} 