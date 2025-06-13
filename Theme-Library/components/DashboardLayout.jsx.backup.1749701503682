import React, { useState } from 'react';
import './DashboardLayout.css';

/**
 * 🎨 MesChain Dashboard Layout Component
 * Yeniden kullanılabilir dashboard layout bileşeni
 */
const DashboardLayout = ({ 
  title = "Dashboard", 
  children, 
  sidebarItems = [],
  headerActions = null,
  theme = "modern"
}) => {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [currentPage, setCurrentPage] = useState('dashboard');

  const defaultSidebarItems = [
    { id: 'dashboard', icon: '📊', label: 'Dashboard', active: true },
    { id: 'products', icon: '📦', label: 'Ürünler' },
    { id: 'orders', icon: '🛒', label: 'Siparişler' },
    { id: 'analytics', icon: '📈', label: 'Analitik' },
    { id: 'settings', icon: '⚙️', label: 'Ayarlar' }
  ];

  const menuItems = sidebarItems.length > 0 ? sidebarItems : defaultSidebarItems;

  return (
    <div className={`dashboard-layout theme-${theme}`}>
      {/* Sidebar */}
      <div className={`sidebar ${sidebarOpen ? 'open' : 'closed'}`}>
        <div className="sidebar-header">
          <div className="logo">
            <span className="logo-icon">🚀</span>
            {sidebarOpen && <span className="logo-text">MesChain</span>}
          </div>
          <button 
            className="sidebar-toggle"
            onClick={() => setSidebarOpen(!sidebarOpen)}
          >
            {sidebarOpen ? '◀' : '▶'}
          </button>
        </div>
        
        <nav className="sidebar-nav">
          {menuItems.map(item => (
            <div 
              key={item.id}
              className={`nav-item ${currentPage === item.id ? 'active' : ''}`}
              onClick={() => setCurrentPage(item.id)}
            >
              <span className="nav-icon">{item.icon}</span>
              {sidebarOpen && <span className="nav-label">{item.label}</span>}
            </div>
          ))}
        </nav>
      </div>

      {/* Main Content */}
      <div className="main-content">
        {/* Header */}
        <header className="main-header">
          <div className="header-left">
            <h1 className="page-title">{title}</h1>
          </div>
          <div className="header-right">
            {headerActions}
            <div className="user-profile">
              <span className="user-avatar">👤</span>
              <span className="user-name">Admin</span>
            </div>
          </div>
        </header>

        {/* Content Area */}
        <main className="content-area">
          {children}
        </main>
      </div>
    </div>
  );
};

export default DashboardLayout; 