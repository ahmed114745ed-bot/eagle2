<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - Dynamic Theme System</title>
    @vite(['Modules/DynamicTheme/Resources/css/app.css', 'Modules/DynamicTheme/Resources/js/admin.js'])
</head>
<body class="bg-gray-900 text-white">
    <div id="admin-app"></div>
</body>
</html>
