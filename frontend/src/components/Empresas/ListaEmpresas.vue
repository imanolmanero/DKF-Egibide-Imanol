<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { useEmpresasStore } from "@/stores/empresas";
import { onMounted, ref } from "vue";
import Toast from "../Notification/Toast.vue";
import router from "@/router";

const props = defineProps<{
  alumnoId: number;
}>();

const empresaStore = useEmpresasStore();

const empresas = ref<Empresa[]>([]);
const empresaSeleccionada = ref<number>(0);
const isLoading = ref(true);

onMounted(async () => {
  try {
    //await empresaStore.fetchEmpresasByCiclo(props.alumnoId);
    await empresaStore.fetchEmpresas();
    empresas.value = empresaStore.empresas;
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

  ok = await empresaStore.asignarEmpresa(
    props.alumnoId,
    empresaSeleccionada.value,
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
    v-if="empresaStore.message"
    :message="empresaStore.message"
    :messageType="empresaStore.messageType"
  />

  <!-- Loading -->
  <div v-if="isLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="mt-3 text-muted">Cargando empresas...</p>
  </div>
  <!-- Sin competencias -->
  <div
    v-else-if="empresas.length === 0"
    class="alert alert-info d-flex align-items-center"
    role="alert"
  >
    <i class="bi bi-info-circle-fill me-2"></i>
    <div>El ciclo del alumno no tiene empresas asignadas.</div>
  </div>
  <!-- Lista de competencias -->
  <div v-else>
    <ul class="list-group">
      <li
        class="list-group-item my-2"
        v-for="empresa in empresas"
        :key="empresa.id"
      >
        <input
          class="form-check-input me-1"
          type="radio"
          name="empresas"
          :id="`empresa-${empresa.id}`"
          :value="empresa.id"
          v-model="empresaSeleccionada"
        />

        <label
          class="form-check-label stretched-link"
          :for="`empresa-${empresa.id}`"
        >
          {{ empresa.nombre }}
        </label>
      </li>
    </ul>
    <button class="btn btn-primary" @click="guardar">Guardar</button>
  </div>
</template>

<style scoped>
label {
  cursor: pointer;
}
</style>
