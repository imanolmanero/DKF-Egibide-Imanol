<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useAlumnosStore } from "@/stores/alumnos";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const alumnosStore = useAlumnosStore();
const file = ref<File | null>(null);
const subiendo = ref(false);
  
const fileError = ref<string | null>(null);

onMounted(() => {
  alumnosStore.fetchMisEntregas();
});

async function eliminar(id: number) {
  if (!confirm("¿Seguro que quieres eliminar esta entrega?")) return;

  try {
    await alumnosStore.eliminarEntrega(id);
  } catch {
    alert("No se pudo eliminar la entrega");
  }
}

function onFile(e: Event) {
  const input = e.target as HTMLInputElement;
  const f = input.files?.[0] ?? null;

  fileError.value = null;
  file.value = null;

  if (!f) return;

  const isPdf =
    f.type === "application/pdf" ||
    f.name.toLowerCase().endsWith(".pdf");

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

  subiendo.value = true;
  await alumnosStore.subirEntrega(file.value);
  subiendo.value = false;

  file.value = null;

  const btn = document.querySelector("#modalEntrega .btn-close") as HTMLElement;
  btn?.click();
}

function resetForm() {
  file.value = null;
}

function formatDate(fecha: string) {
  const onlyDate = fecha.includes("T") ? (fecha.split("T")[0] ?? fecha) : fecha;

  const [y, m, d] = onlyDate.split("-");
  if (!y || !m || !d) return fecha;

  return `${d}/${m}/${y}`;
}

function urlArchivo(id: number) {
  return `http://localhost:8000/api/entregas/${id}/archivo?token=${authStore.token}`;
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

      <button
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalEntrega"
        @click="resetForm"
      >
        + Subir entrega
      </button>
    </div>

    <div
      v-if="alumnosStore.message"
      class="alert"
      :class="alumnosStore.messageType === 'success'
        ? 'alert-success'
        : 'alert-danger'"
    >
      {{ alumnosStore.message }}
    </div>

    <div class="card shadow-sm">
      <div class="card-header">
        <strong>Entregas realizadas</strong>
      </div>

      <div class="card-body p-0">
        <div v-if="alumnosStore.loadingEntregas" class="p-3">
          <div class="spinner-border spinner-border-sm"></div>
          <span class="ms-2">Cargando...</span>
        </div>

        <div
          v-else-if="alumnosStore.entregas.length === 0"
          class="p-3 text-muted"
        >
          Todavía no has subido ninguna entrega.
        </div>

        <table v-else class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 160px;" class="text-align-center">Archivo</th>
              <th style="width: 160px;">Fecha</th>
              <th style="width: 220px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(e, idx) in alumnosStore.entregas" :key="e.id">
              <td class="pl-5">
                <div class="fw-semibold">
                  {{ nombreEntrega(idx) }}
                </div>
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

    <div class="modal fade" id="modalEntrega" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

          <form @submit.prevent="subir">
            <div class="modal-header">
              <h5 class="modal-title">Subir entrega</h5>
              <button
                class="btn-close"
                data-bs-dismiss="modal"
                :disabled="subiendo"
              ></button>
            </div>

            <div class="modal-body">
              <label class="form-label">Archivo</label>
              <input
                type="file"
                class="form-control"
                accept="application/pdf,.pdf"
                required
                @change="onFile"
                :disabled="subiendo"
              />
              <div class="form-text">
                Solo se admite PDF.
              </div>
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
