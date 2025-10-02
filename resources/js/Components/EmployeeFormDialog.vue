<!-- resources/js/Components/EmployeeFormDialog.vue -->
<template>
  <v-dialog v-model="internalDialog" max-width="600px" persistent>
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ isNewEmployee ? 'Create New Employee' : 'Edit Employee' }}</span>
      </v-card-title>

      <v-card-text>
        <v-container>
          <v-form ref="form" v-model="valid" lazy-validation>
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.firstName"
                  label="First Name"
                  :rules="[rules.required, rules.minMax(2, 100)]"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.lastName"
                  label="Last Name"
                  :rules="[rules.required, rules.minMax(2, 100)]"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="editedItem.email"
                  label="Email"
                  :rules="[rules.required, rules.email]"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.phone"
                  label="Phone (optional)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.position"
                  label="Position (optional)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="editedItem.salary"
                  label="Salary"
                  type="number"
                  :rules="[rules.required, rules.minSalary]"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="editedItem.hiredAt"
                  label="Hired At (YYYY-MM-DD, optional)"
                  type="date"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-select
                  v-model="editedItem.status"
                  :items="['active', 'inactive']"
                  label="Status"
                  :rules="[rules.required]"
                  required
                ></v-select>
              </v-col>
            </v-row>
          </v-form>
        </v-container>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" text @click="closeDialog">Cancel</v-btn>
        <v-btn color="blue darken-1" text @click="save">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed, PropType } from 'vue';

// *******************************************************************
// ОНОВЛЕНИЙ ІНТЕРФЕЙС Employee
// Використовуємо camelCase для полів, як вимагає API для відправки даних
// *******************************************************************
interface Employee {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string | null;
  position?: string | null;
  salary: number;
  hiredAt?: string | null; // ISO date YYYY-MM-DD
  status: 'active' | 'inactive';
  updatedAt?: string; // Може бути необов'язковим, оскільки API його генерує
}

// Інтерфейс для даних, які приходять з батьківського компонента (snake_case)
interface EmployeeFromParent {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string | null;
  position?: string | null;
  salary: number;
  hired_at?: string | null;
  status: 'active' | 'inactive';
  updated_at?: string;
}

const props = defineProps({
  modelValue: { // v-model для контролю видимості діалогу
    type: Boolean,
    required: true,
  },
  // Тепер приймаємо employee з батьківського компонента в snake_case
  employee: {
    type: Object as PropType<EmployeeFromParent | null>,
    default: null,
  },
});

// Емітуємо подію 'save' з даними в snake_case, щоб батьківський компонент міг їх обробити
const emit = defineEmits(['update:modelValue', 'save']);

const internalDialog = ref(props.modelValue);
// Ініціалізуємо editedItem з camelCase полями
const editedItem = ref<Employee>({
  id: 0, firstName: '', lastName: '', email: '', salary: 0,
  status: 'active', phone: null, position: null, hiredAt: null, updatedAt: undefined
});

const isNewEmployee = computed(() => editedItem.value.id === 0);

// Валідація
const form = ref<HTMLFormElement | null>(null);
const valid = ref(true);
const rules = {
  required: (value: any) => !!value || 'Required.',
  minMax: (min: number, max: number) => (value: string) =>
    (value && value.length >= min && value.length <= max) || `Must be between ${min} and ${max} characters`,
  email: (value: string) => {
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return pattern.test(value) || 'Invalid e-mail.';
  },
  minSalary: (value: number) => value >= 0 || 'Salary must be 0 or greater.',
};

// *******************************************************************
// Логіка Watch для оновлення editedItem при зміні props.employee
// Тепер коректно перетворюємо snake_case в camelCase для форми
// *******************************************************************
watch(() => props.modelValue, (newVal) => {
  internalDialog.value = newVal;
  if (newVal) {
    if (props.employee) {
      // Перетворюємо snake_case з батьківського компонента в camelCase для форми
      editedItem.value = {
        id: props.employee.id,
        firstName: props.employee.first_name,
        lastName: props.employee.last_name,
        email: props.employee.email,
        phone: props.employee.phone,
        position: props.employee.position,
        salary: props.employee.salary,
        hiredAt: props.employee.hired_at,
        status: props.employee.status,
        updatedAt: props.employee.updated_at,
      };
    } else {
      // Для створення нового працівника (всі поля camelCase)
      editedItem.value = {
        id: 0, firstName: '', lastName: '', email: '', salary: 0,
        status: 'active', phone: null, position: null, hiredAt: null, updatedAt: undefined
      };
    }
    // Скидаємо валідацію, коли діалог відкривається з новими/існуючими даними
    if (form.value) {
      form.value.resetValidation();
    }
  }
}, { immediate: true }); // Додано immediate: true для початкової синхронізації

watch(internalDialog, (newVal) => {
  emit('update:modelValue', newVal);
  if (!newVal && form.value) {
    form.value.resetValidation(); // Скидаємо валідацію при закритті
  }
});

async function save() {
  const { valid: formValid } = await form.value!.validate(); // Перейменував 'valid' на 'formValid' щоб уникнути конфлікту
  if (formValid) {
    // *******************************************************************
    // Перетворюємо camelCase з форми назад у snake_case для збереження в API
    // Батьківський компонент очікує дані в snake_case, щоб відправити їх на бекенд
    // *******************************************************************
    const employeeToSave: EmployeeFromParent = {
      id: editedItem.value.id,
      first_name: editedItem.value.firstName,
      last_name: editedItem.value.lastName,
      email: editedItem.value.email,
      phone: editedItem.value.phone,
      position: editedItem.value.position,
      salary: editedItem.value.salary,
      hired_at: editedItem.value.hiredAt,
      status: editedItem.value.status,
      // updated_at можна не надсилати або надсилати, якщо бекенд цього потребує
      updated_at: editedItem.value.updatedAt,
    };
    emit('save', employeeToSave);
    closeDialog();
  }
}

function closeDialog() {
  internalDialog.value = false;
  // editedItem скидається автоматично при наступному відкритті завдяки watch-функції
}
</script>

<style scoped>
/* Стилі для діалогу */
</style>