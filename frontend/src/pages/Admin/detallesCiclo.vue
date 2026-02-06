<script setup lang="ts">
import type { Ciclo } from "@/interfaces/Ciclo";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useFamiliaProfesionalesStore } from "@/stores/familiasProfesionales";
import { ref, onMounted, computed } from "vue";
import axios from "axios";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const familiasStore = useFamiliaProfesionalesStore();

const baseURL = import.meta.env.VITE_API_BASE_URL;

const ciclo = ref<Ciclo | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// --- SOLUCIÓN ERROR archivoSeleccionado ---
// Añadimos 'undefined' al tipo para que coincida con lo que devuelve el input
const archivoSeleccionado = ref<File | null | undefined>(null);
const isImporting = ref(false);

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  // Si hay archivos, asignamos el primero, si no, null
  archivoSeleccionado.value = target.files?.[0] || null;
};

const subirArchivo = async () => {
  // Verificamos que realmente haya un archivo antes de seguir
  if (!archivoSeleccionado.value) return;

  isImporting.value = true;
  const formData = new FormData();
  
  // TypeScript ya sabe aquí que archivoSeleccionado.value no es ni null ni undefined
  formData.append('file', archivoSeleccionado.value);

  try {
    const response = await axios.post(`${baseURL}/api/importar-datos`, formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    alert("¡Éxito!: " + response.data.message);
    archivoSeleccionado.value = null;
    location.reload(); 
  } catch (err: any) {
    const msg = err.response?.data?.message || "Error al importar";
    alert("Error: " + msg);
  } finally {
    isImporting.value = false;
  }
};
// ------------------------------------------

const cicloId = Number(route.params.cicloId);

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
    const response = await fetch(`${baseURL}/api/admin/ciclos/${cicloId}`, {
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
      },
    });

    if (!response.ok) throw new Error("Error al cargar los datos del ciclo");
    ciclo.value = await response.json();

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
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }} <button @click="volver" class="btn btn-link">Volver</button>
    </div>

    <div v-else-if="ciclo">
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">Ciclos</a>
          </li>
          <li class="breadcrumb-item active">{{ ciclo.nombre }}</li>
        </ol>
      </nav>

      <div class="card mb-4 shadow-sm card-small-text">
        <div class="card-body d-flex align-items-center">
          <div class="avatar-large me-3">
            <i class="bi bi-mortarboard-fill"></i>
          </div>
          <div class="flex-grow-1 d-flex justify-content-between align-items-center">
            <div>
              <h3 class="mb-1">{{ ciclo.nombre }}</h3>
              <p class="text-muted mb-0">{{ nombreFamiliaProfesional }}</p>
              
              <span class="badge bg-secondary">
                Código: {{ (ciclo as any).codigo || 'Sin código' }}
              </span>
            </div>
            <button
              type="button"
              class="btn btn-primary"
              data-bs-toggle="modal"
              data-bs-target="#modalCSV"
            >
              <i class="bi bi-cloud-arrow-up-fill me-1"></i>
              Importar Datos
            </button>
          </div>
        </div>
      </div>

      <div class="modal fade" id="modalCSV" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Importar Datos</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-info py-2 small">
                Sube el archivo de Alumnos o Asignaciones.
              </div>
              <div class="mb-3">
                <input 
                  type="file" 
                  class="form-control" 
                  @change="handleFileUpload" 
                  accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"                />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button 
                type="button" 
                class="btn btn-primary" 
                @click="subirArchivo" 
                :disabled="!archivoSeleccionado || isImporting"
              >
                <span v-if="isImporting" class="spinner-border spinner-border-sm me-2"></span>
                {{ isImporting ? 'Procesando...' : 'Subir Archivo' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Tus estilos aquí... */
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
}
.card-small-text { font-size: 0.85rem; }
</style>