const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

class CursorExtensionInstaller {
    constructor() {
        this.extensions = [
            // Azure Extensions
            'ms-vscode.azure-account',
            'ms-azuretools.vscode-azureappservice',
            'ms-azuretools.vscode-azurefunctions',
            'ms-azuretools.vscode-azurestorage',
            'ms-azuretools.vscode-cosmosdb',
            
            // Frontend Development
            'esbenp.prettier-vscode',
            'dbaeumer.vscode-eslint',
            'bradlc.vscode-tailwindcss',
            'formulahendry.auto-rename-tag',
            'naumovs.color-highlight',
            
            // Productivity
            'eamodio.gitlens',
            'mikestead.dotenv',
            'streetsidesoftware.code-spell-checker',
            'github.copilot',
            'github.vscode-pull-request-github'
        ];
        
        this.logFile = path.join(__dirname, 'cursor_install.log');
        this.startTime = new Date();
        this.installedCount = 0;
        this.failedCount = 0;
        
        this.log(`\n🚀 CURSOR EXTENSION INSTALLER - ${this.startTime.toLocaleString('tr-TR')}`);
        this.log('='.repeat(70));
    }
    
    log(message) {
        const timestamp = new Date().toLocaleString('tr-TR');
        const logMessage = `[${timestamp}] ${message}`;
        console.log(logMessage);
        
        try {
            fs.appendFileSync(this.logFile, logMessage + '\n');
        } catch (error) {
            console.error('Log yazma hatası:', error.message);
        }
    }
    
    async installExtensions() {
        this.log('🔍 Gerekli eklentiler kontrol ediliyor...');
        
        for (const ext of this.extensions) {
            try {
                this.log(`\n📦 ${ext} yükleniyor...`);
                
                // Eklentiyi yükle
                execSync(`cursor --install-extension ${ext} --force`, {
                    stdio: 'inherit',
                    timeout: 60000 // 60 saniye timeout
                });
                
                this.log(`✅ ${ext} başarıyla yüklendi!`);
                this.installedCount++;
                
            } catch (error) {
                this.log(`❌ ${ext} yüklenirken hata: ${error.message}`);
                this.failedCount++;
            }
            
            // Biraz bekle
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
        
        this.showSummary();
    }
    
    showSummary() {
        const endTime = new Date();
        const duration = ((endTime - this.startTime) / 1000).toFixed(2);
        
        this.log('\n' + '='.repeat(70));
        this.log('🎉 KURULUM ÖZETİ');
        this.log('='.repeat(70));
        this.log(`Toplam Eklenti: ${this.extensions.length}`);
        this.log(`Başarılı: ${this.installedCount}`);
        this.log(`Başarısız: ${this.failedCount}`);
        this.log(`Süre: ${duration} saniye`);
        this.log('='.repeat(70));
        
        if (this.failedCount > 0) {
            this.log('\n⚠️  Bazı eklentiler yüklenemedi. Manuel olarak yüklemek için:');
            this.log('1. Cursor\'u açın');
            this.log('2. Cmd+Shift+X ile Extensions panelini açın');
            this.log('3. Hata veren eklentileri tek tek arayıp yükleyin');
        }
        
        this.log('\n✅ KURULUM TAMAMLANDI!');
    }
}

// Çalıştır
const installer = new CursorExtensionInstaller();
installer.installExtensions().catch(console.error);
