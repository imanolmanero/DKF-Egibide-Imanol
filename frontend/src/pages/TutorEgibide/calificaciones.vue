<script setup lang="ts">
import type { Alumno } from "@/interfaces/Alumno";
import type { Asignatura } from "@/interfaces/Asignatura";
import type {
  NotaCompetenciaTecnica,
  NotaCompetenciaTransversal,
} from "@/interfaces/Notas";
import { useAlumnosStore } from "@/stores/alumnos";
import { useCompetenciasStore } from "@/stores/competencias";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const alumnoStore = useAlumnosStore();
const competenciasStore = useCompetenciasStore();

const alumno = ref<Alumno | null>(null);
const asignaturas = ref<Asignatura[]>([]);
const notasTecnicas = ref<NotaCompetenciaTecnica[]>([]);
const notaTransversal = ref<number>(0);

const isLoading = ref(true);
const error = ref<string | null>(null);

// Obtener parámetros de la ruta
const alumnoId = Number(route.params.alumnoId);

onMounted(async () => {
  try {
    // Buscar el alumno
    alumno.value =
      tutorEgibideStore.alumnosAsignados.find((a: Alumno) => {
        return Number(a.pivot?.alumno_id) === alumnoId;
      }) || null;

    if (!alumno.value) {
      error.value = "Alumno no encontrado";
      console.error("No se encontró el alumno con ID:", alumnoId);
    }

    // Obtener asignaturas
    await alumnoStore.getAsignaturasAlumno(alumnoId);
    asignaturas.value = alumnoStore.asignaturas;

    // Obtener y calcular nota tecnica
    const response =
      await competenciasStore.calcularNotasTecnicasByAlumno(alumnoId);
    notasTecnicas.value = response.notas_competenciasTec;

    // Obtener nota transversal
    const responseTrans =
      await competenciasStore.getNotaTransversalByAlumno(alumnoId);
    notaTransversal.value = responseTrans.nota_media;
  } catch (error) {
    console.error("Error al cargar alumnos:", error);
  } finally {
    isLoading.value = false;
  }
});

const notasTecnicasPorAsignatura = computed(() => {
  const map: Record<number, number> = {};

  notasTecnicas.value.forEach((n) => {
    map[n.asignatura_id] = n.nota_media;
  });

  return map;
});

const volver = () => {
  router.back();
};

const volverAlumnos = () => {
  router.back();
  router.back();
};
</script>

<template>
  <div class="container mt-4">
    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando calificaciones del alumno...</p>
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
          Volver a alumno
        </button>
      </div>
    </div>

    <!-- Sin alumno -->
    <div v-else-if="!alumno" class="alert alert-warning">
      No se ha encontrado el alumno
      <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
        Volver
      </button>
    </div>

    <!-- Contenido principal -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a
              href="#"
              @click.prevent="volverAlumnos"
              class="text-decoration-none"
            >
              <i class="bi bi-arrow-left"></i>
              Alumnos
            </a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              {{ alumno.nombre }} {{ alumno.apellidos }}
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Calificaciones
          </li>
        </ol>
      </nav>

      <!-- Cabecera del alumno -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header">
          <h3 class="mb-1">Calificaciones</h3>
        </div>
        <div class="card-body">
          <table
            v-if="asignaturas.length"
            class="table table-striped-columns text-center align-middle"
          >
            <thead>
              <tr>
                <th rowspan="2" class="align-middle"></th>
                <th rowspan="2" class="bg-primary text-light align-middle">
                  Egibide (80%)
                </th>
                <th colspan="3" class="bg-light">Empresa (20%)</th>
              </tr>
              <tr>
                <th>Técnico (60%)</th>
                <th>Transversal (20%)</th>
                <th>Cuaderno (20%)</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(asignatura, index) in asignaturas"
                :key="asignatura.id"
              >
                <td>{{ asignatura.codigo_asignatura }}</td>
                <td></td>
                <td>{{ notasTecnicasPorAsignatura[asignatura.id] ?? "-" }}</td>

                <td v-if="index === 0" :rowspan="asignaturas.length">
                  {{ notaTransversal }}
                </td>

                <td v-if="index === 0" :rowspan="asignaturas.length"></td>
              </tr>
            </tbody>
          </table>

          <div v-else class="alert alert-warning">
            Este alumno no tiene calificaciones
            <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
              Volver
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.breadcrumb-item a {
  color: var(--bs-primary);
}

.breadcrumb-item a:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}
</style>
