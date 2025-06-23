#!/bin/bash
# && OPERATÖRÜ TESPİT VE ENGELLEME SİSTEMİ
# Tüm proje dosyalarında && operatörünü tarar ve engeller

set -e

# Renkli output için
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Log dosyası
LOG_FILE="ampersand_scan_$(date +%Y%m%d_%H%M%S).log"
REPORT_FILE="AMPERSAND_SECURITY_REPORT_$(date +%Y%m%d).md"

echo "🔍 && OPERATÖRÜ TESPİT VE ENGELLEME SİSTEMİ" | tee "$LOG_FILE"
echo "================================================" | tee -a "$LOG_FILE"
echo "📅 Tarih: $(date)" | tee -a "$LOG_FILE"
echo "🎯 Amaç: && operatörü kullanımını tespit et ve engelle" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# İstatistikler
TOTAL_FILES=0
VIOLATION_FILES=0
TOTAL_VIOLATIONS=0

# Taranan dosya türleri
FILE_TYPES=(
    "*.js"
    "*.ts"
    "*.jsx"
    "*.tsx"
    "*.json"
    "*.sh"
    "*.bash"
    "*.zsh"
    "*.yml"
    "*.yaml"
    "*.md"
    "*.php"
    "*.py"
    "*.rb"
    "*.go"
    "*.java"
    "*.cpp"
    "*.c"
    "*.html"
    "*.css"
    "*.scss"
    "*.sass"
    "*.vue"
    "*.svelte"
    "Dockerfile*"
    "*.env*"
    "Makefile*"
    "*.conf"
    "*.ini"
)

echo -e "${BLUE}🔍 TARAMA BAŞLANIYOR...${NC}" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Ana tarama fonksiyonu
scan_files() {
    local pattern="$1"
    echo -e "${YELLOW}📁 $pattern dosyaları taranıyor...${NC}" | tee -a "$LOG_FILE"
    
    find . -name "$pattern" -type f 2>/dev/null | while read -r file; do
        # node_modules, .git, ve diğer ignore edilecek dizinleri atla
        if [[ "$file" == *"/node_modules/"* ]] || \
           [[ "$file" == *"/.git/"* ]] || \
           [[ "$file" == *"/vendor/"* ]] || \
           [[ "$file" == *"/build/"* ]] || \
           [[ "$file" == *"/dist/"* ]] || \
           [[ "$file" == *"/.next/"* ]] || \
           [[ "$file" == *"/coverage/"* ]]; then
            continue
        fi
        
        ((TOTAL_FILES++))
        
        # && operatörü ara
        if grep -n "&&" "$file" > /dev/null 2>&1; then
            ((VIOLATION_FILES++))
            echo -e "${RED}❌ İhlal tespit edildi: $file${NC}" | tee -a "$LOG_FILE"
            
            # Detaylı ihlal bilgisi
            while IFS=: read -r line_num line_content; do
                ((TOTAL_VIOLATIONS++))
                echo -e "   ${RED}Satır $line_num:${NC} $line_content" | tee -a "$LOG_FILE"
            done < <(grep -n "&&" "$file")
            
            echo "" | tee -a "$LOG_FILE"
        fi
    done
}

# Her dosya türü için tarama yap
for file_type in "${FILE_TYPES[@]}"; do
    scan_files "$file_type"
done

