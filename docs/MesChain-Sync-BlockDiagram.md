# MesChain-Sync Block Diagram and System Architecture

## 🏗️ System Architecture Overview

```
┌───────────────────────────────────────────────────────────────────┐
│                     OpenCart Admin Interface                       │
├─────────────┬─────────────┬─────────────┬─────────────┬───────────┤
│  MesChain   │  Marketplace│   System    │  Settings   │   Logs    │
│  Dashboard  │  Selection  │  Utilities  │  & Config   │  & Debug  │
└──────┬──────┴──────┬──────┴──────┬──────┴──────┬──────┴─────┬─────┘
       │             │             │             │            │
       ▼             ▼             ▼             ▼            ▼
┌──────────────┐ ┌───────────┐ ┌───────────┐ ┌─────────┐ ┌─────────┐
│ Core System  │ │Marketplace│ │   Sync    │ │   API   │ │  Logger │
│ Controllers  │ │ Modules   │ │  Engine   │ │Connector│ │ System  │
└──────────────┘ └───────────┘ └───────────┘ └─────────┘ └─────────┘
       │             │             │             │            │
       └─────────────┼─────────────┼─────────────┼────────────┘
                     │             │             │
                     ▼             ▼             ▼
              ┌─────────────────────────────────────────┐
              │            Marketplace APIs             │
              ├──────────┬──────────┬──────────┬────────┤
              │ Trendyol │   N11    │  Amazon  │  More  │
              └──────────┴──────────┴──────────┴────────┘
```

## 📦 Currently Active Modules

| Module Name            | Status     | Controller Path                                | Features                         |
|------------------------|------------|-----------------------------------------------|---------------------------------|
| MesChain Core          | ✅ Active  | extension/mestech/mestech_sync                | Main dashboard, navigation       |
| Trendyol Integration   | ✅ Active  | extension/mestech/trendyol                    | API connection, product sync     |
| N11 Integration        | ✅ Active  | extension/module/n11                          | API integration, dashboard       |
| Amazon Integration     | ✅ Active  | extension/mestech/amazon                      | Product/order management         |
| User Settings          | ✅ Active  | extension/module/user_settings                | User preferences, API keys       |
| Help System            | ✅ Active  | extension/module/help                         | Documentation, guides            |

## 🧩 Component Details

### 1. Core Components

- **MesChain Dashboard**: Central hub showing all marketplace statistics
- **Left Menu Integration**: Custom menu section in OpenCart admin panel
- **Event System**: Hooks into OpenCart events for automatic updates
- **Cron System**: Background processes for syncing data with marketplaces

### 2. N11 Module Components

- **Controller**: `upload/admin/controller/extension/module/n11.php`
- **View Templates**: 
  - Main settings: `upload/admin/view/template/extension/module/n11.twig`
  - Dashboard: `upload/admin/view/template/extension/module/n11_dashboard.twig`
- **Language Files**: English and Turkish translations
- **Helper Class**: `upload/system/helper/n11_helper.php`

### 3. API Flow Architecture

```
┌────────────────┐         ┌───────────────┐         ┌───────────────┐
│  OpenCart      │         │  MesChain     │         │  Marketplace  │
│  Admin Panel   │◄───────►│  Integration  │◄───────►│  API (N11,    │
│  (UI Layer)    │         │  (Business)   │         │  Trendyol...)  │
└────────────────┘         └───────────────┘         └───────────────┘
       ▲                          ▲                          ▲
       │                          │                          │
       ▼                          ▼                          ▼
┌────────────────┐         ┌───────────────┐         ┌───────────────┐
│  OpenCart      │         │  MesChain     │         │  External     │
│  Database      │◄───────►│  Data Models  │◄───────►│  Data Cache   │
│                │         │               │         │               │
└────────────────┘         └───────────────┘         └───────────────┘
```

## 🔄 Synchronization Process

1. **Authentication**: API credentials stored securely in the database
2. **Data Mapping**: Translates between OpenCart and marketplace data formats
3. **Scheduled Jobs**: Cron jobs handle regular synchronization tasks
4. **Manual Controls**: Admin UI provides buttons for immediate sync operations
5. **Logging**: All operations recorded for debugging and auditing

## 📊 Dashboard Integration

The main MesChain dashboard displays:

- Total products synced across all marketplaces
- Recent orders by marketplace
- Status indicators for API connections
- Error warnings and notifications
- Quick action buttons for common tasks

## 📂 File Structure Overview

The extension follows OpenCart's MVC pattern:

- **Controllers**: Business logic and API connections
- **Models**: Database interactions and data structure
- **Views**: User interface templates (TWIG)
- **Language**: Localization files
- **Helper Classes**: Reusable API communication methods

## 🔒 Security Considerations

- API credentials encrypted in database
- Permission-based access control
- Sanitized inputs for API calls
- Rate limiting for external API requests
- Secure token management

## 🔄 Installation Process

1. Upload files to OpenCart directory
2. Install through OpenCart Extension Installer
3. Configure API credentials for each marketplace
4. Set up cron jobs for background sync
5. Test connections and initial synchronization 