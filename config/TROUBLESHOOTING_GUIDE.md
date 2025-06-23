# OpenCart Multi-Port Troubleshooting Guide

Bu kılavuz, OpenCart multi-port kurulumunda karşılaşabileceğiniz sorunları ve çözümlerini içermektedir.

## 🚨 Acil Durum Kontrol Listesi

### Hızlı Sistem Kontrolü
```bash
# Tüm servislerin durumunu kontrol et
sudo systemctl status apache2 nginx php8.1-fpm mysql

# Portların açık olup olmadığını kontrol et
sudo netstat -tlnp | grep -E ':8080|:8090'

# PHP-FPM socket'larını kontrol et
ls -la /var/run/php/php8.1-fpm-*.sock

# Log dosyalarında son hataları kontrol et
sudo tail -20 /var/log/apache2/error.log
sudo tail -20 /var/log/php/opencart-*-error.log
```

## 🔍 Yaygın Sorunlar ve Çözümleri

### 1. OCMOD Yükleme Sorunları

#### Problem: "meschain-trendyol.ocmod file already exists" Kırmızı Uyarı

**Belirtiler:**
- OCMOD dosyası yüklenmiyor
- Kırmızı uyarı mesajı görünüyor
- Admin panelinde hata

**Çözüm Adımları:**
```bash
# 1. Mevcut OCMOD dosyasını kontrol et
find /var/www/html/opencart-* -name "*meschain*" -type f

# 2. Eski dosyaları temizle
sudo rm -f /var/www/html/opencart-*/system/modification/*meschain*
sudo rm -f /var/www/html/opencart-*/system/storage/modification/*meschain*

# 3. Cache'i temizle
sudo rm -rf /var/www/html/opencart-*/system/storage/cache/*
sudo rm -rf /var/www/html/opencart-*/system/storage/modification/*

# 4. İzinleri düzelt
sudo chown -R www-data:www-data /var/www/html/opencart-*
sudo chmod -R 755 /var/www/html/opencart-*
```

**Admin Panel Üzerinden:**
1. Extensions > Modifications > Clear (Temizle)
2. Extensions > Installer > Upload yeni OCMOD dosyası
3. Extensions > Modifications > Refresh (Yenile)
4. Dashboard > Developer Settings > Theme & SASS Cache'i temizle

#### Problem: XML Syntax Hatası

**Belirtiler:**
- "XML parsing error" mesajı
- OCMOD yüklenmiyor

**Çözüm:**
```bash
# XML dosyasını doğrula
xmllint --noout meschain_trendyol.ocmod.xml

# Eğer hata varsa, dosyayı düzelt
nano meschain_trendyol.ocmod.xml
```

### 2. Port Erişim Sorunları

#### Problem: Port 8080/8090 Erişilemiyor

**Belirtiler:**
- "Connection refused" hatası
- Tarayıcıda sayfa açılmıyor

**Çözüm Adımları:**
```bash
# 1. Portların kullanımda olup olmadığını kontrol et
sudo lsof -i :8080
sudo lsof -i :8090

# 2. Web sunucusu durumunu kontrol et
sudo systemctl status apache2
# veya
sudo systemctl status nginx

# 3. Konfigürasyon dosyalarını test et
sudo apache2ctl configtest
# veya
sudo nginx -t

# 4. Firewall ayarlarını kontrol et
sudo ufw status
sudo ufw allow 8080
sudo ufw allow 8090

# 5. SELinux kontrolü (CentOS/RHEL)
sudo setsebool -P httpd_can_network_connect 1
sudo semanage port -a -t http_port_t -p tcp 8080
sudo semanage port -a -t http_port_t -p tcp 8090
```

#### Problem: "Permission Denied" Hatası

**Çözüm:**
```bash
# Dizin izinlerini düzelt
sudo chown -R www-data:www-data /var/www/html/opencart-*
sudo chmod -R 755 /var/www/html/opencart-*
sudo chmod -R 777 /var/www/html/opencart-*/system/storage/
sudo chmod -R 777 /var/www/html/opencart-*/image/
```

### 3. PHP-FPM Sorunları

#### Problem: PHP-FPM Socket Bulunamıyor

