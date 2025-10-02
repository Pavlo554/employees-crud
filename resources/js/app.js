import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router'; 
import App from './App.vue'; 
import EmployeesPage from './Pages/EmployeesPage.vue';

// Налаштування Vue Router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/employees' }, 
    { path: '/employees', component: EmployeesPage },
  ],
});

const app = createApp(App); 
app.use(router);
app.mount('#app'); 