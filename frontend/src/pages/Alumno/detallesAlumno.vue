<script setup lang="ts">
import type { Alumno } from "@/interfaces/Alumno";
import type { Empresa } from "@/interfaces/Empresa";
import { useEmpresasStore } from "@/stores/empresas";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { useTutorEmpresaStore } from "@/stores/tutorEmpresa";
import { useAuthStore } from "@/stores/auth";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

interface Instructor {
  id: number;
  nombre: string;
  apellidos: string;
  telefono: string | null;
  ciudad: string | null;
  empresa_id: number;
}

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const tutorEgibideStore = useTutorEgibideStore();
const tutorEmpresaStore = useTutorEmpresaStore();
const empresaStore = useEmpresasStore();

const baseURL = import.meta.env.VITE_API_BASE_URL;

const isLoading = ref(true);
const error = ref<string | null>(null);

// Modal de asignar instructor
const showAsignarInstructorModal = ref(false);
const instructoresDisponibles = ref<Instructor[]>([]);
const instructorSeleccionado = ref<number | null>(null);
const instructorActualId = ref<number | null>(null);
const empresaAlumno = ref<Empresa | null>(null);
const isLoadingInstructores = ref(false);
const isSubmittingInstructor = ref(false);
const submitErrorInstructor = ref<string | null>(null);
const submitSuccessInstructor = ref<string | null>(null);

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

// Abrir modal de asignar instructor
const abrirModalAsignarInstructor = async () => {
  submitErrorInstructor.value = null;
  submitSuccessInstructor.value = null;
  instructorSeleccionado.value = null;
  showAsignarInstructorModal.value = true;
  await cargarInstructoresDisponibles();
};

// Cargar instructores de la empresa del alumno
const cargarInstructoresDisponibles = async () => {
  isLoadingInstructores.value = true;
  submitErrorInstructor.value = null;

  try {
    const response = await fetch(
      `${baseURL}/api/alumnos/${alumnoId}/instructores`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      }
    );

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Error al cargar instructores");
    }

    if (!data.success) {
      submitErrorInstructor.value = data.message;
      instructoresDisponibles.value = [];
      empresaAlumno.value = null;
      instructorActualId.value = null;
      return;
    }

    instructoresDisponibles.value = data.instructores || [];
    empresaAlumno.value = data.empresa || null;
    instructorActualId.value = data.instructor_actual_id || null;

    // Si hay instructor actual, preseleccionarlo
    if (instructorActualId.value) {
      instructorSeleccionado.value = instructorActualId.value;
    }

  } catch (err: any) {
    console.error(err);
    submitErrorInstructor.value = err.message || "Error al cargar la lista de instructores";
  } finally {
    isLoadingInstructores.value = false;
  }
};

