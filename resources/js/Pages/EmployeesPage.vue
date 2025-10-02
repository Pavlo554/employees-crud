<template>
  <v-container fluid>
    <v-card class="pa-4">
      <v-card-title class="d-flex align-center">
        <v-text-field
          v-model="search"
          label="Search by Name or Email"
          prepend-inner-icon="mdi-magnify"
          single-line
          hide-details
          class="mr-4"
          @input="handleSearch"
        ></v-text-field>

        <v-spacer></v-spacer>

        <v-btn color="success" class="mr-2" @click="exportEmployees">
          <v-icon left>mdi-file-excel</v-icon>
          Export to Excel
        </v-btn>

        <v-btn color="primary" @click="openCreateDialog">
          <v-icon left>mdi-plus</v-icon>
          Add New Employee
        </v-btn>
      </v-card-title>

      <v-card-text>
        <v-data-table-server
          v-model:items-per-page="itemsPerPage"
          :items-length="totalItems"
          :items="employees"
          :headers="headers"
          :loading="loading"
          @update:options="loadItems"
          class="elevation-1"
          item-value="id"
          show-select
          v-model="selectedEmployees"
        >
          <template v-slot:item.status="{ item }">
            <v-chip :color="item.status === 'active' ? 'green' : 'red'" dark>{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <v-icon small class="mr-2" @click="editEmployee(item)">
              mdi-pencil
            </v-icon>
            <v-icon small @click="confirmDelete(item)">
              mdi-delete
            </v-icon>
          </template>
        </v-data-table-server>
      </v-card-text>
    </v-card>
  </v-container>

  <EmployeeFormDialog v-model="dialog" :employee="editedItem" @save="saveEmployee" />

  <v-dialog v-model="deleteConfirmDialog" max-width="500px">
    <v-card>
      <v-card-title class="text-h5">Confirm Delete</v-card-title>
      <v-card-text>Are you sure you want to delete this employee?</v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" text @click="closeDeleteConfirm">Cancel</v-btn>
        <v-btn color="red darken-1" text @click="deleteItemConfirm">Delete</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import EmployeeFormDialog from '../Components/EmployeeFormDialog.vue'; 

interface Employee {
  id: number;
  first_name: string; // <-- snake_case
  last_name: string;  // <-- snake_case
  email: string;
  phone?: string | null;
  position?: string | null;
  salary: number;
  hired_at?: string | null; // <-- snake_case (ISO date)
  status: 'active' | 'inactive';
  updated_at?: string; // <-- snake_case 
}

const employees = ref<Employee[]>([]);
const loading = ref(true);
const totalItems = ref(0);
const itemsPerPage = ref(10);
const search = ref('');
const selectedEmployees = ref([]);

const headers = [
  { title: 'ID', key: 'id', sortable: true },
  { title: 'First Name', key: 'first_name', sortable: true },
  { title: 'Last Name', key: 'last_name', sortable: true },
  { title: 'Email', key: 'email', sortable: false },
  { title: 'Phone', key: 'phone', sortable: false },
  { title: 'Position', key: 'position', sortable: true },
  { title: 'Salary', key: 'salary', sortable: true },
  { title: 'Hired At', key: 'hired_at', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Updated At', key: 'updated_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
];

const dialog = ref(false);
const editedItem = ref<Employee | null>(null); // editedItem має бути типу Employee або null

const deleteConfirmDialog = ref(false);
const employeeToDelete = ref<Employee | null>(null);

async function loadItems({ page, itemsPerPage, sortBy }: { page: number; itemsPerPage: number; sortBy: { key: string; order: string }[] }) {
  loading.value = true;
  try {
    const params = {
      page: page,
      perPage: itemsPerPage,
      search: search.value,
      sort: sortBy.length > 0 ? sortBy[0].key : undefined,
      dir: sortBy.length > 0 ? sortBy[0].order : undefined,
    };
    const response = await axios.get('/api/employees', { params });
    // Дані з бекенду вже приходять у snake_case, тому пряме присвоєння працює
    employees.value = response.data.data;
    totalItems.value = response.data.meta.total;
  } catch (error) {
    console.error("Error fetching employees:", error);
    // TODO: Показати повідомлення про помилку користувачу (snackbar/alert)
  } finally {
    loading.value = false;
  }
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
function handleSearch() {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  searchTimeout = setTimeout(() => {
    loadItems({ page: 1, itemsPerPage: itemsPerPage.value, sortBy: [] });
  }, 300);
}


function openCreateDialog() {
  // Ініціалізуємо editedItem з snake_case полями та усіма обов'язковими полями
  editedItem.value = {
    id: 0,
    first_name: '',
    last_name: '',
    email: '',
    phone: null, // Опціональні поля можуть бути null
    position: null, // Опціональні поля можуть бути null
    salary: 0,
    hired_at: null, // Опціональні поля можуть бути null
    status: 'active', // Статус має бути одним з дозволених 'active' | 'inactive'
    updated_at: undefined // updatedAt може бути undefined для нового об'єкта
  };
  dialog.value = true;
}

function editEmployee(item: Employee) {
  // Копіюємо об'єкт для редагування. item вже має тип Employee (snake_case)
  editedItem.value = { ...item };
  dialog.value = true;
}

async function saveEmployee(employee: Employee) { // employee вже приходить як snake_case з EmployeeFormDialog
  try {
    // employee вже містить snake_case поля, тому можемо відправляти його напряму
    if (employee.id === 0) {
      await axios.post('/api/employees', employee);
    } else {
      await axios.put(`/api/employees/${employee.id}`, employee);
    }
    dialog.value = false;
    await loadItems({ page: 1, itemsPerPage: itemsPerPage.value, sortBy: [] });
    // TODO: Показати snackbar про успіх
  } catch (error: any) {
    console.error("Error saving employee:", error);
    if (error.response && error.response.status === 422) {
        console.error("Validation Errors:", error.response.data.errors);
        // TODO: Показати помилки валідації користувачу, наприклад, через snackbar або передати їх до форми
    }
    // TODO: Показати повідомлення про помилку користувачу
  }
}

function confirmDelete(item: Employee) {
  employeeToDelete.value = item;
  deleteConfirmDialog.value = true;
}

function closeDeleteConfirm() {
  deleteConfirmDialog.value = false;
  employeeToDelete.value = null;
}

async function deleteItemConfirm() {
  if (employeeToDelete.value) {
    try {
      await axios.delete(`/api/employees/${employeeToDelete.value.id}`);
      closeDeleteConfirm();
      await loadItems({ page: 1, itemsPerPage: itemsPerPage.value, sortBy: [] });
      // TODO: Показати snackbar про успіх
    } catch (error) {
      console.error("Error deleting employee:", error);
      // TODO: Показати повідомлення про помилку
    }
  }
}

async function exportEmployees() {
  try {
    const params = {
      search: search.value,
      // Тут можна додати параметри сортування, якщо потрібно для експорту
    };
    const response = await axios.get('/api/employees/export', {
      params,
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'employees.xlsx');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    // TODO: Показати snackbar про успішний експорт
  } catch (error) {
    console.error("Error exporting employees:", error);
    // TODO: Показати повідомлення про помилку користувачу
  }
}
</script>

<style scoped>
/* Стилі для компонента EmployeesPage */
</style>