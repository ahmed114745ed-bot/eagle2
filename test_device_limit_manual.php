<?php
/**
 * Manual Test Script for Device Account Limit Fix
 *
 * This script simulates the scenarios to test if the fix works correctly
 * Run with: php test_device_limit_manual.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Tik\Services\AuthService;
use App\Exceptions\CValidationException;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "═══════════════════════════════════════════════════════════════════════\n";
echo "🧪 Testing Device Account Limit Fix\n";
echo "═══════════════════════════════════════════════════════════════════════\n\n";

$testDeviceToken = 'test_device_' . time();

// ═══════════════════════════════════════════════════════════════════════════
// Test 1: Check actual users count in devicesTokenHistory
// ═══════════════════════════════════════════════════════════════════════════
echo "📝 Test 1: devicesTokenHistory checks actual users count\n";
echo "─────────────────────────────────────────────────────────────────────\n";

try {
    // Simulate: 3 users already logged in (have device_token set)
    echo "Creating 3 users with device_token (simulating Login)...\n";

    $users = [];
    for ($i = 1; $i <= 3; $i++) {
        $user = new User();
        $user->name = "Test User {$i}";
        $user->google_id = "test_google_id_{$i}_" . time();
        $user->email = "test{$i}_" . time() . "@example.com";
        $user->device_token = $testDeviceToken;
        $user->save();
        $users[] = $user;
        echo "  ✓ User {$i} created (ID: {$user->id})\n";
    }

    $actualCount = User::where('device_token', $testDeviceToken)->count();
    echo "  📊 Actual users with this device_token: {$actualCount}\n";

    // Try to call devicesTokenHistory (simulating a 4th registration)
    echo "\n  Attempting to register 4th account...\n";

    $authService = app(AuthService::class);
    $reflection = new ReflectionClass($authService);
    $method = $reflection->getMethod('devicesTokenHistory');
    $method->setAccessible(true);

    try {
        $method->invoke($authService, $testDeviceToken);
        echo "  ❌ FAIL: Registration was allowed (should have been blocked)\n";
    } catch (CValidationException $e) {
        echo "  ✅ PASS: Registration blocked with message: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
} finally {
    // Cleanup
    if (isset($users)) {
        foreach ($users as $user) {
            $user->forceDelete();
        }
    }
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// Test 2: Check Login limit enforcement
// ═══════════════════════════════════════════════════════════════════════════
echo "📝 Test 2: Login is blocked when device has 3 accounts\n";
echo "─────────────────────────────────────────────────────────────────────\n";

$testDeviceToken2 = 'test_device_2_' . time();

try {
    // Create 3 users on same device
    echo "Creating 3 users on device...\n";
    $users = [];
    for ($i = 1; $i <= 3; $i++) {
        $user = new User();
        $user->name = "Test Login User {$i}";
        $user->google_id = "test_login_google_{$i}_" . time();
        $user->email = "testlogin{$i}_" . time() . "@example.com";
        $user->device_token = $testDeviceToken2;
        $user->save();
        $users[] = $user;
        echo "  ✓ User {$i} created (ID: {$user->id})\n";
    }

    // Create a 4th user on different device
    echo "\nCreating 4th user on different device...\n";
    $fourthUser = new User();
    $fourthUser->name = "Fourth User";
    $fourthUser->google_id = "test_fourth_google_" . time();
    $fourthUser->email = "testfourth_" . time() . "@example.com";
    $fourthUser->device_token = 'different_device_' . time();
    $fourthUser->save();
    echo "  ✓ Fourth user created (ID: {$fourthUser->id})\n";

    // Try to login with 4th user from the device that has 3 accounts
    echo "\n  Attempting to login 4th user from device with 3 accounts...\n";

    $authService = app(AuthService::class);
    $reflection = new ReflectionClass($authService);
    $method = $reflection->getMethod('checkDeviceAccountLimit');
    $method->setAccessible(true);

    try {
        $method->invoke($authService, $fourthUser->id, $testDeviceToken2);
        echo "  ❌ FAIL: Login was allowed (should have been blocked)\n";
    } catch (CValidationException $e) {
        echo "  ✅ PASS: Login blocked with message: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
} finally {
    // Cleanup
    if (isset($users)) {
        foreach ($users as $user) {
            $user->forceDelete();
        }
    }
    if (isset($fourthUser)) {
        $fourthUser->forceDelete();
    }
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// Test 3: Same user can login multiple times
// ═══════════════════════════════════════════════════════════════════════════
echo "📝 Test 3: Same user can login multiple times\n";
echo "─────────────────────────────────────────────────────────────────────\n";

$testDeviceToken3 = 'test_device_3_' . time();

try {
    // Create 1 user
    $user = new User();
    $user->name = "Repeat Login User";
    $user->google_id = "test_repeat_google_" . time();
    $user->email = "testrepeat_" . time() . "@example.com";
    $user->device_token = $testDeviceToken3;
    $user->save();
    echo "Created user (ID: {$user->id})\n";

    // Try to "login" same user multiple times
    echo "\n  Attempting to login same user 5 times...\n";

    $authService = app(AuthService::class);
    $reflection = new ReflectionClass($authService);
    $method = $reflection->getMethod('checkDeviceAccountLimit');
    $method->setAccessible(true);

    $allPassed = true;
    for ($i = 1; $i <= 5; $i++) {
        try {
            $method->invoke($authService, $user->id, $testDeviceToken3);
            echo "  ✓ Login attempt {$i}: Allowed\n";
        } catch (CValidationException $e) {
            echo "  ❌ Login attempt {$i}: Blocked (should be allowed)\n";
            $allPassed = false;
        }
    }

    if ($allPassed) {
        echo "\n  ✅ PASS: Same user can login multiple times\n";
    } else {
        echo "\n  ❌ FAIL: Same user was blocked\n";
    }

} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
} finally {
    // Cleanup
    if (isset($user)) {
        $user->forceDelete();
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════════════\n";
echo "✅ All tests completed!\n";
echo "═══════════════════════════════════════════════════════════════════════\n";
