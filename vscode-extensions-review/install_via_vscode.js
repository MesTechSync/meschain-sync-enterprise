const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const readline = require('readline');

class VSCodeExtensionInstaller {
    constructor() {
        this.logFile = path.join(__dirname, 'vscode_install_log.txt');
        this.extensions = [
            'ms-vscode.azure-account',
            'ms-azuretools.vscode-azureappservice',
            'ms-azuretools.vscode-azurefunctions',
            'ms-azuretools.vscode-azurestorage',
            'ms-azuretools.vscode-cosmosdb'
        ];
        
        this.startTime = new Date();
        this.log(`\n🚀 VSCODE AZURE EXTENSIONS INSTALLER - ${this.startTime.toLocaleString('tr-TR')}`);
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

    async run() {
        this.log('🔍 VSCode/Cursor kontrol ediliyor...');
        
        try {
            // Önce VSCode'u bulmaya çalış
            let vscodePath = null;
            const possiblePaths = [
                '/Applications/Visual Studio Code.app/Contents/Resources/app/bin/code',
                '/usr/local/bin/code',
                '/usr/bin/code',
                path.join(process.env.HOME, '/.vscode-server/bin/**/bin/code')
            ];

            for (const p of possiblePaths) {
                try {
                    const resolvedPath = p.includes('**') 
                        ? execSync(`ls -d ${p} 2>/dev/null | head -1`, { encoding: 'utf-8' }).trim()
                        : p;
                        
                    if (resolvedPath && fs.existsSync(resolvedPath)) {
                        vscodePath = resolvedPath;
                        this.log(`✅ VSCode bulundu: ${vscodePath}`);
                        break;
                    }
                } catch (e) {
                    // Hata durumunda devam et
                }
            }

            if (!vscodePath) {
                // which komutu ile dene
                try {
                    const whichOutput = execSync('which code', { encoding: 'utf-8' }).trim();
                    if (whichOutput) {
                        vscodePath = whichOutput;
                        this.log(`✅ VSCode PATH\'te bulundu: ${vscodePath}`);
                    }
                } catch (e) {
                    this.log('❌ VSCode bulunamadı!');
                }
            }

            if (!vscodePath) {
                this.log('❌ VSCode veya Cursor bulunamadı!');
                this.log('Lütfen manuel olarak kurulum yapın:');
                this.log('1. Cursor\'u açın');
                this.log('2. Cmd+Shift+X ile Extensions\'a gidin');
                this.log('3. "Azure Account" ve "Azure App Service" eklentilerini arayıp kurun');
                return;
            }

            // Eklentileri kur
            this.log('\n🚀 Azure eklentileri kuruluyor...');
            this.log('='.repeat(70));

            for (const ext of this.extensions) {
                this.log(`\n📦 ${ext} kuruluyor...`);
                
                try {
                    const result = execSync(`"${vscodePath}" --install-extension ${ext} --force`, {
                        stdio: 'pipe',
                        encoding: 'utf-8',
                        timeout: 30000 // 30 saniye timeout
                    });
                    
                    this.log(`✅ ${ext} başarıyla kuruldu!`);
                    console.log(result);
                    
                } catch (error) {
                    this.log(`❌ ${ext} kurulumunda hata!`);
                    if (error.stdout) this.log(`Çıktı: ${error.stdout}`);
                    if (error.stderr) this.log(`Hata: ${error.stderr}`);
                    
                    // Kritik eklentiler için kontrol
                    if (ext.includes('azure-account') || ext.includes('azureappservice')) {
                        this.log('🚨 KRİTİK HATA: Gerekli eklentiler kurulamadı!');
                    }
                }
                
                // Biraz bekle
                await new Promise(resolve => setTimeout(resolve, 2000));
            }

            this.log('\n🎉 KURULUM TAMAMLANDI!');
            this.log('='.repeat(70));
            
            // Sonraki adımlar
            this.log('\n🚀 SONRAKİ ADIMLAR:');
            this.log('1. Cursor\'u yeniden başlatın');
            this.log('2. Cmd+Shift+P > "Azure: Sign In" ile Azure hesabınıza giriş yapın');
            this.log('3. Sol panelde Azure ikonunu kontrol edin');
            
            const endTime = new Date();
            const duration = ((endTime - this.startTime) / 1000).toFixed(2);
            this.log(`\n⏱️  Toplam süre: ${duration} saniye`);
            this.log('✅ KURULUM TAMAMLANDI! 🎉');
            
        } catch (error) {
            this.log(`❌ Beklenmeyen hata: ${error.message}`);
            if (error.stdout) this.log(`Çıktı: ${error.stdout}`);
            if (error.stderr) this.log(`Hata: ${error.stderr}`);
        }
    }
}

// Çalıştır
const installer = new VSCodeExtensionInstaller();
installer.run().catch(console.error);
