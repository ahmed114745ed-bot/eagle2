import './bootstrap.js';
import { createApp } from 'vue';
import ChildCustomizerPage from './pages/ChildCustomizerPage.vue';

// Create Vue app
const app = createApp(ChildCustomizerPage);

// Mount the app
app.mount('#app');
