/**
 * Gemini AI Cron Tasks
 * ==========================================
 * MesChain-Sync Enterprise için AI görevleri
 * Bu dosya, AI Enhancement Engine ile entegre olup
 * zamanlanmış AI görevlerini tanımlar ve yönetir.
 * 
 * @version 1.0.0
 * @date 14 Haziran 2025
 */

class GeminiAICronTasks {
    constructor() {
        this.apiUrl = 'http://localhost:4102'; // AI Enhancement Engine URL
        this.cronManagerUrl = '/admin/index.php?route=extension/module/advanced_cron';
        this.taskDefinitions = {
            'ai_product_matching': {
                name: 'AI Ürün Eşleştirme',
                description: 'Pazaryerleri arasında ürün eşleştirme algoritması çalıştırır',
                cron_expression: '0 */6 * * *', // Her 6 saatte bir
                command: 'node gemini_ai_product_matching_task.js',
                max_runtime: 3600, // 1 saat
                timeout: 3600,
                log_output: true,
                notification_email: 'ai-alerts@meschain-sync.com',
                user_id: 1, // SuperAdmin
                parameters: JSON.stringify({
                    threshold: 0.85,
                    marketplaces: ['trendyol', 'hepsiburada', 'n11', 'amazon'],
                    use_machine_learning: true
                })
            },
            'ai_price_optimization': {
                name: 'AI Fiyat Optimizasyonu',
                description: 'Rekabetçi fiyatlandırma için makine öğrenmesi modellerini çalıştırır',
                cron_expression: '30 */4 * * *', // Her 4 saatte bir (30. dakikada)
                command: 'node gemini_ai_pricing_algorithm.js',
                max_runtime: 1800, // 30 dakika
                timeout: 1800,
                log_output: true,
                notification_email: 'ai-alerts@meschain-sync.com',
                user_id: 1, // SuperAdmin
                parameters: JSON.stringify({
                    marketplaces: ['trendyol', 'hepsiburada', 'n11', 'amazon'],
                    profit_margin_min: 0.15,
                    competitive_analysis: true,
                    price_elasticity: 0.75
                })
            },
            'ai_predictive_analytics': {
                name: 'AI Öngörücü Analitik',
                description: 'Satış ve stok tahminleri için makine öğrenmesi modellerini çalıştırır',
                cron_expression: '0 1 * * *', // Her gün saat 01:00'de
                command: 'node gemini_ai_predictive_analytics.js',
                max_runtime: 7200, // 2 saat
                timeout: 7200,
                log_output: true,
                notification_email: 'ai-alerts@meschain-sync.com',
                user_id: 1, // SuperAdmin
                parameters: JSON.stringify({
                    forecast_horizon_days: 30,
                    train_model: true,
                    include_external_factors: true,
                    seasonality_adjustment: true
                })
            },
            'ai_model_optimization': {
                name: 'AI Model Optimizasyonu',
                description: 'Makine öğrenmesi modellerinin performansını optimize eder',
                cron_expression: '0 2 * * 0', // Her Pazar saat 02:00'de
                command: 'node gemini_ai_model_optimization.js',
                max_runtime: 14400, // 4 saat
                timeout: 14400,
                log_output: true,
                notification_email: 'ai-alerts@meschain-sync.com',
                user_id: 1, // SuperAdmin
                parameters: JSON.stringify({
                    hyperparameter_tuning: true,
                    cross_validation: true,
                    feature_selection: true,
                    model_pruning: true
                })
            },
            'ai_anomaly_detection': {
                name: 'AI Anormallik Tespiti',
                description: 'Satış ve müşteri davranışlarında anormallikleri tespit eder',
                cron_expression: '0 */8 * * *', // Her 8 saatte bir
                command: 'node gemini_ai_anomaly_detection.js',
                max_runtime: 1800, // 30 dakika
                timeout: 1800,
                log_output: true,
                notification_email: 'ai-alerts@meschain-sync.com',
                user_id: 1, // SuperAdmin
                parameters: JSON.stringify({
                    sensitivity: 0.8,
                    detection_algorithms: ['isolation_forest', 'one_class_svm', 'lof'],
                    alert_threshold: 0.9,
                    auto_mitigation: false
                })
            }
        };
    }

    // Cron görevlerini cron yönetimine kaydet
    async registerTasks() {
        console.log('🚀 AI Görevleri kaydediliyor...');
        
        for (const [taskId, task] of Object.entries(this.taskDefinitions)) {
            try {
                const response = await this.apiCall('/admin/index.php?route=extension/module/advanced_cron/addJob', {
                    method: 'POST',
                    body: JSON.stringify({
                        name: task.name,
                        description: task.description,
                        command: task.command,
                        cron_expression: task.cron_expression,
                        status: 'active',
                        max_runtime: task.max_runtime,
                        timeout: task.timeout,
                        log_output: task.log_output,
                        user_id: task.user_id,
                        notification_email: task.notification_email,
                        parameters: task.parameters
                    })
                });
                
                console.log(`✅ AI Görevi kaydedildi: ${task.name}`);
            } catch (error) {
                console.error(`❌ AI Görevi kaydedilemedi: ${task.name}`, error);
            }
        }
    }
    
    // API çağrısı yap
    async apiCall(endpoint, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-AI-Token': 'gemini-ai-task-manager'
            }
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        const response = await fetch(endpoint, mergedOptions);
        
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        return response.json();
    }
    
    // Görev durumunu kontrol et
    async checkTaskStatus(taskId) {
        try {
            const response = await this.apiCall('/admin/index.php?route=extension/module/advanced_cron/getJobStatus', {
                method: 'POST',
                body: JSON.stringify({ job_id: taskId })
            });
            
            return response;
        } catch (error) {
            console.error(`❌ Görev durumu kontrol edilemedi: ${taskId}`, error);
            return null;
        }
    }
    
    // Dashboard için görev istatistiklerini al
    async getTaskStatistics() {
        try {
            const response = await this.apiCall('/admin/index.php?route=extension/module/advanced_cron/getJobStatistics', {
                method: 'POST',
                body: JSON.stringify({ type: 'ai_tasks' })
            });
            
            return response;
        } catch (error) {
            console.error('❌ Görev istatistikleri alınamadı', error);
            return {
                total: 0,
                active: 0,
                success_rate: 0,
                avg_execution_time: 0
            };
        }
    }
    
    // Tüm AI görevlerini başlat
    async startAllTasks() {
        console.log('🚀 Tüm AI görevleri başlatılıyor...');
        
        for (const [taskId, task] of Object.entries(this.taskDefinitions)) {
            try {
                await this.apiCall('/admin/index.php?route=extension/module/advanced_cron/runJob', {
                    method: 'POST',
                    body: JSON.stringify({ name: task.name })
                });
                
                console.log(`✅ AI Görevi başlatıldı: ${task.name}`);
            } catch (error) {
                console.error(`❌ AI Görevi başlatılamadı: ${task.name}`, error);
            }
        }
    }
}

// Sadece bu dosya doğrudan çalıştırıldığında görevleri kaydet
if (typeof window !== 'undefined' && window.location.href.includes('advanced_cron')) {
    document.addEventListener('DOMContentLoaded', async () => {
        const aiTasks = new GeminiAICronTasks();
        await aiTasks.registerTasks();
        console.log('✅ AI Görevleri başarıyla kaydedildi');
    });
}

// Export the class for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GeminiAICronTasks;
} else if (typeof window !== 'undefined') {
    window.GeminiAICronTasks = GeminiAICronTasks;
}
