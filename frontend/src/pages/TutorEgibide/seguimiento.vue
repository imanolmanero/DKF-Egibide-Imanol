<script setup lang="ts">
import { onMounted } from "vue";
import { useSeguimientosStore } from "@/stores/seguimientos";

const seguimientosStore = useSeguimientosStore();

onMounted(() => {
  seguimientosStore.fetchSeguimientos();
});
</script>

<template>
  <h2>SEGUIMIENTO</h2>
</template>

<style scoped></style>
