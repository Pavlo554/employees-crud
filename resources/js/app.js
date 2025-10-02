// resources/js/app.js (або main.ts)

import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router'; // Для маршрутизації
import App from './App.vue'; // Припустімо, що у вас є кореневий компонент App.vue

// Імпорт ваших сторінок
import EmployeesPage from './Pages/EmployeesPage.vue';

// Налаштування Vue Router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/employees' }, // Перенаправлення на сторінку співробітників
    { path: '/employees', component: EmployeesPage },
    // Додайте інші маршрути тут
  ],
});

const app = createApp(App); // Створіть Vue додаток з кореневим компонентом App

app.use(router); // Використовуйте маршрутизатор
// ... інші плагіни, наприклад Vuetify

app.mount('#app'); // Підключіть додаток до HTML елементу з id="app"