<script setup lang="ts">
import ListaCompetenciasTecnicas from "@/components/Competencias/ListaCompetenciasTecnicas.vue";
import ListaCompetenciasTransversales from "@/components/Competencias/ListaCompetenciasTransversales.vue";
import type { Alumno } from "@/interfaces/Alumno";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();

const alumno = ref<Alumno | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Obtener parámetros de la ruta
const alumnoId = Number(route.params.alumnoId);

onMounted(async () => {
  try {
    // Buscar el alumno
    alumno.value =
      tutorEgibideStore.alumnosAsignados.find((a: Alumno) => {
        return Number(a.pivot?.alumno_id) === alumnoId;
      }) || null;

    if (!alumno.value) {
      error.value = "Alumno no encontrado";
      console.error("No se encontró el alumno con ID:", alumnoId);
    }
  } catch (error) {
    console.error("Error al cargar alumnos:", error);
  } finally {
    isLoading.value = false;
  }
});

const volver = () => {
  router.back();
};

const volverAlumnos = () => {
  router.back();
  router.back();
};
</script>

<template>
  <div class="container mt-4">
    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando competencias del alumno...</p>
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="alert alert-danger d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver a alumno
        </button>
      </div>
    </div>

    <!-- Sin alumno -->
    <div v-else-if="!alumno" class="alert alert-warning">
      No se encontró conpetencias del alumno
      <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
        Volver
      </button>
    </div>

    <!-- Contenido principal -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a
              href="#"
              @click.prevent="volverAlumnos"
              class="text-decoration-none"
            >
              <i class="bi bi-arrow-left"></i>
              Alumnos
            </a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              {{ alumno.nombre }} {{ alumno.apellidos }}
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Competencias
          </li>
        </ol>
      </nav>

      <!-- Cabecera del alumno -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header">
          <h3 class="mb-1">Competencias</h3>
        </div>
        <div class="card-body">
          <p>
            Estos son la competencias que el alumno
            <b>{{ alumno.nombre }} {{ alumno.apellidos }}</b> va a trabajar
            durante su estancia.
          </p>
          <div class="row mb-2">
            <h5>Técnicas</h5>
            <ListaCompetenciasTecnicas
              :alumnoId="alumnoId"
              :asignar="false"
              :tutor-egibide="true"
            />
          </div>
          <hr />
          <div class="row mb-2">
            <h5>Transversales</h5>
            <ListaCompetenciasTransversales
              :alumno-id="alumnoId"
              :tutor-egibide="true"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.breadcrumb-item a {
  color: var(--bs-primary);
}

.breadcrumb-item a:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}
</style>
