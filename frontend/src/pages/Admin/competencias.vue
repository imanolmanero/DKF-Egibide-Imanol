<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useCompetenciasStore } from '@/stores/competencias';

  const competenciasStore = useCompetenciasStore();

  onMounted(() => {
    competenciasStore.fetchCompetencias();
  });
</script>

<template>
  <div>
    <h2>Lista de Competencias</h2>
    <ul class="list-group">
      <li class="list-group-item" v-for="competencia in competenciasStore.competencias" :key="competencia.id">
        {{ competencia.descripcion }}
      </li>
    </ul>
  </div>
</template>

<style scoped></style>
