<script setup>
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

const userRole = computed(() => authStore.currentUser?.role);
const userId = computed(() => authStore.currentUser.id);

</script>

<template>
  <aside class="sidebar island">
    <!-- Menú para Alumnos -->
    <nav v-if="userRole === 'alumno'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3>
          <RouterLink to="/alumno/inicio" class="sidebar-title"
            >Inicio</RouterLink
          >
        </h3>
      </div>

      <div class="sidebar-section">
        <h3>
          <RouterLink to="/alumno/informacion" class="sidebar-title"
            >Información</RouterLink
          >
        </h3>
        <ul class="list-unstyled mb-0">
          <li>
            <RouterLink to="/alumno/empresa" class="sidebar-item"
              >Empresa</RouterLink
            >
          </li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3>
          <RouterLink to="/alumno/seguimiento" class="sidebar-title"
            >Entrega</RouterLink
          >
        </h3>
      </div>

      <div class="sidebar-section">
        <h3>
          <RouterLink :to="`/alumno/${userId}/calificacion`" class="sidebar-title"
            >Calificación</RouterLink
          >
        </h3>
      </div>
    </nav>

    <!-- Menú para Tutores -->
    <nav v-else-if="userRole === 'tutor_egibide'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3>
          <RouterLink to="/tutor-egibide/inicio" class="sidebar-title"
            >Inicio</RouterLink
          >
        </h3>
      </div>

      <div class="sidebar-section">
        <h3>
          <RouterLink to="/tutor-egibide/informacion" class="sidebar-title"
            >Información</RouterLink
          >
        </h3>

        <ul class="list-unstyled mb-0">
          <li>
            <RouterLink to="/tutor-egibide/alumnos" class="sidebar-item"
              >Mis Alumnos</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/tutor-egibide/empresas" class="sidebar-item"
              >Empresas</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/tutor-egibide/grados" class="sidebar-item"
              >Mis Grados</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/tutor-egibide/entregas-cuaderno/nueva" class="sidebar-item">
              Crear entrega cuaderno
            </RouterLink>
          </li>
          <li>
            <RouterLink to="/tutor-egibide/gestionar-grados" class="sidebar-item"
              >Gestionar mis Grados</RouterLink
            >
          </li>
        </ul>
      </div>
    </nav>

    <!-- Menú para Empresas -->
    <nav v-else-if="userRole === 'tutor_empresa'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3>
          <RouterLink to="/tutor-empresa/inicio" class="sidebar-title"
            >Inicio</RouterLink
          >
        </h3>
      </div>

      <div class="sidebar-section">
        <h3>
          <RouterLink to="/tutor-empresa/informacion" class="sidebar-title"
            >Información</RouterLink
          >
        </h3>
        <ul class="list-unstyled mb-0">
          <li>
            <RouterLink
              to="/tutor-empresa/alumnos-asignados"
              class="sidebar-item"
              >Alumnos Asignados</RouterLink
            >
          </li>
        </ul>
      </div>
    </nav>

    <!-- Menú para Admin -->
    <nav v-else-if="userRole === 'admin'" class="sidebar-nav">
      <div class="sidebar-section">
        <h3>
          <RouterLink to="/admin/inicio" class="sidebar-title"
            >Inicio</RouterLink
          >
        </h3>
      </div>

      <div class="sidebar-section">
        <h3>
          <RouterLink to="/admin/informacion" class="sidebar-title"
            >Información</RouterLink
          >
        </h3>        <ul class="list-unstyled mb-0">
          <li>
            <RouterLink to="/admin/ciclos" class="sidebar-item"
              >Ciclos</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/admin/competencias" class="sidebar-item"
              >Competencias</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/admin/empresas" class="sidebar-item"
              >Empresas</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/admin/alumnos" class="sidebar-item"
              >Alumnos</RouterLink
            >
          </li>
        </ul>
      </div>

      <div class="sidebar-section">
        <h3>
          <RouterLink to="/admin/agregar" class="sidebar-title"
            >Agregar</RouterLink
          >
        </h3>          <ul class="list-unstyled mb-0">
          <li>
            <RouterLink to="/admin/nuevo-ciclo" class="sidebar-item"
              >Ciclo</RouterLink
            >
          </li>
           <li>
            <RouterLink to="/admin/nuevo-resultado" class="sidebar-item"
              >Resultado de Aprendizaje</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/admin/nueva-competencia" class="sidebar-item"
              >Competencia</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/admin/nueva-empresa" class="sidebar-item"
              >Empresa</RouterLink
            >
          </li>
          <li>
            <RouterLink to="/admin/nuevo-alumno" class="sidebar-item"
              >Alumno</RouterLink
            >
          </li>
             <li>
            <RouterLink to="/admin/importar" class="sidebar-item"
              >Importar</RouterLink
            >
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