**Belirtiler:**
- "No such file or directory" hatası
- PHP sayfaları çalışmıyor

**Çözüm:**
```bash
# 1. PHP-FPM servis durumunu kontrol et
sudo systemctl status php8.1-fpm

# 2. Pool konfigürasyonlarını kontrol et
sudo php-fpm8.1 -t

# 3. Socket dosyalarının varlığını kontrol et
ls -la /var/run/php/

# 4. PHP-FPM'i yeniden başlat
sudo systemctl restart php8.1-fpm

# 5. Socket izinlerini kontrol et
sudo chmod 660 /var/run/php/php8.1-fpm-*.sock
sudo chown www-data:www-data /var/run/php/php8.1-fpm-*.sock
```

#### Problem: PHP-FPM Pool Çakışması

**Çözüm:**
```bash
# Mevcut pool'ları listele
sudo ls -la /etc/php/8.1/fpm/pool.d/

# Çakışan pool'ları kaldır
sudo rm /etc/php/8.1/fpm/pool.d/www.conf

# Sadece bizim pool'larımızı bırak
sudo systemctl restart php8.1-fpm
```

### 4. Veritabanı Bağlantı Sorunları

#### Problem: "Access denied for user" Hatası

**Çözüm:**
```bash
# 1. MySQL servis durumunu kontrol et
sudo systemctl status mysql

# 2. Kullanıcı izinlerini kontrol et
mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User LIKE 'opencart_%';"

# 3. Kullanıcıları yeniden oluştur
mysql -u root -p < config/database/mysql-setup.sql

# 4. Bağlantıyı test et
mysql -u opencart_8080 -p opencart_8080 -e "SHOW TABLES;"
```

#### Problem: "Too many connections" Hatası

**Çözüm:**
```bash
# MySQL konfigürasyonunu düzenle
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Aşağıdaki satırları ekle:
# max_connections = 500
# max_user_connections = 100

# MySQL'i yeniden başlat
sudo systemctl restart mysql
```

### 5. SSL Sertifika Sorunları

#### Problem: SSL Sertifikası Çalışmıyor

**Çözüm:**
```bash
# 1. Sertifika dosyalarını kontrol et
sudo ls -la /etc/ssl/certs/opencart-*.crt
sudo ls -la /etc/ssl/private/opencart-*.key

# 2. Sertifika geçerliliğini kontrol et
sudo openssl x509 -in /etc/ssl/certs/opencart-8080.crt -text -noout

# 3. Sertifikaları yeniden oluştur
sudo ./config/ssl/generate-certificates.sh

# 4. Web sunucusu konfigürasyonunu kontrol et
sudo apache2ctl configtest
# veya
sudo nginx -t
```

### 6. Performans Sorunları

#### Problem: Yavaş Sayfa Yükleme

**Çözüm:**
```bash
# 1. PHP OPcache'i etkinleştir
echo "opcache.enable=1" | sudo tee -a /etc/php/8.1/fpm/php.ini
echo "opcache.memory_consumption=256" | sudo tee -a /etc/php/8.1/fpm/php.ini

# 2. PHP-FPM pool ayarlarını optimize et
sudo nano /etc/php/8.1/fpm/pool.d/php-fpm-8080.conf
# pm.max_children = 50 (artır)
# pm.start_servers = 10 (artır)

# 3. MySQL query cache'i etkinleştir
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# query_cache_type = 1
# query_cache_size = 64M

# 4. Servisleri yeniden başlat
sudo systemctl restart php8.1-fpm mysql apache2
```

#### Problem: Yüksek CPU Kullanımı

**Çözüm:**
```bash
# 1. Aktif işlemleri kontrol et
top -p $(pgrep -d',' php-fpm)

# 2. Slow query log'u etkinleştir
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# slow_query_log = 1
# slow_query_log_file = /var/log/mysql/slow.log
# long_query_time = 2

# 3. PHP slow log'u kontrol et
sudo tail -f /var/log/php/opencart-*-slow.log
```

## 🔧 Gelişmiş Sorun Giderme

### Log Analizi

