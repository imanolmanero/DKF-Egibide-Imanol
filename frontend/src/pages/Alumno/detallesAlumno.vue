<script setup lang="ts">
import type { Alumno } from "@/interfaces/Alumno";
import type { Empresa } from "@/interfaces/Empresa";
import { useEmpresasStore } from "@/stores/empresas";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { useTutorEmpresaStore } from "@/stores/tutorEmpresa";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const tutorEmpresaStore = useTutorEmpresaStore();
const empresaStore = useEmpresasStore();



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

let alumno = ref<Alumno | null>(null);

onMounted(async () => {
  try {
    isLoading.value = true;

      await store.value.fetchAlumnosAsignados(tutorId);
    
    alumno.value = store.value.alumnosAsignados.find(a => a.id === alumnoId) || null;
    if (!alumno.value) {
      error.value = "Alumno no encontrado";
    }

  } catch (err) {
    console.error(err);
    error.value = "Error al cargar los datos del alumno";
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
    name: "tutor_empresa-calificacion",
    params: { alumnoId: alumnoId },
  });
};

const irAsignarEmpresa = () => {
  router.push({
    name: "tutor_egibide-alumno_empresa",
    params: { alumnoId: alumnoId },
  });
};

const irAsignarHorasPeriodo = () => {
  router.push({
    name: "tutor_egibide-horas_periodo",
    params: { alumnoId: alumnoId },
    query: { tipoTutor: tipoTutor, tutorId: tutorId },
  });
};

const irSeguimiento = () => {
  router.push({
    name: "tutor_egibide-seguimiento",
    params: { alumnoId: alumnoId },
  });
};

const irCompetencias = () => {
  router.push({
    name: "tutor_egibide-competencias",
    params: { alumnoId: alumnoId },
  });
};

const irCalificacionesEgibide = () => {
  router.push({
    name: "tutor_egibide-calificaciones",
    params: { alumnoId: alumnoId },
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
      <div class="spinner-border" style="color: #81045f;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">
        Cargando información del alumno...
      </p>
    </div>

    <!-- Error al cargar -->
    <div v-else-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver a alumnos
        </button>
      </div>
    </div>

    <!-- Alumno no encontrado -->
    <div v-else-if="!alumno" class="alert alert-warning d-flex align-items-center">
      <i class="bi bi-person-x-fill me-2"></i>
      <div>
        No se encontró información del alumno.
        <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
          Volver
        </button>
      </div>
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
  <div class="col-md-6">
    <div class="info-item">
      <i class="bi bi-telephone-fill text-primary me-2"></i>
      <span class="text-muted">Teléfono:</span>
      <strong class="ms-2">{{ alumno.telefono }}</strong>
    </div>
  </div>
  <div class="col-md-6">
    <div class="info-item">
      <i class="bi bi-geo-alt-fill text-primary me-2"></i>
      <span class="text-muted">Ciudad:</span>
      <strong class="ms-2">{{ alumno.ciudad }}</strong>
    </div>
  </div>
  <div class="col-md-6">
    <div class="info-item">
      <i class="bi bi-geo-alt-fill text-primary me-2"></i>
      <span class="text-muted">Empresa:</span>
      <strong class="ms-2">
        {{ alumno.estancias?.[0]?.empresa?.nombre ?? 'Sin asignar' }}
      </strong>
    </div>
  </div>
  <div class="col-md-6">
    <div class="info-item">
      <i class="bi bi-briefcase-fill text-primary me-2"></i>
      <span class="text-muted">Puesto:</span>
      <strong class="ms-2">{{ alumno.estancias?.[0]?.puesto ?? 'Sin asignar' }}</strong>
    </div>
  </div>
  <div class="col-md-6">
    <div class="info-item">
      <i class="bi bi-clock-fill text-primary me-2"></i>
      <span class="text-muted">Horas totales:</span>
      <strong class="ms-2">
        {{ alumno.estancias?.[0]?.horas_totales ? alumno.estancias?.[0]?.horas_totales + 'h' : 'Sin asignar' }}
      </strong>
    </div>
  </div>
  <div class="col-md-6">
    <div class="info-item">
      <i class="bi bi-calendar-range-fill text-primary me-2"></i>
      <span class="text-muted">Periodo:</span>
      <strong class="ms-2" v-if="alumno.estancias?.[0]?.fecha_inicio && alumno.estancias?.[0]?.fecha_fin">
        {{  formatDate(alumno.estancias?.[0]?.fecha_inicio) }} -
        {{ formatDate(alumno.estancias?.[0]?.fecha_fin) }}
      </strong>
      <strong class="ms-2" v-else>
        Por definir
      </strong>
    </div>
  </div>
</div>

        </div>
      </div>

      <!-- Acciones principales -->
      <div class="row g-3">
        <div class="col-md-6" v-if="tipoTutor === 'empresa'">
          <div class="card h-100 action-card" @click="irACompetencias" role="button" tabindex="0">
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
          <div class="card h-100 action-card" @click="irACalificacion" role="button" tabindex="0">
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
          <div class="card h-100 action-card" @click="irAsignarEmpresa" role="button" tabindex="0">
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
        <!-- Boton horario y calendario -->
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div class="card h-100 action-card" @click="irAsignarHorasPeriodo" role="button" tabindex="0">
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-calendar-plus display-4 text-success"></i>
              </div>
              <h5 class="card-title">Asignar horas y periodo</h5>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>
        <!-- Boton seguimiento -->
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div class="card h-100 action-card" @click="irSeguimiento" role="button" tabindex="0">
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
        <!-- Boton competencias -->
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div class="card h-100 action-card" @click="irCompetencias" role="button" tabindex="0">
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-list-check display-4 text-primary"></i>
              </div>
              <h5 class="card-title">Competencias</h5>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>
        <!-- Boton Calificaciones -->
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div class="card h-100 action-card" @click="irCalificacionesEgibide" role="button" tabindex="0">
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-mortarboard display-4 text-primary"></i>
              </div>
              <h5 class="card-title">Calificaciones</h5>
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
  background: linear-gradient(135deg,
      #81045f 0%,
      #4a90e2 100%);
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
