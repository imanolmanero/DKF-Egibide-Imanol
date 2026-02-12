<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import type { TutorEgibide } from "@/interfaces/TutorEgibide";
import { useAlumnosStore } from "@/stores/alumnos";
import { useCiclosStore } from "@/stores/ciclos";
import { onMounted, ref, watch } from "vue";

const alumnoStore = useAlumnosStore();
const cicloStore = useCiclosStore();

const nombre = ref<string>("");
const apellidos = ref<string>("");
const telefono = ref<number>(0);
const poblacion = ref<string>("");
const ciclo = ref<number>(0);
const tutor = ref<number>(0);

const listaCiclos = ref<any[]>([]);
const listaTutores = ref<TutorEgibide[]>([]);

watch(ciclo, async (newVal) => {
  if (!newVal) {
    listaTutores.value = [];
    tutor.value = 0;
    return;
  }

  await cicloStore.fetchTutoresByCiclos(newVal);
  listaTutores.value = cicloStore.tutores;
  tutor.value = 0;
});

onMounted(async () => {
  await cicloStore.fetchCiclos();
  listaCiclos.value = cicloStore.ciclos;
});

async function agregarAlumno() {

  let ok = false;

  ok = await alumnoStore.createAlumno(
    nombre.value,
    apellidos.value,
    telefono.value,
    poblacion.value,
    tutor.value,
  );

  if (ok) {
    resetForms();
  }
}

function resetForms() {
  nombre.value = "";
  apellidos.value = "";
  telefono.value = 0;
  poblacion.value = "";
  ciclo.value = 0;
  tutor.value = 0;
}
</script>

<template>
  <h2>NUEVO ALUMNO</h2>
  <hr />
  <Toast
    v-if="alumnoStore.message"
    :message="alumnoStore.message"
    :messageType="alumnoStore.messageType"
  />
  <form @submit.prevent="agregarAlumno" class="row">
    <div class="col">
      <div class="row-cols-1">
        <div class="mb-3 col-10">
          <label for="nombre" class="form-label">Nombre:</label>
          <input
            type="text"
            class="form-control"
            placeholder="Markel"
            v-model="nombre"
            aria-label="nombre"
            id="nombre"
            pattern="^[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)*$"
            required
          />
        </div>

        <div class="mb-3 col-10">
          <label for="apellido" class="form-label">Apellidos:</label>
          <input
            type="text"
            class="form-control"
            placeholder="Goikoetxea"
            v-model="apellidos"
            aria-label="apellido"
            id="apellido"
            pattern="^[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)*$"
            required
          />
        </div>

        <div class="mb-3 col-4">
          <label for="telefono" class="form-label">Telefono:</label>
          <input
            type="tel"
            class="form-control"
            placeholder="644202601"
            v-model="telefono"
            aria-label="telefono"
            id="telefono"
            pattern="^\d{9}$"
            required
          />
        </div>

        <div class="mb-3 col-10">
          <label for="poblacion" class="form-label">Población:</label>
          <input
            type="text"
            class="form-control"
            placeholder="Vitoria-Gasteiz, Agurain..."
            v-model="poblacion"
            aria-label="poblacion"
            id="poblacion"
            required
          />
        </div>
      </div>
    </div>
    <div class="col">
      <div class="row-cols-1">
        <div class="mb-3 col-8">
          <label for="ciclo" class="form-label">Ciclo:</label>
          <select
            class="form-select"
            v-model.number="ciclo"
            id="ciclo"
            required
          >
            <option :value="0" disabled>-- Selecciona una opción --</option>
            <option v-for="c in listaCiclos" :key="c.id" :value="c.id">
              {{ c.nombre }}
            </option>
          </select>
        </div>

        <div class="mb-3 col-8">
          <label for="tutor" class="form-label">Tutor:</label>
          <select
            class="form-select"
            v-model.number="tutor"
            id="tutor"
            required
          >
            <option :value="0" disabled>-- Selecciona una opción --</option>
            <option v-for="t in listaTutores" :key="t.id" :value="t.id">
              {{ t.nombre }} {{ t.apellidos }}
            </option>
          </select>
        </div>
      </div>
    </div>
    <div class="row">
      <button type="submit" class="btn btn-primary col-2 ">Agregar</button>
    </div>
  </form>
</template>

<style scoped></style>
