import { createApp } from 'vue';
import ClientDashboard from './components/ClientDashboard.vue';

import './bootstrap';

const app = createApp(ClientDashboard);
app.mount('#client-dashboard');
