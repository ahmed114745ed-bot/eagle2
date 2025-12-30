/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './Modules/DynamicTheme/Resources/**/*.blade.php',
        './Modules/DynamicTheme/Resources/**/*.js',
        './Modules/DynamicTheme/Resources/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        extend: {},
    },
    plugins: [],
}
