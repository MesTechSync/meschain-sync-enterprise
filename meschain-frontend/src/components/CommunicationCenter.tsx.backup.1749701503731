import React, { useState, useEffect } from 'react';

interface NotificationChannel {
  id: string;
  name: string;
  type: 'email' | 'sms' | 'push' | 'webhook' | 'slack';
  isActive: boolean;
  config: Record<string, any>;
  lastUsed: string;
  messagesSent: number;
  successRate: number;
}

interface CommunicationTemplate {
  id: string;
  name: string;
  type: 'order' | 'stock' | 'error' | 'marketing' | 'system';
  channels: string[];
  subject: string;
  content: string;
  variables: string[];
  isActive: boolean;
}

interface MessageHistory {
  id: string;
  template: string;
  channel: string;
  recipient: string;
  status: 'sent' | 'delivered' | 'failed' | 'pending';
  sentAt: string;
  deliveredAt?: string;
  errorMessage?: string;
}

const CommunicationCenter: React.FC = () => {
  const [channels, setChannels] = useState<NotificationChannel[]>([]);
  const [templates, setTemplates] = useState<CommunicationTemplate[]>([]);
  const [messageHistory, setMessageHistory] = useState<MessageHistory[]>([]);
  const [selectedTab, setSelectedTab] = useState<'overview' | 'channels' | 'templates' | 'history'>('overview');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadCommunicationData();
  }, []);

  const loadCommunicationData = async () => {
    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 1000));

      const mockChannels: NotificationChannel[] = [
        {
          id: '1',
          name: 'Email Bildirimleri',
          type: 'email',
          isActive: true,
          config: { smtp: 'smtp.gmail.com', port: 587 },
          lastUsed: '2025-06-02T10:30:00Z',
          messagesSent: 1247,
          successRate: 98.5
        },
        {
          id: '2',
          name: 'SMS Bildirimleri',
          type: 'sms',
          isActive: true,
          config: { provider: 'Twilio', apiKey: '***' },
          lastUsed: '2025-06-02T09:45:00Z',
          messagesSent: 456,
          successRate: 97.2
        },
        {
          id: '3',
          name: 'Push Notifications',
          type: 'push',
          isActive: true,
          config: { fcmKey: '***' },
          lastUsed: '2025-06-02T10:15:00Z',
          messagesSent: 2847,
          successRate: 95.8
        },
        {
          id: '4',
          name: 'Slack Entegrasyonu',
          type: 'slack',
          isActive: false,
          config: { webhook: '***' },
          lastUsed: '2025-06-01T15:20:00Z',
          messagesSent: 89,
          successRate: 100
        }
      ];

      const mockTemplates: CommunicationTemplate[] = [
        {
          id: '1',
          name: 'Yeni Sipariş Bildirimi',
          type: 'order',
          channels: ['email', 'push'],
          subject: 'Yeni Sipariş Alındı - #{order_id}',
          content: 'Merhaba {customer_name}, {order_id} numaralı siparişiniz alınmıştır.',
          variables: ['order_id', 'customer_name', 'total_amount'],
          isActive: true
        },
        {
          id: '2',
          name: 'Düşük Stok Uyarısı',
          type: 'stock',
          channels: ['email', 'sms'],
          subject: 'Stok Uyarısı - {product_name}',
          content: '{product_name} ürününde stok azaldı. Kalan: {stock_count}',
          variables: ['product_name', 'stock_count', 'marketplace'],
          isActive: true
        }
      ];

      const mockHistory: MessageHistory[] = [
        {
          id: '1',
          template: 'Yeni Sipariş Bildirimi',
          channel: 'email',
          recipient: 'admin@meschain.com',
          status: 'delivered',
          sentAt: '2025-06-02T10:30:00Z',
          deliveredAt: '2025-06-02T10:30:15Z'
        },
        {
          id: '2',
          template: 'Düşük Stok Uyarısı',
          channel: 'sms',
          recipient: '+905551234567',
          status: 'sent',
          sentAt: '2025-06-02T10:25:00Z'
        }
      ];

      setChannels(mockChannels);
      setTemplates(mockTemplates);
      setMessageHistory(mockHistory);
    } catch (error) {
      console.error('Communication data loading error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const getChannelIcon = (type: string) => {
    switch (type) {
      case 'email': return '📧';
      case 'sms': return '📱';
      case 'push': return '🔔';
      case 'webhook': return '🔗';
      case 'slack': return '💬';
      default: return '📢';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'sent': return 'bg-blue-100 text-blue-800';
      case 'delivered': return 'bg-green-100 text-green-800';
      case 'failed': return 'bg-red-100 text-red-800';
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">İletişim merkezi yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">İletişim Merkezi</h1>
          <p className="text-sm text-gray-500 mt-1">Çok kanallı bildirim ve iletişim yönetimi</p>
        </div>
        <div className="flex space-x-4">
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>➕</span>
            <span>Yeni Şablon</span>
          </button>
          <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>📤</span>
            <span>Test Mesajı</span>
          </button>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Aktif Kanallar</p>
              <p className="text-2xl font-bold text-gray-900">{channels.filter(c => c.isActive).length}</p>
            </div>
            <div className="p-3 rounded-full text-2xl bg-blue-100">📡</div>
          </div>
        </div>
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Bugün Gönderilen</p>
              <p className="text-2xl font-bold text-gray-900">2,847</p>
            </div>
            <div className="p-3 rounded-full text-2xl bg-green-100">📤</div>
          </div>
        </div>
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Başarı Oranı</p>
              <p className="text-2xl font-bold text-gray-900">97.8%</p>
            </div>
            <div className="p-3 rounded-full text-2xl bg-yellow-100">🎯</div>
          </div>
        </div>
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Aktif Şablonlar</p>
              <p className="text-2xl font-bold text-gray-900">{templates.filter(t => t.isActive).length}</p>
            </div>
            <div className="p-3 rounded-full text-2xl bg-purple-100">📝</div>
          </div>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        <div className="border-b border-gray-200">
          <nav className="flex space-x-8 px-6">
            {[
              { id: 'overview', label: 'Genel Bakış', icon: '📊' },
              { id: 'channels', label: 'Kanallar', icon: '📡' },
              { id: 'templates', label: 'Şablonlar', icon: '📝' },
              { id: 'history', label: 'Mesaj Geçmişi', icon: '📋' }
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setSelectedTab(tab.id as any)}
                className={`py-4 px-1 border-b-2 font-medium text-sm flex items-center space-x-2 ${
                  selectedTab === tab.id
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                <span>{tab.icon}</span>
                <span>{tab.label}</span>
              </button>
            ))}
          </nav>
        </div>

        <div className="p-6">
          {/* Overview Tab */}
          {selectedTab === 'overview' && (
            <div className="space-y-6">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Channel Status */}
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">Kanal Durumu</h3>
                  <div className="space-y-3">
                    {channels.map((channel) => (
                      <div key={channel.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div className="flex items-center space-x-3">
                          <span className="text-2xl">{getChannelIcon(channel.type)}</span>
                          <div>
                            <p className="font-medium text-gray-900">{channel.name}</p>
                            <p className="text-sm text-gray-500">{channel.messagesSent} mesaj gönderildi</p>
                          </div>
                        </div>
                        <div className="text-right">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                            channel.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                          }`}>
                            {channel.isActive ? 'Aktif' : 'Pasif'}
                          </span>
                          <p className="text-sm text-gray-500 mt-1">%{channel.successRate} başarı</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Recent Messages */}
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">Son Mesajlar</h3>
                  <div className="space-y-3">
                    {messageHistory.slice(0, 5).map((message) => (
                      <div key={message.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                          <p className="font-medium text-gray-900">{message.template}</p>
                          <p className="text-sm text-gray-500">{message.recipient}</p>
                        </div>
                        <div className="text-right">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(message.status)}`}>
                            {message.status}
                          </span>
                          <p className="text-sm text-gray-500 mt-1">
                            {new Date(message.sentAt).toLocaleString('tr-TR')}
                          </p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Channels Tab */}
          {selectedTab === 'channels' && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <h3 className="text-lg font-semibold text-gray-900">Bildirim Kanalları</h3>
                <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                  Yeni Kanal Ekle
                </button>
              </div>
              
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {channels.map((channel) => (
                  <div key={channel.id} className="bg-white border border-gray-200 rounded-lg p-6">
                    <div className="flex justify-between items-start mb-4">
                      <div className="flex items-center space-x-3">
                        <span className="text-3xl">{getChannelIcon(channel.type)}</span>
                        <div>
                          <h4 className="font-semibold text-gray-900">{channel.name}</h4>
                          <p className="text-sm text-gray-500 capitalize">{channel.type}</p>
                        </div>
                      </div>
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        channel.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {channel.isActive ? 'Aktif' : 'Pasif'}
                      </span>
                    </div>
                    
                    <div className="space-y-3">
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Gönderilen Mesaj:</span>
                        <span className="text-sm font-medium">{channel.messagesSent.toLocaleString()}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Başarı Oranı:</span>
                        <span className="text-sm font-medium">%{channel.successRate}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Son Kullanım:</span>
                        <span className="text-sm font-medium">
                          {new Date(channel.lastUsed).toLocaleDateString('tr-TR')}
                        </span>
                      </div>
                    </div>
                    
                    <div className="mt-4 flex space-x-2">
                      <button className="flex-1 bg-blue-50 text-blue-600 py-2 px-3 rounded text-sm hover:bg-blue-100">
                        Yapılandır
                      </button>
                      <button className="flex-1 bg-gray-50 text-gray-600 py-2 px-3 rounded text-sm hover:bg-gray-100">
                        Test Et
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Templates Tab */}
          {selectedTab === 'templates' && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <h3 className="text-lg font-semibold text-gray-900">Mesaj Şablonları</h3>
                <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                  Yeni Şablon
                </button>
              </div>
              
              <div className="space-y-4">
                {templates.map((template) => (
                  <div key={template.id} className="bg-white border border-gray-200 rounded-lg p-6">
                    <div className="flex justify-between items-start mb-4">
                      <div>
                        <h4 className="font-semibold text-gray-900">{template.name}</h4>
                        <p className="text-sm text-gray-500 capitalize">{template.type}</p>
                      </div>
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        template.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {template.isActive ? 'Aktif' : 'Pasif'}
                      </span>
                    </div>
                    
                    <div className="space-y-3">
                      <div>
                        <span className="text-sm text-gray-600">Konu:</span>
                        <p className="text-sm font-medium">{template.subject}</p>
                      </div>
                      <div>
                        <span className="text-sm text-gray-600">İçerik:</span>
                        <p className="text-sm text-gray-700">{template.content}</p>
                      </div>
                      <div>
                        <span className="text-sm text-gray-600">Kanallar:</span>
                        <div className="flex space-x-2 mt-1">
                          {template.channels.map((channel, index) => (
                            <span key={index} className="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                              {channel}
                            </span>
                          ))}
                        </div>
                      </div>
                    </div>
                    
                    <div className="mt-4 flex space-x-2">
                      <button className="bg-blue-50 text-blue-600 py-2 px-3 rounded text-sm hover:bg-blue-100">
                        Düzenle
                      </button>
                      <button className="bg-green-50 text-green-600 py-2 px-3 rounded text-sm hover:bg-green-100">
                        Test Et
                      </button>
                      <button className="bg-gray-50 text-gray-600 py-2 px-3 rounded text-sm hover:bg-gray-100">
                        Kopyala
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* History Tab */}
          {selectedTab === 'history' && (
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <h3 className="text-lg font-semibold text-gray-900">Mesaj Geçmişi</h3>
                <div className="flex space-x-2">
                  <select className="bg-white border border-gray-300 rounded px-3 py-2 text-sm">
                    <option>Tüm Kanallar</option>
                    <option>Email</option>
                    <option>SMS</option>
                    <option>Push</option>
                  </select>
                  <select className="bg-white border border-gray-300 rounded px-3 py-2 text-sm">
                    <option>Tüm Durumlar</option>
                    <option>Gönderildi</option>
                    <option>Teslim Edildi</option>
                    <option>Başarısız</option>
                  </select>
                </div>
              </div>
              
              <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Şablon
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kanal
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Alıcı
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Durum
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Gönderim Zamanı
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {messageHistory.map((message) => (
                      <tr key={message.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {message.template}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {message.channel}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {message.recipient}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(message.status)}`}>
                            {message.status}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {new Date(message.sentAt).toLocaleString('tr-TR')}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default CommunicationCenter; 