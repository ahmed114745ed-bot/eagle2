<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تخصيص العناصر الفرعية - Child Customizer</title>
    @vite(['Modules/DynamicTheme/Resources/js/app.js'])
</head>
<body>
    <div id="app">
        <div class="min-h-screen bg-gray-100">
            <!-- Loading State -->
            <div class="flex items-center justify-center h-screen">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">جاري التحميل...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize the app when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // This will be handled by the Vue app in app.js
        });
    </script>
</body>
</html>
