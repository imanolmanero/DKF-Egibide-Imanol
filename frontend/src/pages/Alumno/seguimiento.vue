<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useAlumnosStore } from "@/stores/alumnos";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const alumnosStore = useAlumnosStore();

const file = ref<File | null>(null);
const subiendo = ref(false);
const fileError = ref<string | null>(null);

const entregaSeleccionada = ref<any | null>(null);

const baseURL = import.meta.env.VITE_API_BASE_URL;

onMounted(() => {
  // Carga pendientes + realizadas (y también rellena entregas con realizadas)
  alumnosStore.fetchMisPendientesYRealizadas();
});

async function eliminar(id: number) {
  if (!confirm("¿Seguro que quieres eliminar esta entrega?")) return;

  try {
    await alumnosStore.eliminarEntrega(id);
    // refrescar listas (por si borras una realizada y vuelve a pendiente)
    await alumnosStore.fetchMisPendientesYRealizadas();
  } catch {
    alert("No se pudo eliminar la entrega");
  }
}

function abrirModal(entrega: any) {
  entregaSeleccionada.value = entrega;
  resetForm();
}

function onFile(e: Event) {
  const input = e.target as HTMLInputElement;
  const f = input.files?.[0] ?? null;

  fileError.value = null;
  file.value = null;

  if (!f) return;

  const isPdf = f.type === "application/pdf" || f.name.toLowerCase().endsWith(".pdf");

  if (!isPdf) {
    input.value = "";
    fileError.value = "Solo se admite PDF.";
    return;
  }

  file.value = f;
}

function nombreEntrega(idx: number) {
  const numero = alumnosStore.entregas.length - idx;
  return `Entrega ${numero}`;
}

async function subir() {
  if (!file.value) return;

  if (!entregaSeleccionada.value?.id) {
    alert("Selecciona una entrega pendiente primero.");
    return;
  }

  subiendo.value = true;
  try {
    await alumnosStore.subirEntrega(file.value, entregaSeleccionada.value.id);
  } finally {
    subiendo.value = false;
  }

  file.value = null;
  entregaSeleccionada.value = null;

  const btn = document.querySelector("#modalEntrega .btn-close") as HTMLElement;
  btn?.click();
}

function resetForm() {
  file.value = null;
  fileError.value = null;
}

function formatDate(fecha: string) {
  const onlyDate = fecha.includes("T") ? (fecha.split("T")[0] ?? fecha) : fecha;

  const parts = onlyDate.split("-");
  if (parts.length !== 3) return fecha;

  const [y, m, d] = parts;
  return `${d}/${m}/${y}`;
}

function urlArchivo(id: number) {
  return `${baseURL}/api/entregas/${id}/archivo?token=${authStore.token}`;
}

function descargar(id: number) {
  const a = document.createElement("a");
  a.href = urlArchivo(id);
  a.download = "";
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h3 class="mb-1">Mis entregas</h3>
        <p class="text-muted mb-0">Cuaderno de prácticas</p>
      </div>
    </div>

    <div
      v-if="alumnosStore.message"
      class="alert"
      :class="alumnosStore.messageType === 'success' ? 'alert-success' : 'alert-danger'"
    >
      {{ alumnosStore.message }}
    </div>

    <!-- ===================== -->
    <!-- PENDIENTES -->
    <!-- ===================== -->
    <div class="card shadow-sm mb-3">
      <div class="card-header">
        <strong>Entregas pendientes</strong>
      </div>

      <div class="card-body p-0">
        <div v-if="alumnosStore.loadingPendientes" class="p-3">
          <div class="spinner-border spinner-border-sm"></div>
          <span class="ms-2">Cargando...</span>
        </div>

        <div v-else-if="alumnosStore.pendientes.length === 0" class="p-3 text-muted">
          No tienes entregas pendientes.
        </div>

        <table v-else class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Título</th>
              <th style="width: 180px;">Fecha límite</th>
              <th style="width: 160px;">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="p in alumnosStore.pendientes" :key="p.id">
              <td>
                <div class="fw-semibold">{{ p.titulo }}</div>
                <div v-if="p.descripcion" class="text-muted small">
                  {{ p.descripcion }}
                </div>
              </td>

              <td>{{ formatDate(p.fecha_limite) }}</td>

              <td>
                <button
                  class="btn btn-sm btn-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#modalEntrega"
                  @click="abrirModal(p)"
                >
                  Subir
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ===================== -->
    <!-- REALIZADAS -->
    <!-- ===================== -->
    <div class="card shadow-sm">
      <div class="card-header">
        <strong>Entregas realizadas</strong>
      </div>

      <div class="card-body p-0">
        <div v-if="alumnosStore.loadingEntregas" class="p-3">
          <div class="spinner-border spinner-border-sm"></div>
          <span class="ms-2">Cargando...</span>
        </div>

        <div v-else-if="alumnosStore.entregas.length === 0" class="p-3 text-muted">
          Todavía no has subido ninguna entrega.
        </div>

        <table v-else class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 160px;">Archivo</th>
              <th style="width: 160px;">Fecha</th>
              <th style="width: 220px;">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(e, idx) in alumnosStore.entregas" :key="e.id">
              <td class="pl-5">
                <div class="fw-semibold">{{ nombreEntrega(idx) }}</div>
              </td>

              <td>{{ formatDate(e.fecha) }}</td>

              <td>
                <div class="d-flex gap-2">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="descargar(e.id)"
                  >
                    Descargar
                  </button>

                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    @click="eliminar(e.id)"
                  >
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ===================== -->
    <!-- MODAL SUBIDA -->
    <!-- ===================== -->
    <div class="modal fade" id="modalEntrega" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form @submit.prevent="subir">
            <div class="modal-header">
              <h5 class="modal-title">
                Subir entrega
                <span v-if="entregaSeleccionada" class="text-muted">
                  — {{ entregaSeleccionada.titulo }}
                </span>
              </h5>

              <button
                class="btn-close"
                data-bs-dismiss="modal"
                :disabled="subiendo"
              ></button>
            </div>

            <div class="modal-body">
              <div v-if="entregaSeleccionada" class="alert alert-info">
                <div><strong>Entrega:</strong> {{ entregaSeleccionada.titulo }}</div>
                <div><strong>Vence:</strong> {{ formatDate(entregaSeleccionada.fecha_limite) }}</div>
              </div>

              <label class="form-label">Archivo (PDF)</label>
              <input
                type="file"
                class="form-control"
                accept="application/pdf,.pdf"
                required
                @change="onFile"
                :disabled="subiendo"
              />

              <div v-if="fileError" class="text-danger mt-2">
                {{ fileError }}
              </div>

              <div class="form-text">Solo se admite PDF.</div>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-outline-secondary"
                data-bs-dismiss="modal"
                :disabled="subiendo"
              >
                Cancelar
              </button>

              <button class="btn btn-primary" type="submit" :disabled="subiendo">
                <span
                  v-if="subiendo"
                  class="spinner-border spinner-border-sm me-2"
                ></span>
                Subir
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped></style>
