#!/bin/bash

# Quick Test Script for Child Customizer System

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  🎨 Child Customizer - Quick Verification Test            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

cd /media/leader/par1/Doc/GitHub/Eagle

# Check 1: Verify Laravel Installation
echo "✓ Checking Laravel Installation..."
if php artisan --version > /dev/null 2>&1; then
    echo "   ✅ Laravel $(php artisan --version)"
else
    echo "   ❌ Laravel not found"
    exit 1
fi
echo ""

# Check 2: Verify Database Connection
echo "✓ Checking Database Connection..."
if php artisan tinker --execute="DB::connection()->getPdo()" > /dev/null 2>&1; then
    echo "   ✅ Database connected"
else
    echo "   ⚠️  Database might not be connected"
fi
echo ""

# Check 3: Check Route Files
echo "✓ Checking Route Files..."
if [ -f "Modules/DynamicTheme/Routes/web.php" ]; then
    echo "   ✅ web.php exists"
    if grep -q "child-customizers" Modules/DynamicTheme/Routes/web.php; then
        echo "   ✅ Child Customizer route registered"
    else
        echo "   ❌ Child Customizer route NOT found"
    fi
else
    echo "   ❌ web.php not found"
fi
echo ""

# Check 4: Check View File
echo "✓ Checking View Files..."
if [ -f "Modules/DynamicTheme/Resources/views/child-customizer.blade.php" ]; then
    echo "   ✅ child-customizer.blade.php exists"
else
    echo "   ❌ child-customizer.blade.php NOT found"
fi
echo ""

# Check 5: Check Vue Component
echo "✓ Checking Vue Component..."
if [ -f "Modules/DynamicTheme/Resources/js/components/ChildCustomizerComponent.vue" ]; then
    lines=$(wc -l < Modules/DynamicTheme/Resources/js/components/ChildCustomizerComponent.vue)
    echo "   ✅ ChildCustomizerComponent.vue exists ($lines lines)"
else
    echo "   ❌ ChildCustomizerComponent.vue NOT found"
fi
echo ""

# Check 6: Check Model File
echo "✓ Checking Model File..."
if [ -f "Modules/DynamicTheme/Entities/ChildCustomizer.php" ]; then
    echo "   ✅ ChildCustomizer.php exists"
else
    echo "   ❌ ChildCustomizer.php NOT found"
fi
echo ""

# Check 7: Check Controller File
echo "✓ Checking Controller File..."
if [ -f "Modules/DynamicTheme/Http/Controllers/ChildCustomizerController.php" ]; then
    echo "   ✅ ChildCustomizerController.php exists"
else
    echo "   ❌ ChildCustomizerController.php NOT found"
fi
echo ""

# Check 8: Check Service File
echo "✓ Checking Service File..."
if [ -f "Modules/DynamicTheme/Services/CustomizerService.php" ]; then
    echo "   ✅ CustomizerService.php exists"
else
    echo "   ❌ CustomizerService.php NOT found"
fi
echo ""

# Check 9: Check Migration File
echo "✓ Checking Migration File..."
migration_file=$(find Modules/DynamicTheme/Database/Migrations -name "*child_customizers*" 2>/dev/null | head -1)
if [ -n "$migration_file" ]; then
    echo "   ✅ Migration file found: $(basename "$migration_file")"
else
    echo "   ⚠️  Migration file not found (may need to run: php artisan migrate)"
fi
echo ""

# Check 10: Check Navigation Link
echo "✓ Checking Dashboard Navigation..."
if grep -q "Child Customizer" Modules/DynamicTheme/Resources/js/components/DashboardApp.vue; then
    echo "   ✅ Navigation link found in DashboardApp.vue"
else
    echo "   ❌ Navigation link NOT found"
fi
echo ""

# Check 11: Check Node Modules
echo "✓ Checking Node Modules..."
if [ -d "node_modules" ]; then
    echo "   ✅ node_modules directory exists"
else
    echo "   ⚠️  node_modules directory not found (run: npm install)"
fi
echo ""

# Check 12: Check Vite Config
echo "✓ Checking Vite Configuration..."
if [ -f "vite.config.mjs" ]; then
    echo "   ✅ vite.config.mjs exists"
else
    echo "   ⚠️  vite.config.mjs not found"
fi
echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  ✅ Verification Complete                                 ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "🚀 Next Steps:"
echo "  1. Run: npx vite build"
echo "  2. Run: php artisan migrate (if first time)"
echo "  3. Run: php artisan serve"
echo "  4. Visit: http://localhost:8000/admin/child-customizers"
echo ""
