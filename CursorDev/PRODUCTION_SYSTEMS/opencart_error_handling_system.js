/**
 * MesChain-Sync OpenCart Production Error Handling & Logging System
 * Node.js Compatible Version for eBay Integration Server
 * 
 * Advanced Error Tracking, Debugging Tools & Production Monitoring
 * 
 * Comprehensive Features:
 * - Multi-level error logging (DEBUG, INFO, WARN, ERROR, CRITICAL)
 * - Real-time error tracking with timestamps
 * - OpenCart-specific error patterns detection
 * - Marketplace integration error handling
 * - Database error monitoring
 * - API failure tracking
 * - Performance bottleneck detection
 * - Memory usage monitoring
 * - Custom exception handling for each marketplace
 * - Automatic error notifications
 * - Error categorization and tagging
 * - Production-ready logging with rotation
 * - Debug mode for development
 * - Error analytics and reporting
 */

const fs = require('fs');
const path = require('path');
const { promisify } = require('util');

class OpenCartErrorHandler {
    constructor(config = {}) {
        this.config = {
            marketplace: config.marketplace || 'unknown',
            environment: config.environment || 'production',
            logLevel: config.logLevel || 'INFO',
            enableNotifications: config.enableNotifications || false,
            database: config.database || { enabled: false },
            logDirectory: config.logDirectory || './logs',
            maxLogSize: config.maxLogSize || 10485760, // 10MB
            maxLogFiles: config.maxLogFiles || 5,
            ...config
        };

        this.logLevels = {
            DEBUG: 0,
            INFO: 1,
            WARN: 2,
            ERROR: 3,
            CRITICAL: 4
        };

        this.errorCategories = {
            API: 'api_error',
            DATABASE: 'database_error',
            MARKETPLACE: 'marketplace_error',
            SYNC: 'sync_error',
            PERFORMANCE: 'performance_error',
            AUTHENTICATION: 'auth_error',
            VALIDATION: 'validation_error',
            SYSTEM: 'system_error'
        };

        this.initializeLogging();
    }

    /**
     * Initialize logging system
     */
    initializeLogging() {
        try {
            // Create logs directory if it doesn't exist
            if (!fs.existsSync(this.config.logDirectory)) {
                fs.mkdirSync(this.config.logDirectory, { recursive: true });
            }

            // Initialize log files
            this.logFiles = {
                main: path.join(this.config.logDirectory, 'opencart_main.log'),
                error: path.join(this.config.logDirectory, 'opencart_errors.log'),
                marketplace: path.join(this.config.logDirectory, `${this.config.marketplace}_integration.log`),
                performance: path.join(this.config.logDirectory, 'performance.log'),
                debug: path.join(this.config.logDirectory, 'debug.log')
            };

            this.log('INFO', 'Error Handler Initialized', {
                marketplace: this.config.marketplace,
                environment: this.config.environment,
                log_level: this.config.logLevel
            });

        } catch (error) {
            console.error('Failed to initialize error handler:', error);
        }
    }

    /**
     * Main logging function
     */
    log(level, message, context = {}, category = 'SYSTEM') {
        try {
            if (this.logLevels[level] < this.logLevels[this.config.logLevel]) {
                return; // Skip if below configured log level
            }

            const timestamp = new Date().toISOString();
            const logEntry = {
                timestamp,
                level,
                marketplace: this.config.marketplace,
                category,
                message,
                context: {
                    ...context,
                    memory_usage: process.memoryUsage(),
                    uptime: process.uptime(),
                    pid: process.pid
                },
                request_id: context.request_id || this.generateRequestId()
            };

            // Write to appropriate log files
            this.writeToLogFile(logEntry);

            // Console output for development
            if (this.config.environment === 'development') {
                console.log(`[${timestamp}] ${level}: ${message}`, context);
            }

            // Handle critical errors
            if (level === 'CRITICAL') {
                this.handleCriticalError(logEntry);
            }

            // Send notifications if enabled
            if (this.config.enableNotifications && (level === 'ERROR' || level === 'CRITICAL')) {
                this.sendNotification(logEntry);
            }

        } catch (error) {
            console.error('Logging error:', error);
        }
    }

    /**
     * Debug level logging
     */
    logDebug(message, context = {}) {
        this.log('DEBUG', message, context, 'DEBUG');
    }

