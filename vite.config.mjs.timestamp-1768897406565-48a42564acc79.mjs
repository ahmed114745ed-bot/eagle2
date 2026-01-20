// vite.config.mjs
import { defineConfig } from "file:///media/leader/par1/Doc/GitHub/Eagle/node_modules/vite/dist/node/index.js";
import laravel from "file:///media/leader/par1/Doc/GitHub/Eagle/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///media/leader/par1/Doc/GitHub/Eagle/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import path from "path";
import { fileURLToPath } from "url";
var __vite_injected_original_import_meta_url = "file:///media/leader/par1/Doc/GitHub/Eagle/vite.config.mjs";
var __filename = fileURLToPath(__vite_injected_original_import_meta_url);
var __dirname = path.dirname(__filename);
var vite_config_default = defineConfig({
  plugins: [
    vue(),
    laravel({
      input: [
        "Modules/DynamicTheme/Resources/css/app.css",
        "Modules/DynamicTheme/Resources/js/app.js",
        "Modules/DynamicTheme/Resources/js/admin.js"
      ],
      refresh: [
        "Modules/DynamicTheme/Resources/views/**/*",
        "Modules/DynamicTheme/Resources/js/**/*",
        "Modules/DynamicTheme/Resources/css/**/*"
      ]
    })
  ],
  resolve: {
    alias: {
      "@dynamictheme": path.resolve(__dirname, "Modules/DynamicTheme/Resources/js")
    }
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcubWpzIl0sCiAgInNvdXJjZXNDb250ZW50IjogWyJjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZGlybmFtZSA9IFwiL21lZGlhL2xlYWRlci9wYXIxL0RvYy9HaXRIdWIvRWFnbGVcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIi9tZWRpYS9sZWFkZXIvcGFyMS9Eb2MvR2l0SHViL0VhZ2xlL3ZpdGUuY29uZmlnLm1qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vbWVkaWEvbGVhZGVyL3BhcjEvRG9jL0dpdEh1Yi9FYWdsZS92aXRlLmNvbmZpZy5tanNcIjtpbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tICd2aXRlJztcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nO1xuaW1wb3J0IHZ1ZSBmcm9tICdAdml0ZWpzL3BsdWdpbi12dWUnO1xuaW1wb3J0IHBhdGggZnJvbSAncGF0aCc7XG5pbXBvcnQgeyBmaWxlVVJMVG9QYXRoIH0gZnJvbSAndXJsJztcblxuY29uc3QgX19maWxlbmFtZSA9IGZpbGVVUkxUb1BhdGgoaW1wb3J0Lm1ldGEudXJsKTtcbmNvbnN0IF9fZGlybmFtZSA9IHBhdGguZGlybmFtZShfX2ZpbGVuYW1lKTtcblxuZXhwb3J0IGRlZmF1bHQgZGVmaW5lQ29uZmlnKHtcbiAgICBwbHVnaW5zOiBbXG4gICAgICAgIHZ1ZSgpLFxuICAgICAgICBsYXJhdmVsKHtcbiAgICAgICAgICAgIGlucHV0OiBbXG4gICAgICAgICAgICAgICAgJ01vZHVsZXMvRHluYW1pY1RoZW1lL1Jlc291cmNlcy9jc3MvYXBwLmNzcycsXG4gICAgICAgICAgICAgICAgJ01vZHVsZXMvRHluYW1pY1RoZW1lL1Jlc291cmNlcy9qcy9hcHAuanMnLFxuICAgICAgICAgICAgICAgICdNb2R1bGVzL0R5bmFtaWNUaGVtZS9SZXNvdXJjZXMvanMvYWRtaW4uanMnLFxuICAgICAgICAgICAgXSxcbiAgICAgICAgICAgIHJlZnJlc2g6IFtcbiAgICAgICAgICAgICAgICAnTW9kdWxlcy9EeW5hbWljVGhlbWUvUmVzb3VyY2VzL3ZpZXdzLyoqLyonLFxuICAgICAgICAgICAgICAgICdNb2R1bGVzL0R5bmFtaWNUaGVtZS9SZXNvdXJjZXMvanMvKiovKicsXG4gICAgICAgICAgICAgICAgJ01vZHVsZXMvRHluYW1pY1RoZW1lL1Jlc291cmNlcy9jc3MvKiovKicsXG4gICAgICAgICAgICBdLFxuICAgICAgICB9KSxcbiAgICBdLFxuICAgIHJlc29sdmU6IHtcbiAgICAgICAgYWxpYXM6IHtcbiAgICAgICAgICAgICdAZHluYW1pY3RoZW1lJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJ01vZHVsZXMvRHluYW1pY1RoZW1lL1Jlc291cmNlcy9qcycpLFxuICAgICAgICB9LFxuICAgIH0sXG59KTtcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBNlIsU0FBUyxvQkFBb0I7QUFDMVQsT0FBTyxhQUFhO0FBQ3BCLE9BQU8sU0FBUztBQUNoQixPQUFPLFVBQVU7QUFDakIsU0FBUyxxQkFBcUI7QUFKaUosSUFBTSwyQ0FBMkM7QUFNaE8sSUFBTSxhQUFhLGNBQWMsd0NBQWU7QUFDaEQsSUFBTSxZQUFZLEtBQUssUUFBUSxVQUFVO0FBRXpDLElBQU8sc0JBQVEsYUFBYTtBQUFBLEVBQ3hCLFNBQVM7QUFBQSxJQUNMLElBQUk7QUFBQSxJQUNKLFFBQVE7QUFBQSxNQUNKLE9BQU87QUFBQSxRQUNIO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxNQUNKO0FBQUEsTUFDQSxTQUFTO0FBQUEsUUFDTDtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsTUFDSjtBQUFBLElBQ0osQ0FBQztBQUFBLEVBQ0w7QUFBQSxFQUNBLFNBQVM7QUFBQSxJQUNMLE9BQU87QUFBQSxNQUNILGlCQUFpQixLQUFLLFFBQVEsV0FBVyxtQ0FBbUM7QUFBQSxJQUNoRjtBQUFBLEVBQ0o7QUFDSixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=
