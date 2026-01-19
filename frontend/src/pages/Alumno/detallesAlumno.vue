<script setup lang="ts">
import type { Alumno } from "@/interfaces/Alumno";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { useTutorEmpresaStore } from "@/stores/tutorEmpresa";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const tutorEmpresaStore = useTutorEmpresaStore();

const alumno = ref<Alumno | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Obtener parámetros de la ruta
const alumnoId = Number(route.params.alumnoId);
const tipoTutor = (route.query.tipoTutor as "egibide" | "empresa") || "empresa";
const tutorId = route.query.tutorId as string;

// Store dinámico
const store = computed(() =>
  tipoTutor === "egibide" ? tutorEgibideStore : tutorEmpresaStore,
);

onMounted(async () => {
  try {
    isLoading.value = true;

    // Si no hay alumnos en el store, cargarlos
    if (
      !store.value.alumnosAsignados ||
      store.value.alumnosAsignados.length === 0
    ) {
      await store.value.fetchAlumnosAsignados(tutorId);
    }

    // Buscar el alumno (comparando números)
    alumno.value =
      store.value.alumnosAsignados.find((a: Alumno) => {
        return Number(a.id) === alumnoId;
      }) || null;

    if (!alumno.value) {
      error.value = "Alumno no encontrado";
      console.error("No se encontró el alumno con ID:", alumnoId);
    }
  } catch (err) {
    console.error("Error al cargar alumno:", err);
    error.value =
      "Error al cargar los datos del alumno: " + (err as Error).message;
  } finally {
    isLoading.value = false;
  }
});

const irACompetencias = () => {
  router.push({
    name: "tutor_empresa-competencias",
    params: { alumnoId: alumnoId },
  });
};

const irACalificacion = () => {
  router.push({
    name: "CalificacionAlumno",
    params: { alumnoId: alumnoId },
    query: { tipoTutor: tipoTutor, tutorId: tutorId },
  });
};

const irAsignarEmpresa = () => {
  router.push({
    name: "tutor_egibide-alumno_empresa",
    params: { alumnoId: alumnoId },
    query: { tipoTutor: tipoTutor, tutorId: tutorId },
  });
};

const irAsignarHorario = () => {
  router.push({
    name: "tutor_egibide-horario_calendario",
    params: { alumnoId: alumnoId },
    query: { tipoTutor: tipoTutor, tutorId: tutorId },
  });
};

const irSeguimiento = () => {
  router.push({
    name: "tutor_egibide-seguimiento",
    params: { alumnoId: alumnoId },
    query: { tipoTutor: tipoTutor, tutorId: tutorId },
  });
};

const irCompetencias = () => {
  router.push({
    name: "tutor_egibide-competencias",
    params: { alumnoId: alumnoId },
    query: { tipoTutor: tipoTutor, tutorId: tutorId },
  });
};

const volver = () => {
  router.back();
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("es-ES", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};
</script>

<template>
  <div class="container mt-4">
    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando información del alumno...</p>
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
          Volver a alumnos
        </button>
      </div>
    </div>

    <!-- Sin alumno -->
    <div v-else-if="!alumno" class="alert alert-warning">
      No se encontró información del alumno
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
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i>
              Alumnos
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ alumno.nombre }} {{ alumno.apellidos }}
          </li>
        </ol>
      </nav>

      <!-- Cabecera del alumno -->
      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar-large me-3">
              <i class="bi bi-person-fill"></i>
            </div>
            <div class="flex-grow-1">
              <h3 class="mb-1">{{ alumno.nombre }} {{ alumno.apellidos }}</h3>
            </div>
          </div>

          <!-- Información adicional -->
          <div class="row g-3 mt-2">
            <div class="col-md-6" v-if="alumno.telefono">
              <div class="info-item">
                <i class="bi bi-telephone-fill text-primary me-2"></i>
                <span class="text-muted">Teléfono:</span>
                <strong class="ms-2">{{ alumno.telefono }}</strong>
              </div>
            </div>
            <div class="col-md-6" v-if="alumno.ciudad">
              <div class="info-item">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                <span class="text-muted">Ciudad:</span>
                <strong class="ms-2">{{ alumno.ciudad }}</strong>
              </div>
            </div>
            <div class="col-md-6" v-if="alumno.pivot?.puesto">
              <div class="info-item">
                <i class="bi bi-briefcase-fill text-primary me-2"></i>
                <span class="text-muted">Puesto:</span>
                <strong class="ms-2">{{ alumno.pivot.puesto }}</strong>
              </div>
            </div>
            <div class="col-md-6" v-if="alumno.pivot?.horas_totales">
              <div class="info-item">
                <i class="bi bi-clock-fill text-primary me-2"></i>
                <span class="text-muted">Horas totales:</span>
                <strong class="ms-2">{{ alumno.pivot.horas_totales }}h</strong>
              </div>
            </div>
            <div
              class="col-12"
              v-if="alumno.pivot?.fecha_inicio && alumno.pivot?.fecha_fin"
            >
              <div class="info-item">
                <i class="bi bi-calendar-range-fill text-primary me-2"></i>
                <span class="text-muted">Periodo:</span>
                <strong class="ms-2">
                  {{ formatDate(alumno.pivot.fecha_inicio) }} -
                  {{ formatDate(alumno.pivot.fecha_fin) }}
                </strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Acciones principales -->
      <div class="row g-3">
        <div class="col-md-6" v-if="tipoTutor === 'empresa'">
          <div
            class="card h-100 action-card"
            @click="irACompetencias"
            role="button"
            tabindex="0"
          >
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-list-check display-4 text-primary"></i>
              </div>
              <h5 class="card-title">Gestionar Competencias</h5>
              <p class="card-text text-muted">
                Asignar y gestionar las competencias del alumno
              </p>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md" v-if="tipoTutor === 'empresa'">
          <div
            class="card h-100 action-card"
            @click="irACalificacion"
            role="button"
            tabindex="0"
          >
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-pencil-square display-4 text-success"></i>
              </div>
              <h5 class="card-title">Calificar</h5>
              <p class="card-text text-muted">
                Evaluar las competencias asignadas al alumno
              </p>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Tutor egibide -->
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div
            class="card h-100 action-card"
            @click="irAsignarEmpresa"
            role="button"
            tabindex="0"
          >
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-building-add display-4 text-success"></i>
              </div>
              <h5 class="card-title">Asignar empresa</h5>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div
            class="card h-100 action-card"
            @click="irAsignarHorario"
            role="button"
            tabindex="0"
          >
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-calendar-plus display-4 text-success"></i>
              </div>
              <h5 class="card-title">Asignar horario y calendario</h5>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div
            class="card h-100 action-card"
            @click="irSeguimiento"
            role="button"
            tabindex="0"
          >
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-chat-left-text display-4 text-primary"></i>
              </div>
              <h5 class="card-title">Seguimiento</h5>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div
            class="card h-100 action-card"
            @click="irCompetencias"
            role="button"
            tabindex="0"
          >
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-eye display-4 text-primary"></i>
              </div>
              <h5 class="card-title">Visualizar competencias</h5>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
  flex-shrink: 0;
}

.action-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.action-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  border-color: var(--bs-primary);
}

.action-card:focus {
  outline: 2px solid var(--bs-primary);
  outline-offset: 2px;
}

.icon-wrapper {
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.breadcrumb-item a {
  color: var(--bs-primary);
}

.breadcrumb-item a:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}

.info-item {
  padding: 0.75rem;
  background-color: #f8f9fa;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
}
</style>
