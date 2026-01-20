<script setup lang="ts">
import type { Competencia } from "@/interfaces/Competencia";
import { useCompetenciasStore } from "@/stores/competencias";
import { onMounted, ref } from "vue";
import Toast from "../Notification/Toast.vue";
import router from "@/router";

const props = defineProps<{
  alumnoId: number;
  asignar: boolean;
  tutorEgibide: boolean;
}>();

const competenciaStore = useCompetenciasStore();

const competencias = ref<Competencia[]>([]);
const competenciasSeleccionadas = ref<number[]>([]);
const competenciasCalificadas = ref<Record<number, number>>({});

const isLoading = ref(true);

const tieneCalificacion = (competenciaId: number): boolean => {
  return competenciaId in competenciasCalificadas.value;
};

onMounted(async () => {
  try {
    await competenciaStore.fetchCompetenciasTecnicasByAlumno(props.alumnoId);
    competencias.value = competenciaStore.competencias;

    // Cargar las calificaciones existentes
    const calificaciones =
      await competenciaStore.getCalificacionesCompetenciasTecnicas(
        props.alumnoId,
      );

    // Transformar array de objetos a Record<number, number>
    if (Array.isArray(calificaciones)) {
      calificaciones.forEach((item: any) => {
        const competenciaId = item.competencia_tec_id;
        competenciasCalificadas.value[competenciaId] = Math.round(
          Number(item.nota),
        );

        // Si hay asignación, marcar como seleccionada en los checkboxes
        if (props.asignar) {
          competenciasSeleccionadas.value.push(competenciaId);
        }
      });
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

async function guardar() {
  let ok = false;

  ok = await competenciaStore.asignarCompetenciasTecnica(
    props.alumnoId,
    competenciasSeleccionadas.value,
  );

  if (ok) {
    setTimeout(() => {
      volver();
    }, 1000);
  }
}

async function guardarCalificacionesTecnicas() {
  let ok = false;

  const payload = Object.entries(competenciasCalificadas.value).map(
    ([competenciaId, calificacion]) => ({
      competencia_id: Number(competenciaId),
      calificacion,
    }),
  );

  ok = await competenciaStore.calificarCompetenciasTecnicas(
    props.alumnoId,
    payload,
  );

  if (ok) {
    setTimeout(() => {
      volver();
    }, 1000);
  }
}
</script>

<template>
  <Toast
    v-if="competenciaStore.message"
    :message="competenciaStore.message"
    :messageType="competenciaStore.messageType"
  />

  <!-- Loading -->
  <div v-if="isLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="mt-3 text-muted">Cargando competencias técnicas...</p>
  </div>
  <!-- Sin competencias -->
  <div
    v-else-if="competencias.length === 0"
    class="alert alert-info d-flex align-items-center"
    role="alert"
  >
    <i class="bi bi-info-circle-fill me-2"></i>
    <div>El ciclo del alumno no tiene competencias asignadas.</div>
  </div>
  <!-- Lista de competencias -->
  <div v-else>
    <ul class="list-group">
      <li
        class="list-group-item my-2"
        v-for="competencia in competencias"
        :key="competencia.id"
        v-if="props.asignar"
      >
        <input
          class="form-check-input me-1"
          type="checkbox"
          :id="`competencia-${competencia.id}`"
          :value="competencia.id"
          v-model="competenciasSeleccionadas"
          :disabled="tieneCalificacion(competencia.id)"
        />

        <label
          class="form-check-label stretched-link"
          :for="`competencia-${competencia.id}`"
          :class="{ 'text-muted': tieneCalificacion(competencia.id) }"
        >
          {{ competencia.descripcion }}
          <small v-if="tieneCalificacion(competencia.id)" class="text-muted">
            (ya tiene calificación)
          </small>
        </label>
      </li>
      <li
        class="list-group-item my-2 d-flex align-items-center"
        v-for="competencia in competencias"
        :key="competencia.id"
        v-if="!props.asignar"
      >
        <label :for="`competencia-${competencia.id}`" class="mx-1">
          {{ competencia.descripcion }}
          <i class="bi bi-arrow-right mr-1"></i>
          <b> Calificación:</b>
        </label>
        <select
          class="form-select form-select-sm bg-light"
          aria-label="Calificación competencias"
          style="width: 8rem"
          :id="`competencia-${competencia.id}`"
          v-model.number="competenciasCalificadas[competencia.id]"
          required
          :disabled="props.tutorEgibide"
        >
          <option v-if="!tieneCalificacion(competencia.id)" :value="null">
            Sin calificar
          </option>
          <option :value="1">1</option>
          <option :value="2">2</option>
          <option :value="3">3</option>
          <option :value="4">4</option>
        </select>
      </li>
    </ul>
    <button
      class="btn btn-primary"
      v-if="props.asignar && !props.tutorEgibide"
      @click="guardar"
    >
      Guardar
    </button>
    <button
      class="btn btn-primary"
      v-if="!props.asignar && !props.tutorEgibide"
      @click="guardarCalificacionesTecnicas"
    >
      Guardar Calificación Técnicas
    </button>
  </div>
</template>

<style scoped>
label {
  cursor: pointer;
}
</style>
