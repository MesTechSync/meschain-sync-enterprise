#!/bin/bash

# 🔧 SSH Key Kurulum ve GitHub Erişim Otomatik Script
# Her iki takım için SSH erişim sorunlarını çözer

set -e

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
PURPLE='\033[0;35m'
NC='\033[0m'

echo -e "${PURPLE}"
cat << "EOF"
╔══════════════════════════════════════════════════════════════════╗
║                🔧 GITHUB SSH ACCESS TROUBLESHOOTING             ║
║                  MesChain-Sync Repository Access                ║
║                                                                  ║
║  🚀 MUSTI Team: SSH Key Configuration                          ║
║  🔧 Mezbjen Team: Second Computer Setup                         ║
║  🔑 Repository: meschain-sync-enterprise                        ║
╚══════════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo ""
echo -e "${BLUE}🔍 GitHub Erişim Sorunları Tespit ve Çözüm Script${NC}"
echo ""

# Takım seçimi
echo -e "${YELLOW}Hangi takım için setup yapıyorsunuz?${NC}"
echo "1) 🚀 MUSTI Team (SSH key issues)"
echo "2) 🔧 Mezbjen Team (Second computer setup)"
echo "3) 🌐 Her iki takım (Complete troubleshooting)"
echo ""
read -p "Seçim yapın (1/2/3): " team_choice

# Email için input
echo ""
read -p "📧 Email adresi girin (SSH key için): " user_email
read -p "👤 Takım adı girin (key label için): " team_name

# Repository bilgileri
REPO_URL="https://github.com/MesTechSync/meschain-sync-enterprise.git"
REPO_SSH="git@github.com:MesTechSync/meschain-sync-enterprise.git"

# Function: SSH Key Generate
generate_ssh_key() {
    local email=$1
    local team=$2
    local key_name=$3
    
    echo -e "${BLUE}🔑 SSH Key oluşturuluyor...${NC}"
    
    # SSH key oluştur
    ssh-keygen -t ed25519 -C "$email" -f ~/.ssh/$key_name -N ""
    
    # SSH agent başlat
    eval "$(ssh-agent -s)"
    
    # SSH key'i agent'a ekle
    ssh-add ~/.ssh/$key_name
    
    echo -e "${GREEN}✅ SSH Key başarıyla oluşturuldu!${NC}"
    echo ""
    echo -e "${YELLOW}📋 SSH Public Key (GitHub'a eklenecek):${NC}"
    echo "----------------------------------------"
    cat ~/.ssh/$key_name.pub
    echo "----------------------------------------"
    echo ""
}

# Function: SSH Config Setup
setup_ssh_config() {
    local key_name=$1
    
    echo -e "${BLUE}⚙️ SSH Config ayarlanıyor...${NC}"
    
    # SSH config dosyası oluştur/güncelle
    cat >> ~/.ssh/config << EOF

# MesChain-Sync Repository Access
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/$key_name
    IdentitiesOnly yes
EOF
    
    echo -e "${GREEN}✅ SSH Config başarıyla ayarlandı!${NC}"
}

# Function: Test SSH Connection
test_ssh_connection() {
    echo -e "${BLUE}🔍 SSH bağlantısı test ediliyor...${NC}"
    
    if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
        echo -e "${GREEN}✅ SSH bağlantısı başarılı!${NC}"
        return 0
    else
        echo -e "${RED}❌ SSH bağlantısı başarısız${NC}"
        echo -e "${YELLOW}Manuel SSH key ekleme gerekiyor:${NC}"
        echo "1. https://github.com/settings/keys adresine gidin"
        echo "2. 'New SSH key' butonuna tıklayın"
        echo "3. Yukarıda görüntülenen public key'i ekleyin"
        return 1
    fi
}

# Function: Repository Clone
clone_repository() {
    local use_ssh=$1
    local workspace_dir=$2
    
    echo -e "${BLUE}📁 Repository klonlanıyor...${NC}"
    
    # Workspace klasörü oluştur
    mkdir -p "$workspace_dir"
    cd "$workspace_dir"
    
    # Repository'yi klon et
    if [ "$use_ssh" = "true" ]; then
        git clone "$REPO_SSH" 2>/dev/null || {
            echo -e "${YELLOW}⚠️ SSH ile klonlama başarısız, HTTPS denenecek...${NC}"
            git clone "$REPO_URL"
        }
    else
        git clone "$REPO_URL"
    fi
    
    cd meschain-sync-enterprise
    
    echo -e "${GREEN}✅ Repository başarıyla klonlandı!${NC}"
    echo -e "${BLUE}📍 Konum: $(pwd)${NC}"
}

# Function: Verify Repository Access
verify_repository_access() {
    echo -e "${BLUE}🔍 Repository erişimi doğrulanıyor...${NC}"
    
    # Git durumunu kontrol et
    git status
    
    # Branch'leri kontrol et
    echo ""
    echo -e "${BLUE}📊 Available branches:${NC}"
    git branch -a
    
    # Son commit'leri göster
    echo ""
    echo -e "${BLUE}📈 Recent commits:${NC}"
    git log --oneline -5
    
    # File count
    echo ""
    echo -e "${BLUE}📁 Repository stats:${NC}"
    echo "Total files: $(find . -type f | wc -l | tr -d ' ')"
    echo "Directories: $(find . -type d | wc -l | tr -d ' ')"
    
    echo -e "${GREEN}✅ Repository erişimi doğrulandı!${NC}"
}

