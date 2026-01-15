import './bootstrap.js';
import { createApp } from 'vue';

// Import components
import DashboardApp from './components/DashboardApp.vue';

// Create Vue app if element exists
const appElement = document.getElementById('app');
if (appElement) {
    const app = createApp(DashboardApp);
    app.mount('#app');
}
 