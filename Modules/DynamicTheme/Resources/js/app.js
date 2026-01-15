import './bootstrap.js';
import { createApp } from 'vue';

// Import components
import DashboardApp from './components/DashboardApp.vue';
import DrawingCanvas from './components/DrawingCanvas.vue';
import ChildCustomizerComponentV2 from './components/ChildCustomizerComponentV2.vue';
import ChildCustomizerPage from './pages/ChildCustomizerPage.vue';

// Create Vue app if element exists
const appElement = document.getElementById('app');
if (appElement) {
    const app = createApp(DashboardApp);
    
    // Register components globally
    app.component('DrawingCanvas', DrawingCanvas);
    app.component('ChildCustomizerComponentV2', ChildCustomizerComponentV2);
    app.component('ChildCustomizerPage', ChildCustomizerPage);
    
    app.mount('#app');
}

 