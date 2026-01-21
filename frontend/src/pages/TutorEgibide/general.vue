<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { useSeguimientosStore } from "@/stores/seguimientos";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const { message, messageType } = storeToRefs(tutorEgibideStore);
const seguimientosStore = useSeguimientosStore();

const alumno = ref<any>(null);
const isLoading = ref(true);

const alumnoId = Number(route.params.alumnoId);
const tutorId = route.query.tutorId as string;

onMounted(async () => {
  try {
    isLoading.value = true;

    if (!tutorEgibideStore.alumnosAsignados.length) {
      if (!tutorId) throw new Error("Falta tutorId en la query");
      await tutorEgibideStore.fetchAlumnosAsignados(tutorId);
    }

    alumno.value =
      tutorEgibideStore.alumnosAsignados.find(
        (a) => Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId
      ) || null;

    if (!alumno.value) {
      console.warn("Alumno no encontrado en el store");
      return;
    }

    await seguimientosStore.fetchSeguimientos(alumnoId);
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
});

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

    <!-- Contenido -->
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
          <li class="breadcrumb-item active text-capitalize">General</li>
        </ol>
      </nav>

      <!-- Lista de seguimientos -->
      <div class="card shadow-sm">
        <div class="card-header">
          <h3 class="mb-1 text-capitalize">General</h3>
        </div>
        <div class="card-body">
          <ul class="list-group">
            <li
              v-if="seguimientosStore.seguimientos.length === 0"
              class="list-group-item text-muted text-center"
            >
              No hay seguimientos aún
            </li>
            <li
              v-else
              v-for="seguimiento in seguimientosStore.seguimientos"
              :key="seguimiento.id"
              class="list-group-item"
            >
              <strong>{{ seguimiento.fecha }}</strong> — {{ seguimiento.descripcion }}
            </li>
          </ul>
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
