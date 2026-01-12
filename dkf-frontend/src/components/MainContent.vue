<script setup lang="ts">
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

const role = computed(() => authStore.currentUser?.role.trim().toLowerCase());

// Contenido dinámico según rol
const mainContentCards = computed(() => {
  switch (role.value) {
    case "alumno":
      return [{ title: "Alumno", text: "Eres alumno" }];
    case "tutor_empresa":
      return [{ title: "Tutor Empresa", text: "Eres tutor de empresa" }];
    case "tutor_egibide":
      return [{ title: "Tutor Egibide", text: "Eres tutor de Egibide" }];
    case "admin":
      return [{ title: "Admin", text: "Eres administrador" }];
    default:
      return [];
  }
});
</script>

<template>
  <main class="flex-grow-1 p-4 overflow-auto island">
    <RouterView name="main"></RouterView>
  </main>
</template>

<style scoped></style>
