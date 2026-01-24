<script setup>
import UploadCiclosCSV from "@/components/Ciclos/UploadCiclosCSV.vue";
</script>

<template>
  <h2>Agregar</h2>
  <hr />

  <div class="info-links">
    <RouterLink to="/admin/nuevo-ciclo" class="info-card">
      <h4>Ciclo</h4>
      <p>Agregar un nuevo ciclo formativo</p>
    </RouterLink>

    <RouterLink to="/admin/nueva-competencia" class="info-card">
      <h4>Competencia</h4>
      <p>Agregar una nueva competencia</p>
    </RouterLink>

    <RouterLink to="/admin/nueva-empresa" class="info-card">
      <h4>Empresa</h4>
      <p>Agregar una nueva empresa colaboradora</p>
    </RouterLink>

    <RouterLink to="/admin/nuevo-alumno" class="info-card">
      <h4>Alumno</h4>
      <p>Agregar un nuevo alumno</p>
    </RouterLink>
  </div>
  <UploadCiclosCSV />
</template>

<style scoped>
.info-links {
  display: grid;
  gap: 1.5rem; /* separación entre cards */
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

/* Card */
.info-card {
  padding: 1.5rem;
  border: 2px solid #81045f;
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
  background-color: #e3e6ea;
}

.info-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}
</style>