// Asignar instructor al alumno
const asignarInstructor = async () => {
  submitErrorInstructor.value = null;

  if (!instructorSeleccionado.value) {
    submitErrorInstructor.value = "Debes seleccionar un instructor";
    return;
  }

  isSubmittingInstructor.value = true;

  try {
    const response = await fetch(`${baseURL}/api/alumnos/asignar-instructor`, {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        alumno_id: alumnoId,
        instructor_id: instructorSeleccionado.value,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Error al asignar el instructor");
    }

    submitSuccessInstructor.value = "Instructor asignado exitosamente";

    // Esperar un momento y cerrar modal
    setTimeout(async () => {
      showAsignarInstructorModal.value = false;
      // Recargar datos del alumno
      await store.value.fetchAlumnosAsignados(tutorId);
      alumno.value = store.value.alumnosAsignados.find(a => a.id === alumnoId) || null;
      submitSuccessInstructor.value = null;
    }, 1500);

  } catch (err: any) {
    console.error(err);
    submitErrorInstructor.value = err.message || "Error al asignar el instructor";
  } finally {
    isSubmittingInstructor.value = false;
  }
};

// Cerrar modal de instructor
const cerrarModalAsignarInstructor = () => {
  if (!isSubmittingInstructor.value) {
    showAsignarInstructorModal.value = false;
    instructorSeleccionado.value = null;
    instructoresDisponibles.value = [];
    submitErrorInstructor.value = null;
    submitSuccessInstructor.value = null;
  }
};

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

// Nombre del instructor actual
const nombreInstructorActual = computed(() => {
  if (!instructorActualId.value || instructoresDisponibles.value.length === 0) {
    return null;
  }
  const instructor = instructoresDisponibles.value.find(i => i.id === instructorActualId.value);
  return instructor ? `${instructor.nombre} ${instructor.apellidos}` : null;
});
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
                  {{ formatDate(alumno.estancias?.[0]?.fecha_inicio) }} -
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
              <h5 v-if="alumno.pivot?.empresa_id === null" class="card-title">
                Asignar empresa
              </h5>
              <h5 v-else class="card-title">
                Cambiar empresa
              </h5>
              <div class="mt-3">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- NUEVO: Botón Asignar Instructor -->
        <div class="col-md" v-if="tipoTutor === 'egibide'">
          <div class="card h-100 action-card" @click="abrirModalAsignarInstructor" role="button" tabindex="0">
            <div class="card-body text-center p-4">
              <div class="icon-wrapper mb-3">
                <i class="bi bi-person-badge display-4 text-warning"></i>
              </div>
              <h5 class="card-title">Asignar Instructor</h5>
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

    <!-- Modal Asignar Instructor -->
    <div
      class="modal fade"
      :class="{ show: showAsignarInstructorModal }"
      :style="{ display: showAsignarInstructorModal ? 'block' : 'none' }"
      tabindex="-1"
      @click.self="cerrarModalAsignarInstructor"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-person-badge-fill me-2"></i>
              Asignar Instructor
            </h5>
            <button
              type="button"
              class="btn-close"
              @click="cerrarModalAsignarInstructor"
              :disabled="isSubmittingInstructor"
            ></button>
          </div>
          <div class="modal-body">
            <!-- Mensajes de error/éxito -->
            <div v-if="submitErrorInstructor" class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              {{ submitErrorInstructor }}
            </div>
            <div v-if="submitSuccessInstructor" class="alert alert-success" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              {{ submitSuccessInstructor }}
            </div>

            <!-- Loading instructores -->
            <div v-if="isLoadingInstructores" class="text-center py-3">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando instructores...</span>
              </div>
              <p class="mt-2 text-muted">Cargando instructores...</p>
            </div>

            <!-- Formulario de selección -->
            <div v-else-if="instructoresDisponibles.length > 0">
              <!-- Información de la empresa -->
              <div v-if="empresaAlumno" class="alert alert-info mb-3">
                <i class="bi bi-building me-2"></i>
                <strong>Empresa:</strong> {{ empresaAlumno.nombre }}
              </div>

              <!-- Instructor actual -->
              <div v-if="nombreInstructorActual" class="alert alert-secondary mb-3">
                <i class="bi bi-person-check-fill me-2"></i>
                <strong>Instructor actual:</strong> {{ nombreInstructorActual }}
              </div>

              <div class="mb-3">
                <label for="instructorSelect" class="form-label"
                  >Seleccionar Instructor *</label
                >
                <select
                  class="form-select"
                  id="instructorSelect"
                  v-model="instructorSeleccionado"
                  :disabled="isSubmittingInstructor"
                  required
                >
                  <option :value="null" disabled>
                    -- Selecciona un instructor --
                  </option>
                  <option
                    v-for="instructor in instructoresDisponibles"
                    :key="instructor.id"
                    :value="instructor.id"
                  >
                    {{ instructor.nombre }} {{ instructor.apellidos }}
                    <span v-if="instructor.id === instructorActualId">
                      (Actual)
                    </span>
                  </option>
                </select>
              </div>
            </div>

            <!-- Sin instructores disponibles -->
            <div v-else-if="!isLoadingInstructores && !submitErrorInstructor" class="alert alert-warning">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              No hay instructores disponibles en la empresa asignada.
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="cerrarModalAsignarInstructor"
              :disabled="isSubmittingInstructor"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="asignarInstructor"
              :disabled="isSubmittingInstructor || !instructorSeleccionado || instructoresDisponibles.length === 0"
            >
              <span
                v-if="isSubmittingInstructor"
                class="spinner-border spinner-border-sm me-2"
              ></span>
              {{ isSubmittingInstructor ? "Asignando..." : "Asignar Instructor" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal backdrop -->
    <div
      v-if="showAsignarInstructorModal"
      class="modal-backdrop fade show"
    ></div>
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

/* Modal styles */
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
  display: block !important;
}

.btn-primary {
  background-color: #81045f;
  border-color: #81045f;
}

.btn-primary:hover {
  background-color: #6a0350;
  border-color: #6a0350;
}
</style>