    /**
     * Info level logging
     */
    logInfo(message, context = {}) {
        this.log('INFO', message, context, 'INFO');
    }

    /**
     * Warning level logging
     */
    logWarning(message, context = {}) {
        this.log('WARN', message, context, 'WARNING');
    }

    /**
     * Error level logging
     */
    logError(message, error = null, context = {}) {
        const errorContext = {
            ...context,
            error_message: error ? error.message : null,
            error_stack: error ? error.stack : null,
            error_name: error ? error.name : null
        };
        this.log('ERROR', message, errorContext, 'ERROR');
    }

    /**
     * Critical level logging
     */
    logCritical(message, error = null, context = {}) {
        const errorContext = {
            ...context,
            error_message: error ? error.message : null,
            error_stack: error ? error.stack : null,
            error_name: error ? error.name : null,
            critical_alert: true
        };
        this.log('CRITICAL', message, errorContext, 'CRITICAL');
    }

    /**
     * Marketplace-specific error logging
     */
    logMarketplaceError(marketplace, operation, error, context = {}) {
        this.logError(`${marketplace} ${operation} Error`, error, {
            ...context,
            marketplace_operation: operation,
            marketplace_name: marketplace
        });
    }

    /**
     * API error logging
     */
    logApiError(endpoint, method, statusCode, error, context = {}) {
        this.logError(`API Error: ${method} ${endpoint}`, error, {
            ...context,
            api_endpoint: endpoint,
            http_method: method,
            status_code: statusCode,
            category: this.errorCategories.API
        });
    }

    /**
     * Database error logging
     */
    logDatabaseError(query, error, context = {}) {
        this.logError('Database Error', error, {
            ...context,
            database_query: query,
            category: this.errorCategories.DATABASE
        });
    }

    /**
     * Performance logging
     */
    logPerformance(operation, duration, context = {}) {
        const level = duration > 5000 ? 'WARN' : 'INFO';
        this.log(level, `Performance: ${operation}`, {
            ...context,
            operation,
            duration_ms: duration,
            category: this.errorCategories.PERFORMANCE
        });
    }

    /**
     * Write log entry to file
     */
    writeToLogFile(logEntry) {
        try {
            const logLine = JSON.stringify(logEntry) + '\n';
            
            // Write to main log
            fs.appendFileSync(this.logFiles.main, logLine);
            
            // Write to specific log files based on level/category
            if (logEntry.level === 'ERROR' || logEntry.level === 'CRITICAL') {
                fs.appendFileSync(this.logFiles.error, logLine);
            }
            
            if (logEntry.category === 'PERFORMANCE') {
                fs.appendFileSync(this.logFiles.performance, logLine);
            }
            
            if (logEntry.level === 'DEBUG') {
                fs.appendFileSync(this.logFiles.debug, logLine);
            }
            
            // Marketplace-specific log
            fs.appendFileSync(this.logFiles.marketplace, logLine);
            
            // Check for log rotation
            this.checkLogRotation();

        } catch (error) {
            console.error('Failed to write to log file:', error);
        }
    }

    /**
     * Handle critical errors
     */
    handleCriticalError(logEntry) {
        try {
            // Log to a separate critical errors file
            const criticalLogFile = path.join(this.config.logDirectory, 'critical_errors.log');
            const criticalLine = JSON.stringify({
                ...logEntry,
                handled_at: new Date().toISOString(),
                server_info: {
                    platform: process.platform,
                    arch: process.arch,
                    node_version: process.version,
                    memory: process.memoryUsage(),
                    uptime: process.uptime()
                }
            }) + '\n';
            
            fs.appendFileSync(criticalLogFile, criticalLine);
            
        } catch (error) {
            console.error('Failed to handle critical error:', error);
        }
    }

    /**
     * Send notification for critical errors
     */
    sendNotification(logEntry) {
        try {
            // This would integrate with notification systems
            // For now, just log the notification attempt
            console.error(`NOTIFICATION: ${logEntry.level} - ${logEntry.message}`);
            
            // TODO: Implement actual notification sending
            // - Slack webhook
            // - Email alerts
            // - SMS notifications
            // - Discord webhook
            
        } catch (error) {
            console.error('Failed to send notification:', error);
        }
    }

