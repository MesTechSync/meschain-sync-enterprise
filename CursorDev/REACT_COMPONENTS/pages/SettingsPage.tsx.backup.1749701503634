import React, { useState, useEffect } from 'react';
import apiService from '../services/api';

interface SystemSettings {
  general: {
    siteName: string;
    timezone: string;
    language: string;
    currency: string;
    dateFormat: string;
  };
  notifications: {
    emailNotifications: boolean;
    smsNotifications: boolean;
    webhookNotifications: boolean;
    lowStockAlert: boolean;
    orderNotifications: boolean;
  };
  sync: {
    autoSync: boolean;
    syncInterval: number;
    batchSize: number;
    retryAttempts: number;
  };
  security: {
    twoFactorAuth: boolean;
    sessionTimeout: number;
    ipWhitelist: string[];
    apiRateLimit: number;
  };
}

interface APIKeys {
  amazon: string;
  ebay: string;
  trendyol: string;
  n11: string;
  hepsiburada: string;
  ozon: string;
}

const SettingsPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState('general');
  const [settings, setSettings] = useState<SystemSettings | null>(null);
  const [apiKeys, setApiKeys] = useState<APIKeys | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [testingConnection, setTestingConnection] = useState<string | null>(null);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setIsLoading(true);
      
      // Mock data - replace with actual API calls
      const mockSettings: SystemSettings = {
        general: {
          siteName: 'MesChain-Sync',
          timezone: 'Europe/Istanbul',
          language: 'tr-TR',
          currency: 'TRY',
          dateFormat: 'DD/MM/YYYY'
        },
        notifications: {
          emailNotifications: true,
          smsNotifications: false,
          webhookNotifications: true,
          lowStockAlert: true,
          orderNotifications: true
        },
        sync: {
          autoSync: true,
          syncInterval: 15,
          batchSize: 100,
          retryAttempts: 3
        },
        security: {
          twoFactorAuth: false,
          sessionTimeout: 60,
          ipWhitelist: ['192.168.1.0/24'],
          apiRateLimit: 1000
        }
      };

      const mockApiKeys: APIKeys = {
        amazon: 'AKIA***************',
        ebay: 'EBAY***************',
        trendyol: 'TY***************',
        n11: 'N11***************',
        hepsiburada: 'HB***************',
        ozon: 'OZON***************'
      };

      setSettings(mockSettings);
      setApiKeys(mockApiKeys);
    } catch (error) {
      console.error('Settings fetch error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSaveSettings = async () => {
    if (!settings) return;

    try {
      setIsSaving(true);
      const response = await apiService.updateSystemSettings(settings);
      
      if (response.success) {
        alert('Ayarlar başarıyla kaydedildi!');
      } else {
        alert('Kaydetme hatası: ' + response.error);
      }
    } catch (error) {
      console.error('Save settings error:', error);
      alert('Ayarlar kaydedilirken bir hata oluştu');
    } finally {
      setIsSaving(false);
    }
  };

  const handleSaveApiKey = async (marketplace: string, apiKey: string) => {
    try {
      const response = await apiService.updateAPIKey(marketplace, apiKey);
      
      if (response.success) {
        alert(`${marketplace} API anahtarı başarıyla kaydedildi!`);
      } else {
        alert('API anahtarı kaydetme hatası: ' + response.error);
      }
    } catch (error) {
      console.error('Save API key error:', error);
      alert('API anahtarı kaydedilirken bir hata oluştu');
    }
  };

  const handleTestConnection = async (marketplace: string) => {
    try {
      setTestingConnection(marketplace);
      const response = await apiService.testAPIConnection(marketplace);
      
      if (response.success) {
        alert(`${marketplace} bağlantı testi başarılı!`);
      } else {
        alert(`${marketplace} bağlantı testi başarısız: ` + response.error);
      }
    } catch (error) {
      console.error('Connection test error:', error);
      alert('Bağlantı testi sırasında bir hata oluştu');
    } finally {
      setTestingConnection(null);
    }
  };

  const updateSettings = (section: keyof SystemSettings, field: string, value: any) => {
    if (!settings) return;
    
    setSettings({
      ...settings,
      [section]: {
        ...settings[section],
        [field]: value
      }
    });
  };

  const updateApiKey = (marketplace: keyof APIKeys, value: string) => {
    if (!apiKeys) return;
    
    setApiKeys({
      ...apiKeys,
      [marketplace]: value
    });
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-600"></div>
      </div>
    );
  }

  if (!settings || !apiKeys) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="text-6xl mb-4">⚙️</div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Ayarlar Yüklenemedi</h2>
          <p className="text-gray-600">Lütfen daha sonra tekrar deneyin.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 flex items-center">
          <span className="mr-3">⚙️</span>
          Sistem Ayarları
        </h1>
        <p className="mt-2 text-gray-600">
          Sistem konfigürasyonunu ve API ayarlarını yönetin
        </p>
      </div>

      {/* Navigation Tabs */}
      <div className="mb-6">
        <nav className="flex space-x-8">
          {[
            { id: 'general', label: '🏠 Genel', icon: '🏠' },
            { id: 'api', label: '🔑 API Anahtarları', icon: '🔑' },
            { id: 'notifications', label: '🔔 Bildirimler', icon: '🔔' },
            { id: 'sync', label: '🔄 Senkronizasyon', icon: '🔄' },
            { id: 'security', label: '🔒 Güvenlik', icon: '🔒' }
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`py-2 px-1 border-b-2 font-medium text-sm ${
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </nav>
      </div>

      {/* General Settings */}
      {activeTab === 'general' && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-6">Genel Ayarlar</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Site Adı</label>
              <input
                type="text"
                value={settings.general.siteName}
                onChange={(e) => updateSettings('general', 'siteName', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Zaman Dilimi</label>
              <select
                value={settings.general.timezone}
                onChange={(e) => updateSettings('general', 'timezone', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="Europe/Istanbul">Europe/Istanbul</option>
                <option value="UTC">UTC</option>
                <option value="America/New_York">America/New_York</option>
                <option value="Europe/London">Europe/London</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Dil</label>
              <select
                value={settings.general.language}
                onChange={(e) => updateSettings('general', 'language', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="tr-TR">Türkçe</option>
                <option value="en-US">English</option>
                <option value="de-DE">Deutsch</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Para Birimi</label>
              <select
                value={settings.general.currency}
                onChange={(e) => updateSettings('general', 'currency', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="TRY">TRY (₺)</option>
                <option value="USD">USD ($)</option>
                <option value="EUR">EUR (€)</option>
                <option value="GBP">GBP (£)</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Tarih Formatı</label>
              <select
                value={settings.general.dateFormat}
                onChange={(e) => updateSettings('general', 'dateFormat', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
              </select>
            </div>
          </div>
        </div>
      )}

      {/* API Keys */}
      {activeTab === 'api' && (
        <div className="space-y-6">
          {Object.entries(apiKeys).map(([marketplace, key]) => (
            <div key={marketplace} className="bg-white rounded-lg shadow p-6">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-900 capitalize">
                  {marketplace} API Anahtarı
                </h3>
                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                  key ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
                  {key ? '🟢 Yapılandırıldı' : '🔴 Yapılandırılmadı'}
                </span>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">API Anahtarı</label>
                  <input
                    type="password"
                    value={key}
                    onChange={(e) => updateApiKey(marketplace as keyof APIKeys, e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder={`${marketplace} API anahtarınızı girin`}
                  />
                </div>
                
                <div className="flex items-end space-x-2">
                  <button
                    onClick={() => handleSaveApiKey(marketplace, key)}
                    className="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                  >
                    💾 Kaydet
                  </button>
                  <button
                    onClick={() => handleTestConnection(marketplace)}
                    disabled={testingConnection === marketplace || !key}
                    className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                  >
                    {testingConnection === marketplace ? '⏳' : '🔗'} Test
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Notifications */}
      {activeTab === 'notifications' && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-6">Bildirim Ayarları</h3>
          
          <div className="space-y-4">
            {Object.entries(settings.notifications).map(([key, value]) => (
              <div key={key} className="flex items-center justify-between">
                <div>
                  <h4 className="text-sm font-medium text-gray-900">
                    {key === 'emailNotifications' && '📧 E-posta Bildirimleri'}
                    {key === 'smsNotifications' && '📱 SMS Bildirimleri'}
                    {key === 'webhookNotifications' && '🔗 Webhook Bildirimleri'}
                    {key === 'lowStockAlert' && '📦 Düşük Stok Uyarısı'}
                    {key === 'orderNotifications' && '🛒 Sipariş Bildirimleri'}
                  </h4>
                  <p className="text-sm text-gray-500">
                    {key === 'emailNotifications' && 'Önemli olaylar için e-posta bildirimleri alın'}
                    {key === 'smsNotifications' && 'Acil durumlar için SMS bildirimleri alın'}
                    {key === 'webhookNotifications' && 'Webhook URL\'lerine bildirim gönderin'}
                    {key === 'lowStockAlert' && 'Stok azaldığında uyarı alın'}
                    {key === 'orderNotifications' && 'Yeni siparişler için bildirim alın'}
                  </p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={value}
                    onChange={(e) => updateSettings('notifications', key, e.target.checked)}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Sync Settings */}
      {activeTab === 'sync' && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-6">Senkronizasyon Ayarları</h3>
          
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <div>
                <h4 className="text-sm font-medium text-gray-900">🔄 Otomatik Senkronizasyon</h4>
                <p className="text-sm text-gray-500">Belirli aralıklarla otomatik senkronizasyon yapın</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={settings.sync.autoSync}
                  onChange={(e) => updateSettings('sync', 'autoSync', e.target.checked)}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Senkronizasyon Aralığı (dakika)</label>
                <input
                  type="number"
                  value={settings.sync.syncInterval}
                  onChange={(e) => updateSettings('sync', 'syncInterval', parseInt(e.target.value))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  min="5"
                  max="1440"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Batch Boyutu</label>
                <input
                  type="number"
                  value={settings.sync.batchSize}
                  onChange={(e) => updateSettings('sync', 'batchSize', parseInt(e.target.value))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  min="10"
                  max="1000"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Yeniden Deneme Sayısı</label>
                <input
                  type="number"
                  value={settings.sync.retryAttempts}
                  onChange={(e) => updateSettings('sync', 'retryAttempts', parseInt(e.target.value))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  min="1"
                  max="10"
                />
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Security Settings */}
      {activeTab === 'security' && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-6">Güvenlik Ayarları</h3>
          
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <div>
                <h4 className="text-sm font-medium text-gray-900">🔐 İki Faktörlü Kimlik Doğrulama</h4>
                <p className="text-sm text-gray-500">Hesap güvenliği için ek koruma katmanı</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={settings.security.twoFactorAuth}
                  onChange={(e) => updateSettings('security', 'twoFactorAuth', e.target.checked)}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Oturum Zaman Aşımı (dakika)</label>
                <input
                  type="number"
                  value={settings.security.sessionTimeout}
                  onChange={(e) => updateSettings('security', 'sessionTimeout', parseInt(e.target.value))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  min="15"
                  max="480"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">API Rate Limit (istek/saat)</label>
                <input
                  type="number"
                  value={settings.security.apiRateLimit}
                  onChange={(e) => updateSettings('security', 'apiRateLimit', parseInt(e.target.value))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  min="100"
                  max="10000"
                />
              </div>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">IP Beyaz Listesi</label>
              <textarea
                value={settings.security.ipWhitelist.join('\n')}
                onChange={(e) => updateSettings('security', 'ipWhitelist', e.target.value.split('\n').filter(ip => ip.trim()))}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                rows={4}
                placeholder="Her satıra bir IP adresi veya CIDR bloğu girin"
              />
              <p className="text-sm text-gray-500 mt-1">
                Örnek: 192.168.1.0/24, 10.0.0.1
              </p>
            </div>
          </div>
        </div>
      )}

      {/* Save Button */}
      <div className="mt-8 flex justify-end">
        <button
          onClick={handleSaveSettings}
          disabled={isSaving}
          className="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-3 rounded-md font-medium transition-colors"
        >
          {isSaving ? (
            <span className="flex items-center">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              Kaydediliyor...
            </span>
          ) : (
            '💾 Ayarları Kaydet'
          )}
        </button>
      </div>
    </div>
  );
};

export default SettingsPage; 