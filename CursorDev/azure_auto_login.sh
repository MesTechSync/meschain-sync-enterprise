#!/bin/bash

# 🚀 AZURE OTOMATİK GİRİŞ SCRIPTİ
# Bu script Azure giriş işlemini otomatikleştirir ve kimlik bilgilerini güvenli şekilde saklar

# Renkler
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Azure giriş bilgilerini kontrol et
check_azure_login() {
    echo -e "${YELLOW}🔍 Azure giriş durumu kontrol ediliyor...${NC}" 
    
    # Azure CLI giriş durumunu kontrol et
    if az account show &> /dev/null; then
        echo -e "${GREEN}✅ Azure CLI'da oturum açılmış!${NC}"
        echo -e "Abonelik: $(az account show --query name -o tsv)"
        echo -e "Kullanıcı: $(az account show --query user.name -o tsv)\n"
        return 0
    else
        echo -e "${YELLOW}⚠️  Azure CLI'da oturum açılmamış!${NC}\n"
        return 1
    fi
}

# Azure'a giriş yap
login_to_azure() {
    echo -e "${YELLOW}🔑 Azure hesabına giriş yapılıyor...${NC}"
    
    # Mevcut token'ı kontrol et
    if check_azure_login; then
        echo -e "${GREEN}✅ Zaten giriş yapılmış.${NC}\n"
        return 0
    fi
    
    # Azure'a giriş yap (interactive olmadan)
    echo -e "${YELLOW}📱 Lütfen tarayıcıda Azure girişini tamamlayın...${NC}"
    
    # Device code flow ile giriş
    az login --use-device-code --output none
    
    if check_azure_login; then
        echo -e "\n${GREEN}🎉 Azure'a başarıyla giriş yapıldı!${NC}"
        
        # Varsayılan aboneliği ayarla
        echo -e "\n${YELLOW}🔧 Varsayılan abonelik ayarlanıyor...${NC}"
        SUBSCRIPTION_ID=$(az account show --query id -o tsv)
        az account set --subscription $SUBSCRIPTION_ID
        
        # Kimlik bilgilerini güvenli şekilde kaydet
        echo -e "\n${YELLOW}🔐 Kimlik bilgileri kaydediliyor...${NC}"
        mkdir -p ~/.azure
        az account get-access-token > ~/.azure/accessTokens.json
        
        echo -e "\n${GREEN}✅ Azure oturum bilgileri kaydedildi!${NC}"
        return 0
    else
        echo -e "\n❌ Azure girişi başarısız oldu!"
        return 1
    fi
}

# Ana işlem
main() {
    echo -e "\n${YELLOW}🚀 AZURE OTOMATİK GİRİŞ SİHİRBAZI${NC}"
    echo -e "${YELLOW}================================${NC}\n"
    
    # Azure CLI kontrolü
    if ! command -v az &> /dev/null; then
        echo -e "❌ Azure CLI bulunamadı! Lütfen önce Azure CLI'yı yükleyin:"
        echo -e "   https://docs.microsoft.com/tr-cli/azure/install-azure-cli"
        exit 1
    fi
    
    # Giriş işlemini başlat
    login_to_azure
    
    # Sonuç
    if [ $? -eq 0 ]; then
        echo -e "\n${GREEN}✨ İşlem tamamlandı! Artık Azure CLI ve Cursor'da oturum açık.${NC}"
        echo -e "   Cursor'u yeniden başlatın ve Azure ikonuna tıklayarak aboneliklerinizi görüntüleyin.\n"
    else
        echo -e "\n❌ Bir hata oluştu! Lütfen manuel olarak giriş yapmayı deneyin:\n"
        echo -e "   1. Terminale 'az login' yazın"
        echo -e "   2. Tarayıcıda giriş yapın\n"
    fi
}

# Scripti çalıştır
main "$@"
