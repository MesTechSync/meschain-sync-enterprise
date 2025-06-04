# 📁 MesChain-Sync File Structure

This document provides a detailed overview of the MesChain-Sync extension's file and directory structure, explaining the purpose of each component.

## 📂 Root Directory Structure

```
meschain-sync/                       # Project root directory
├── docs/                           # Documentation files
│   ├── CHANGELOG.md                # Version history and changes
│   ├── MODULE_GUIDE.md             # Guide for creating new marketplace modules
│   ├── TECH_STACK.md               # Technologies used in the project
│   └── INSTALL_GUIDE.md            # Installation instructions
├── logs/                           # Log files directory
├── tests/                          # Test files and cases
├── upload/                         # Files to be uploaded to OpenCart
│   ├── admin/                      # Admin panel files
│   ├── catalog/                    # Frontend files
│   └── system/                     # System libraries and config
├── AI_Kod_Analiz_Talimatı.md       # AI code analysis instructions
├── meschain_sync_todo_plan.md      # Todo list and development plan
├── PROJECT_OVERVIEW.md             # Project overview and purpose
├── README.md                       # General readme file
├── STRUCTURE.md                    # This file - structure documentation
└── YENI_YAZILIM_HARITASI.md        # New software map/roadmap
```

## 📂 Upload Directory (OpenCart Integration Files)

The `upload` directory contains all files that need to be uploaded to an OpenCart installation:

```
upload/
├── admin/                          # Admin panel components
│   ├── controller/extension/module/
│   │   ├── trendyol.php            # Trendyol marketplace controller
│   │   ├── trendyol_helper.php     # Trendyol API helper functions
│   │   ├── amazon.php              # Amazon marketplace controller
│   │   ├── amazon_helper.php       # Amazon API helper functions
│   │   ├── announcement.php        # Announcement system controller
│   │   ├── help.php                # Help system controller
│   │   ├── n11.php                 # N11 marketplace controller
│   │   ├── n11_helper.php          # N11 API helper functions
│   │   ├── hepsiburada.php         # Hepsiburada marketplace controller
│   │   ├── hepsiburada_helper.php  # Hepsiburada API helper functions
│   │   ├── ebay.php                # eBay marketplace controller
│   │   ├── ebay_helper.php         # eBay API helper functions
│   │   ├── ozon.php                # Ozon marketplace controller
│   │   ├── ozon_helper.php         # Ozon API helper functions
│   │   └── user_settings.php       # User preferences controller
│   ├── language/en-gb/extension/module/
│   │   ├── trendyol.php            # Trendyol language strings
│   │   ├── amazon.php              # Amazon language strings
│   │   ├── announcement.php        # Announcement language strings
│   │   ├── help.php                # Help language strings
│   │   └── ...                     # Other language files
│   ├── model/extension/module/
│   │   ├── trendyol.php            # Trendyol database operations
│   │   ├── amazon.php              # Amazon database operations
│   │   └── ...                     # Other model files
│   └── view/template/extension/module/
│       ├── trendyol.twig           # Trendyol settings template
│       ├── trendyol_dashboard.twig # Trendyol dashboard template
│       ├── amazon.twig             # Amazon settings template
│       ├── amazon_dashboard.twig   # Amazon dashboard template
│       ├── announcement.twig       # Announcement system template
│       ├── help.twig               # Help system template
│       └── ...                     # Other template files
├── catalog/                        # Frontend components (if any)
│   ├── controller/extension/module/
│   ├── model/extension/module/
│   └── view/theme/default/template/extension/module/
└── system/                         # System components
    └── library/entegrator/
        ├── config_trendyol.php     # Trendyol configuration
        ├── config_amazon.php       # Amazon configuration
        └── ...                     # Other configurations
```

## 📄 Controller Files

Controllers handle the business logic and user requests:

### Core Controllers
- `trendyol.php` - Main controller for Trendyol marketplace integration
- `amazon.php` - Main controller for Amazon marketplace integration
- `n11.php` - Main controller for N11 marketplace integration
- `hepsiburada.php` - Main controller for Hepsiburada marketplace integration
- `ebay.php` - Main controller for eBay marketplace integration
- `ozon.php` - Main controller for Ozon marketplace integration

### Support Controllers
- `announcement.php` - System announcement management
- `help.php` - Help documentation system
- `user_settings.php` - User preferences and settings

## 📄 Helper Files

Helper files contain API functions and utility methods:

- `trendyol_helper.php` - API functions for Trendyol
- `amazon_helper.php` - API functions for Amazon
- `n11_helper.php` - API functions for N11
- `hepsiburada_helper.php` - API functions for Hepsiburada
- `ebay_helper.php` - API functions for eBay
- `ozon_helper.php` - API functions for Ozon

## 📄 View Templates

Templates define the user interface for the admin panel:

- `trendyol.twig` - Trendyol settings page
- `trendyol_dashboard.twig` - Trendyol dashboard with statistics
- `amazon.twig` - Amazon settings page
- `amazon_dashboard.twig` - Amazon dashboard with statistics
- `help.twig` - Help documentation display
- `announcement.twig` - System announcements display
- `user_settings.twig` - User preferences settings

## 📄 Configuration Files

System library files and configurations:

- `config_trendyol.php` - Trendyol API endpoints and settings
- `config_amazon.php` - Amazon API endpoints and settings
- Other marketplace configuration files

## 🔍 Module Organization

Each marketplace integration includes:

1. **Controller** - Manages admin interface and operations
2. **Helper** - Contains API functions and utilities
3. **Template** - User interface templates
4. **Configuration** - API endpoints and settings
5. **Language** - Text strings and translations

## 📜 Documentation Files

- `README.md` - General project information
- `PROJECT_OVERVIEW.md` - Project purpose and components
- `STRUCTURE.md` - This file, explaining directory structure
- `CHANGELOG.md` - Version history and updates
- `meschain_sync_todo_plan.md` - Development tasks and plan
- `YENI_YAZILIM_HARITASI.md` - New software roadmap

## 📝 Module-Specific Documentation

Each marketplace module should contain its own documentation:

- `README.md` - Module purpose and usage
- `TODO.md` - Module-specific tasks
- `test_cases.md` - Manual test procedures

---

📅 Last Updated: 2023-11-19 