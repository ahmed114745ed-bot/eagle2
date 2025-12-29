<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Client Configurations - Dynamic Theme</title>
    @vite(['Resources/css/app.css', 'Resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <div id="client-dashboard"></div>
</body>
</html>
