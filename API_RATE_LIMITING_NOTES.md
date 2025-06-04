# 🚨 API Rate Limiting ve Zaman Tabanlı Senkronizasyon Notları

## 📋 VSCode Dev Ekibi İçin Öncelik Notları

### **🎯 ÖNCELİK: API İLETİŞİMİ**
- **Gerçek API testleri henüz yapılmadı**
- **Trendyol API'leri temin edildi - test edilmeli**
- **Rate limiting sınırları bilinmiyor**
- **Online veri akışı yerine zaman tabanlı senkronizasyon gerekli**

---

## 🔄 **ZAMAN TABANLI SENKRONİZASYON STRATEJİSİ**

### **Mevcut Durum**
- ❌ Anlık webhook çağrıları (rate limit riski)
- ❌ Sürekli API polling (API kotası tükenir)
- ❌ Gerçek zamanlı veri akışı (sürdürülebilir değil)

### **Yeni Yaklaşım**
- ✅ **Zamanlanmış Senkronizasyon** (5-15-30 dakika aralıklar)
- ✅ **Batch İşlemler** (Toplu veri çekme)
- ✅ **Rate Limit Yönetimi** (API çağrı sayısı kontrolü)
- ✅ **Öncelikli Senkronizasyon** (Kritik veriler önce)

---

## ⏰ **ÖNERİLEN ZAMAN ARALIĞI**

### **Yüksek Öncelik (5 dakika)**
- Sipariş durumu değişiklikleri
- Stok kritik seviye uyarıları
- Ödeme durumu güncellemeleri

### **Orta Öncelik (15 dakika)**
- Ürün fiyat güncellemeleri
- Stok miktarı senkronizasyonu
- Yeni sipariş kontrolü

### **Düşük Öncelik (30-60 dakika)**
- Ürün bilgileri güncelleme
- Kategori senkronizasyonu
- Raporlama verileri

---

## 🛡️ **RATE LIMIT KORUMA STRATEJİLERİ**

### **1. API Çağrı Limitleri**
```php
// Örnek rate limit yönetimi
class ApiRateLimiter {
    private $maxCallsPerMinute = 60;
    private $maxCallsPerHour = 1000;
    private $maxCallsPerDay = 10000;
    
    public function canMakeCall($marketplace) {
        // Rate limit kontrolü
        return $this->checkLimits($marketplace);
    }
}
```

### **2. Sıralı İşlem (Queue System)**
```php
// API çağrılarını sıraya koy
class ApiQueue {
    public function addToQueue($marketplace, $endpoint, $data) {
        // Sıraya ekle, zamanla işle
    }
}
```

### **3. Öncelik Sistemi**
```php
// Kritik işlemler önce
class PriorityManager {
    const HIGH_PRIORITY = 1;    // 5 dakika
    const MEDIUM_PRIORITY = 2;  // 15 dakika  
    const LOW_PRIORITY = 3;     // 30+ dakika
}
```

---

## 📊 **PAZARYERI BAZLI RATE LIMIT TAHMİNLERİ**

### **Trendyol**
- **Bilinmeyen limitler** - Test edilmeli
- **Önerilen:** 30 saniye aralıkla başla
- **Monitoring:** API response headers kontrol et

### **Amazon**
- **Çok sıkı limitler** - Dikkatli ol
- **Önerilen:** 1 dakika minimum aralık
- **Throttling:** Exponential backoff kullan

### **N11**
- **Orta seviye limitler**
- **Önerilen:** 15-30 saniye aralık

### **Hepsiburada**
- **Bilinmeyen limitler**
- **Önerilen:** Konservatif yaklaşım

### **eBay**
- **Günlük limitler var**
- **Önerilen:** Günlük kota takibi

### **Ozon**
- **Saat bazlı limitler**
- **Önerilen:** Saatlik dağılım

---

## 🔧 **UYGULAMA STRATEJİSİ**

### **Aşama 1: Cron Job Sistemi**
```bash
# Her 5 dakika - Yüksek öncelik
*/5 * * * * php /path/to/sync_high_priority.php

# Her 15 dakika - Orta öncelik  
*/15 * * * * php /path/to/sync_medium_priority.php

# Her saat - Düşük öncelik
0 * * * * php /path/to/sync_low_priority.php
```

### **Aşama 2: API Response Monitoring**
```php
// API yanıt sürelerini izle
class ApiMonitor {
    public function logApiCall($marketplace, $endpoint, $responseTime, $statusCode) {
        // Rate limit yaklaşıyorsa uyar
        if ($statusCode == 429) {
            $this->handleRateLimit($marketplace);
        }
    }
}
```

### **Aşama 3: Fallback Mekanizması**
```php
// API başarısız olursa
class FallbackHandler {
    public function handleApiFailure($marketplace, $data) {
        // Veriyi geçici olarak sakla
        // Sonra tekrar dene
        $this->queueForRetry($marketplace, $data);
    }
}
```

---

## 🎯 **VSCode DEV EKİBİ İÇİN GÖREVLER**

### **Öncelik 1: API Test ve Doğrulama**
1. **Trendyol API'lerini test et** (Temin edilmiş)
2. **Rate limit sınırlarını belirle**
3. **Response formatlarını doğrula**
4. **Error handling test et**

### **Öncelik 2: Zaman Tabanlı Sistem**
1. **Cron job sistemi kur**
2. **Queue mekanizması ekle**
3. **Rate limit monitoring**
4. **Retry logic implement et**

### **Öncelik 3: Monitoring ve Logging**
1. **API call tracking**
2. **Performance monitoring**
3. **Error rate tracking**
4. **Success rate analytics**

---

## 📝 **TEST SENARYOLARI**

### **Rate Limit Testi**
```php
// Hızlı ardışık çağrılar yap
for ($i = 0; $i < 100; $i++) {
    $response = $api->makeCall();
    if ($response->getStatusCode() == 429) {
        echo "Rate limit hit at call #$i";
        break;
    }
}
```

### **Zaman Aralığı Testi**
```php
// Farklı aralıklarla test et
$intervals = [5, 10, 15, 30, 60]; // saniye
foreach ($intervals as $interval) {
    $this->testApiWithInterval($interval);
}
```

---

## ⚠️ **UYARILAR**

### **Yapılmaması Gerekenler**
- ❌ Sürekli polling yapma
- ❌ Rate limit'i görmezden gelme  
- ❌ Error handling olmadan API çağrısı
- ❌ Retry logic olmadan bırakma

### **Yapılması Gerekenler**
- ✅ Her API çağrısını logla
- ✅ Rate limit headers'ı kontrol et
- ✅ Exponential backoff kullan
- ✅ Circuit breaker pattern uygula

---

## 📞 **İLETİŞİM**

**API Test Sonuçları için:**
- Rate limit değerleri
- Response süreleri  
- Error oranları
- Başarılı call sayıları

**Bu bilgiler sistem optimizasyonu için kritik!**

---

**🎯 SONUÇ:** Gerçek API testleri yapılana kadar zaman tabanlı senkronizasyon ile devam et. Rate limit'leri öğrendikten sonra sistemi optimize et. 