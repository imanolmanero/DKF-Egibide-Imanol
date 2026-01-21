<script setup lang="ts">
import { onMounted } from "vue";
import { useAlumnosStore } from "@/stores/alumnos";

const alumnosStore = useAlumnosStore();

onMounted(async () => {
  await alumnosStore.fetchNotaCuaderno();
});
</script>

<template>
  <div class="container mt-4">
    <h2 class="mb-4">CALIFICACION</h2>

    <div v-if="alumnosStore.notaCuaderno !== null" class="nota card">
      <div class="card-body">
        <p><strong>Nota:</strong> {{ alumnosStore.notaCuaderno }}</p>
      </div>
    </div>

    <div v-else class="msg">
      <p>{{ alumnosStore.notaCuadernoMsg ?? "No hay nota disponible." }}</p>
    </div>
  </div>
</template>

<style scoped>
.nota,
.msg {
  margin-top: 12px;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
}
</style>
