<script setup lang="ts">
import { onMounted } from "vue";
import { useSeguimientosStore } from "@/stores/seguimientos";

const seguimientosStore = useSeguimientosStore();

onMounted(() => {
  seguimientosStore.fetchSeguimientos();
});
</script>

<template>
  <h2>COMPETENCIAS</h2>
</template>

<style scoped></style>
