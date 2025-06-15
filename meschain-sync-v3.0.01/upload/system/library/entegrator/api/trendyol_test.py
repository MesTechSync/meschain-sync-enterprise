# trendyol_test.py
# Amaç: Trendyol API bağlantısını ve sipariş çekme işlemini test eden örnek Python scripti.
# Loglama: Test sonuçları ve hatalar api_log_example.log dosyasına kaydedilmelidir.
#
# Kullanım: Geliştiriciler, Trendyol API bağlantısını ve temel sipariş çekme fonksiyonunu bu script ile test edebilir.

import requests
import base64

# Trendyol API Giriş Bilgileri
supplier_id = 1076956
username = 'f4KhSfv7ihjXcJFlJeim'
password = 'GLs2YLpJwPJtEX6dSPbi'

# Token oluştur (opsiyonel çünkü Token yukarıda base64 verilmiş)
credentials = f"{username}:{password}"
token = base64.b64encode(credentials.encode()).decode()

headers = {
    "Authorization": f"Basic {token}",
    "Content-Type": "application/json"
}

# Sipariş Listeleme URL
url = f"https://api.trendyol.com/sapigw/suppliers/{supplier_id}/orders"

params = {
    "status": "Created",   # Yalnızca yeni siparişler
    "size": 5              # Son 5 siparişi getir
}

response = requests.get(url, headers=headers, params=params)

# Çıktıyı yazdır
if response.status_code == 200:
    print("✅ Trendyol API bağlantısı başarılı!")
    print("📦 Sipariş verileri:")
    print(response.json())
else:
    print("❌ Bağlantı başarısız!")
    print("Kod:", response.status_code)
    print("Hata Mesajı:", response.text) 