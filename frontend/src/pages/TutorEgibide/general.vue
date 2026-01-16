<script setup lang="ts">
import { onMounted } from "vue";
import { useSeguimientosStore } from "@/stores/seguimientos";

const seguimientosStore = useSeguimientosStore();

onMounted(() => {
  seguimientosStore.fetchSeguimientos();
});
</script>

<template>
  <div>
    <h2>Lista de Seguimientos</h2>
    <ul class="list-group">
      <li class="list-group-item" v-for="seguimiento in seguimientosStore.seguimientos" :key="seguimiento.id_seguimiento">
        <strong>{{ seguimiento.fecha }}</strong> — {{ seguimiento.descripcion }}
      </li>
    </ul>
  </div>
</template>

<style scoped></style>
