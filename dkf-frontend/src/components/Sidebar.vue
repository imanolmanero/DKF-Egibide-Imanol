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
        <h3 class="sidebar-title">
          <RouterLink to="/alumno/inicio">Inicio</RouterLink>
        </h3>
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
        <h3 class="sidebar-title">
          <RouterLink to="/alumno/seguimiento">Seguimiento</RouterLink>
        </h3>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">
          <RouterLink to="/alumno/calificacion">Calificación</RouterLink>
        </h3>
      </div>
    </nav>

    <!-- Menú para Tutores -->
    <nav v-else-if="userRole === 'tutor_egibide'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3 class="sidebar-title">
          <RouterLink to="/tutor-egibide/inicio">Inicio</RouterLink>
        </h3>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Información</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">          
            <RouterLink to="/tutor-egibide/alumnos">Mis Alumnos</RouterLink>
          </li>
          <li class="sidebar-item">          
            <RouterLink to="/tutor-egibide/empresas">Empresas</RouterLink>
          </li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Asignaciones</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">          
            <RouterLink to="/tutor-egibide/alumno-empresa">Alumno x Empresa</RouterLink>
          </li>
          <li class="sidebar-item">
            <RouterLink to="/tutor-egibide/horario-calendario">Horario + Calendario</RouterLink>
          </li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Seguimiento</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">
            <RouterLink to="/tutor-egibide/general">General</RouterLink>
          </li>
          <li class="sidebar-item">            
            <RouterLink to="/tutor-egibide/cuaderno">Cuaderno</RouterLink>
          </li>
        </ul>
      </div>

      
    </nav>

    <!-- Menú para Empresas -->
    <nav v-else-if="userRole === 'tutor_empresa'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3 class="sidebar-title">
          <RouterLink to="/tutor-empresa/inicio">Inicio</RouterLink>
        </h3>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Información</h3>
        <ul class="list-unstyled mb-0">
          <RouterLink to="/tutor-empresa/alumnos-asignados">Alumnos Asignados</RouterLink>

          <li class="sidebar-item"></li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">          
          <RouterLink to="/tutor-empresa/competencias">Competencias</RouterLink>
        </h3>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">
          <RouterLink to="/tutor-empresa/calificacion">Calificación</RouterLink>
        </h3>
      </div>
    </nav>

    <!-- Menú para Admin -->
    <nav v-else-if="userRole === 'admin'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3 class="sidebar-title">
          <RouterLink to="/admin/inicio">Inicio</RouterLink>
        </h3>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Información</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">
            <RouterLink to="/admin/ciclos">Ciclos</RouterLink>
          </li>
          <li class="sidebar-item">
            <RouterLink to="/admin/competencias">Competencias</RouterLink>
          </li>
          <li class="sidebar-item">
            <RouterLink to="/admin/empresas">Empresas</RouterLink>
          </li>
          <li class="sidebar-item">
            <RouterLink to="/admin/alumnos">Alumnos</RouterLink>
          </li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3 class="sidebar-title">Añadir</h3>
        <ul class="list-unstyled mb-0">
          <li class="sidebar-item">
            <RouterLink to="/admin/añadir-ciclos">Ciclos</RouterLink>
          </li>
          <li class="sidebar-item">            
            <RouterLink to="/admin/añadir-competencias">Competencias</RouterLink>
          </li>
          <li class="sidebar-item">
            <RouterLink to="/admin/añadir-empresas">Empresas</RouterLink>
          </li>
          <li class="sidebar-item">            
            <RouterLink to="/admin/añadir-alumnos">Alumnos</RouterLink>
          </li>
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
