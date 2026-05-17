# AreaManager Package 🌍

A comprehensive regional management package for Laravel applications with support for geographical areas, regions, country mapping, and hierarchical area manager structure.

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/yourusername/area-manager)
[![Laravel](https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-Proprietary-green.svg)](LICENSE)

---

## 📖 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Structure](#database-structure)
- [API Endpoints](#api-endpoints)
- [PackageHelper Integration](#packagehelper-integration)
- [Uninstallation](#uninstallation)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## 🌟 Overview

AreaManager is a powerful Laravel package designed to manage geographical regions, assign area managers, and organize hierarchical management structures. Perfect for applications that need regional administrative control with multi-level management.

### What's Inside?

- **Regional Management** - Create and manage geographical areas
- **Country Mapping** - Visual country assignment to regions
- **Hierarchical Structure** - Area managers and sub-area managers
- **Charge System** - Built-in wallet and financial operations
- **Web Dashboard** - Complete admin panel with map integration
- **Mobile API** - RESTful API for mobile applications
- **Multi-Language** - Arabic and English support
- **PackageHelper** - Safe cross-package model access

---

## ✨ Features

### 🗺️ Regional Management
- Create custom geographical regions
- Assign countries to regions using interactive maps
- Visual representation of area coverage
- Regional hierarchy and organization

### 👥 User Management
- Area Manager accounts with full dashboard access
- Sub-Area Manager hierarchical structure
- Role-based permissions and access control
- Integration with SuperAdmin module

### 💰 Financial System
- Built-in wallet (diamonds) for area managers
- Charge system for financial operations
- Charge history and reporting
- Multi-currency support (USD/Coins)

### 📱 Multi-Platform Support
- **Web Admin Panel** - Full-featured dashboard
- **AreaManager Dashboard** - Dedicated manager interface
- **Mobile API** - RESTful endpoints for apps
- **Real-time Notifications** - Event broadcasting

### 🔗 Integration
- **PackageHelper** - Safe cross-package model references
- **Agency Package** - Seamless agency management integration
- **SuperAdmin Module** - Hierarchical admin structure
- **Encore Admin** - Built on Laravel Admin framework

---

## 📋 Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.2 or higher |
| Laravel | 10.x or 11.x |
| MySQL | 5.7 or higher |
| Encore Laravel Admin | 1.8+ |
| Laravel Sanctum | Latest |

### Required Packages
- `utd/agency` - Agency management integration
- `nwidart/laravel-modules` - Module system support

---

## 🚀 Installation

### Quick Install (5 minutes)

1. **Copy Package Files**
   ```bash
   cp -r AreaManager packages/Utd/
   ```

2. **Update composer.json**
   ```json
   {
       "repositories": [
           {
               "type": "path",
               "url": "packages/Utd/AreaManager",
               "options": {
                   "symlink": true
               }
           }
       ],
       "require": {
           "utd/area-manager": "*"
       }
   }
   ```

3. **Install the Package**
   ```bash
   composer install --no-interaction
   ```

4. **Register in PackageHelper**
   
   Add to `app/Support/PackageHelper.php`:
   ```php
   use Utd\AreaManager\Entities\AreaManager;

   private static array $packages = [
       // ... other packages
       'areaManager' => AreaManager::class,
   ];
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Clear Cache**
   ```bash
   php artisan optimize:clear
   ```

### Verify Installation

```bash
# Check package installed
composer show utd/area-manager

# Check routes
php artisan route:list | grep -i "area"

# Check PackageHelper
php artisan tinker --execute="echo \App\Support\PackageHelper::isInstalled('areaManager') ? 'INSTALLED' : 'NOT_INSTALLED';"
```

**📄 For detailed installation instructions, see [INSTALLATION.html](INSTALLATION.html)**

---

## ⚙️ Configuration

### Publish Configuration

```bash
php artisan vendor:publish --tag=area-manager-config
```

### Configuration File

Edit `config/area_manager.php`:

```php
return [
    // Enable/disable package
    'enabled' => env('AREA_MANAGER_ENABLED', true),

    // Default region name
    'default_region' => 'Default Region for Default Manager',

    // Model mappings
    'models' => [
        'area_manager' => \Utd\AreaManager\Entities\AreaManager::class,
        'sub_area_manager' => \Utd\AreaManager\Entities\SubAreaManager::class,
        'region' => \Utd\AreaManager\Entities\Region::class,
    ],
];
```

---

## 💡 Usage

### Creating an Area Manager

```php
use Utd\AreaManager\Entities\AreaManager;
use Utd\AreaManager\Entities\Region;

// Create area manager
$areaManager = AreaManager::create([
    'name' => 'John Doe',
    'username' => 'john_area',
    'password' => Hash::make('password'),
    'app_id' => $user->id,
    'phone' => '1234567890',
    'phone_code' => '+1',
]);

// Create region
$region = Region::create([
    'name' => 'North America',
    'manager_id' => $areaManager->id,
]);

// Assign countries
$region->countries()->attach([1, 2, 3]); // Country IDs
```

### Accessing Area Manager Dashboard

```
Web: https://your-domain.com/areaManager/login
Username: john_area
Password: password
```

### Using PackageHelper

```php
use App\Support\PackageHelper;

// Check if package is installed
if (PackageHelper::isInstalled('areaManager')) {
    $areaManager = \Utd\AreaManager\Entities\AreaManager::find(1);
}

// Safe relation (in other models)
public function areaManager()
{
    return PackageHelper::checkRelation($this, 'areaManager', 'belongsTo')
        ?? $this->belongsTo(AreaManager::class);
}
```

### Mobile API Example

```php
// Login
POST /api/areaManager/login
{
    "username": "john_area",
    "password": "password"
}

// Get dashboard data
GET /api/areaManager/home
Headers: Authorization: Bearer {token}

// Get agencies
GET /api/areaManager/agencies
Headers: Authorization: Bearer {token}
```

---

## 🗄️ Database Structure

### Tables Created

| Table | Description |
|-------|-------------|
| `admin_users` | Extended with area manager fields |
| `regions` | Regional areas |
| `region_countries` | Country-region mapping (pivot) |
| `sub_area_managers` | Sub-area manager hierarchy |

### Key Columns

**admin_users (extended)**
- `type` - User type ('area-manager', 'sub-area-manager')
- `app_id` - Reference to users table
- `parent_id` - Parent area manager
- `di` - Wallet balance (diamonds)
- `default` - Is default manager

**regions**
- `id` - Primary key
- `name` - Region name
- `manager_id` - Area manager reference

**region_countries**
- `region_id` - Region reference
- `country_id` - Country reference

---

## 🔌 API Endpoints

### Web Admin Routes

| Route | Description |
|-------|-------------|
| `/admin/area-manager-users` | Area managers list |
| `/admin/area-manager-users/create` | Create new area manager |
| `/admin/area-manager-users/{id}/edit` | Edit area manager |
| `/admin/area-manager-charges-reports` | Charge reports |

### AreaManager Dashboard

| Route | Description |
|-------|-------------|
| `/areaManager/login` | Manager login |
| `/areaManager/home` | Dashboard home |
| `/areaManager/agencies` | Manage agencies |
| `/areaManager/users` | Manage users |
| `/areaManager/charges` | Charge management |

### Mobile API

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/api/areaManager/login` | Login |
| GET | `/api/areaManager/home` | Dashboard data |
| GET | `/api/areaManager/agencies` | List agencies |
| POST | `/api/areaManager/charge-agency` | Charge agency |

---

## 🔗 PackageHelper Integration

AreaManager uses PackageHelper for safe cross-package model access.

### Why PackageHelper?

- ✅ Prevents errors when package is not installed
- ✅ Returns empty relations instead of exceptions
- ✅ Allows package to be optional dependency
- ✅ Enables clean package uninstallation

### Implementation

```php
// In app/Support/PackageHelper.php
use Utd\AreaManager\Entities\AreaManager;

private static array $packages = [
    'areaManager' => AreaManager::class,
];

// Usage in other models
use App\Support\PackageHelper;

public function areaManager()
{
    return PackageHelper::checkRelation($this, 'areaManager', 'belongsTo')
        ?? $this->belongsTo(AreaManager::class, 'charger_id');
}
```

### Protected Relations

The following models use PackageHelper guards:
- `App\Models\Charge` - areaManager(), subAreaManager()
- `App\Models\Admin` - Region relation
- `App\Models\Country` - regionCountries relation

---

## 🗑️ Uninstallation

### ⚠️ Warning
Uninstalling will remove all area manager data. **Backup your database first!**

### Steps

1. **Backup Database**
   ```bash
   mysqldump -u username -p database_name > backup.sql
   ```

2. **Rollback Migrations**
   ```bash
   php artisan migrate:rollback --path=vendor/utd/area-manager/database/migrations
   ```

3. **Remove from PackageHelper**
   
   Delete from `app/Support/PackageHelper.php`:
   ```php
   'areaManager' => AreaManager::class,
   ```

4. **Update composer.json**
   
   Remove repository and require entries

5. **Remove Files**
   ```bash
   rm -rf packages/Utd/AreaManager
   rm -rf vendor/utd/area-manager
   composer install --no-interaction
   ```

6. **Clear Cache**
   ```bash
   php artisan optimize:clear
   ```

**📄 For detailed uninstallation instructions, see [INSTALLATION.html](INSTALLATION.html#uninstallation)**

---

## 🔧 Troubleshooting

### Class AreaManagerServiceProvider not found

**Solution:**
```bash
composer install --no-interaction
ls -la vendor/utd/area-manager  # Verify symlink
```

### Routes Not Found (404)

**Solution:**
```bash
php artisan optimize:clear
php artisan route:list | grep area
```

### PackageHelper Not Recognizing Package

**Solution:**
Verify PackageHelper registration:
```php
// app/Support/PackageHelper.php
'areaManager' => \Utd\AreaManager\Entities\AreaManager::class,
```

### Database Migration Errors

**Solution:**
```bash
php artisan migrate:status
# If already migrated, migrations will be skipped
```

---

## 📚 Documentation

- **[INSTALLATION.html](INSTALLATION.html)** - Complete Arabic installation & uninstallation guide (27KB)
- **README.md** - This file

---

## 📝 License

Proprietary - All rights reserved.

---

## 📞 Support

For support or questions:
- **Documentation**: See HTML guides in package root
- **Issues**: Contact development team

---

## 📊 Package Info

- **Package Name**: `utd/area-manager`
- **Version**: 1.0.0
- **Namespace**: `Utd\AreaManager`
- **Service Provider**: `Utd\AreaManager\AreaManagerServiceProvider`
- **Config File**: `config/area_manager.php`

---

<div align="center">

**Made with ❤️ for Laravel Applications**

Version 1.0.0 | May 2026

</div>
