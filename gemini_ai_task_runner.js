/**
 * Gemini AI Task Runner
 * =============================
 * AI görevlerini çalıştırmak için temel sınıf
 * 
 * @version 1.0.0
 * @date 14 Haziran 2025
 */

const fetch = require('node-fetch');
const fs = require('fs');
const path = require('path');

class GeminiAITaskRunner {
    constructor(options = {}) {
        this.taskName = options.taskName || 'ai_task';
        this.taskId = options.taskId || Date.now();
        this.apiUrl = options.apiUrl || 'http://localhost:4102';
        this.parameters = options.parameters || {};
        this.logPath = options.logPath || path.join(__dirname, 'logs', 'ai_tasks');
        this.startTime = new Date();
        
        // Çalışma modu
        this.verbose = options.verbose || true;
        
        // Log dizini oluştur
        if (!fs.existsSync(this.logPath)) {
            fs.mkdirSync(this.logPath, { recursive: true });
        }
        
        this.log(`🚀 ${this.taskName} görevi başlatılıyor... (ID: ${this.taskId})`);
        this.log(`Parametreler: ${JSON.stringify(this.parameters, null, 2)}`);
    }
    
    // Görev çalıştırma metodu - alt sınıflar tarafından override edilecek
    async run() {
        throw new Error('Bu metod alt sınıflar tarafından uygulanmalıdır');
    }
    
    // Çalıştır ve sonuç döndür
    async execute() {
        try {
            this.log(`⚙️ ${this.taskName} çalıştırılıyor...`);
            
            // Görev başladı bildirimi
            await this.updateTaskStatus('running');
            
            // Asıl görevi çalıştır
            const result = await this.run();
            
            // Tamamlandı bildirimi
            const executionTime = (new Date() - this.startTime) / 1000;
            await this.updateTaskStatus('completed', {
                success: true,
                executionTime,
                result
            });
            
            this.log(`✅ ${this.taskName} başarıyla tamamlandı (${executionTime.toFixed(2)} saniye)`);
            return { success: true, result, executionTime };
            
        } catch (error) {
            const executionTime = (new Date() - this.startTime) / 1000;
            
            // Hata bildirimi
            await this.updateTaskStatus('failed', {
                success: false,
                executionTime,
                error: error.message
            });
            
            this.log(`❌ ${this.taskName} çalıştırılırken hata oluştu: ${error.message}`, 'error');
            return { success: false, error: error.message, executionTime };
        }
    }
    
    // API çağrısı yap
    async apiCall(endpoint, options = {}) {
        try {
            const defaultOptions = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-AI-Task-ID': this.taskId.toString()
                }
            };
            
            const mergedOptions = { ...defaultOptions, ...options };
            const response = await fetch(`${this.apiUrl}${endpoint}`, mergedOptions);
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            return response.json();
        } catch (error) {
            this.log(`API çağrısı başarısız: ${error.message}`, 'error');
            throw error;
        }
    }
    
    // Görev durum güncellemesi
    async updateTaskStatus(status, data = {}) {
        try {
            return await this.apiCall('/task-status', {
                method: 'POST',
                body: JSON.stringify({
                    taskId: this.taskId,
                    taskName: this.taskName,
                    status,
                    timestamp: new Date().toISOString(),
                    ...data
                })
            });
        } catch (error) {
            this.log(`Durum güncellenemedi: ${error.message}`, 'error');
        }
    }
    
    // Log kaydetme
    log(message, level = 'info') {
        const timestamp = new Date().toISOString();
        const logMessage = `[${timestamp}] [${level.toUpperCase()}] ${message}`;
        
        if (this.verbose) {
            console.log(logMessage);
        }
        
        const logFile = path.join(
            this.logPath, 
            `${this.taskName}_${this.startTime.toISOString().split('T')[0]}.log`
        );
        
        fs.appendFileSync(logFile, logMessage + '\n');
    }
}

module.exports = GeminiAITaskRunner;
