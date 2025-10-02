<template>
  <v-app>
    <v-app-bar app color="primary" dark>
      <v-app-bar-title>Employees CRUD</v-app-bar-title>
      <v-spacer></v-spacer>
    </v-app-bar>

    <v-main>
      <v-container fluid class="fill-height">
        <v-row align="start" justify="center"> 
          <v-col cols="12"> 
            <EmployeesPage /> 
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <v-footer app>
    </v-footer>
  </v-app>
</template>

<script setup lang="ts">
import EmployeesPage from './Pages/EmployeesPage.vue'; 
</script>

<style scoped>
</style>