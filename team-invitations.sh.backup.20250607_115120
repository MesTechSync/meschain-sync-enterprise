#!/bin/bash

# 👥 GitHub Takım Davetiyeli Otomatik Gönderme
# Bu script takım üyelerine otomatik davetiye gönderir

echo "👥 GitHub Takım Davetiyeli Gönderiliyor..."
echo "📍 Repository: MesTechSync/meschain-sync-enterprise"
echo ""

# GitHub Personal Access Token kontrolü
if [ -z "$GITHUB_TOKEN" ]; then
    echo "❌ GITHUB_TOKEN environment variable gerekli!"
    exit 1
fi

REPO_OWNER="MesTechSync"
REPO_NAME="meschain-sync-enterprise"
API_BASE="https://api.github.com/repos/$REPO_OWNER/$REPO_NAME"

echo "📝 Takım üyesi bilgileri girmeniz gerekiyor..."
echo ""

# Fonksiyon: Davetiye gönder
send_invitation() {
    local username=$1
    local permission=$2
    local team_name=$3
    
    echo "📧 Davetiye gönderiliyor: $username ($team_name - $permission)"
    
    response=$(curl -s -w "%{http_code}" -o /tmp/github_response \
      -X PUT \
      -H "Authorization: token $GITHUB_TOKEN" \
      -H "Accept: application/vnd.github.v3+json" \
      "$API_BASE/collaborators/$username" \
      -d "{\"permission\": \"$permission\"}")
    
    if [ "$response" -eq 201 ] || [ "$response" -eq 204 ]; then
        echo "✅ Başarılı: $username davet edildi"
    else
        echo "❌ Hata: $username davet edilemedi (HTTP: $response)"
    fi
}

echo "🤖 VSCode Team (Backend) - Admin yetkisi"
echo "GitHub username'leri girin (boş bırakıp Enter'a basarak geçin):"
vscode_count=1
while true; do
    read -p "VSCode Team Üye $vscode_count username: " vscode_user
    if [ -z "$vscode_user" ]; then
        break
    fi
    send_invitation "$vscode_user" "admin" "VSCode Team"
    ((vscode_count++))
done

echo ""
echo "🎨 Cursor Team (Frontend) - Write yetkisi"
echo "GitHub username'leri girin (boş bırakıp Enter'a basarak geçin):"
cursor_count=1
while true; do
    read -p "Cursor Team Üye $cursor_count username: " cursor_user
    if [ -z "$cursor_user" ]; then
        break
    fi
    send_invitation "$cursor_user" "push" "Cursor Team"
    ((cursor_count++))
done

echo ""
echo "🚀 MUSTI Team (DevOps) - Admin yetkisi"
echo "GitHub username'leri girin (boş bırakıp Enter'a basarak geçin):"
musti_count=1
while true; do
    read -p "MUSTI Team Üye $musti_count username: " musti_user
    if [ -z "$musti_user" ]; then
        break
    fi
    send_invitation "$musti_user" "admin" "MUSTI Team"
    ((musti_count++))
done

echo ""
echo "✅ Takım davetiyeli tamamlandı!"
echo ""
echo "📊 Özet:"
echo "   🤖 VSCode Team: $((vscode_count-1)) üye davet edildi"
echo "   🎨 Cursor Team: $((cursor_count-1)) üye davet edildi"
echo "   🚀 MUSTI Team: $((musti_count-1)) üye davet edildi"
echo ""
echo "📧 Davet edilen kişiler email ile bilgilendirilecek"
echo "🔗 Repository: https://github.com/$REPO_OWNER/$REPO_NAME"
echo ""
echo "⚠️  Not: Davet edilen kişiler GitHub hesaplarında davetiyeyi kabul etmelidir."