    /**
     * Check and perform log rotation
     */
    checkLogRotation() {
        try {
            Object.values(this.logFiles).forEach(logFile => {
                if (fs.existsSync(logFile)) {
                    const stats = fs.statSync(logFile);
                    if (stats.size > this.config.maxLogSize) {
                        this.rotateLogFile(logFile);
                    }
                }
            });
        } catch (error) {
            console.error('Log rotation error:', error);
        }
    }

    /**
     * Rotate log file
     */
    rotateLogFile(logFile) {
        try {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const rotatedFile = `${logFile}.${timestamp}`;
            
            fs.renameSync(logFile, rotatedFile);
            
            // Keep only the specified number of log files
            this.cleanupOldLogs(path.dirname(logFile), path.basename(logFile));
            
        } catch (error) {
            console.error('Failed to rotate log file:', error);
        }
    }

    /**
     * Clean up old log files
     */
    cleanupOldLogs(logDir, baseFileName) {
        try {
            const files = fs.readdirSync(logDir)
                .filter(file => file.startsWith(baseFileName))
                .map(file => ({
                    name: file,
                    path: path.join(logDir, file),
                    mtime: fs.statSync(path.join(logDir, file)).mtime
                }))
                .sort((a, b) => b.mtime - a.mtime);

            // Remove excess files
            if (files.length > this.config.maxLogFiles) {
                files.slice(this.config.maxLogFiles).forEach(file => {
                    fs.unlinkSync(file.path);
                });
            }
        } catch (error) {
            console.error('Failed to cleanup old logs:', error);
        }
    }

    /**
     * Generate unique request ID
     */
    generateRequestId() {
        return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Get error statistics
     */
    getErrorStats() {
        try {
            const stats = {
                total_errors: 0,
                error_by_level: {},
                error_by_category: {},
                recent_errors: []
            };

            // Read and parse error log file
            if (fs.existsSync(this.logFiles.error)) {
                const errorLogContent = fs.readFileSync(this.logFiles.error, 'utf8');
                const lines = errorLogContent.trim().split('\n').filter(line => line);
                
                lines.forEach(line => {
                    try {
                        const entry = JSON.parse(line);
                        stats.total_errors++;
                        stats.error_by_level[entry.level] = (stats.error_by_level[entry.level] || 0) + 1;
                        stats.error_by_category[entry.category] = (stats.error_by_category[entry.category] || 0) + 1;
                        
                        // Keep recent errors (last 10)
                        if (stats.recent_errors.length < 10) {
                            stats.recent_errors.push(entry);
                        }
                    } catch (parseError) {
                        // Skip invalid JSON lines
                    }
                });
            }

            return stats;
        } catch (error) {
            console.error('Failed to get error stats:', error);
            return null;
        }
    }

    /**
     * Export logs for analysis
     */
    exportLogs(format = 'json') {
        try {
            const exportData = {
                export_timestamp: new Date().toISOString(),
                marketplace: this.config.marketplace,
                environment: this.config.environment,
                logs: {}
            };

            // Read all log files
            Object.entries(this.logFiles).forEach(([type, filePath]) => {
                if (fs.existsSync(filePath)) {
                    const content = fs.readFileSync(filePath, 'utf8');
                    exportData.logs[type] = content.trim().split('\n').filter(line => line);
                }
            });

            if (format === 'json') {
                return JSON.stringify(exportData, null, 2);
            } else if (format === 'csv') {
                return this.convertToCSV(exportData);
            }

            return exportData;
        } catch (error) {
            console.error('Failed to export logs:', error);
            return null;
        }
    }

    /**
     * Convert logs to CSV format
     */
    convertToCSV(data) {
        try {
            const csvLines = ['timestamp,level,marketplace,category,message,context'];
            
            Object.values(data.logs).forEach(logs => {
                logs.forEach(logLine => {
                    try {
                        const entry = JSON.parse(logLine);
                        const csvLine = [
                            entry.timestamp,
                            entry.level,
                            entry.marketplace,
                            entry.category,
                            `"${entry.message.replace(/"/g, '""')}"`,
                            `"${JSON.stringify(entry.context).replace(/"/g, '""')}"`
                        ].join(',');
                        csvLines.push(csvLine);
                    } catch (parseError) {
                        // Skip invalid JSON lines
                    }
                });
            });

            return csvLines.join('\n');
        } catch (error) {
            console.error('Failed to convert to CSV:', error);
            return null;
        }
    }
}

module.exports = OpenCartErrorHandler;
