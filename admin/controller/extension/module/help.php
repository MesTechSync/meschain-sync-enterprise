<?php
/**
 * help.php
 *
 * Amaç: MesChain-Sync yardım ve dokümantasyon sisteminin controller dosyasıdır.
 *
 * Loglama: Tüm önemli işlemler ve hatalar help_controller.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */

class ControllerExtensionModuleHelp extends Controller {
    private $error = array();
    
    /**
     * Yardım ana sayfası
     */
    public function index() {
        $this->load->language('extension/module/help');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Log
        $this->writeLog('admin', 'YARDIM_GIRIS', 'Yardım sayfası görüntülendi.');
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Yardım kategorileri
        $data['help_categories'] = $this->getHelpCategories();
        
        // Seçili kategori ve konu
        $data['selected_category'] = isset($this->request->get['category']) ? $this->request->get['category'] : 'genel';
        $data['selected_topic'] = isset($this->request->get['topic']) ? $this->request->get['topic'] : 'giris';
        
        // Yardım içeriği
        $data['help_content'] = $this->getHelpContent($data['selected_category'], $data['selected_topic']);
        
        // URL'ler
        $data['help_url'] = $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'], true);
        $data['search_url'] = $this->url->link('extension/module/help/search', 'user_token=' . $this->session->data['user_token'], true);
        $data['contact_url'] = $this->url->link('extension/module/help/contact', 'user_token=' . $this->session->data['user_token'], true);
        
        // Sayfa başlığı
        $data['page_title'] = $this->language->get('heading_title');
        
        // Header, Column Left, Footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/help', $data));
    }
    
    /**
     * Yardım kategorilerini getir
     */
    private function getHelpCategories() {
        return [
            'genel' => [
                'name' => 'Genel Yardım',
                'icon' => 'fa-info-circle',
                'topics' => [
                    'giris' => 'MesChain-Sync Nedir?',
                    'kurulum' => 'Kurulum Kılavuzu',
                    'baslangic' => 'Başlangıç Rehberi',
                    'guncelleme' => 'Güncelleme Rehberi'
                ]
            ],
            'trendyol' => [
                'name' => 'Trendyol Entegrasyonu',
                'icon' => 'fa-shopping-bag',
                'topics' => [
                    'api_ayarlari' => 'API Ayarları',
                    'urun_gonderme' => 'Ürün Gönderimi',
                    'siparis_cekme' => 'Sipariş Çekme',
                    'stok_guncelleme' => 'Stok Güncelleme',
                    'hata_kodlari' => 'Hata Kodları'
                ]
            ],
            'amazon' => [
                'name' => 'Amazon Entegrasyonu',
                'icon' => 'fa-amazon',
                'topics' => [
                    'api_ayarlari' => 'API Ayarları',
                    'urun_gonderme' => 'Ürün Gönderimi',
                    'siparis_cekme' => 'Sipariş Çekme',
                    'stok_guncelleme' => 'Stok Güncelleme',
                    'hata_kodlari' => 'Hata Kodları'
                ]
            ],
            'n11' => [
                'name' => 'N11 Entegrasyonu',
                'icon' => 'fa-shopping-cart',
                'topics' => [
                    'api_ayarlari' => 'API Ayarları',
                    'urun_gonderme' => 'Ürün Gönderimi',
                    'siparis_cekme' => 'Sipariş Çekme',
                    'stok_guncelleme' => 'Stok Güncelleme',
                    'hata_kodlari' => 'Hata Kodları'
                ]
            ],
            'hepsiburada' => [
                'name' => 'Hepsiburada Entegrasyonu',
                'icon' => 'fa-shopping-basket',
                'topics' => [
                    'api_ayarlari' => 'API Ayarları',
                    'urun_gonderme' => 'Ürün Gönderimi',
                    'siparis_cekme' => 'Sipariş Çekme',
                    'stok_guncelleme' => 'Stok Güncelleme',
                    'hata_kodlari' => 'Hata Kodları'
                ]
            ],
            'ayarlar' => [
                'name' => 'Ayarlar ve Yapılandırma',
                'icon' => 'fa-cogs',
                'topics' => [
                    'genel_ayarlar' => 'Genel Ayarlar',
                    'kullanici_ayarlari' => 'Kullanıcı Ayarları',
                    'log_yonetimi' => 'Log Yönetimi',
                    'yedekleme' => 'Yedekleme ve Geri Yükleme'
                ]
            ],
            'sorun_giderme' => [
                'name' => 'Sorun Giderme',
                'icon' => 'fa-wrench',
                'topics' => [
                    'sikca_sorulanlar' => 'Sıkça Sorulan Sorular',
                    'api_hatalari' => 'API Hataları',
                    'log_analizi' => 'Log Analizi',
                    'performans' => 'Performans İyileştirme'
                ]
            ]
        ];
    }
    
