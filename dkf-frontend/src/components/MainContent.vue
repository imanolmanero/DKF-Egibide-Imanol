<script setup lang="ts">
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

const role = computed(() => authStore.currentUser?.role.trim().toLowerCase());

// Contenido dinámico según rol
const mainContentCards = computed(() => {
  switch (role.value) {
    case "alumno":
      return [
        { title: "Alumno", text: "Eres alumno" }
      ];
    case "tutor_empresa":
      return [
        { title: "Tutor Empresa", text: "Eres tutor de empresa" }
      ];
    case "tutor_egibide":
      return [
        { title: "Tutor Egibide", text: "Eres tutor de Egibide" }
      ];
    case "admin":
      return [
        { title: "Admin", text: "Eres administrador" }
      ];
    default:
      return [];
  }
});
</script>

<template>
  <main class="flex-grow-1 p-4 bg-light overflow-auto rounded shadow-sm bg-white">
    <h1 class="mb-4">Panel principal</h1>

    <div class="row g-3">
      <div v-for="(card, index) in mainContentCards" :key="index" class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">{{ card.title }}</h5>
            <p class="card-text">{{ card.text }}</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>


<style scoped></style>
