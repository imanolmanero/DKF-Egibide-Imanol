<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useCiclosStore } from "@/stores/ciclos";
import Toast from "../Notification/Toast.vue";

const props = defineProps<{
  cicloId: number | string;
}>();

const ciclosStore = useCiclosStore();

const archivoSeleccionado = ref<File | null>(null);
const cargando = ref(false);
const mensaje = ref("");
const mensajeTipo = ref("");
const resultado = ref<any>(null);

const fileInput = ref<HTMLInputElement | null>(null);

function handleFileUpload(event: Event) {
  const target = event.target as HTMLInputElement;

  archivoSeleccionado.value = target.files?.[0] ?? null;
  mensaje.value = "";
  resultado.value = null;
}

function mostrarMensaje(texto: string, tipo: string) {
  mensaje.value = texto;
  mensajeTipo.value = tipo === "success" ? "alert-success" : "alert-danger";
}

async function importarCSV() {
  if (!archivoSeleccionado.value) {
    mostrarMensaje("Seleccione un archivo", "error");
    return;
  }

  const formData = new FormData();
  formData.append("ciclo_id", String(props.cicloId));
  formData.append("csv_file", archivoSeleccionado.value);

  cargando.value = true;
  mensaje.value = "";

  await ciclosStore.uploadCSV(formData);
}

async function descargarPlantilla() {
  try {
    const response = await ciclosStore.downloadPlantillaCSV();

    // Crear archivo CSV
    const link = document.createElement("a");
    link.href = URL.createObjectURL(response);
    link.download = "plantilla_ciclo.csv";
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(link.href);
  } catch (error) {
    mostrarMensaje("Error al descargar plantilla", "error");
  }
}
</script>

<template>
  <Toast
    v-if="ciclosStore.message"
    :message="ciclosStore.message"
    :messageType="ciclosStore.messageType"
  />

  <div class="modal" id="modalCSV" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Importar datos al Ciclo</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body d-flex">
          <input
            type="file"
            ref="fileInput"
            @change="handleFileUpload"
            accept=".csv"
            style="display: none"
          />

          <button
            @click="fileInput?.click()"
            :disabled="cargando"
            class="btn border-secondary"
            style="width: 100%; border-style: dashed; border-width: 2px"
          >
            <span v-if="!archivoSeleccionado">
              <i class="bi bi-file-earmark-arrow-up"></i>
              Seleccionar archivo CSV
            </span>
            <span v-else>
              <i class="bi bi-check-circle-fill"></i>
              {{ archivoSeleccionado.name }}
            </span>
          </button>

          <!-- Mensajes -->
          <div v-if="mensaje" :class="['alert', mensajeTipo]">
            {{ mensaje }}
          </div>

          <!-- Resultados -->
          <div v-if="resultado" class="resultado">
            <h3>Importación completada</h3>

            <div
              v-if="resultado.errores && resultado.errores.length > 0"
              class="errores"
            >
              <strong>Errores:</strong>
              <ul>
                <li v-for="(error, idx) in resultado.errores" :key="idx">
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button @click="descargarPlantilla" class="btn btn-info">
            <i class="bi bi-file-earmark-arrow-down"></i>
            Descargar plantilla
          </button>
          <button
            type="button"
            class="btn btn-primary"
            @click="importarCSV"
            :disabled="!archivoSeleccionado"
          >
            <span v-if="cargando">Importando...</span>
            <span v-else
              ><i class="bi bi-cloud-arrow-up-fill"></i> Importar</span
            >
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