echo -e "${BLUE}📊 TARAMA SONUÇLARI${NC}" | tee -a "$LOG_FILE"
echo "==================" | tee -a "$LOG_FILE"
echo "📁 Toplam taranan dosya: $TOTAL_FILES" | tee -a "$LOG_FILE"
echo "⚠️ İhlal içeren dosya sayısı: $VIOLATION_FILES" | tee -a "$LOG_FILE"
echo "🚨 Toplam ihlal sayısı: $TOTAL_VIOLATIONS" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Detaylı rapor oluştur
create_detailed_report() {
    cat > "$REPORT_FILE" << EOF
# 🚨 && OPERATÖRÜ GÜVENLİK RAPORU

**📅 Tarih:** $(date)  
**🎯 Tarama Kapsamı:** Tüm proje dosyaları  
**🔍 Taranan Dosya Türleri:** ${#FILE_TYPES[@]} farklı dosya türü  

---

## 📊 ÖZET İSTATİSTİKLER

| Metrik | Değer |
|--------|-------|
| 📁 Toplam Taranan Dosya | $TOTAL_FILES |
| ⚠️ İhlal İçeren Dosya | $VIOLATION_FILES |
| 🚨 Toplam İhlal Sayısı | $TOTAL_VIOLATIONS |
| 📈 İhlal Oranı | $(( VIOLATION_FILES * 100 / (TOTAL_FILES > 0 ? TOTAL_FILES : 1) ))% |

---

## 🔍 DETAYLI İHLAL RAPORU

EOF

    if [ "$TOTAL_VIOLATIONS" -eq 0 ]; then
        cat >> "$REPORT_FILE" << EOF
### ✅ TEBRİKLER!

Bu projede **hiçbir && operatörü ihlali** tespit edilmedi.

**🏆 Güvenlik Skoru:** 100/100  
**🛡️ Durum:** GÜVENLI  
**📋 Öneri:** Mevcut güvenlik standartlarını koruyun  

EOF
    else
        cat >> "$REPORT_FILE" << EOF
### ⚠️ İHLAL TESPİT EDİLDİ!

Aşağıdaki dosyalarda && operatörü kullanımı tespit edildi:

EOF
        
        # İhlalleri detayına listele
        for file_type in "${FILE_TYPES[@]}"; do
            find . -name "$file_type" -type f 2>/dev/null | while read -r file; do
                if [[ "$file" == *"/node_modules/"* ]] || \
                   [[ "$file" == *"/.git/"* ]] || \
                   [[ "$file" == *"/vendor/"* ]] || \
                   [[ "$file" == *"/build/"* ]] || \
                   [[ "$file" == *"/dist/"* ]]; then
                    continue
                fi
                
                if grep -n "&&" "$file" > /dev/null 2>&1; then
                    echo "" >> "$REPORT_FILE"
                    echo "#### 📄 \`$file\`" >> "$REPORT_FILE"
                    echo "" >> "$REPORT_FILE"
                    echo '```bash' >> "$REPORT_FILE"
                    grep -n "&&" "$file" | head -5 >> "$REPORT_FILE"
                    echo '```' >> "$REPORT_FILE"
                    echo "" >> "$REPORT_FILE"
                fi
            done
        done
        
        cat >> "$REPORT_FILE" << EOF

---

## 🛠️ ÇÖzüm ÖNERİLERİ

### 1. **Acil Düzeltme Adımları:**

\`\`\`bash
# Her ihlal için ayrı ayrı komut çalıştırın
# Örnek: npm install && npm start
# Yerine:
npm install
npm start
\`\`\`

### 2. **Güvenli Alternatifler:**

\`\`\`bash
# ❌ YANLIŞ
git add . && git commit -m "update" && git push

# ✅ DOĞRU
git add .
git commit -m "update"
git push
\`\`\`

### 3. **Script İyileştirmesi:**

\`\`\`bash
#!/bin/bash
set -e  # Hata durumunda dur

echo "Step 1: Installing..."
npm install

echo "Step 2: Testing..."
npm test

echo "Step 3: Building..."
npm run build
\`\`\`

---

## 📋 SONRAKI ADIMLAR

- [ ] Tüm ihlalleri düzelt
- [ ] Git pre-commit hook'ları kur
- [ ] Ekip eğitimini tamamla
- [ ] Otomatik koruma sistemini aktifleştir
- [ ] Haftalık güvenlik taraması planla

---

**🔐 Güvenlik Durumu:** KRİTİK - ACİL MÜDAHALE GEREKLİ  
**📞 İletişim:** security@meschain.com  
**📖 Kılavuz:** AMPERSAND_OPERATOR_PREVENTION_SYSTEM_JUNE7_2025.md

EOF
    fi
}

# Detaylı rapor oluştur
create_detailed_report

# Sonuç değerlendirmesi
if [ "$TOTAL_VIOLATIONS" -eq 0 ]; then
    echo -e "${GREEN}✅ TEBRİKLER! HİÇBİR && OPERATÖRÜ İHLALİ TESPİT EDİLMEDİ!${NC}" | tee -a "$LOG_FILE"
    echo -e "${GREEN}🏆 Güvenlik skoru: 100/100${NC}" | tee -a "$LOG_FILE"
    echo -e "${GREEN}🛡️ Proje güvenlik standartlarını karşılıyor${NC}" | tee -a "$LOG_FILE"
else
    echo -e "${RED}🚨 UYARI: $TOTAL_VIOLATIONS ADET && OPERATÖRÜ İHLALİ TESPİT EDİLDİ!${NC}" | tee -a "$LOG_FILE"
    echo -e "${RED}⚠️ Bu ihlaller acil olarak düzeltilmelidir${NC}" | tee -a "$LOG_FILE"
    echo -e "${YELLOW}📖 Düzeltme kılavuzu: AMPERSAND_OPERATOR_PREVENTION_SYSTEM_JUNE7_2025.md${NC}" | tee -a "$LOG_FILE"
fi

echo "" | tee -a "$LOG_FILE"
echo -e "${BLUE}📄 Log dosyası: $LOG_FILE${NC}" | tee -a "$LOG_FILE"
echo -e "${BLUE}📊 Detaylı rapor: $REPORT_FILE${NC}" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Package.json özel kontrolü
if [ -f "package.json" ]; then
    echo -e "${YELLOW}🔍 Package.json özel kontrolü...${NC}" | tee -a "$LOG_FILE"
    
    if grep -q "&&" package.json; then
        echo -e "${RED}⚠️ package.json içerisinde && operatörü tespit edildi!${NC}" | tee -a "$LOG_FILE"
        echo -e "${YELLOW}📄 İhlal içeren script'ler:${NC}" | tee -a "$LOG_FILE"
        grep -n "&&" package.json | tee -a "$LOG_FILE"
        echo "" | tee -a "$LOG_FILE"
        
        # Package.json düzeltme önerisi oluştur
        echo "📝 package.json düzeltme önerisi oluşturuluyor..." | tee -a "$LOG_FILE"
        
        cat > "package_json_fix_suggestions.md" << EOF
# 📦 PACKAGE.JSON && OPERATÖRÜ DÜZELTMELERİ

## 🚨 Tespit Edilen İhlaller:

\`\`\`json
$(grep -n "&&" package.json)
\`\`\`

## ✅ Önerilen Düzeltmeler:

### Örnek 1: Build Script
\`\`\`json
// ❌ YANLIŞ
"build": "npm run clean && npm run compile && npm run test"

// ✅ DOĞRU
"clean": "rimraf dist",
"compile": "tsc",
"test": "jest",
"build": "npm run clean; npm run compile; npm run test"
\`\`\`

### Örnek 2: Development Script
\`\`\`json
// ❌ YANLIŞ  
"dev": "npm install && npm run build && npm start"

// ✅ DOĞRU
"predev": "npm install",
"prebuild": "echo 'Starting build...'",
"build": "webpack",
"dev": "npm run build; npm start"
\`\`\`

## 💡 En İyi Pratikler:

1. **npm pre/post hooks kullan**
2. **; operatörü ile komutları ayır** 
3. **Hata kontrolü için ayrı script'ler yaz**
4. **Makefile kullanmayı düşün**

EOF
        
        echo -e "${GREEN}✅ Düzeltme önerileri: package_json_fix_suggestions.md${NC}" | tee -a "$LOG_FILE"
    else
        echo -e "${GREEN}✅ package.json temiz - && operatörü yok${NC}" | tee -a "$LOG_FILE"
    fi
fi

# Git hooks kurulum önerisi
if [ -d ".git" ]; then
    echo "" | tee -a "$LOG_FILE"
    echo -e "${YELLOW}🔧 Git pre-commit hook kurulum önerisi...${NC}" | tee -a "$LOG_FILE"
    
    if [ ! -f ".git/hooks/pre-commit" ]; then
        echo -e "${BLUE}📝 Pre-commit hook oluşturuluyor...${NC}" | tee -a "$LOG_FILE"
        
        cat > ".git/hooks/pre-commit" << 'EOF'
#!/bin/bash
# && operatörü engelleme pre-commit hook

echo "🔍 && operatörü kontrolü..."

if git diff --cached --name-only | xargs grep -l "&&" 2>/dev/null; then
    echo "🚨 HATA: Commit'te && operatörü tespit edildi!"
    echo "📄 İhlal içeren dosyalar:"
    git diff --cached --name-only | xargs grep -l "&&" 2>/dev/null
    echo ""
    echo "💡 Lütfen && operatörlerini kaldırın ve tekrar commit edin"
    echo "📖 Kılavuz: AMPERSAND_OPERATOR_PREVENTION_SYSTEM_JUNE7_2025.md"
    exit 1
fi

echo "✅ && operatörü kontrolü başarılı"
EOF
        
        chmod +x ".git/hooks/pre-commit"
        echo -e "${GREEN}✅ Git pre-commit hook kuruldu${NC}" | tee -a "$LOG_FILE"
    else
        echo -e "${YELLOW}⚠️ Pre-commit hook zaten mevcut${NC}" | tee -a "$LOG_FILE"
    fi
fi

# Özet
echo "" | tee -a "$LOG_FILE"
echo "===========================================" | tee -a "$LOG_FILE"
echo -e "${BLUE}📋 TARAMA TAMAMLANDI${NC}" | tee -a "$LOG_FILE"
echo "===========================================" | tee -a "$LOG_FILE"

if [ "$TOTAL_VIOLATIONS" -eq 0 ]; then
    echo -e "${GREEN}🎉 PROJENİZ GÜVENLİK STANDARTLARINI KARŞILIYOR!${NC}"
    exit 0
else
    echo -e "${RED}⚠️ ACİL MÜDAHALE GEREKLİ: $TOTAL_VIOLATIONS İHLAL TESPİT EDİLDİ${NC}"
    exit 1
fi
