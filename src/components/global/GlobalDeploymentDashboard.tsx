/**
 * Global Deployment Dashboard - Simplified version
 * Essential monitoring for international deployments
 */

import React from 'react';

const GlobalDeploymentDashboard: React.FC = () => {
  return (
    <div style={{ padding: '20px' }}>
      <h1>🌍 Global Deployment Dashboard</h1>
      
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '20px', marginBottom: '30px' }}>
        <div style={{ background: '#f5f5f5', padding: '20px', borderRadius: '8px' }}>
          <h3>📊 Global Metrics</h3>
          <p>Total Deployments: <strong>12</strong></p>
          <p>Active Marketplaces: <strong>8</strong></p>
          <p>Supported Languages: <strong>24</strong></p>
          <p>Compliance Score: <strong>96.8%</strong></p>
        </div>
        
        <div style={{ background: '#e3f2fd', padding: '20px', borderRadius: '8px' }}>
          <h3>🌎 Regional Status</h3>
          <p>🇪🇺 Europe: <span style={{ color: 'green' }}>✅ Active</span></p>
          <p>🇦🇸 Asia: <span style={{ color: 'green' }}>✅ Active</span></p>
          <p>🇺🇸 Americas: <span style={{ color: 'orange' }}>🔄 Deploying</span></p>
          <p>🇦🇺 Oceania: <span style={{ color: 'blue' }}>🔧 Maintenance</span></p>
        </div>
        
        <div style={{ background: '#f3e5f5', padding: '20px', borderRadius: '8px' }}>
          <h3>🛡️ Compliance</h3>
          <p>GDPR: <strong>98.5%</strong></p>
          <p>CCPA: <strong>94.2%</strong></p>
          <p>LGPD: <strong>92.1%</strong></p>
          <p>Active Alerts: <strong>2</strong></p>
        </div>
        
        <div style={{ background: '#e8f5e8', padding: '20px', borderRadius: '8px' }}>
          <h3>💰 Revenue</h3>
          <p>Monthly: <strong>$2.85M</strong></p>
          <p>Conversion: <strong>94.2%</strong></p>
          <p>Growth: <strong>+12.5%</strong></p>
          <p>Top Region: <strong>Europe</strong></p>
        </div>
      </div>
      
      <div style={{ background: 'white', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
        <h3>🗺️ Marketplace Overview</h3>
        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
          <thead>
            <tr style={{ borderBottom: '2px solid #ddd' }}>
              <th style={{ padding: '10px', textAlign: 'left' }}>Region</th>
              <th style={{ padding: '10px', textAlign: 'left' }}>Marketplace</th>
              <th style={{ padding: '10px', textAlign: 'left' }}>Status</th>
              <th style={{ padding: '10px', textAlign: 'left' }}>Revenue</th>
              <th style={{ padding: '10px', textAlign: 'left' }}>Languages</th>
            </tr>
          </thead>
          <tbody>
            <tr style={{ borderBottom: '1px solid #eee' }}>
              <td style={{ padding: '10px' }}>🇩🇪 Germany</td>
              <td style={{ padding: '10px' }}>Amazon.de</td>
              <td style={{ padding: '10px', color: 'green' }}>✅ Active</td>
              <td style={{ padding: '10px' }}>$485K</td>
              <td style={{ padding: '10px' }}>DE, EN</td>
            </tr>
            <tr style={{ borderBottom: '1px solid #eee' }}>
              <td style={{ padding: '10px' }}>🇫🇷 France</td>
              <td style={{ padding: '10px' }}>Amazon.fr</td>
              <td style={{ padding: '10px', color: 'green' }}>✅ Active</td>
              <td style={{ padding: '10px' }}>$420K</td>
              <td style={{ padding: '10px' }}>FR, EN</td>
            </tr>
            <tr style={{ borderBottom: '1px solid #eee' }}>
              <td style={{ padding: '10px' }}>🇸🇬 Singapore</td>
              <td style={{ padding: '10px' }}>Shopee</td>
              <td style={{ padding: '10px', color: 'green' }}>✅ Active</td>
              <td style={{ padding: '10px' }}>$340K</td>
              <td style={{ padding: '10px' }}>EN, ZH, MS</td>
            </tr>
            <tr style={{ borderBottom: '1px solid #eee' }}>
              <td style={{ padding: '10px' }}>🇧🇷 Brazil</td>
              <td style={{ padding: '10px' }}>MercadoLibre</td>
              <td style={{ padding: '10px', color: 'orange' }}>🔄 Deploying</td>
              <td style={{ padding: '10px' }}>$280K</td>
              <td style={{ padding: '10px' }}>PT, ES</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div style={{ marginTop: '20px', display: 'flex', gap: '10px' }}>
        <button style={{ padding: '10px 20px', background: '#2196f3', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}>
          🔄 Refresh Data
        </button>
        <button style={{ padding: '10px 20px', background: '#4caf50', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}>
          📊 Export Report
        </button>
        <button style={{ padding: '10px 20px', background: '#ff9800', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}>
          🚀 New Deployment
        </button>
      </div>
    </div>
  );
};

export default GlobalDeploymentDashboard;