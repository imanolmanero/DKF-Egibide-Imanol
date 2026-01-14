<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useCiclosStore } from '@/stores/ciclos';

  const ciclosStore = useCiclosStore();

  onMounted(() => {
    ciclosStore.fetchCiclos();
  });
</script>

<template>
  <div>
    <h2>Lista de Ciclos</h2>
    <ul class="list-group">
      <li class="list-group-item" v-for="ciclo in ciclosStore.ciclos" :key="ciclo.id">
        {{ ciclo.nombre }}
      </li>
    </ul>
  </div>
</template>

<style scoped></style>
