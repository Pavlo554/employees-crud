<!-- resources/js/App.vue -->
<template>
  <v-app>
    <v-app-bar app color="primary" dark>
      <v-app-bar-title>Employees CRUD</v-app-bar-title>
      <v-spacer></v-spacer>
      <!-- Ці кнопки тепер будуть всередині EmployeesPage, якщо ви будете слідувати структурі ТЗ -->
      <!-- <v-btn icon>
        <v-icon>mdi-export</v-icon>
      </v-btn>
      <v-btn icon>
        <v-icon>mdi-plus</v-icon>
      </v-btn> -->
    </v-app-bar>

    <v-main>
      <v-container fluid class="fill-height">
        <v-row align="start" justify="center"> <!-- Змінено align="center" на align="start" -->
          <v-col cols="12"> <!-- Змінено md="10" lg="8" на cols="12" для повного контейнера -->
            <EmployeesPage /> <!-- Тут монтуємо наш компонент EmployeesPage -->
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <v-footer app>
      <!-- Футер, якщо потрібно -->
    </v-footer>
  </v-app>
</template>

<script setup lang="ts">
import EmployeesPage from './Pages/EmployeesPage.vue'; // Імпортуємо новий компонент
</script>

<style scoped>
/* Стилі для цього компонента */
</style>