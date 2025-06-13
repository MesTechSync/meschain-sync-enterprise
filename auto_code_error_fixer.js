#!/usr/bin/env node

/**
 * MesChain-Sync Otomatik Kod Hatası Tespit ve Düzeltme Sistemi
 * v1.0 - Haziran 2025
 * 
 * Bu script şu işlemleri yapar:
 * 1. JavaScript syntax hatalarını tespit eder
 * 2. HTML validation hatalarını kontrol eder
 * 3. CSS/SCSS hatalarını tespit eder
 * 4. Otomatik düzeltme önerileri sunar
 * 5. Güvenlik açıklarını tespit eder
 * 6. Performans sorunlarını analiz eder
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class MesChainCodeAnalyzer {
    constructor() {
        this.errors = [];
        this.warnings = [];
        this.suggestions = [];
        this.fixedIssues = [];
        
        this.projectPath = process.cwd();
        this.logFile = path.join(this.projectPath, 'code_analysis_report.md');
        
        // Analiz edilecek dosya tipleri
        this.fileTypes = {
            js: ['.js', '.jsx', '.ts', '.tsx'],
            html: ['.html', '.htm'],
            css: ['.css', '.scss', '.sass'],
            php: ['.php']
        };
        
        // console.log('🔍 MesChain-Sync Kod Analiz Sistemi Başlatılıyor...');
    }

    /**
     * Ana analiz fonksiyonu
     */
    async analyze() {
        try {
            // console.log('\n📂 Proje dosyaları taranıyor...');
            const files = this.getAllFiles(this.projectPath);
            
            // console.log(`📄 ${files.length} dosya bulundu`);
            
            // JavaScript dosyalarını analiz et
            await this.analyzeJavaScriptFiles(files);
            
            // HTML dosyalarını analiz et
            await this.analyzeHTMLFiles(files);
            
            // CSS dosyalarını analiz et
            await this.analyzeCSSFiles(files);
            
            // Güvenlik analizini çalıştır
            await this.securityAnalysis(files);
            
            // Performans analizini çalıştır
            await this.performanceAnalysis(files);
            
            // Otomatik düzeltmeleri uygula
            await this.applyAutoFixes();
            
            // Rapor oluştur
            await this.generateReport();
            
            // console.log('\n✅ Analiz tamamlandı!');
            // console.log(`📋 Rapor: ${this.logFile}`);
            
        } catch (error) {
            console.error('❌ Analiz sırasında hata:', error.message);
        }
    }

    /**
     * Tüm dosyaları özyinelemeli olarak bulur
     */
    getAllFiles(dir, fileList = []) {
        const files = fs.readdirSync(dir);
        
        files.forEach(file => {
            const filePath = path.join(dir, file);
            const stat = fs.statSync(filePath);
            
            if (stat.isDirectory()) {
                // node_modules, .git gibi klasörleri atla
                if (!['node_modules', '.git', 'dist', 'build'].includes(file)) {
                    this.getAllFiles(filePath, fileList);
                }
            } else {
                fileList.push(filePath);
            }
        });
        
        return fileList;
    }

    /**
     * JavaScript dosyalarını analiz eder
     */
    async analyzeJavaScriptFiles(files) {
        // console.log('\n🔍 JavaScript dosyaları analiz ediliyor...');
        
        const jsFiles = files.filter(file => 
            this.fileTypes.js.some(ext => file.endsWith(ext))
        );
        
        for (const file of jsFiles) {
            // console.log(`📄 Analiz ediliyor: ${path.basename(file)}`);
            
            try {
                const content = fs.readFileSync(file, 'utf8');
                
                // Syntax kontrolü
                this.checkJSSyntax(file, content);
                
                // Yaygın hataları tespit et
                this.detectCommonJSErrors(file, content);
                
                // ESLint benzeri kuralları kontrol et
                this.checkESLintRules(file, content);
                
            } catch (error) {
                this.errors.push({
                    file: file,
                    type: 'SYNTAX',
                    message: `Dosya okunurken hata: ${error.message}`,
                    line: 0
                });
            }
        }
    }

    /**
     * JavaScript syntax kontrolü
     */
    checkJSSyntax(file, content) {
        try {
            // Basit syntax kontrolü için eval yerine Function kullan
            new Function(content);
        } catch (error) {
            const match = error.message.match(/line (\d+)/);
            const line = match ? parseInt(match[1]) : 0;
            
            this.errors.push({
                file: file,
                type: 'SYNTAX_ERROR',
                message: error.message,
                line: line,
                autoFixable: false
            });
        }
    }

    /**
     * Yaygın JavaScript hatalarını tespit eder
     */
    detectCommonJSErrors(file, content) {
        const lines = content.split('\n');
        
        lines.forEach((line, index) => {
            const lineNum = index + 1;
            
            // Tanımlanmamış değişken kullanımı
            if (line.match(/console\.log\(.*undefined.*\)/)) {
                this.warnings.push({
                    file: file,
                    type: 'UNDEFINED_VARIABLE',
                    message: 'Tanımlanmamış değişken kullanılıyor olabilir',
                    line: lineNum,
                    autoFixable: false
                });
            }
            
            // Missing semicolon
            if (line.match(/^[\s]*[a-zA-Z_$][a-zA-Z0-9_$]*\s*=\s*.*[^;{}]\s*$/)) {
                this.warnings.push({
                    file: file,
                    type: 'MISSING_SEMICOLON',
                    message: 'Eksik noktalı virgül',
                    line: lineNum,
                    autoFixable: true,
                    fix: line + ';'
                });
            }
            
            // Unused variables (basit tespit)
            const varMatch = line.match(/(?:var|let|const)\s+([a-zA-Z_$][a-zA-Z0-9_$]*)/);
            if (varMatch) {
                const varName = varMatch[1];
                const usageRegex = new RegExp(`\\b${varName}\\b`, 'g');
                const matches = content.match(usageRegex);
                
                if (matches && matches.length === 1) {
                    this.warnings.push({
                        file: file,
                        type: 'UNUSED_VARIABLE',
                        message: `Kullanılmayan değişken: ${varName}`,
                        line: lineNum,
                        autoFixable: false
                    });
                }
            }
            
            // Function not defined errors
            const functionCallMatch = line.match(/this\.([a-zA-Z_$][a-zA-Z0-9_$]*)\(/);
            if (functionCallMatch) {
                const funcName = functionCallMatch[1];
                const funcDefRegex = new RegExp(`${funcName}\\s*\\(`);
                
                if (!content.match(funcDefRegex)) {
                    this.errors.push({
                        file: file,
                        type: 'FUNCTION_NOT_DEFINED',
                        message: `Tanımlanmamış fonksiyon çağrısı: ${funcName}`,
                        line: lineNum,
                        autoFixable: false
                    });
                }
            }
        });
    }

    /**
     * ESLint benzeri kuralları kontrol eder
     */
    checkESLintRules(file, content) {
        const lines = content.split('\n');
        
        lines.forEach((line, index) => {
            const lineNum = index + 1;
            
            // console.log kullanımı
            if (line.includes('console.log') && !line.includes('//')) {
                this.warnings.push({
                    file: file,
                    type: 'CONSOLE_LOG',
                    message: 'Production kodunda // console.log kullanımı',
                    line: lineNum,
                    autoFixable: true,
                    fix: line.replace('console.log', '// console.log')
                });
            }
            
            // Equality operatörü
            if (line.match(/[^!=]===[^=]/)) {
                this.warnings.push({
                    file: file,
                    type: 'LOOSE_EQUALITY',
                    message: '=== yerine === kullanın',
                    line: lineNum,
                    autoFixable: true,
                    fix: line.replace(/([^!=])===([^=])/g, '$1===$2')
                });
            }
            
            // var yerine let/const
            if (line.match(/^\s*var\s+/)) {
                this.suggestions.push({
                    file: file,
                    type: 'VAR_TO_LET_CONST',
                    message: 'var yerine let veya const kullanmayı düşünün',
                    line: lineNum,
                    autoFixable: true,
                    fix: line.replace(/^\s*var\s+/, 'let ')
                });
            }
        });
    }

    /**
     * HTML dosyalarını analiz eder
     */
    async analyzeHTMLFiles(files) {
        // console.log('\n🔍 HTML dosyaları analiz ediliyor...');
        
        const htmlFiles = files.filter(file => 
            this.fileTypes.html.some(ext => file.endsWith(ext))
        );
        
        for (const file of htmlFiles) {
            // console.log(`📄 Analiz ediliyor: ${path.basename(file)}`);
            
            try {
                const content = fs.readFileSync(file, 'utf8');
                this.validateHTML(file, content);
            } catch (error) {
                this.errors.push({
                    file: file,
                    type: 'FILE_READ_ERROR',
                    message: `Dosya okunurken hata: ${error.message}`,
                    line: 0
                });
            }
        }
    }

    /**
     * HTML doğrulama
     */
    validateHTML(file, content) {
        const lines = content.split('\n');
        
        lines.forEach((line, index) => {
            const lineNum = index + 1;
            
            // Kapatılmamış taglar (basit kontrol)
            const openTags = line.match(/<([a-zA-Z][a-zA-Z0-9]*)[^>]*>/g);
            const closeTags = line.match(/<\/([a-zA-Z][a-zA-Z0-9]*)>/g);
            
            if (openTags && !closeTags) {
                const selfClosingTags = ['img', 'br', 'hr', 'input', 'meta', 'link'];
                const hasUnclosedTags = openTags.some(tag => {
                    const tagName = tag.match(/<([a-zA-Z][a-zA-Z0-9]*)/)[1].toLowerCase();
                    return !selfClosingTags.includes(tagName) && !tag.endsWith('/>');
                });
                
                if (hasUnclosedTags) {
                    this.warnings.push({
                        file: file,
                        type: 'UNCLOSED_TAG',
                        message: 'Kapatılmamış HTML tag olabilir',
                        line: lineNum,
                        autoFixable: false
                    });
                }
            }
            
            // Alt attribute eksikliği
            if (line.match(/<img[^>]*src=[^>]*>/i) && !line.match(/alt=/i)) {
                this.warnings.push({
                    file: file,
                    type: 'MISSING_ALT',
                    message: 'img taginde alt attribute eksik',
                    line: lineNum,
                    autoFixable: true,
                    fix: line.replace(/<img([^>]*)>/i, '<img$1 alt="">')
                });
            }
            
            // DOCTYPE kontrolü (sadece ilk satır için)
            if (lineNum === 1 && !content.toLowerCase().includes('<!doctype')) {
                this.warnings.push({
                    file: file,
                    type: 'MISSING_DOCTYPE',
                    message: 'DOCTYPE bildirimi eksik',
                    line: 1,
                    autoFixable: true,
                    fix: '<!DOCTYPE html>\n' + content
                });
            }
        });
    }

    /**
     * CSS dosyalarını analiz eder
     */
    async analyzeCSSFiles(files) {
        // console.log('\n🔍 CSS dosyaları analiz ediliyor...');
        
        const cssFiles = files.filter(file => 
            this.fileTypes.css.some(ext => file.endsWith(ext))
        );
        
        for (const file of cssFiles) {
            // console.log(`📄 Analiz ediliyor: ${path.basename(file)}`);
            
            try {
                const content = fs.readFileSync(file, 'utf8');
                this.validateCSS(file, content);
            } catch (error) {
                this.errors.push({
                    file: file,
                    type: 'FILE_READ_ERROR',
                    message: `Dosya okunurken hata: ${error.message}`,
                    line: 0
                });
            }
        }
    }

    /**
     * CSS doğrulama
     */
    validateCSS(file, content) {
        const lines = content.split('\n');
        
        lines.forEach((line, index) => {
            const lineNum = index + 1;
            
            // Eksik noktalı virgül
            if (line.match(/:\s*[^;{}]+$/) && !line.trim().endsWith('}')) {
                this.warnings.push({
                    file: file,
                    type: 'MISSING_CSS_SEMICOLON',
                    message: 'CSS özelliğinde eksik noktalı virgül',
                    line: lineNum,
                    autoFixable: true,
                    fix: line + ';'
                });
            }
            
            // Kullanılmayan vendor prefix'ler
            if (line.match(/-webkit-|-moz-|-ms-|-o-/)) {
                this.suggestions.push({
                    file: file,
                    type: 'VENDOR_PREFIX',
                    message: 'Vendor prefix gerekli olup olmadığını kontrol edin',
                    line: lineNum,
                    autoFixable: false
                });
            }
        });
    }

    /**
     * Güvenlik analizi
     */
    async securityAnalysis(files) {
        // console.log('\n🔒 Güvenlik analizi yapılıyor...');
        
        for (const file of files) {
            try {
                const content = fs.readFileSync(file, 'utf8');
                const lines = content.split('\n');
                
                lines.forEach((line, index) => {
                    const lineNum = index + 1;
                    
                    // eval() kullanımı
                    if (line.includes('eval(')) {
                        this.errors.push({
                            file: file,
                            type: 'SECURITY_EVAL',
                            message: 'eval() kullanımı güvenlik riski oluşturur',
                            line: lineNum,
                            autoFixable: false
                        });
                    }
                    
                    // innerHTML kullanımı
                    if (line.includes('innerHTML') && !line.includes('textContent')) {
                        this.warnings.push({
                            file: file,
                            type: 'SECURITY_INNERHTML',
                            message: 'innerHTML yerine textContent kullanmayı düşünün',
                            line: lineNum,
                            autoFixable: false
                        });
                    }
                    
                    // Hardcoded passwords/keys
                    if (line.match(/(password|key|secret|token)\s*[=:]\s*['"]/i)) {
                        this.errors.push({
                            file: file,
                            type: 'SECURITY_HARDCODED',
                            message: 'Hardcoded şifre/anahtar tespit edildi',
                            line: lineNum,
                            autoFixable: false
                        });
                    }
                });
                
            } catch (error) {
                // Dosya okuma hatası
            }
        }
    }

    /**
     * Performans analizi
     */
    async performanceAnalysis(files) {
        // console.log('\n⚡ Performans analizi yapılıyor...');
        
        for (const file of files) {
            try {
                const content = fs.readFileSync(file, 'utf8');
                const lines = content.split('\n');
                
                lines.forEach((line, index) => {
                    const lineNum = index + 1;
                    
                    // document.write kullanımı
                    if (line.includes('document.write')) {
                        this.warnings.push({
                            file: file,
                            type: 'PERFORMANCE_DOCUMENT_WRITE',
                            message: 'document.write performans sorunu yaratabilir',
                            line: lineNum,
                            autoFixable: false
                        });
                    }
                    
                    // Synchronous AJAX
                    if (line.includes('async: false') || line.includes('async:false')) {
                        this.warnings.push({
                            file: file,
                            type: 'PERFORMANCE_SYNC_AJAX',
                            message: 'Synchronous AJAX kullanımı performansı etkiler',
                            line: lineNum,
                            autoFixable: false
                        });
                    }
                });
                
            } catch (error) {
                // Dosya okuma hatası
            }
        }
    }

    /**
     * Otomatik düzeltmeleri uygula
     */
    async applyAutoFixes() {
        // console.log('\n🔧 Otomatik düzeltmeler uygulanıyor...');
        
        const autoFixableIssues = [
            ...this.errors.filter(e => e.autoFixable),
            ...this.warnings.filter(w => w.autoFixable),
            ...this.suggestions.filter(s => s.autoFixable)
        ];
        
        const groupedByFile = {};
        autoFixableIssues.forEach(issue => {
            if (!groupedByFile[issue.file]) {
                groupedByFile[issue.file] = [];
            }
            groupedByFile[issue.file].push(issue);
        });
        
        for (const [file, issues] of Object.entries(groupedByFile)) {
            try {
                let content = fs.readFileSync(file, 'utf8');
                let lines = content.split('\n');
                
                // Satır numarasına göre sırala (tersine)
                issues.sort((a, b) => b.line - a.line);
                
                issues.forEach(issue => {
                    if (issue.fix && issue.line > 0) {
                        lines[issue.line - 1] = issue.fix;
                        this.fixedIssues.push(issue);
                    }
                });
                
                // Backup oluştur
                const backupFile = file + '.backup.' + Date.now();
                fs.writeFileSync(backupFile, content);
                
                // Düzeltilmiş içeriği yaz
                fs.writeFileSync(file, lines.join('\n'));
                
                // console.log(`✅ ${path.basename(file)} - ${issues.length} sorun düzeltildi`);
                
            } catch (error) {
                console.error(`❌ Düzeltme hatası ${file}: ${error.message}`);
            }
        }
    }

    /**
     * Analiz raporu oluştur
     */
    async generateReport() {
        const reportContent = `# MesChain-Sync Kod Analiz Raporu
**Tarih:** ${new Date().toLocaleString('tr-TR')}
**Analiz Edilen Proje:** ${path.basename(this.projectPath)}

## 📊 Özet
- **Hatalar:** ${this.errors.length}
- **Uyarılar:** ${this.warnings.length}
- **Öneriler:** ${this.suggestions.length}
- **Otomatik Düzeltilen:** ${this.fixedIssues.length}

## ❌ Hatalar (${this.errors.length})
${this.errors.map(error => 
    `### ${error.type}
**Dosya:** \`${path.basename(error.file)}\`
**Satır:** ${error.line}
**Mesaj:** ${error.message}
**Otomatik Düzeltme:** ${error.autoFixable ? '✅' : '❌'}
`).join('\n')}

## ⚠️ Uyarılar (${this.warnings.length})
${this.warnings.map(warning => 
    `### ${warning.type}
**Dosya:** \`${path.basename(warning.file)}\`
**Satır:** ${warning.line}
**Mesaj:** ${warning.message}
**Otomatik Düzeltme:** ${warning.autoFixable ? '✅' : '❌'}
`).join('\n')}

## 💡 Öneriler (${this.suggestions.length})
${this.suggestions.map(suggestion => 
    `### ${suggestion.type}
**Dosya:** \`${path.basename(suggestion.file)}\`
**Satır:** ${suggestion.line}
**Mesaj:** ${suggestion.message}
**Otomatik Düzeltme:** ${suggestion.autoFixable ? '✅' : '❌'}
`).join('\n')}

## ✅ Otomatik Düzeltilen Sorunlar (${this.fixedIssues.length})
${this.fixedIssues.map(fixed => 
    `### ${fixed.type}
**Dosya:** \`${path.basename(fixed.file)}\`
**Satır:** ${fixed.line}
**Mesaj:** ${fixed.message}
`).join('\n')}

## 🔧 Önerilen Araçlar ve Eklentiler

### VS Code Eklentileri:
1. **ESLint** - JavaScript kod kalitesi
2. **Prettier** - Kod formatlama
3. **HTMLHint** - HTML doğrulama
4. **Stylelint** - CSS/SCSS analizi
5. **SonarLint** - Kod kalitesi ve güvenlik
6. **Auto Rename Tag** - HTML tag eşleştirme
7. **Bracket Pair Colorizer** - Bracket eşleştirme
8. **Error Lens** - Inline hata gösterimi

### Geliştirme Araçları:
1. **Webpack Bundle Analyzer** - Bundle analizi
2. **Lighthouse** - Performans testi
3. **JSHint** - JavaScript analizi
4. **W3C Validator** - HTML/CSS doğrulama
5. **OWASP ZAP** - Güvenlik testi

### Kurulum Komutları:
\`\`\`bash
# ESLint kurulumu
npm install -g eslint
npm install --save-dev @eslint/js

# Prettier kurulumu
npm install -g prettier

# Stylelint kurulumu
npm install -g stylelint stylelint-config-standard

# HTMLHint kurulumu
npm install -g htmlhint
\`\`\`

### Otomatik Düzeltme Komutları:
\`\`\`bash
# ESLint ile otomatik düzeltme
eslint --fix **/*.js

# Prettier ile kod formatlama
prettier --write **/*.{js,html,css}

# Bu scripti çalıştırma
node auto_code_error_fixer.js
\`\`\`

---
**Not:** Bu rapor MesChain-Sync Otomatik Kod Analiz Sistemi v1.0 tarafından oluşturulmuştur.
`;

        fs.writeFileSync(this.logFile, reportContent);
        // console.log(`📋 Rapor oluşturuldu: ${this.logFile}`);
    }
}

// Script çalıştırma
if (require.main === module) {
    const analyzer = new MesChainCodeAnalyzer();
    analyzer.analyze().catch(console.error);
}

module.exports = MesChainCodeAnalyzer; 