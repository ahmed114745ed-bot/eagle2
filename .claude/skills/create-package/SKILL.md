---
description: Create a new Laravel package under packages/Utd/ following UTD project conventions
---

# Create UTD Package

Guide for creating a new Laravel package in this project under `packages/Utd/`.

## Arguments
- $ARGUMENTS: Package name in PascalCase (e.g., `Bd`, `Gifts`, `Moments`)

## Instructions

You are creating a new UTD package. Follow the patterns established by existing packages (Reals, Moments, Room, Gifts).

## Common Issues & Prevention

### Issue: "Class ServiceProvider not found" when running php artisan

**Cause:** Package is registered in `composer.json` but not installed in `vendor/` or not in autoload files.

**Prevention:**
1. Always run `composer install` (not `composer update`) after adding a new local package
2. Ensure repository is added with `"symlink": true` option
3. Verify `vendor/utd/{package-name}` symlink exists
4. Check `vendor/composer/installed.json` contains your package

**Fix:**
```bash
# Quick fix - reinstall all packages
composer install --no-interaction

# Manual fix - create symlink then install
ln -sf ../../packages/Utd/{PackageName} vendor/utd/{package-name}
composer install --no-interaction
```

### Issue: Package migrations not running

**Cause:** Missing `loadMigrationsFrom()` in ServiceProvider or migrations path is wrong.

**Prevention:**
- Always add `$this->loadMigrationsFrom(__DIR__.'/../database/migrations');` in ServiceProvider `boot()` method
- Use `Schema::hasTable()` guards in migrations to prevent duplicate table errors

### Issue: Routes not loading

**Cause:** Missing route registration in ServiceProvider or middleware not applied.

**Prevention:**
- Register routes in `registerRoutes()` method with correct middleware
- Check route file exists in `routes/api.php` or `routes/web.php`

### Issue: PackageHelper not recognizing the package

**Cause:** Package not added to `app/Support/PackageHelper.php` registry.

**Prevention:**
- Always add package to `PackageHelper::$packages` array after creation
- Use the primary entity class as the package identifier

### Step 1: Create Directory Structure

```
packages/Utd/{PackageName}/
├── composer.json
├── config/{package_name}.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── lang/
│   └── views/
├── routes/
│   ├── api.php      (API routes — if needed)
│   └── web.php      (Web/admin routes — if needed)
├── src/
│   ├── {PackageName}ServiceProvider.php
│   ├── Entities/     (Eloquent Models)
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Requests/  (Form Requests)
│   ├── Services/
│   ├── Transformers/  (JSON Resources)
│   ├── Repositories/  (if needed)
│   ├── Scopes/        (if needed)
│   ├── Actions/       (if needed)
│   ├── Jobs/          (if needed)
│   └── Events/        (if needed)
└── tests/
```

### Step 2: Create `composer.json`

```json
{
    "name": "utd/{package-name}",
    "description": "{Package description}",
    "type": "library",
    "license": "proprietary",
    "authors": [
        {
            "name": "UTD Team",
            "email": "dev@utd.com"
        }
    ],
    "require": {
        "php": "^8.1",
        "illuminate/support": "^10.0|^11.0",
        "illuminate/database": "^10.0|^11.0",
        "illuminate/http": "^10.0|^11.0"
    },
    "autoload": {
        "psr-4": {
            "Utd\\{PackageName}\\": "src/",
            "Utd\\{PackageName}\\Database\\Seeders\\": "database/seeders/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Utd\\{PackageName}\\{PackageName}ServiceProvider"
            ]
        }
    },
    "config": {
        "sort-packages": true
    },
    "minimum-stability": "stable"
}
```

### Step 3: Create ServiceProvider

File: `src/{PackageName}ServiceProvider.php`

```php
<?php

namespace Utd\{PackageName};

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class {PackageName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/{package_name}.php', '{package_name}');

        // Bind contracts to implementations
        // $this->app->singleton(SomeContract::class, SomeService::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerPublishing();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    protected function registerRoutes(): void
    {
        // API routes
        Route::prefix('api')
            ->middleware(['api', 'auth:sanctum'])
            ->group(__DIR__.'/../routes/api.php');

        // Web routes (if needed)
        Route::middleware('web')
            ->group(__DIR__.'/../routes/web.php');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', '{package_name}');
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/{package_name}.php' => config_path('{package_name}.php'),
            ], '{package_name}-config');
        }
    }

    protected function registerCommands(): void
    {
        // $this->commands([...]);
    }
}
```

### Step 4: Create Config File

File: `config/{package_name}.php`

```php
<?php

return [
    'enabled' => env('{PACKAGE_NAME}_ENABLED', true),
];
```

### Step 5: Create Entities (Models)

File: `src/Entities/{EntityName}.php`