#### Önemli Log Dosyaları
```bash
# Web sunucusu logları
sudo tail -f /var/log/apache2/opencart-8080-error.log
sudo tail -f /var/log/apache2/opencart-8090-error.log
sudo tail -f /var/log/nginx/opencart-8080-error.log
sudo tail -f /var/log/nginx/opencart-8090-error.log

# PHP logları
sudo tail -f /var/log/php/opencart-8080-error.log
sudo tail -f /var/log/php/opencart-8090-error.log

# MySQL logları
sudo tail -f /var/log/mysql/error.log

# Sistem logları
sudo tail -f /var/log/syslog | grep -E 'apache2|nginx|php-fpm|mysql'
```

#### Log Analiz Komutları
```bash
# En sık görülen hataları bul
sudo grep -c "ERROR" /var/log/apache2/opencart-*-error.log

# Son 1 saatteki hataları listele
sudo find /var/log -name "*opencart*" -mmin -60 -exec grep "ERROR" {} \;

# Belirli bir hatayı ara
sudo grep -r "Fatal error" /var/log/apache2/
```

### Sistem Monitoring

#### Gerçek Zamanlı İzleme
```bash
# Sistem kaynaklarını izle
htop

# Ağ bağlantılarını izle
sudo netstat -tulpn | grep -E ':8080|:8090'

# Disk kullanımını kontrol et
df -h
du -sh /var/www/html/opencart-*

# Memory kullanımını kontrol et
free -h
```

#### Otomatik Monitoring Script'i
```bash
#!/bin/bash
# monitoring.sh

while true; do
    echo "=== $(date) ==="
    echo "CPU Usage: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)"
    echo "Memory Usage: $(free | grep Mem | awk '{printf("%.2f%%", $3/$2 * 100.0)}')"
    echo "Port 8080: $(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080)"
    echo "Port 8090: $(curl -s -o /dev/null -w "%{http_code}" http://localhost:8090)"
    echo "---"
    sleep 60
done
```

### Backup ve Recovery

#### Acil Durum Backup
```bash
# Veritabanı backup
mysqldump -u root -p opencart_8080 > backup_8080_$(date +%Y%m%d_%H%M%S).sql
mysqldump -u root -p opencart_8090 > backup_8090_$(date +%Y%m%d_%H%M%S).sql

# Dosya backup
tar -czf opencart_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/html/opencart-*

# Konfigürasyon backup
tar -czf config_backup_$(date +%Y%m%d_%H%M%S).tar.gz config/
```

#### Recovery İşlemi
```bash
# Veritabanı restore
mysql -u root -p opencart_8080 < backup_8080_YYYYMMDD_HHMMSS.sql

# Dosya restore
tar -xzf opencart_files_YYYYMMDD_HHMMSS.tar.gz -C /

# İzinleri düzelt
sudo chown -R www-data:www-data /var/www/html/opencart-*
```

## 📞 Destek Alma

### Bilgi Toplama
Destek talep etmeden önce aşağıdaki bilgileri toplayın:

```bash
# Sistem bilgileri
uname -a
lsb_release -a

# Servis durumları
sudo systemctl status apache2 nginx php8.1-fpm mysql

# Konfigürasyon testleri
sudo apache2ctl configtest
sudo nginx -t
sudo php-fpm8.1 -t

# Log dosyalarının son 50 satırı
sudo tail -50 /var/log/apache2/opencart-*-error.log
sudo tail -50 /var/log/php/opencart-*-error.log
```

### Destek Kanalları
- **E-posta**: support@meschain.com
- **GitHub Issues**: https://github.com/meschain/opencart-multiport/issues
- **Dokümantasyon**: https://docs.meschain.com

### Hızlı Çözüm Kontrol Listesi
- [ ] Tüm servisler çalışıyor mu?
- [ ] Portlar açık mı?
- [ ] PHP-FPM socket'ları mevcut mu?
- [ ] Veritabanı bağlantısı çalışıyor mu?
- [ ] Log dosyalarında hata var mı?
- [ ] İzinler doğru ayarlanmış mı?
- [ ] Firewall kuralları uygun mu?
- [ ] Disk alanı yeterli mi?

---

**Not**: Bu kılavuz sürekli güncellenmektedir. Yeni sorunlar ve çözümler için dokümantasyon sitesini kontrol edin.
