import './bootstrap';
import { createApp } from 'vue';
import Dashboard from './components/Dashboard.vue';
import ContentCalendar from './components/ContentCalendar.vue';
import ContentArchive from './components/ContentArchive.vue';
import ContentAnalytics from './components/ContentAnalytics.vue';
import UserManagement from './components/UserManagement.vue';
import LiveChat from './components/LiveChat.vue';

const app = createApp({});

// Register components
app.component('dashboard', Dashboard);
app.component('content-calendar', ContentCalendar);
app.component('content-archive', ContentArchive);
app.component('content-analytics', ContentAnalytics);
app.component('user-management', UserManagement);
app.component('live-chat', LiveChat);

app.mount('#app');
