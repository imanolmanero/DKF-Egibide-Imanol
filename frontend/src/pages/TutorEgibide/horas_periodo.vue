<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import type { Alumno } from "@/interfaces/Alumno";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const { message, messageType } = storeToRefs(tutorEgibideStore);

const alumno = ref<Alumno | null>(null);
const isLoading = ref(true);

const calendarioInicio = ref("");
const calendarioFin = ref("");
const horasTotales = ref<number | null>(null);

const alumnoId = Number(route.params.alumnoId);

onMounted(() => {
  alumno.value =
    tutorEgibideStore.alumnosAsignados.find(
      (a) => Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId
    ) || null;

  isLoading.value = false;
});

const volver = () => router.back();
const volverAlumnos = () => {
  router.back();
  router.back();
};

const guardarHorario = async () => {
  const ok = await tutorEgibideStore.guardarHorarioAlumno(
    alumnoId,
    calendarioInicio.value,
    calendarioFin.value,
    horasTotales.value!
  );

  if (ok) {
    // Mostrar toast primero
    setTimeout(() => {
      volver();
    }, 1000);
  }
};
</script>

<template>
  <Toast
    v-if="message"
    :message="message"
    :messageType="messageType"
  />

  <div class="container mt-4">
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando datos del alumno...</p>
    </div>

    <div v-else-if="!alumno" class="alert alert-danger d-flex align-items-center">
      <div>Alumno no encontrado</div>
      <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">Volver</button>
    </div>

    <div v-else>
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <i class="bi bi-arrow-left me-1"></i>
            <a href="#" @click.prevent="volverAlumnos">Alumnos</a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver">
              {{ alumno?.nombre }} {{ alumno?.apellidos }}
            </a>
          </li>
          <li class="breadcrumb-item active">Asignar horas y periodo</li>
        </ol>
      </nav>

      <div class="card shadow-sm">
        <div class="card-header">
          <h3 class="mb-1">Horas y periodo</h3>
        </div>

        <div class="card-body">
          <p>Introduce las horas y periodo del alumno <b>{{ alumno?.nombre }}</b></p>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Fecha inicio *</label>
              <input type="date" class="form-control" v-model="calendarioInicio" />
            </div>

            <div class="col-md-6 mb-3">
              <label>Fecha fin</label>
              <input type="date" class="form-control" v-model="calendarioFin" />
            </div>
          </div>

          <div class="mb-3">
            <label>Horas totales *</label>
            <input type="number" min="1" class="form-control" v-model="horasTotales" />
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary" @click="volver">Cancelar</button>
            <button class="btn btn-primary" @click="guardarHorario">Guardar</button>
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
