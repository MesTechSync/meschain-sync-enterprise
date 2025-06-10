import React from 'react';

interface I18nProviderProps {
  children: React.ReactNode;
}

export const I18nProvider: React.FC<I18nProviderProps> = ({ children }) => {
  // Simple i18n context for now, can be extended later
  return <>{children}</>;
}; 