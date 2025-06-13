import React from 'react';
import { useTranslation } from 'react-i18next';

interface PWAPromptProps {
  isInstallable: boolean;
  isOffline: boolean;
  updateAvailable: boolean;
  onInstall: () => void;
  onUpdate: () => void;
  onDismissInstall: () => void;
  onDismissUpdate: () => void;
}

const PWAPrompt: React.FC<PWAPromptProps> = ({
  isInstallable,
  isOffline,
  updateAvailable,
  onInstall,
  onUpdate,
  onDismissInstall,
  onDismissUpdate,
}) => {
  const { t } = useTranslation();

  return (
    <>
      {/* Install Prompt */}
      {isInstallable && (
        <div className="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
          <div className="flex items-start">
            <div className="flex-shrink-0">
              <span className="text-2xl">📱</span>
            </div>
            <div className="ml-3 flex-1">
              <h3 className="text-sm font-medium text-gray-900">
                {t('pwa.installPrompt')}
              </h3>
              <div className="mt-3 flex space-x-2">
                <button
                  onClick={onInstall}
                  className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                >
                  {t('pwa.install')}
                </button>
                <button
                  onClick={onDismissInstall}
                  className="bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-200"
                >
                  {t('pwa.dismiss')}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Update Prompt */}
      {updateAvailable && (
        <div className="fixed top-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-blue-50 border border-blue-200 rounded-lg shadow-lg p-4 z-50">
          <div className="flex items-start">
            <div className="flex-shrink-0">
              <span className="text-2xl">🔄</span>
            </div>
            <div className="ml-3 flex-1">
              <h3 className="text-sm font-medium text-blue-900">
                {t('pwa.updateAvailable')}
              </h3>
              <div className="mt-3 flex space-x-2">
                <button
                  onClick={onUpdate}
                  className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                >
                  {t('pwa.updateNow')}
                </button>
                <button
                  onClick={onDismissUpdate}
                  className="bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm hover:bg-blue-200"
                >
                  {t('pwa.updateLater')}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Offline Status */}
      {isOffline && (
        <div className="fixed top-4 left-1/2 transform -translate-x-1/2 bg-yellow-50 border border-yellow-200 rounded-lg shadow-lg p-3 z-50">
          <div className="flex items-center">
            <span className="text-yellow-600 mr-2">📡</span>
            <span className="text-sm font-medium text-yellow-800">
              {t('pwa.offlineMode')}
            </span>
          </div>
        </div>
      )}
    </>
  );
};

export default PWAPrompt; 