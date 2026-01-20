<script setup lang="ts">
import type { Competencia } from "@/interfaces/Competencia";
import { useCompetenciasStore } from "@/stores/competencias";
import { onMounted, ref } from "vue";
import Toast from "../Notification/Toast.vue";
import router from "@/router";

const props = defineProps<{
  alumnoId: number;
  tutorEgibide: boolean;
}>();

const competenciaStore = useCompetenciasStore();

const competencias = ref<Competencia[]>([]);
const competenciasCalificadas = ref<Record<number, number>>({});

const isLoading = ref(true);

const tieneCalificacion = (competenciaId: number): boolean => {
  return competenciaId in competenciasCalificadas.value;
};

onMounted(async () => {
  try {
    await competenciaStore.fetchCompetenciasTransversalesByAlumno(
      props.alumnoId,
    );

    competencias.value = competenciaStore.competencias;

    // Cargar las calificaciones existentes
    const calificaciones =
      await competenciaStore.getCalificacionesCompetenciasTransversales(
        props.alumnoId,
      );

    // Transformar array de objetos a Record<number, number>
    if (Array.isArray(calificaciones)) {
      calificaciones.forEach((item: any) => {
        competenciasCalificadas.value[item.competencia_trans_id] = Math.round(
          Number(item.nota),
        );
      });
    }
  } catch (error) {
    console.error("Error al cargar competencias:", error);
  } finally {
    isLoading.value = false;
  }
});

const volver = () => {
  router.back();
};

async function guardarCalificacionesTransversales() {
  let ok = false;

  const payload = Object.entries(competenciasCalificadas.value).map(
    ([competenciaId, calificacion]) => ({
      competencia_id: Number(competenciaId),
      calificacion,
    }),
  );

  ok = await competenciaStore.calificarCompetenciasTransversales(
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
    <p class="mt-3 text-muted">Cargando competencias transversales...</p>
  </div>
  <!-- Sin competencias -->
  <div
    v-else-if="competencias.length === 0"
    class="alert alert-info d-flex align-items-center"
    role="alert"
  >
    <i class="bi bi-info-circle-fill me-2"></i>
    <div>
      La familia profesional del alumno no tiene competencias transversales
      asignadas.
    </div>
  </div>
  <!-- Lista de competencias -->
  <div v-else>
    <ul class="list-group">
      <li
        class="list-group-item my-2 d-flex align-items-center"
        v-for="competencia in competencias"
        :key="competencia.id"
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
      v-if="!props.tutorEgibide"
      @click="guardarCalificacionesTransversales"
    >
      Guardar Calificaciónes Transversales
    </button>
  </div>
</template>

<style scoped></style>
