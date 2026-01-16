<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useEmpresasStore } from '@/stores/empresas';

  const empresasStore = useEmpresasStore();

  onMounted(() => {
    empresasStore.fetchEmpresas();
  });
</script>

<template>
  <div>
    <h2>Lista de Empresas</h2>
    <ul class="list-group">
      <li class="list-group-item" v-for="empresa in empresasStore.empresas" :key="empresa.id">
        {{ empresa.nombre }}
      </li>
    </ul>
  </div>
</template>

<style scoped></style>
