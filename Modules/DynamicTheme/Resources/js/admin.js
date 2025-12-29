import { createApp } from 'vue';
import AdminApp from './admin/AdminApp.vue';
import '../css/app.css';

const app = createApp(AdminApp);
app.mount('#admin-app');