```php
<?php

namespace Utd\{PackageName}\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class {EntityName} extends Model
{
    protected $table = '{table_name}';

    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Step 6: Create Migrations

File: `database/migrations/{date}_create_{table}_table.php`

IMPORTANT: Always guard with `Schema::hasTable()` check:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('{table_name}')) {
            Schema::create('{table_name}', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('{table_name}');
    }
};
```

#### Moving existing migrations from other packages

When extracting a package, move all migrations for tables owned by the package:

1. **Merge migrations per table** — consolidate all create/alter/add-column migrations for the same table into a single create migration with the final schema. Use `Schema::hasTable()` guard. This keeps one file per table instead of many incremental ones:

```php
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('{table_name}')) {
            return;
        }

        Schema::create('{table_name}', function (Blueprint $table) {
            // Final schema with all columns at their final types/sizes
            $table->id();
            $table->decimal('amount', 20, 4)->default(0);
            // ...
            $table->timestamps();
        });
    }
};
```

2. **Migrations that modify external tables** (e.g., adding `bd_id` to `agencies`): merge into a single file per external table. Ensure `Schema::hasTable()` / `Schema::hasColumn()` guards so they skip gracefully if the external table doesn't exist:

```php
public function up(): void
{
    if (! Schema::hasTable('agencies')) {
        return;
    }
    if (! Schema::hasColumn('agencies', 'bd_id')) {
        Schema::table('agencies', function (Blueprint $table) {
            $table->bigInteger('bd_id')->nullable();
        });
    }
}
```

3. **Shared "ensure" migrations** (e.g., `ensure_all_agency_tables_exist.php`): remove the package-specific table creation blocks from the shared migration — the dedicated migrations in the package will handle them
4. **Delete all original migration files** from the source package after merging

### Step 7: Create Controllers

File: `src/Http/Controllers/{Name}Controller.php`

```php
<?php

namespace Utd\{PackageName}\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class {Name}Controller extends Controller
{
    public function __construct(private readonly {Service} $service)
    {
    }

    public function index()
    {
        $data = $this->service->all();
        return Common::apiResponse(1, 'success', $data);
    }
}
```

### Step 8: Create Services

File: `src/Services/{Name}Service.php`

```php
<?php

namespace Utd\{PackageName}\Services;

class {Name}Service
{
    // Business logic here
}
```

### Step 9: Create Transformers (JSON Resources)

File: `src/Transformers/{Name}Resource.php`

```php
<?php

namespace Utd\{PackageName}\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class {Name}Resource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // ...
        ];
    }
}
```

### Step 10: Create Routes

File: `routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use Utd\{PackageName}\Http\Controllers\{Name}Controller;

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'update.last.seen'])
    ->group(function () {
        Route::prefix('{route_prefix}')->group(function () {
            Route::apiResource('', {Name}Controller::class)
                ->parameters(['' => '{param_name}']);
        });
    });
```

### Step 11: Register in PackageHelper

Add the package to `app/Support/PackageHelper.php`:

1. Add use statement: `use Utd\{PackageName}\Entities\{EntityName};`
2. Add entry to `$packages` array: `'{package_key}' => {EntityName}::class,`

### Step 12: Cross-Package Model References

When referencing models from **other packages** (outside your package), use `PackageHelper` guards:

#### Relations (in Model classes)

Use `PackageHelper::checkRelation()` when defining a relation to a model from another package:

```php
use App\Support\PackageHelper;
use Utd\OtherPackage\Entities\OtherModel;

public function otherModel()
{
    return PackageHelper::checkRelation($this, 'otherPackage', 'belongsTo')
        ?? $this->belongsTo(OtherModel::class, 'other_id');
}
```

- `checkRelation()` returns an empty relation if the package isn't installed, or `null` if it is (allowing the real relation via `??`)
- Supported relation types: `hasMany`, `hasOne`, `belongsTo`, `belongsToMany`, `hasOneThrough`

#### Regular Code (Controllers, Services, Jobs, etc.)

Use `PackageHelper::isInstalled()` to guard code that uses models from another package:

```php
use App\Support\PackageHelper;
use Utd\OtherPackage\Entities\OtherModel;

if (PackageHelper::isInstalled('otherPackage')) {
    $items = OtherModel::where('user_id', $userId)->get();
}
```

**IMPORTANT:** This only applies to code **outside** the package. Code within the same package does NOT need PackageHelper guards.

#### Moving Package-Specific Code Inside the Package

When extracting a package, check for classes, helpers, or functions outside the package that are **entirely package-specific logic**:

- **A class whose entire logic is about the package** (e.g., `BdSalaryMigrationController` that only dispatches BD jobs) → Move inside the package's `src/` directory
- **Helper functions specific to the package** (e.g., `bd_url()`) → Move to `src/Support/helpers.php` inside the package and register in `composer.json` autoload `files`
- **An empty/unused service class** (e.g., `BDChargeService` with no real logic) → Delete it

