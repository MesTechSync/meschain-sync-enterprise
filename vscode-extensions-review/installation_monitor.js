#!/usr/bin/env node

/**
 * 🚨 AZURE EXTENSIONS INSTALLATION MONITOR
 * Real-time installation progress tracker for Cursor team tasks
 * 
 * @author MesChain Development Team
 * @date June 13, 2025, 12:46
 * @priority CRITICAL - Live installation monitoring
 */

const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

class InstallationMonitor {
    constructor() {
        this.startTime = new Date();
        this.installationSteps = [
            { id: 'extensions_panel', name: 'Extensions Panel Açıldı', status: 'pending' },
            { id: 'azure_account_search', name: 'Azure Account Arandı', status: 'pending' },
            { id: 'azure_account_install', name: 'Azure Account Kuruldu', status: 'pending' },
            { id: 'azure_app_service_search', name: 'Azure App Service Arandı', status: 'pending' },
            { id: 'azure_app_service_install', name: 'Azure App Service Kuruldu', status: 'pending' },
            { id: 'cursor_reload', name: 'Cursor Yeniden Başlatıldı', status: 'pending' },
            { id: 'azure_signin', name: 'Azure Hesabına Giriş', status: 'pending' },
            { id: 'verification', name: 'Doğrulama Testleri', status: 'pending' }
        ];
        
        this.criticalExtensions = [
            'ms-vscode.azure-account',
            'ms-azuretools.vscode-azureappservice'
        ];
        
        this.logFile = path.join(__dirname, 'installation_log.txt');
        this.statusFile = path.join(__dirname, 'installation_status.json');
        
        this.initializeMonitoring();
    }

    initializeMonitoring() {
        console.log('🚨 AZURE EXTENSIONS KURULUM İZLEYİCİSİ BAŞLATILDI');
        console.log('=' .repeat(60));
        console.log(`⏰ Başlangıç Zamanı: ${this.startTime.toLocaleString('tr-TR')}`);
        console.log(`📁 Log Dosyası: ${this.logFile}`);
        console.log(`📊 Durum Dosyası: ${this.statusFile}`);
        console.log('=' .repeat(60));
        
        this.log('🚨 KURULUM İZLEME BAŞLATILDI');
        this.log(`Başlangıç: ${this.startTime.toISOString()}`);
        
        // İlk durum kaydet
        this.saveStatus();
        
        // Periyodik kontrol başlat
        this.startPeriodicCheck();
        
        // Kullanıcı talimatları
        this.showUserInstructions();
    }

    log(message) {
        const timestamp = new Date().toLocaleString('tr-TR');
        const logEntry = `[${timestamp}] ${message}\n`;
        
        console.log(`[${timestamp}] ${message}`);
        
        try {
            fs.appendFileSync(this.logFile, logEntry);
        } catch (error) {
            console.error('Log yazma hatası:', error.message);
        }
    }

    saveStatus() {
        const status = {
            startTime: this.startTime.toISOString(),
            currentTime: new Date().toISOString(),
            elapsedMinutes: Math.round((Date.now() - this.startTime.getTime()) / 60000),
            steps: this.installationSteps,
            progress: this.calculateProgress(),
            criticalExtensions: this.criticalExtensions,
            nextAction: this.getNextAction()
        };
        
        try {
            fs.writeFileSync(this.statusFile, JSON.stringify(status, null, 2));
        } catch (error) {
            console.error('Durum kaydetme hatası:', error.message);
        }
    }

    calculateProgress() {
        const completedSteps = this.installationSteps.filter(step => step.status === 'completed').length;
        const totalSteps = this.installationSteps.length;
        return Math.round((completedSteps / totalSteps) * 100);
    }

    getNextAction() {
        const pendingStep = this.installationSteps.find(step => step.status === 'pending');
        return pendingStep ? pendingStep.name : 'Kurulum Tamamlandı!';
    }

    checkExtensionInstallation() {
        return new Promise((resolve) => {
            // Cursor executable'ını bulmaya çalış
            const possiblePaths = [
                '/Applications/Cursor.app/Contents/Resources/app/bin/cursor',
                '/usr/local/bin/cursor',
                '/opt/homebrew/bin/cursor'
            ];
            
            let cursorPath = null;
            for (const path of possiblePaths) {
                if (fs.existsSync(path)) {
                    cursorPath = path;
                    break;
                }
            }
            
            if (!cursorPath) {
                // which komutu ile dene
                exec('which cursor', (error, stdout) => {
                    if (!error && stdout.trim()) {
                        cursorPath = stdout.trim();
                    } else {
                        exec('which code', (error, stdout) => {
                            if (!error && stdout.trim()) {
                                cursorPath = stdout.trim();
                            }
                        });
                    }
                });
            }
            
            if (cursorPath) {
                exec(`"${cursorPath}" --list-extensions`, (error, stdout) => {
                    if (error) {
                        this.log(`❌ Eklenti listesi alınamadı: ${error.message}`);
                        resolve({ success: false, extensions: [] });
                        return;
                    }
                    
                    const installedExtensions = stdout.split('\n').filter(ext => ext.trim());
                    const azureExtensions = installedExtensions.filter(ext => 
                        ext.includes('azure') || ext.includes('ms-vscode.azure')
                    );
                    
                    this.log(`📦 Kurulu eklentiler kontrol edildi: ${installedExtensions.length} eklenti`);
                    this.log(`🔵 Azure eklentileri: ${azureExtensions.length} adet`);
                    
                    resolve({ 
                        success: true, 
                        extensions: installedExtensions,
                        azureExtensions: azureExtensions
                    });
                });
            } else {
                this.log('⚠️ Cursor executable bulunamadı - Manuel kontrol gerekli');
                resolve({ success: false, extensions: [] });
            }
        });
    }

