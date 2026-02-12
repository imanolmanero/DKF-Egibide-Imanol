<script setup>
import { ref, onMounted } from "vue";
import api from "@/axios"; // ajusta si tu import real es diferente

const ciclos = ref([]);
const loading = ref(false);
const error = ref("");
const ok = ref("");

const form = ref({
  titulo: "",
  descripcion: "",
  fecha_limite: "",
  ciclo_id: "",
});

onMounted(async () => {
  try {
    const { data } = await api.get("/ciclos");
    ciclos.value = data;
  } catch (e) {
    error.value = "No se pudieron cargar los ciclos.";
  }
});

const submit = async () => {
  error.value = "";
  ok.value = "";
  loading.value = true;

  try {
    await api.post("/entregas-cuaderno", {
      titulo: form.value.titulo,
      descripcion: form.value.descripcion || null,
      fecha_limite: form.value.fecha_limite,
      ciclo_id: Number(form.value.ciclo_id),
    });

    ok.value = "Entrega de cuaderno creada correctamente.";
    form.value = { titulo: "", descripcion: "", fecha_limite: "", ciclo_id: "" };
  } catch (e) {
    error.value = "Error creando la entrega. Revisa los campos y permisos.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="container py-3">
    <h2>Crear entrega de cuaderno</h2>

    <div v-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-if="ok" class="alert alert-success">{{ ok }}</div>

    <form @submit.prevent="submit" class="mt-3">
      <div class="mb-3">
        <label class="form-label">Ciclo</label>
        <select v-model="form.ciclo_id" class="form-select" required>
          <option value="" disabled>Selecciona un ciclo...</option>
          <option v-for="c in ciclos" :key="c.id" :value="c.id">
            {{ c.nombre }} <span v-if="c.grupo">({{ c.grupo }})</span>
          </option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Título</label>
        <input v-model="form.titulo" class="form-control" required maxlength="255" />
      </div>

      <div class="mb-3">
        <label class="form-label">Descripción (opcional)</label>
        <textarea v-model="form.descripcion" class="form-control" rows="3" />
      </div>

      <div class="mb-3">
        <label class="form-label">Fecha límite</label>
        <input v-model="form.fecha_limite" type="date" class="form-control" required />
      </div>

      <button class="btn btn-primary" :disabled="loading">
        {{ loading ? "Creando..." : "Crear entrega" }}
      </button>
    </form>
  </div>
</template>
