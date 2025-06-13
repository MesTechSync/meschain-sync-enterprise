import React from 'react';
import { usePWA } from '../hooks/usePWA';

const PWAPrompt: React.FC = () => {
  const {
    isInstallable,
    isOffline,
    isUpdateAvailable,
    installApp,
    updateApp,
    dismissInstallPrompt
  } = usePWA();

  const handleInstall = async () => {
    const success = await installApp();
    if (success) {
      console.log('App installed successfully!');
    }
  };

  const handleUpdate = async () => {
    await updateApp();
  };

  return (
    <>
      {/* Offline Indicator */}
      {isOffline && (
        <div className="fixed top-0 left-0 right-0 bg-yellow-500 text-white text-center py-2 px-4 z-50">
          <div className="flex items-center justify-center space-x-2">
            <span className="animate-pulse">📡</span>
            <span className="font-medium">Çevrimdışı Mod - Önbelleğe alınmış veriler gösteriliyor</span>
          </div>
        </div>
      )}

      {/* Install Prompt */}
      {isInstallable && (
        <div className="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white rounded-lg shadow-lg border border-gray-200 p-4 z-40">
          <div className="flex items-start space-x-3">
            <div className="flex-shrink-0">
              <div className="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                <span className="text-white text-lg">📱</span>
              </div>
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="text-sm font-medium text-gray-900">
                MesChain-Sync'i Yükle
              </h3>
              <p className="text-sm text-gray-500 mt-1">
                Daha hızlı erişim için uygulamayı cihazınıza yükleyin
              </p>
              <div className="mt-3 flex space-x-2">
                <button
                  onClick={handleInstall}
                  className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                  <span className="mr-1">⬇️</span>
                  Yükle
                </button>
                <button
                  onClick={dismissInstallPrompt}
                  className="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                  Daha Sonra
                </button>
              </div>
            </div>
            <div className="flex-shrink-0">
              <button
                onClick={dismissInstallPrompt}
                className="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition-colors"
              >
                <span className="sr-only">Kapat</span>
                <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Update Available Prompt */}
      {isUpdateAvailable && (
        <div className="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-green-50 rounded-lg shadow-lg border border-green-200 p-4 z-40">
          <div className="flex items-start space-x-3">
            <div className="flex-shrink-0">
              <div className="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                <span className="text-white text-lg">🔄</span>
              </div>
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="text-sm font-medium text-green-900">
                Güncelleme Mevcut
              </h3>
              <p className="text-sm text-green-700 mt-1">
                Yeni özellikler ve iyileştirmeler için uygulamayı güncelleyin
              </p>
              <div className="mt-3 flex space-x-2">
                <button
                  onClick={handleUpdate}
                  className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                >
                  <span className="mr-1">⬆️</span>
                  Güncelle
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default PWAPrompt; 