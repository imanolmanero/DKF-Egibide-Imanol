<script>
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";

export default {
  name: "Sidebar",
  setup() {
    const authStore = useAuthStore();

    const userRole = computed(() => authStore.currentUser?.role);

    return {
      userRole,
    };
  },
};
</script>

<template>
  <aside class="sidebar island">
    <!-- Menú para Alumnos -->
    <nav v-if="userRole === 'alumno'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3 class="sidebar-title">Inicio</h3>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Información</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">
            <RouterLink to="/alumno/mis-datos">Mis Datos</RouterLink>
          </li>
          <li class="sidebar-item">
            <RouterLink to="/alumno/empresa">Empresa</RouterLink>
          </li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Seguimiento</h3>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Calificación</h3>
      </div>
    </nav>

    <!-- Menú para Tutores -->
    <nav v-else-if="userRole === 'tutor'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3 class="sidebar-title">Información</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">Mis Alumnos</li>
          <li class="sidebar-item">Empresas</li>
          <li class="sidebar-item">Evaluaciones</li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Seguimiento</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">Estado General</li>
          <li class="sidebar-item">Incidencias</li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Reportes</h3>
      </div>
    </nav>

    <!-- Menú para Empresas -->
    <nav v-else-if="userRole === 'empresa'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3 class="sidebar-title">Mi Empresa</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">Datos</li>
          <li class="sidebar-item">Alumnos Asignados</li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Evaluación</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">Competencias</li>
          <li class="sidebar-item">Seguimiento</li>
        </ul>
      </div>
    </nav>

    <!-- Menú por defecto -->
    <nav v-else class="sidebar-nav">
      <div class="sidebar-section">
        <p class="text-muted text-center py-4 mb-0">
          No se ha identificado el tipo de usuario
        </p>
      </div>
    </nav>
  </aside>
</template>

<style scoped></style>