    async startPeriodicCheck() {
        const checkInterval = 30000; // 30 saniye
        
        setInterval(async () => {
            this.log('🔍 Periyodik eklenti kontrolü yapılıyor...');
            
            const result = await this.checkExtensionInstallation();
            
            if (result.success) {
                // Azure Account kontrolü
                if (result.azureExtensions.some(ext => ext.includes('ms-vscode.azure-account'))) {
                    this.updateStepStatus('azure_account_install', 'completed');
                    this.log('✅ Azure Account eklentisi tespit edildi!');
                }
                
                // Azure App Service kontrolü
                if (result.azureExtensions.some(ext => ext.includes('ms-azuretools.vscode-azureappservice'))) {
                    this.updateStepStatus('azure_app_service_install', 'completed');
                    this.log('✅ Azure App Service eklentisi tespit edildi!');
                }
                
                // İlerleme raporu
                const progress = this.calculateProgress();
                this.log(`📊 Kurulum İlerlemesi: %${progress}`);
                
                if (progress === 100) {
                    this.log('🎉 KURULUM TAMAMLANDI!');
                    this.showCompletionMessage();
                }
            }
            
            this.saveStatus();
            this.showCurrentStatus();
            
        }, checkInterval);
    }

    updateStepStatus(stepId, status) {
        const step = this.installationSteps.find(s => s.id === stepId);
        if (step && step.status !== status) {
            step.status = status;
            this.log(`🔄 Adım güncellendi: ${step.name} -> ${status}`);
        }
    }

    showCurrentStatus() {
        console.log('\n' + '='.repeat(60));
        console.log('📊 MEVCUT KURULUM DURUMU');
        console.log('='.repeat(60));
        
        this.installationSteps.forEach((step, index) => {
            const statusIcon = step.status === 'completed' ? '✅' : 
                             step.status === 'in_progress' ? '🔄' : '⏳';
            console.log(`${index + 1}. ${statusIcon} ${step.name}`);
        });
        
        const progress = this.calculateProgress();
        const elapsed = Math.round((Date.now() - this.startTime.getTime()) / 60000);
        
        console.log('='.repeat(60));
        console.log(`📈 İlerleme: %${progress}`);
        console.log(`⏰ Geçen Süre: ${elapsed} dakika`);
        console.log(`🎯 Sonraki Adım: ${this.getNextAction()}`);
        console.log('='.repeat(60));
    }

    showUserInstructions() {
        console.log('\n🚨 KULLANICI TALİMATLARI:');
        console.log('1. Cursor\'da Cmd+Shift+X ile Extensions panelini aç');
        console.log('2. "Azure Account" ara ve Microsoft\'un resmi eklentisini kur');
        console.log('3. "Azure App Service" ara ve Microsoft Azure Tools eklentisini kur');
        console.log('4. Cursor\'u yeniden başlat (Cmd+Shift+P > Developer: Reload Window)');
        console.log('5. Azure hesabına giriş yap (Cmd+Shift+P > Azure: Sign In)');
        console.log('\n📊 Bu izleyici kurulum ilerlemesini otomatik takip edecek...\n');
    }

    showCompletionMessage() {
        console.log('\n' + '🎉'.repeat(20));
        console.log('🎉 KURULUM BAŞARIYLA TAMAMLANDI! 🎉');
        console.log('🎉'.repeat(20));
        console.log('\n✅ Tamamlanan İşlemler:');
        console.log('- Azure Account eklentisi kuruldu');
        console.log('- Azure App Service eklentisi kuruldu');
        console.log('- Cursor yapılandırması tamamlandı');
        console.log('\n🚀 Cursor Team Görevleri için hazır!');
        console.log('- Super Admin Panel modernizasyonu devam edebilir');
        console.log('- Trendyol API entegrasyonu devam edebilir');
        console.log('- Azure deployment işlemleri aktif');
        console.log('\n📊 Sonraki Adımlar:');
        console.log('1. Sol panelde Azure ikonunu kontrol et');
        console.log('2. Azure subscription\'larını seç');
        console.log('3. Deployment işlemlerine başla');
    }
}

// Kurulum izleyicisini başlat
if (require.main === module) {
    console.log('🚨 AZURE EXTENSIONS KURULUM İZLEYİCİSİ');
    console.log('Başlatılıyor...\n');
    
    const monitor = new InstallationMonitor();
    
    // Graceful shutdown
    process.on('SIGINT', () => {
        console.log('\n\n🛑 Kurulum izleyicisi durduruluyor...');
        monitor.log('🛑 İzleme durduruldu');
        process.exit(0);
    });
}

module.exports = InstallationMonitor;
