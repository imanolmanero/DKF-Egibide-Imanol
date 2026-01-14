<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useAlumnosStore } from '@/stores/alumnos';

  const alumnosStore = useAlumnosStore();

  onMounted(() => {
    alumnosStore.fetchAlumnos();
  });
</script>

<template>
  <div>
    <h2>Lista de Alumnos</h2>
    <ul class="list-group">
      <li class="list-group-item" v-for="alumno in alumnosStore.alumnos" :key="alumno.id">
        {{ alumno.nombre }}
      </li>
    </ul>
  </div>
</template>

<style scoped></style>
