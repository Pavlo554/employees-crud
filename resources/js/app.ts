import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';

// Vue Router
import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'; // <-- add RouteRecordRaw
import EmployeesPage from './Pages/EmployeesPage.vue'; 

// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi'; // <-- add mdi
import '@mdi/font/css/materialdesignicons.css'; // icon  Material Design

const vuetify = createVuetify({
  components,
  directives,
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: {
      mdi,
    },
  },
});


const routes: Array<RouteRecordRaw> = [ 
    { path: '/', component: EmployeesPage }, 
    // { path: '/employees/:id/edit', component: EmployeeFormDialog },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

createApp(App)
  .use(vuetify)
  .use(router)
  .mount('#app');