const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

class AzureExtensionsInstaller {
    constructor() {
        this.logFile = path.join(__dirname, 'auto_install_log.txt');
        this.extensions = [
            'ms-vscode.azure-account',
            'ms-azuretools.vscode-azureappservice',
            'ms-azuretools.vscode-azurefunctions',
            'ms-azuretools.vscode-azurestorage',
            'ms-azuretools.vscode-cosmosdb'
        ];
        
        this.startTime = new Date();
        this.log(`\n🚀 AZURE EXTENSIONS AUTO-INSTALLER - ${this.startTime.toLocaleString('tr-TR')}`);
        this.log('='.repeat(60));
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

    runCommand(command) {
        try {
            this.log(`▶️ Çalıştırılıyor: ${command}`);
            const output = execSync(command, { stdio: 'pipe', encoding: 'utf-8' });
            this.log(`✅ Başarılı:\n${output}`);
            return { success: true, output };
        } catch (error) {
            this.log(`❌ Hata: ${error.message}`);
            if (error.stdout) this.log(`Çıktı: ${error.stdout}`);
            if (error.stderr) this.log(`Hata: ${error.stderr}`);
            return { success: false, error };
        }
    }

    async installExtensions() {
        this.log('\n🔍 Cursor executable kontrol ediliyor...');
        
        // Cursor executable'ını bul
        let cursorPath = null;
        const possiblePaths = [
            '/Applications/Cursor.app/Contents/Resources/app/bin/cursor',
            '/usr/local/bin/cursor',
            '/opt/homebrew/bin/cursor',
            process.env.HOME + '/.cursor/bin/cursor'
        ];

        for (const path of possiblePaths) {
            if (fs.existsSync(path)) {
                cursorPath = path;
                this.log(`✅ Cursor bulundu: ${path}`);
                break;
            }
        }

        if (!cursorPath) {
            try {
                const whichOutput = execSync('which cursor', { encoding: 'utf-8' }).trim();
                if (whichOutput) {
                    cursorPath = whichOutput;
                    this.log(`✅ Cursor PATH\'te bulundu: ${cursorPath}`);
                }
            } catch (error) {
                this.log('❌ Cursor executable bulunamadı!');
                process.exit(1);
            }
        }

        // Eklentileri kur
        this.log('\n🚀 Azure eklentileri kuruluyor...');
        this.log('='.repeat(60));

        for (const ext of this.extensions) {
            this.log(`\n📦 ${ext} kuruluyor...`);
            const result = this.runCommand(`"${cursorPath}" --install-extension ${ext} --force`);
            
            if (!result.success) {
                this.log(`❌ ${ext} kurulumunda hata!`);
                // Kritik eklentiler için kontrol
                if (ext.includes('azure-account') || ext.includes('azureappservice')) {
                    this.log('🚨 KRİTİK HATA: Gerekli eklentiler kurulamadı!');
                    process.exit(1);
                }
            } else {
                this.log(`✅ ${ext} başarıyla kuruldu!`);
            }
            
            // Biraz bekle
            await new Promise(resolve => setTimeout(resolve, 2000));
        }

        this.log('\n🎉 TÜM EKLENTİLER KURULDU!');
        this.log('='.repeat(60));
        
        // Sonraki adımlar
        this.log('\n🚀 SONRAKİ ADIMLAR:');
        this.log('1. Cursor\'u yeniden başlatın');
        this.log('2. Cmd+Shift+P > "Azure: Sign In" ile Azure hesabınıza giriş yapın');
        this.log('3. Sol panelde Azure ikonunu kontrol edin');
        
        const endTime = new Date();
        const duration = (endTime - this.startTime) / 1000;
        this.log(`\n⏱️  Toplam süre: ${duration} saniye`);
        this.log('✅ KURULUM TAMAMLANDI! 🎉');
    }
}

// Çalıştır
const installer = new AzureExtensionsInstaller();
installer.installExtensions().catch(error => {
    console.error('Beklenmeyen hata:', error);
    process.exit(1);
});
