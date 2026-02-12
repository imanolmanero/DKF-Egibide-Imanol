<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import type { Alumno } from "@/interfaces/Alumno";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, reactive, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const { message, messageType } = storeToRefs(tutorEgibideStore);

const alumno = ref<Alumno | null>(null);
const isLoading = ref(true);

const alumnoId = Number(route.params.alumnoId);

/* ============================
   FORMULARIO HORARIO
============================ */

const diasSemana = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes"];

const formulario = reactive({
  fecha_inicio: "",
  fecha_fin: "",
  horario: diasSemana.map((dia) => ({
    dia,
    franjas: [{ hora_inicial: 8, hora_final: 15 }],
  })),
});

/* ============================
   CARGA INICIAL
============================ */

function formatoFecha(dateStr: string | null): string {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${d.getFullYear()}-${month}-${day}`;
}

onMounted(async () => {
  alumno.value =
    tutorEgibideStore.alumnosAsignados.find(
      (a) =>
        Number(a.pivot?.alumno_id) === alumnoId ||
        Number(a.id) === alumnoId
    ) || null;

  if (alumno.value) {
    // Traemos el horario desde la API
    const data = await tutorEgibideStore.fetchHorarioAlumno(alumnoId);

    if (data) {
      formulario.fecha_inicio = formatoFecha(data.fecha_inicio) || "";
      formulario.fecha_fin = formatoFecha(data.fecha_fin) || "";

      // Reemplazamos el horario si hay
      if (data.horario && data.horario.length) {
        formulario.horario = diasSemana.map((dia) => {
          const diaData = data.horario.find((d: any) => d.dia.toLowerCase() === dia.toLowerCase());
          return diaData
            ? { dia, franjas: diaData.franjas }
            : { dia, franjas: [{ hora_inicial: 8, hora_final: 15 }] };
        });
      }
    }
  }

  isLoading.value = false;
});


/* ============================
   MÉTODOS
============================ */

const volver = () => router.back();

const agregarFranja = (indexDia: number) => {
  formulario.horario[indexDia].franjas.push({
    hora_inicial: 8,
    hora_final: 15,
  });
};

const eliminarFranja = (indexDia: number, indexFranja: number) => {
  if (formulario.horario[indexDia].franjas.length > 1) {
    formulario.horario[indexDia].franjas.splice(indexFranja, 1);
  }
};

/* ============================
   VALIDACIONES
============================ */

function validarFormulario(): boolean {
  if (!formulario.fecha_inicio) {
    tutorEgibideStore.setMessage("Debes indicar fecha de inicio", "error");
    return false;
  }

  if (
    formulario.fecha_fin &&
    formulario.fecha_fin < formulario.fecha_inicio
  ) {
    tutorEgibideStore.setMessage(
      "La fecha fin no puede ser menor que la fecha inicio",
      "error"
    );
    return false;
  }

  for (const dia of formulario.horario) {
    for (const franja of dia.franjas) {
      if (franja.hora_final <= franja.hora_inicial) {
        tutorEgibideStore.setMessage(
          `En ${dia.dia} la hora final debe ser mayor que la inicial`,
          "error"
        );
        return false;
      }
    }
  }

  return true;
}

/* ============================
   CÁLCULO HORAS TOTALES
============================ */

const horasTotales = computed(() => {
  let total = 0;

  formulario.horario.forEach((dia) => {
    dia.franjas.forEach((franja) => {
      total += franja.hora_final - franja.hora_inicial;
    });
  });

  return total;
});

/* ============================
   GUARDAR
============================ */

const guardarHorario = async () => {
  if (!validarFormulario()) return;

  const ok = await tutorEgibideStore.guardarHorarioAlumno(
    alumnoId,
    {
      fecha_inicio: formulario.fecha_inicio,
      fecha_fin: formulario.fecha_fin || null,
      horario: formulario.horario,
    }
  );

  if (ok) {
    setTimeout(() => {
      volver();
    }, 1000);
  }
};

</script>

<template>
  <Toast
    v-if="message"
    :message="message"
    :messageType="messageType"
  />

  <div class="container mt-4">
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;"></div>
      <p class="mt-3 text-muted fw-semibold">
        Cargando información del alumno...
      </p>
    </div>

    <div v-else-if="!alumno" class="alert alert-danger">
      Alumno no encontrado
      <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
        Volver
      </button>
    </div>

    <div v-else>
      <div class="card shadow-sm">
        <div class="card-header">
          <h3>Horario y Periodo</h3>
        </div>

        <div class="card-body">
          <p>
            Configura el horario de
            <b>{{ alumno?.nombre }} {{ alumno?.apellidos }}</b>
          </p>

          <!-- FECHAS -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Fecha inicio *</label>
              <input
                type="date"
                class="form-control"
                v-model="formulario.fecha_inicio"
                required
              />
            </div>

            <div class="col-md-6">
              <label class="form-label">Fecha fin</label>
              <input
                type="date"
                class="form-control"
                v-model="formulario.fecha_fin"
              />
            </div>
          </div>

          <hr />

          <!-- HORARIO SEMANAL -->
          <h5 class="mb-3">Horario semanal</h5>

          <div
            v-for="(diaData, index) in formulario.horario"
            :key="diaData.dia"
            class="border rounded p-3 mb-3"
          >
            <h6 class="fw-bold">{{ diaData.dia }}</h6>

            <div
              v-for="(franja, indexFranja) in diaData.franjas"
              :key="indexFranja"
              class="row align-items-end mb-2"
            >
              <div class="col-md-4">
                <label class="form-label small">Hora inicio</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="franja.hora_inicial"
                  min="0"
                  max="23"
                />
              </div>

              <div class="col-md-4">
                <label class="form-label small">Hora fin</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="franja.hora_final"
                  min="0"
                  max="23"
                />
              </div>

              <div class="col-md-4">
                <button
                  type="button"
                  class="btn btn-danger btn-sm"
                  @click="eliminarFranja(index, indexFranja)"
                  :disabled="diaData.franjas.length === 1"
                >
                  Eliminar
                </button>
              </div>
            </div>

            <button
              type="button"
              class="btn btn-secondary btn-sm"
              @click="agregarFranja(index)"
            >
              + Añadir franja
            </button>
          </div>

          <!-- HORAS CALCULADAS -->
          <div class="alert alert-info">
            <b>Horas semanales calculadas:</b> {{ horasTotales }} horas
          </div>

          <!-- BOTONES -->
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary" @click="volver">
              Cancelar
            </button>
            <button class="btn btn-primary" @click="guardarHorario">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
