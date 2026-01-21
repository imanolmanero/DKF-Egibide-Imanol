<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { useAlumnosStore } from "@/stores/alumnos";
import { useAuthStore } from "@/stores/auth";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";

// Route & Router
const route = useRoute();
const router = useRouter();

// Stores
const tutorEgibideStore = useTutorEgibideStore();
const { message, messageType } = storeToRefs(tutorEgibideStore);
const alumnosStore = useAlumnosStore();
const authStore = useAuthStore();

// Alumno y carga
const alumno = ref<any>(null);
const isLoading = ref(true);

// Params
const alumnoId = Number(route.params.alumnoId);
const tutorId = route.query.tutorId as string;

// Función para descargar entregas
function urlArchivo(id: number) {
  return `http://localhost:8000/api/entregas/${id}/archivo?token=${authStore.token}`;
}

function descargar(id: number) {
  const a = document.createElement("a");
  a.href = urlArchivo(id);
  a.download = "";
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

// Fetch inicial
onMounted(async () => {
  try {
    isLoading.value = true;

    // Traer alumnos asignados si no están
    if (!tutorEgibideStore.alumnosAsignados.length) {
      if (!tutorId) throw new Error("Falta tutorId en la query");
      await tutorEgibideStore.fetchAlumnosAsignados(tutorId);
    }

    // Buscar alumno
    alumno.value =
      tutorEgibideStore.alumnosAsignados.find(
        (a) => Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId
      ) || null;

    if (!alumno.value) {
      console.warn("Alumno no encontrado en el store");
      return;
    }

    // Traer entregas del alumno
    await alumnosStore.fetchEntregasAlumno(alumnoId);
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
});

// Navegación
const volver = () => router.back();
const volverAlumno = () => {
  router.back();
  router.back();
};
const volverAlumnos = () => {
  router.back();
  router.back();
  router.back();
};

function formatDate(fecha: string) {
  const [y, m, d] = fecha.split("-");
  return `${d}/${m}/${y}`;
}
</script>

<template>
  <Toast v-if="message" :message="message" :messageType="messageType" />

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

    <!-- Contenido Cuaderno -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <i class="bi bi-arrow-left me-1"></i>
            <a href="#" @click.prevent="volverAlumnos">Alumnos</a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volverAlumno">
              {{ alumno?.nombre }} {{ alumno?.apellidos }}
            </a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver">
              Seguimiento
            </a>
          </li>
          <li class="breadcrumb-item active text-capitalize">Cuaderno</li>
        </ol>
      </nav>

      <!-- Tabla de entregas -->
      <div class="card shadow-sm mb-4">
        <div class="card-header">
          <h3 class="mb-1">Cuaderno</h3>
        </div>
        <div class="card-body p-0">
          <div v-if="alumnosStore.loadingEntregas" class="p-3">
            <div class="spinner-border spinner-border-sm"></div>
            <span class="ms-2">Cargando...</span>
          </div>

          <div v-else-if="alumnosStore.entregas.length === 0" class="p-3 text-muted">
            Todavía no hay entregas.
          </div>

          <table v-else class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 160px;">Archivo</th>
                <th style="width: 160px;">Fecha</th>
                <th style="width: 220px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in alumnosStore.entregas" :key="e.id">
                <td class="text-truncate" style="max-width: 500px;">{{ e.archivo }}</td>
                <td>{{ formatDate(e.fecha) }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" @click="descargar(e.id)">
                    Descargar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
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
  text-decoration: underline !important;
}
</style>
