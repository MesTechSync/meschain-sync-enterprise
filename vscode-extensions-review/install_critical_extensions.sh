#!/bin/bash

# 🚨 CRITICAL EXTENSIONS INSTALLER FOR CURSOR TEAM TASKS
# Azure Account ve Azure App Service eklentilerini otomatik kurulum
# 
# @author MesChain Development Team
# @date June 13, 2025
# @priority URGENT - Critical for Cursor team task completion

echo "🚨 CRITICAL EXTENSIONS INSTALLER - BAŞLATILIYOR..."
echo "=================================================="

# Renk kodları
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Cursor executable path'ini bul
CURSOR_PATH=""

echo "🔍 Cursor executable'ını arıyor..."

# Olası Cursor yolları
POSSIBLE_PATHS=(
    "/Applications/Cursor.app/Contents/Resources/app/bin/cursor"
    "/usr/local/bin/cursor"
    "/opt/homebrew/bin/cursor"
    "$HOME/.cursor/bin/cursor"
    "/Applications/Cursor.app/Contents/MacOS/Cursor"
)

for path in "${POSSIBLE_PATHS[@]}"; do
    if [ -f "$path" ]; then
        CURSOR_PATH="$path"
        echo -e "${GREEN}✅ Cursor bulundu: $path${NC}"
        break
    fi
done

# Cursor bulunamazsa alternatif yöntemler
if [ -z "$CURSOR_PATH" ]; then
    echo -e "${YELLOW}⚠️ Cursor executable bulunamadı, alternatif yöntemler deneniyor...${NC}"
    
    # which komutu ile dene
    if command -v cursor &> /dev/null; then
        CURSOR_PATH="cursor"
        echo -e "${GREEN}✅ Cursor PATH'te bulundu${NC}"
    elif command -v code &> /dev/null; then
        CURSOR_PATH="code"
        echo -e "${YELLOW}⚠️ VSCode bulundu, Cursor yerine kullanılacak${NC}"
    else
        echo -e "${RED}❌ Ne Cursor ne de VSCode bulunamadı${NC}"
        echo "Manuel kurulum gerekli:"
        echo "1. Cursor'u açın"
        echo "2. Cmd+Shift+X (Extensions)"
        echo "3. 'Azure Account' arayın ve kurun"
        echo "4. 'Azure App Service' arayın ve kurun"
        exit 1
    fi
fi

echo -e "${BLUE}🎯 Kullanılacak executable: $CURSOR_PATH${NC}"

# Kritik eklentileri tanımla
declare -A CRITICAL_EXTENSIONS=(
    ["ms-vscode.azure-account"]="Azure Account (KRİTİK)"
    ["ms-azuretools.vscode-azureappservice"]="Azure App Service (DEPLOYMENT)"
)

# Opsiyonel eklentiler
declare -A OPTIONAL_EXTENSIONS=(
    ["ms-azuretools.vscode-azurefunctions"]="Azure Functions"
    ["ms-azuretools.vscode-azurestorage"]="Azure Storage"
    ["ms-azuretools.vscode-cosmosdb"]="Azure Databases"
)

# Kurulum fonksiyonu
install_extension() {
    local ext_id="$1"
    local ext_name="$2"
    local is_critical="$3"
    
    echo -e "\n${BLUE}📦 Kuruluyor: $ext_name${NC}"
    echo "Extension ID: $ext_id"
    
    # Önce kurulu mu kontrol et
    if "$CURSOR_PATH" --list-extensions | grep -q "$ext_id"; then
        echo -e "${GREEN}✅ Zaten kurulu: $ext_name${NC}"
        return 0
    fi
    
    # Kurulumu dene
    if "$CURSOR_PATH" --install-extension "$ext_id" --force; then
        echo -e "${GREEN}✅ Başarıyla kuruldu: $ext_name${NC}"
        return 0
    else
        if [ "$is_critical" = "true" ]; then
            echo -e "${RED}❌ KRİTİK HATA: $ext_name kurulamadı!${NC}"
            return 1
        else
            echo -e "${YELLOW}⚠️ Opsiyonel eklenti kurulamadı: $ext_name${NC}"
            return 0
        fi
    fi
}

# Ana kurulum süreci
echo -e "\n${RED}🔴 KRİTİK EKLENTILER KURULUYOR...${NC}"
echo "=================================="

