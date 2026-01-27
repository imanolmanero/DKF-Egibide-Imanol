<script setup lang="ts">
import type { Ciclo } from "@/interfaces/Ciclo";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useFamiliaProfesionalesStore } from "@/stores/familiasProfesionales";
import { ref, onMounted, computed } from "vue";
import UploadCiclosCSV from "@/components/Ciclos/UploadCiclosCSV.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const familiasStore = useFamiliaProfesionalesStore();

const baseURL = import.meta.env.VITE_API_BASE_URL;

const ciclo = ref<Ciclo | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Obtener parámetro de la ruta
const cicloId = Number(route.params.cicloId);

// Computed para obtener el nombre de la familia profesional
const nombreFamiliaProfesional = computed(() => {
  if (!ciclo.value || !ciclo.value.familia_profesional_id) return "";
  const fam = familiasStore.familiasProfesionales.find(
    (f) => f.id === ciclo.value!.familia_profesional_id,
  );
  return fam ? fam.nombre : "";
});

const cargarDetalleCiclo = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    // Cargar detalle del ciclo
    const response = await fetch(
      `${baseURL}/api/admin/ciclos/${cicloId}`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    if (!response.ok) throw new Error("Error al cargar los datos del ciclo");
    ciclo.value = await response.json();

    // Cargar familias profesionales si aún no están
    if (familiasStore.familiasProfesionales.length === 0) {
      await familiasStore.fetchFamiliasProfesionales();
    }
  } catch (err) {
    console.error(err);
    error.value = "No se pudo cargar la información del ciclo";
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => cargarDetalleCiclo());

const volver = () => router.back();
</script>

<template>
  <div class="container mt-4">
    <UploadCiclosCSV :cicloId="cicloId" />

    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando información del ciclo...</p>
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="alert alert-danger d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <!-- Sin ciclo -->
    <div v-else-if="!ciclo" class="alert alert-warning">
      No se encontró información del ciclo
      <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
        Volver
      </button>
    </div>

    <!-- Detalle -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i>
              Ciclos
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ ciclo.nombre }}
          </li>
        </ol>
      </nav>

      <!-- Cabecera del ciclo -->
      <div class="card mb-4 shadow-sm card-small-text">
        <div class="card-body d-flex align-items-center">
          <div class="avatar-large me-3">
            <i class="bi bi-mortarboard-fill"></i>
          </div>
          <div class="flex-grow-1 d-flex justify-content-between">
            <div>
              <h3 class="mb-1">{{ ciclo.nombre }}</h3>
              <p class="text-muted mb-0" v-if="nombreFamiliaProfesional">
                {{ nombreFamiliaProfesional }}
              </p>
            </div>
            <button
              type="button"
              class="btn btn-primary"
              data-bs-toggle="modal"
              data-bs-target="#modalCSV"
            >
              <i class="bi bi-cloud-arrow-up-fill"></i>
              Importar Datos
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.avatar-large {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
  flex-shrink: 0;
}

.breadcrumb-item a {
  color: var(--bs-primary);
}

.breadcrumb-item a:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}

/* Card con texto reducido */
.card-small-text {
  font-size: 0.85rem; /* letra más pequeña para toda la carta */
}

.card-small-text h3 {
  font-size: 1.1rem; /* ligeramente más grande que el resto */
  margin-bottom: 0.25rem;
}

.card-small-text p {
  font-size: 0.8rem; /* más pequeño para subtítulos */
  margin-bottom: 0;
}
</style>
