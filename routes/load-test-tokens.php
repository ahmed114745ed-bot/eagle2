<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ⚠️ TEMPORARY ROUTE - للاختبار فقط!
 *
 * Route لتوليد tokens للـ load testing
 * احذف الملف ده بعد انتهاء الاختبار!
 *
 * Usage:
 * POST https://eagle.utdsoftware.com/api/dev/generate-test-tokens
 * Body: {
 *   "user_ids": [2419, 2418, 2417, 2416, 1174],
 *   "secret": "load-test-2026"
 * }
 */

Route::post('/dev/generate-test-tokens', function (Request $request) {

    // ⚠️ Simple security - احذف أو غير الـ secret ده
    $secret = $request->input('secret');
    if ($secret !== 'load-test-2026') {
        return response()->json([
            'error' => 'Unauthorized - Invalid secret'
        ], 401);
    }

    // ⚠️ فقط في development/staging - ممنوع في production
    if (config('app.env') === 'production') {
        return response()->json([
            'error' => 'This endpoint is disabled in production'
        ], 403);
    }

    // Get user IDs from request
    $userIds = $request->input('user_ids', [2419, 2418, 2417, 2416, 1174]);

    if (empty($userIds)) {
        return response()->json([
            'error' => 'user_ids array is required'
        ], 400);
    }

    $results = [];
    $errors = [];

    foreach ($userIds as $userId) {
        $user = User::find($userId);

        if (!$user) {
            $errors[] = [
                'user_id' => $userId,
                'error' => 'User not found'
            ];
            continue;
        }

        // Generate token
        $tokenName = 'load-test-' . date('Ymd-His');
        $token = $user->createToken($tokenName);

        $results[] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'balance' => $user->balance,
            'token' => 'Bearer ' . $token->plainTextToken,
            'token_name' => $tokenName,
        ];
    }

    // Generate ready-to-use config
    $configArray = [];
    foreach ($results as $result) {
        $configArray[] = [
            'user_id' => (string)$result['user_id'],
            'token' => $result['token'],
            'name' => $result['name'],
        ];
    }

    return response()->json([
        'success' => true,
        'message' => 'Tokens generated successfully',
        'count' => count($results),
        'tokens' => $results,
        'errors' => $errors,

        // Ready to copy config
        'config_ready' => [
            'users' => $configArray
        ],

        // Ready to copy for load-test-config.php
        'copy_this_to_config' => "'users' => " . var_export($configArray, true) . ",",
    ], 200);
});


/**
 * GET endpoint - للاستخدام من المتصفح مباشرة
 *
 * Usage:
 * GET https://eagle.utdsoftware.com/api/dev/generate-test-tokens?secret=load-test-2026&user_ids=2419,2418,2417,2416,1174
 */
Route::get('/dev/generate-test-tokens', function (Request $request) {

    // ⚠️ Simple security
    $secret = $request->input('secret');
    if ($secret !== 'load-test-2026') {
        return response()->json([
            'error' => 'Unauthorized - Invalid secret'
        ], 401);
    }

    // ⚠️ فقط في development/staging
    if (config('app.env') === 'production') {
        return response()->json([
            'error' => 'This endpoint is disabled in production'
        ], 403);
    }

    // Parse user_ids from comma-separated string
    $userIdsString = $request->input('user_ids', '2419,2418,2417,2416,1174');
    $userIds = array_map('intval', explode(',', $userIdsString));

    $results = [];
    $errors = [];

    foreach ($userIds as $userId) {
        $user = User::find($userId);

        if (!$user) {
            $errors[] = [
                'user_id' => $userId,
                'error' => 'User not found'
            ];
            continue;
        }

        $tokenName = 'load-test-' . date('Ymd-His');
        $token = $user->createToken($tokenName);

        $results[] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'balance' => $user->balance,
            'token' => 'Bearer ' . $token->plainTextToken,
            'token_name' => $tokenName,
        ];
    }

    // Build HTML response for easy copying
    $html = '<!DOCTYPE html>
<html>
<head>
    <title>Load Test Tokens</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .section { margin: 20px 0; padding: 15px; background: #252526; border-radius: 5px; }
        h2 { color: #4ec9b0; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .token { color: #ce9178; word-break: break-all; }
        .copy-btn { background: #0e639c; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius: 3px; }
        .copy-btn:hover { background: #1177bb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #333; }
        th { color: #4ec9b0; }
    </style>
</head>
<body>
    <h1>🔑 Load Test Tokens Generated</h1>
    <p>Generated at: ' . date('Y-m-d H:i:s') . '</p>

    <div class="section">
        <h2>📊 Summary</h2>
        <p>✅ Success: ' . count($results) . ' tokens generated</p>
        <p>❌ Errors: ' . count($errors) . '</p>
    </div>

    <div class="section">
        <h2>👥 Tokens Table</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Balance</th>
                    <th>Token</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($results as $result) {
        $html .= '<tr>
            <td>' . $result['user_id'] . '</td>
            <td>' . htmlspecialchars($result['name']) . '</td>
            <td>' . number_format($result['balance']) . '</td>
            <td class="token">' . htmlspecialchars(substr($result['token'], 0, 50)) . '...</td>
        </tr>';
    }

    $html .= '</tbody>
        </table>
    </div>

    <div class="section">
        <h2>📋 Copy to load-test-config.php</h2>
        <button class="copy-btn" onclick="copyConfig()">📋 Copy Config</button>
        <pre id="config-code">';

    $html .= htmlspecialchars("'users' => [\n");
    foreach ($results as $result) {
        $html .= htmlspecialchars("    [\n");
        $html .= htmlspecialchars("        'user_id' => '{$result['user_id']}',\n");
        $html .= htmlspecialchars("        'token' => '{$result['token']}',\n");
        $html .= htmlspecialchars("        'name' => '{$result['name']}',\n");
        $html .= htmlspecialchars("    ],\n");
    }
    $html .= htmlspecialchars("],\n");

    $html .= '</pre>
    </div>

    <div class="section">
        <h2>📄 JSON Response</h2>
        <button class="copy-btn" onclick="copyJson()">📋 Copy JSON</button>
        <pre id="json-code">' . htmlspecialchars(json_encode([
            'tokens' => $results,
            'errors' => $errors
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>
    </div>

    <script>
        function copyConfig() {
            const code = document.getElementById("config-code").textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert("✅ Config copied to clipboard!");
            });
        }

        function copyJson() {
            const code = document.getElementById("json-code").textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert("✅ JSON copied to clipboard!");
            });
        }
    </script>
</body>
</html>';

    return response($html)->header('Content-Type', 'text/html');
});