    /**
     * Yardım içeriğini getir
     */
    private function getHelpContent($category, $topic) {
        $content = [
            'genel' => [
                'giris' => [
                    'title' => 'MesChain-Sync Nedir?',
                    'content' => '<h2>MesChain-Sync Hakkında</h2>
                    <p>MesChain-Sync, OpenCart tabanlı e-ticaret mağazanızı çeşitli pazaryerleri ile entegre etmenizi sağlayan güçlü bir eklentidir. Bu eklenti sayesinde ürünlerinizi, siparişlerinizi ve envanterinizi tek bir noktadan yönetebilirsiniz.</p>
                    <h3>Ana Özellikler</h3>
                    <ul>
                        <li>Çoklu pazaryeri entegrasyonu (Trendyol, Amazon, N11, Hepsiburada, eBay, Ozon)</li>
                        <li>Ürün eşleştirme ve senkronizasyon</li>
                        <li>Otomatik sipariş çekme ve işleme</li>
                        <li>Stok ve fiyat güncelleme</li>
                        <li>Detaylı raporlama ve analiz</li>
                    </ul>
                    <p>MesChain-Sync\'ın modüler yapısı sayesinde ihtiyacınız olan pazaryeri entegrasyonlarını ayrı ayrı etkinleştirebilir ve yönetebilirsiniz.</p>'
                ],
                'kurulum' => [
                    'title' => 'Kurulum Kılavuzu',
                    'content' => '<h2>MesChain-Sync Kurulum Adımları</h2>
                    <p>MesChain-Sync\'ı OpenCart mağazanıza kurmak için aşağıdaki adımları izleyin:</p>
                    <ol>
                        <li><strong>Eklenti Dosyalarını Yükleme</strong>
                            <ul>
                                <li>Eklenti paketini indirin ve dosyaları çıkartın</li>
                                <li>"upload" klasöründeki tüm dosyaları OpenCart kurulumunuzun kök dizinine kopyalayın</li>
                            </ul>
                        </li>
                        <li><strong>Eklentiyi Yükleme</strong>
                            <ul>
                                <li>OpenCart admin paneline giriş yapın</li>
                                <li>Eklentiler > Eklentiler menüsüne gidin</li>
                                <li>Modül tipini seçin</li>
                                <li>MesChain-Sync modülünü bulun ve Kur butonuna tıklayın</li>
                            </ul>
                        </li>
                        <li><strong>İzinleri Ayarlama</strong>
                            <ul>
                                <li>Sistem > Kullanıcılar > Kullanıcı Grupları menüsüne gidin</li>
                                <li>Admin grubunu düzenleyin ve MesChain-Sync için tüm izinleri etkinleştirin</li>
                            </ul>
                        </li>
                        <li><strong>Pazaryeri Entegrasyonlarını Yapılandırma</strong>
                            <ul>
                                <li>Her pazaryeri için API anahtarlarını ve gerekli bilgileri girin</li>
                                <li>Bağlantıyı test edin</li>
                                <li>Kategori ve ürün eşleştirmelerini yapılandırın</li>
                            </ul>
                        </li>
                    </ol>
                    <p><strong>Not:</strong> Kurulum sırasında herhangi bir sorunla karşılaşırsanız, log dosyalarını kontrol edin ve gerekirse destek ekibimizle iletişime geçin.</p>'
                ]
            ],
            'trendyol' => [
                'api_ayarlari' => [
                    'title' => 'Trendyol API Ayarları',
                    'content' => '<h2>Trendyol API Ayarları</h2>
                    <p>Trendyol entegrasyonu için aşağıdaki API bilgilerini yapılandırmanız gerekmektedir:</p>
                    <ul>
                        <li><strong>API Key:</strong> Trendyol satıcı panelinizden alacağınız API anahtarı</li>
                        <li><strong>API Secret:</strong> Trendyol satıcı panelinizden alacağınız API şifresi</li>
                        <li><strong>Cari ID:</strong> Trendyol tarafından size verilen satıcı numarası</li>
                        <li><strong>Entegrasyon Referans Kodu:</strong> Trendyol entegrasyon kimliği</li>
                    </ul>
                    <h3>API Bilgilerini Nereden Alabilirim?</h3>
                    <ol>
                        <li>Trendyol satıcı panelinize giriş yapın (https://partner.trendyol.com)</li>
                        <li>Hesabım > Entegrasyon bilgileri menüsüne gidin</li>
                        <li>API bilgilerini kopyalayın</li>
                    </ol>
                    <h3>Güvenlik Önlemleri</h3>
                    <p>API bilgilerinizi güvende tutmak için aşağıdaki önlemleri alın:</p>
                    <ul>
                        <li>API bilgilerinizi başkalarıyla paylaşmayın</li>
                        <li>API anahtarlarınızı düzenli olarak yenileyin</li>
                        <li>Sadece güvenli bağlantılar üzerinden erişim sağlayın</li>
                    </ul>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> API bağlantısını test etmek için "Bağlantıyı Test Et" butonunu kullanabilirsiniz.
                    </div>'
                ]
            ],
            'amazon' => [
                'api_ayarlari' => [
                    'title' => 'Amazon API Ayarları',
                    'content' => '<h2>Amazon Selling Partner API Ayarları</h2>
                    <p>Amazon entegrasyonu için aşağıdaki API bilgilerini yapılandırmanız gerekmektedir:</p>
                    <ul>
                        <li><strong>API Key:</strong> Amazon Seller Central\'dan alacağınız API anahtarı</li>
                        <li><strong>API Secret:</strong> Amazon Seller Central\'dan alacağınız API şifresi</li>
                        <li><strong>Refresh Token:</strong> API yetkilendirme için gereken token</li>
                        <li><strong>Seller ID:</strong> Amazon satıcı kimliğiniz</li>
                        <li><strong>Marketplace ID:</strong> Entegrasyon yapacağınız Amazon pazaryeri kimliği</li>
                        <li><strong>Region:</strong> Amazon bölgesi (eu, na, fe)</li>
                    </ul>
                    <h3>API Bilgilerini Nasıl Alabilirim?</h3>
                    <ol>
                        <li>Amazon Seller Central\'a giriş yapın</li>
                        <li>Ayarlar > Kullanıcı İzinleri menüsüne gidin</li>
                        <li>Yeni bir uygulama oluşturun ve gerekli izinleri verin</li>
                        <li>API bilgilerini kaydedin</li>
                    </ol>
                    <h3>Marketplace ID Bilgileri</h3>
                    <table class="table table-bordered">
                        <tr>
                            <th>Ülke</th>
                            <th>Marketplace ID</th>
                        </tr>
                        <tr>
                            <td>Almanya</td>
                            <td>A1PA6795UKMFR9</td>
                        </tr>
                        <tr>
                            <td>Fransa</td>
                            <td>A13V1IB3VIYZZH</td>
                        </tr>
                        <tr>
                            <td>İngiltere</td>
                            <td>A1F83G8C2ARO7P</td>
                        </tr>
                    </table>'
                ]
            ],
            'sorun_giderme' => [
                'sikca_sorulanlar' => [
                    'title' => 'Sıkça Sorulan Sorular',
                    'content' => '<h2>Sıkça Sorulan Sorular</h2>
                    <div class="panel-group" id="accordion">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
                                        Ürünlerim pazaryerinde görünmüyor, ne yapmalıyım?</a>
                                </h4>
                            </div>
                            <div id="collapse1" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <p>Ürünlerinizin pazaryerinde görünmemesinin birkaç nedeni olabilir:</p>
                                    <ul>
                                        <li>API bağlantınızı kontrol edin ve test edin</li>
                                        <li>Ürün senkronizasyonunu manuel olarak tetikleyin</li>
                                        <li>Ürünlerinizin pazaryeri gereksinimlerini karşıladığından emin olun (gerekli alanlar, kategoriler, vb.)</li>
                                        <li>Log dosyalarını kontrol ederek hata mesajlarını inceleyin</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
                                        Siparişler otomatik çekilmiyor, nasıl çözebilirim?</a>
                                </h4>
                            </div>
                            <div id="collapse2" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <p>Sipariş çekme işlemi için:</p>
                                    <ul>
                                        <li>Cron job\'un doğru yapılandırıldığından emin olun</li>
                                        <li>API izinlerinin sipariş çekme yetkisi içerdiğini kontrol edin</li>
                                        <li>Manuel olarak sipariş senkronizasyonunu tetikleyin</li>
                                        <li>Log dosyalarında hata mesajlarını kontrol edin</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
                                        Stok güncellemesi çalışmıyor, neden?</a>
                                </h4>
                            </div>
                            <div id="collapse3" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <p>Stok güncellemesi sorunları için:</p>
                                    <ul>
                                        <li>Ürünlerin pazaryeri ile doğru eşleştiğinden emin olun</li>
                                        <li>API bağlantısını kontrol edin</li>
                                        <li>Manuel stok güncellemesini deneyin</li>
                                        <li>Log dosyalarını kontrol edin</li>
                                        <li>Pazaryerindeki ürün durumlarını kontrol edin (bazı ürünler "askıya alınmış" olabilir)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>'
                ]
            ]
        ];
        
        // İstenen içerik yoksa varsayılan içeriği göster
        if (!isset($content[$category][$topic])) {
            return [
                'title' => 'İçerik Bulunamadı',
                'content' => '<div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> Aradığınız yardım içeriği henüz oluşturulmamış veya mevcut değil.
                </div>
                <p>Lütfen soldaki menüden başka bir konu seçin veya yardım almak için destek ekibimizle iletişime geçin.</p>'
            ];
        }
        
        return $content[$category][$topic];
    }
    
    /**
     * Yardım içeriği arama
     */
    public function search() {
        $this->load->language('extension/module/help');
        
        $json = array();
        
        if (isset($this->request->get['query'])) {
            $query = $this->request->get['query'];
            
            // Log
            $this->writeLog('admin', 'YARDIM_ARAMA', 'Arama sorgusu: ' . $query);
            
            // Arama sonuçları (örnek)
            $results = $this->searchHelpContent($query);
            
            $json['results'] = $results;
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Yardım içeriği arama işlemi
     */
    private function searchHelpContent($query) {
        // Gerçek uygulamada veritabanından veya dosyalardan arama yapılabilir
        // Şimdilik örnek sonuçlar döndürelim
        
        $results = array();
        
        if (stripos('trendyol', $query) !== false || stripos('api', $query) !== false) {
            $results[] = array(
                'title' => 'Trendyol API Ayarları',
                'url' => $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'] . '&category=trendyol&topic=api_ayarlari', true),
                'excerpt' => 'Trendyol entegrasyonu için API ayarlarını nasıl yapılandıracağınızı öğrenin.'
            );
        }
        
        if (stripos('amazon', $query) !== false || stripos('api', $query) !== false) {
            $results[] = array(
                'title' => 'Amazon API Ayarları',
                'url' => $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'] . '&category=amazon&topic=api_ayarlari', true),
                'excerpt' => 'Amazon Selling Partner API ayarlarını nasıl yapılandıracağınızı öğrenin.'
            );
        }
        
        if (stripos('kurulum', $query) !== false || stripos('yükleme', $query) !== false) {
            $results[] = array(
                'title' => 'Kurulum Kılavuzu',
                'url' => $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'] . '&category=genel&topic=kurulum', true),
                'excerpt' => 'MesChain-Sync\'ı OpenCart mağazanıza nasıl kuracağınızı öğrenin.'
            );
        }
        
        if (stripos('sorun', $query) !== false || stripos('hata', $query) !== false || stripos('sık', $query) !== false) {
            $results[] = array(
                'title' => 'Sıkça Sorulan Sorular',
                'url' => $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'] . '&category=sorun_giderme&topic=sikca_sorulanlar', true),
                'excerpt' => 'Yaygın sorunlar ve çözümleri hakkında bilgi alın.'
            );
        }
        
        return $results;
    }
    
    /**
     * İletişim formu
     */
    public function contact() {
        $this->load->language('extension/module/help');
        
        $json = array();
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateContactForm()) {
            // İletişim formu verileri
            $name = $this->request->post['name'];
            $email = $this->request->post['email'];
            $subject = $this->request->post['subject'];
            $message = $this->request->post['message'];
            
            // Log
            $this->writeLog('admin', 'YARDIM_ILETISIM', 'İletişim formu gönderildi: ' . $subject);
            
            // E-posta gönderme işlemi (örnek)
            $mail_sent = true; // Gerçek uygulamada mail() veya SMTP ile e-posta gönderilecek
            
            if ($mail_sent) {
                $json['success'] = $this->language->get('text_contact_success');
            } else {
                $json['error'] = $this->language->get('error_contact_mail');
            }
        } else {
            $json['error'] = isset($this->error['message']) ? $this->error['message'] : $this->language->get('error_contact_form');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * İletişim formu doğrulama
     */
    private function validateContactForm() {
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
            $this->error['message'] = $this->language->get('error_contact_name');
            return false;
        }
        
        if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['message'] = $this->language->get('error_contact_email');
            return false;
        }
        
        if ((utf8_strlen($this->request->post['subject']) < 3) || (utf8_strlen($this->request->post['subject']) > 64)) {
            $this->error['message'] = $this->language->get('error_contact_subject');
            return false;
        }
        
        if ((utf8_strlen($this->request->post['message']) < 10) || (utf8_strlen($this->request->post['message']) > 1000)) {
            $this->error['message'] = $this->language->get('error_contact_message');
            return false;
        }
        
        return true;
    }
    
    /**
     * Kurulum işlemi
     */
    public function install() {
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/help');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/help');
        
        $this->writeLog('admin', 'KURULUM', 'Yardım modülü kuruldu.');
    }
    
    /**
     * Kaldırma işlemi
     */
    public function uninstall() {
        $this->writeLog('admin', 'KALDIRMA', 'Yardım modülü kaldırıldı.');
    }
    
    /**
     * Loglama fonksiyonu
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'help_controller.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 