<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const { message, messageType } = storeToRefs(tutorEgibideStore);

const alumno = ref<any>(null);
const isLoading = ref(true);

const alumnoId = Number(route.params.alumnoId);
const tutorId = route.query.tutorId as string;

onMounted(async () => {
  try {
    isLoading.value = true;

    // Si el store no tiene alumnos, hacemos fetch
    if (!tutorEgibideStore.alumnosAsignados.length) {
      await tutorEgibideStore.fetchAlumnosAsignados(tutorId);
    }

    alumno.value =
      tutorEgibideStore.alumnosAsignados.find(
        (a) => Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId
      ) || null;

    if (!alumno.value) {
      console.warn("Alumno no encontrado en el store");
    }
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
});

// Navegación
const volver = () => router.back();
const volverAlumnos = () => {
  router.back();
  router.back();
};

const irSeguimientoGeneral = () => {
  router.push({
    name: "tutor_egibide-seguimiento-general",
    params: { alumnoId },
  });
};

const irSeguimientoCuaderno = () => {
  router.push({
    name: "tutor_egibide-seguimiento-cuaderno",
    params: { alumnoId },
  });
};
</script>

<template>
  <Toast
    v-if="message"
    :message="message"
    :messageType="messageType"
  />

  <div class="container mt-4">
    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando datos del alumno...</p>
    </div>

    <!-- Alumno no encontrado -->
    <div v-else-if="!alumno" class="alert alert-danger d-flex align-items-center">
      <div>Alumno no encontrado</div>
      <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">Volver</button>
    </div>

    <!-- Contenido -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <i class="bi bi-arrow-left me-1"></i>
            <a href="#" @click.prevent="volverAlumnos">Alumnos</a>
          </li>
          <li class="breadcrumb-item active">
            <a href="#" @click.prevent="volver">
              {{ alumno?.nombre }} {{ alumno?.apellidos }}
            </a>
          </li>
          <li class="breadcrumb-item active">Seguimiento</li>
        </ol>
      </nav>

      <div class="card shadow-sm mb-4">
        <div class="card-header">
          <h3 class="mb-1">Seguimiento</h3>
        </div>

        <div class="card-body">
          <div class="row g-3">
            <!-- General -->
            <div class="col-md-6">
              <div
                class="card h-100 action-card"
                @click="irSeguimientoGeneral"
                role="button"
                tabindex="0"
              >
                <div class="card-body text-center p-4">
                  <div class="icon-wrapper mb-3">
                    <i class="bi bi-bar-chart-fill display-4 text-primary"></i>
                  </div>
                  <h5 class="card-title">General</h5>
                  <p class="card-text text-muted">
                    Resumen general del seguimiento del alumno
                  </p>
                  <div class="mt-3">
                    <i class="bi bi-arrow-right-circle"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cuaderno -->
            <div class="col-md-6">
              <div
                class="card h-100 action-card"
                @click="irSeguimientoCuaderno"
                role="button"
                tabindex="0"
              >
                <div class="card-body text-center p-4">
                  <div class="icon-wrapper mb-3">
                    <i class="bi bi-journal-text display-4 text-success"></i>
                  </div>
                  <h5 class="card-title">Cuaderno</h5>
                  <p class="card-text text-muted">
                    Detalle de actividades y notas del alumno
                  </p>
                  <div class="mt-3">
                    <i class="bi bi-arrow-right-circle"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.action-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  border-radius: 0.5rem;
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
</style>