CRITICAL_FAILED=0

for ext_id in "${!CRITICAL_EXTENSIONS[@]}"; do
    ext_name="${CRITICAL_EXTENSIONS[$ext_id]}"
    if ! install_extension "$ext_id" "$ext_name" "true"; then
        CRITICAL_FAILED=1
    fi
done

# Kritik eklentiler başarısızsa dur
if [ $CRITICAL_FAILED -eq 1 ]; then
    echo -e "\n${RED}💥 KRİTİK HATA: Gerekli eklentiler kurulamadı!${NC}"
    echo "Manuel kurulum gerekli:"
    echo "1. Cursor'u açın"
    echo "2. Cmd+Shift+X tuşlarına basın"
    echo "3. Aşağıdaki eklentileri arayın ve kurun:"
    for ext_id in "${!CRITICAL_EXTENSIONS[@]}"; do
        echo "   - ${CRITICAL_EXTENSIONS[$ext_id]} ($ext_id)"
    done
    exit 1
fi

echo -e "\n${YELLOW}🟡 OPSIYONEL EKLENTILER KURULUYOR...${NC}"
echo "===================================="

for ext_id in "${!OPTIONAL_EXTENSIONS[@]}"; do
    ext_name="${OPTIONAL_EXTENSIONS[$ext_id]}"
    install_extension "$ext_id" "$ext_name" "false"
done

# PHP Intelephense kontrolü
echo -e "\n${BLUE}🟣 PHP INTELEPHENSE KONTROLÜ...${NC}"
echo "==============================="

if "$CURSOR_PATH" --list-extensions | grep -q "bmewburn.vscode-intelephense-client"; then
    echo -e "${GREEN}✅ PHP Intelephense kurulu ve aktif${NC}"
    echo -e "${YELLOW}💡 Premium lisans kontrolü gerekli${NC}"
else
    echo -e "${YELLOW}⚠️ PHP Intelephense bulunamadı, kuruluyor...${NC}"
    install_extension "bmewburn.vscode-intelephense-client" "PHP Intelephense" "false"
fi

# Kurulum özeti
echo -e "\n${GREEN}🎉 KURULUM TAMAMLANDI!${NC}"
echo "======================"

echo -e "\n📊 KURULUM ÖZETİ:"
echo "Kurulu eklentiler:"
"$CURSOR_PATH" --list-extensions | grep -E "(azure|intelephense)" | while read -r ext; do
    echo "  ✅ $ext"
done

# Sonraki adımlar
echo -e "\n${BLUE}🚀 SONRAKİ ADIMLAR:${NC}"
echo "=================="
echo "1. Cursor'u yeniden başlatın"
echo "2. Cmd+Shift+P > 'Azure: Sign In' ile Azure'a giriş yapın"
echo "3. Sol panelde Azure ikonunu kontrol edin"
echo "4. PHP Intelephense premium lisansını kontrol edin"

# Azure yapılandırma rehberi
echo -e "\n${YELLOW}⚙️ AZURE YAPILANDIRMA:${NC}"
echo "====================="
echo "1. Cursor > Command Palette (Cmd+Shift+P)"
echo "2. 'Azure: Sign In' yazın ve Enter"
echo "3. Browser'da Azure hesabınızla giriş yapın"
echo "4. Subscription'ınızı seçin"
echo "5. Sol panelde Azure ikonunu kontrol edin"

# PHP Intelephense premium rehberi
echo -e "\n${YELLOW}💎 PHP INTELEPHENSE PREMIUM:${NC}"
echo "==========================="
echo "1. Settings > Extensions > PHP Intelephense"
echo "2. 'Licence Key' alanına premium anahtarınızı girin"
echo "3. Cursor'u yeniden başlatın"

# Doğrulama testi
echo -e "\n${BLUE}🧪 DOĞRULAMA TESTİ:${NC}"
echo "=================="
echo "Aşağıdaki komutları Cursor Command Palette'te test edin:"
echo "- 'Azure: Sign In'"
echo "- 'Azure: Select Subscriptions'"
echo "- 'PHP Intelephense: Index workspace'"

echo -e "\n${GREEN}✅ Script tamamlandı - Cursor team görevleri için hazır!${NC}"
echo "Sorun yaşarsanız URGENT_EXTENSION_INSTALLER.md dosyasını kontrol edin."

exit 0