```json
"autoload": {
    "files": [
        "src/Support/helpers.php"
    ]
}
```

If a class is **general-purpose** but happens to use a package model in a few lines, do NOT move it — just wrap those lines with `PackageHelper::isInstalled()` instead.

### Step 13: Register in Root Project

1. Add to `composer.json` repositories:
```json
{
    "type": "path",
    "url": "packages/Utd/{PackageName}",
    "options": {
        "symlink": true
    }
}
```

2. Add to `composer.json` require:
```json
"utd/{package-name}": "*"
```

3. **Install the package** (CRITICAL - prevents "ServiceProvider not found" errors):
```bash
composer install --no-interaction
```

**IMPORTANT:** Always use `composer install` (not `composer update`) when adding a new local package to ensure proper registration in `vendor/composer/installed.json` and autoload files.

4. **Verify installation** - Check that the package is properly installed:
```bash
# Verify symlink exists
ls -la vendor/utd/{package-name}

# Verify autoload is registered
php artisan --version

# Should output Laravel version without errors
```

5. **If you get "Class ServiceProvider not found" error:**
```bash
# Solution 1: Run full composer install
composer install --no-interaction

# Solution 2: If still failing, manually create symlink then install
ln -sf ../../packages/Utd/{PackageName} vendor/utd/{package-name}
composer install --no-interaction
```

### Step 14: Add Test Suite

Create test directories and register in `phpunit.xml`.

#### Directory structure

```
tests/
├── Unit/{PackageName}/
│   ├── {Entity}Test.php          (model attributes, scopes, relations)
│   └── {Service}ServiceTest.php  (service layer logic)
└── Feature/{PackageName}/
    └── {Flow}FlowTest.php        (integration tests across services)
```

#### Register suite in `phpunit.xml`

```xml
<testsuite name="{PackageName}">
    <directory suffix="Test.php">./tests/Unit/{PackageName}</directory>
    <directory suffix="Test.php">./tests/Feature/{PackageName}</directory>
</testsuite>
```

#### Test base pattern

Tests extend `Tests\TestCase` and use `DatabaseTransactions`. Create required tables in `setUp()` with `Schema::hasTable` guards. The `DatabaseTransactions` trait handles transaction management automatically — do NOT call `$this->beginDatabaseTransaction()` manually:

```php
<?php

namespace Tests\Unit\{PackageName};

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class {Entity}Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('{table_name}')) {
            Schema::create('{table_name}', function (Blueprint $table) {
                $table->id();
                // ... columns matching the real migration
                $table->timestamps();
            });
        }

        $this->beginDatabaseTransaction();
    }

    public function test_example(): void
    {
        // Use DB::table() to insert raw records (bypasses model events/scopes)
        // Use model classes to test the code under test
    }
}
```

#### Tips

- Use `\DB::table()->insertGetId()` to create test records without triggering model boot events
- Use `uniqid()` in unique columns to avoid collisions across tests
- Test computed attributes (accessors), relations, and service methods
- Feature tests should verify the full flow across multiple services
- Run with `./vendor/bin/phpunit --testsuite={PackageName}`

### Naming Conventions

| Item | Convention | Example |
|------|-----------|---------|
| Package dir | PascalCase | `packages/Utd/Gifts/` |
| Composer name | lowercase kebab | `utd/gifts` |
| Namespace | PascalCase | `Utd\Gifts` |
| Config key | snake_case | `gifts` |
| Route prefix | kebab-case | `gifts` |
| Table name | snake_case plural | `gift_logs` |
| Entity class | PascalCase singular | `Gift` |
| Service class | PascalCase + Service | `GiftService` |
| Controller | PascalCase + Controller | `GiftController` |
| Resource | PascalCase + Resource | `GiftResource` |

## Post-Creation Checklist

After creating a new package, verify everything works:

- [ ] **Package installed:** `ls -la vendor/utd/{package-name}` shows symlink
- [ ] **Laravel boots:** `php artisan --version` runs without errors
- [ ] **Autoload works:** `php artisan list` shows package commands (if any)
- [ ] **Migrations load:** `php artisan migrate:status` lists package migrations
- [ ] **Routes registered:** `php artisan route:list | grep {route-prefix}` shows package routes
- [ ] **Config published:** `php artisan vendor:publish --tag={package-name}-config` works
- [ ] **PackageHelper updated:** `PackageHelper::isInstalled('{packageKey}')` returns true
- [ ] **Tests pass:** `./vendor/bin/phpunit --testsuite={PackageName}` runs successfully
- [ ] **No errors:** Check `storage/logs/laravel.log` for package-related errors

If any check fails, review the corresponding step in this guide.