# Main execution based on team choice
case $team_choice in
    1)
        # MUSTI Team SSH Setup
        echo -e "${BLUE}🚀 MUSTI Team SSH Setup başlatılıyor...${NC}"
        
        key_name="id_ed25519_musti_team"
        workspace_dir="$HOME/Desktop/meschain-musti-workspace"
        
        generate_ssh_key "$user_email" "$team_name" "$key_name"
        setup_ssh_config "$key_name"
        
        echo -e "${YELLOW}📋 MANUEL ADIM - SSH Key'i GitHub'a ekleyin:${NC}"
        echo "1. https://github.com/settings/keys adresine gidin"
        echo "2. 'New SSH key' butonuna tıklayın"
        echo "3. Title: 'MUSTI Team - $team_name'"
        echo "4. Yukarıda gösterilen public key'i ekleyin"
        echo ""
        read -p "SSH key'i GitHub'a ekledikten sonra Enter'a basın..." -r
        
        test_ssh_connection
        clone_repository "true" "$workspace_dir"
        
        cd "$workspace_dir/meschain-sync-enterprise"
        verify_repository_access
        
        echo ""
        echo -e "${GREEN}🎉 MUSTI Team setup tamamlandı!${NC}"
        echo -e "${BLUE}📂 Workspace: $workspace_dir/meschain-sync-enterprise${NC}"
        ;;
        
    2)
        # Mezbjen Team Second Computer Setup
        echo -e "${BLUE}🔧 Mezbjen Team Second Computer Setup başlatılıyor...${NC}"
        
        key_name="id_ed25519_mezbjen_2"
        workspace_dir="$HOME/Desktop/meschain-mezbjen-workspace"
        
        generate_ssh_key "$user_email" "$team_name" "$key_name"
        setup_ssh_config "$key_name"
        
        echo -e "${YELLOW}📋 MANUEL ADIM - SSH Key'i GitHub'a ekleyin:${NC}"
        echo "1. https://github.com/settings/keys adresine gidin"
        echo "2. 'New SSH key' butonuna tıklayın"
        echo "3. Title: 'Mezbjen Team - Second Computer'"
        echo "4. Yukarıda gösterilen public key'i ekleyin"
        echo ""
        read -p "SSH key'i GitHub'a ekledikten sonra Enter'a basın..." -r
        
        test_ssh_connection
        clone_repository "true" "$workspace_dir"
        
        cd "$workspace_dir/meschain-sync-enterprise"
        verify_repository_access
        
        echo ""
        echo -e "${GREEN}🎉 Mezbjen Team second computer setup tamamlandı!${NC}"
        echo -e "${BLUE}📂 Workspace: $workspace_dir/meschain-sync-enterprise${NC}"
        ;;
        
    3)
        # Complete troubleshooting for both teams
        echo -e "${BLUE}🌐 Complete troubleshooting başlatılıyor...${NC}"
        
        # Alternative HTTPS method
        echo -e "${YELLOW}🔧 HTTPS Alternative Setup (SSH sorunları için)${NC}"
        echo ""
        echo "GitHub Personal Access Token gerekiyor:"
        echo "1. https://github.com/settings/tokens adresine gidin"
        echo "2. 'Generate new token (classic)' tıklayın"
        echo "3. Permissions: repo, read:packages, write:packages"
        echo "4. Token'ı kopyalayın"
        echo ""
        read -p "Personal Access Token girin: " github_token
        
        workspace_dir="$HOME/Desktop/meschain-complete-workspace"
        
        echo -e "${BLUE}📁 Repository HTTPS ile klonlanıyor...${NC}"
        mkdir -p "$workspace_dir"
        cd "$workspace_dir"
        
        # HTTPS with token
        git clone "https://${github_token}@github.com/MesTechSync/meschain-sync-enterprise.git"
        
        cd meschain-sync-enterprise
        
        # Set up git config for this repository
        git config user.name "$team_name"
        git config user.email "$user_email"
        
        verify_repository_access
        
        echo ""
        echo -e "${GREEN}🎉 Complete setup tamamlandı!${NC}"
        echo -e "${BLUE}📂 Workspace: $workspace_dir/meschain-sync-enterprise${NC}"
        echo -e "${YELLOW}💡 Bu setup SSH olmadan çalışır${NC}"
        ;;
        
    *)
        echo -e "${RED}❌ Geçersiz seçim!${NC}"
        exit 1
        ;;
esac

# Final instructions
echo ""
echo -e "${PURPLE}=================================================================${NC}"
echo -e "${GREEN}🎯 SETUP TAMAMLANDI!${NC}"
echo ""
echo -e "${BLUE}📋 Sonraki adımlar:${NC}"
echo "1. Repository'de değişiklik yapabilirsiniz"
echo "2. Pull requests oluşturabilirsiniz"
echo "3. Issues takip edebilirsiniz"
echo "4. Team coordination'a katkıda bulunabilirsiniz"
echo ""
echo -e "${BLUE}🔗 Repository URL:${NC}"
echo "   https://github.com/MesTechSync/meschain-sync-enterprise"
echo ""
echo -e "${BLUE}📞 Destek gerekirse:${NC}"
echo "   - Repository issues oluşturun"
echo "   - MezBjen ile iletişime geçin"
echo ""
echo -e "${GREEN}✅ GITHUB ACCESS SORUNLARI ÇÖZÜLDÜ!${NC}"
echo -e "${PURPLE}=================================================================${NC}"
