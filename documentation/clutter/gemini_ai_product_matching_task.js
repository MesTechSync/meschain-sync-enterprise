/**
 * Gemini AI Ürün Eşleştirme Görevi
 * =============================
 * Farklı pazaryerleri arasında ürünleri eşleştirmek için AI algoritması çalıştırır
 * 
 * @version 1.0.0
 * @date 14 Haziran 2025
 */

const GeminiAITaskRunner = require('./gemini_ai_task_runner');
const fs = require('fs');
const path = require('path');

class ProductMatchingTask extends GeminiAITaskRunner {
    constructor(options = {}) {
        super({
            ...options,
            taskName: 'ai_product_matching'
        });
        
        // Varsayılan parametreler
        this.threshold = this.parameters.threshold || 0.85;
        this.marketplaces = this.parameters.marketplaces || ['trendyol', 'hepsiburada', 'n11', 'amazon'];
        this.useMachineLearning = this.parameters.use_machine_learning !== undefined ? 
            this.parameters.use_machine_learning : true;
        
        // Geçici dosyalar için dizin
        this.tempDir = path.join(__dirname, 'temp', 'ai_matching');
        if (!fs.existsSync(this.tempDir)) {
            fs.mkdirSync(this.tempDir, { recursive: true });
        }
        
        this.log('Ürün eşleştirme parametreleri:');
        this.log(`- Eşleşme eşiği: ${this.threshold}`);
        this.log(`- Pazaryerleri: ${this.marketplaces.join(', ')}`);
        this.log(`- AI Modeli: ${this.useMachineLearning ? 'Makine Öğrenmesi' : 'Kural Tabanlı'}`);
    }
    
    async run() {
        this.log('Ürün eşleştirme görevi başlatılıyor...');
        
        // 1. Pazaryerlerinden ürün verilerini al
        const products = await this.fetchProductsFromMarketplaces();
        this.log(`${Object.values(products).reduce((sum, arr) => sum + arr.length, 0)} ürün alındı`);
        
        // 2. Eşleştirme algoritması çalıştır
        const matchResults = await this.runMatchingAlgorithm(products);
        this.log(`Eşleştirme tamamlandı: ${matchResults.matches.length} eşleşme bulundu`);
        
        // 3. Sonuçları veritabanına kaydet
        await this.saveMatchResults(matchResults);
        this.log('Eşleştirme sonuçları veritabanına kaydedildi');
        
        // 4. İstatistikleri raporla
        const statistics = this.calculateStatistics(matchResults);
        this.log(`İstatistikler: Doğruluk: ${statistics.accuracy.toFixed(2)}%, Kapsam: ${statistics.coverage.toFixed(2)}%`);
        
        return {
            matches: matchResults.matches.length,
            statistics
        };
    }
    
    // Pazaryerlerinden ürün verilerini al
    async fetchProductsFromMarketplaces() {
        this.log('Pazaryerlerinden ürün verileri alınıyor...');
        
        const products = {};
        
        for (const marketplace of this.marketplaces) {
            this.log(`${marketplace} verileri alınıyor...`);
            
            try {
                // API çağrısıyla ürünleri al
                const response = await this.apiCall('/marketplace-products', {
                    method: 'POST',
                    body: JSON.stringify({
                        marketplace,
                        limit: 1000  // Gerçek sistemde pagination ile tüm ürünler alınabilir
                    })
                });
                
                products[marketplace] = response.products;
                this.log(`${marketplace}: ${response.products.length} ürün alındı`);
                
            } catch (error) {
                this.log(`${marketplace} ürünleri alınırken hata: ${error.message}`, 'error');
                products[marketplace] = [];
            }
        }
        
        return products;
    }
    
    // Eşleştirme algoritması çalıştır
    async runMatchingAlgorithm(products) {
        this.log(`Eşleştirme algoritması çalıştırılıyor (Mod: ${this.useMachineLearning ? 'AI' : 'Kural tabanlı'})`);
        
        try {
            // Eşleştirme API'sine istek gönder
            const response = await this.apiCall('/ai/product-matching', {
                method: 'POST',
                body: JSON.stringify({
                    products,
                    threshold: this.threshold,
                    use_machine_learning: this.useMachineLearning
                })
            });
            
            return response;
            
        } catch (error) {
            this.log(`Eşleştirme algoritması hata: ${error.message}`, 'error');
            
            // Hata durumunda boş sonuç döndür
            return {
                matches: [],
                unmatched: [],
                uncertain: []
            };
        }
    }
    
    // Eşleştirme sonuçlarını kaydet
    async saveMatchResults(results) {
        this.log('Eşleştirme sonuçları kaydediliyor...');
        
        try {
            // Sonuçları veritabanına kaydet
            await this.apiCall('/product-matches', {
                method: 'POST',
                body: JSON.stringify({
                    matches: results.matches,
                    timestamp: new Date().toISOString(),
                    task_id: this.taskId
                })
            });
            
            // Ayrıca yerel dosyaya da kaydet (yedek)
            const resultFile = path.join(
                this.tempDir,
                `matching_results_${this.startTime.toISOString().split('T')[0]}.json`
            );
            
            fs.writeFileSync(
                resultFile,
                JSON.stringify({
                    task_id: this.taskId,
                    timestamp: new Date().toISOString(),
                    results
                }, null, 2)
            );
            
            this.log(`Sonuçlar yerel dosyaya kaydedildi: ${resultFile}`);
            
        } catch (error) {
            this.log(`Sonuçlar kaydedilirken hata: ${error.message}`, 'error');
            throw error;
        }
    }
    
    // İstatistikleri hesapla
    calculateStatistics(results) {
        const totalProducts = results.matches.length + results.unmatched.length + results.uncertain.length;
        const accuracy = results.matches.length > 0 ? 
            (results.matches.filter(m => m.confidence >= 0.9).length / results.matches.length) * 100 : 0;
        const coverage = totalProducts > 0 ? 
            (results.matches.length / totalProducts) * 100 : 0;
            
        return {
            accuracy,
            coverage,
            totalProducts,
            matchedProducts: results.matches.length,
            unmatchedProducts: results.unmatched.length,
            uncertainProducts: results.uncertain.length,
            highConfidenceMatches: results.matches.filter(m => m.confidence >= 0.9).length,
            mediumConfidenceMatches: results.matches.filter(m => m.confidence >= 0.7 && m.confidence < 0.9).length,
            lowConfidenceMatches: results.matches.filter(m => m.confidence < 0.7).length
        };
    }
}

// Doğrudan çalıştırılırsa görevi yürüt
if (require.main === module) {
    // Komut satırı parametrelerini al
    const args = process.argv.slice(2);
    let params = {};
    
    try {
        // İlk parametre JSON formatında parametreler olabilir
        if (args.length > 0) {
            params = JSON.parse(args[0]);
        }
    } catch (e) {
        console.error('Parametre formatı hatalı, varsayılan değerler kullanılacak');
    }
    
    const task = new ProductMatchingTask({ parameters: params });
    
    task.execute()
        .then(result => {
            process.exit(result.success ? 0 : 1);
        })
        .catch(err => {
            console.error('Görev çalıştırılırken kritik hata:', err);
            process.exit(1);
        });
}

module.exports = ProductMatchingTask;
