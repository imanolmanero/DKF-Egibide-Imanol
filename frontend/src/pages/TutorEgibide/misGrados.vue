<script setup>
import { useTutorEgibideStore } from '@/stores/tutorEgibide';
import { onMounted, ref, computed } from 'vue';
import Toast from '@/components/Notification/Toast.vue';

const storeTutor = useTutorEgibideStore();
let message = ref(null);
let messageType = ref(null);

// Paginación
const paginaActual = ref(1);
const porPagina = 5;

const totalPaginas = computed(() =>
  Math.ceil(storeTutor.misCursos.length / porPagina)
);

const ciclosPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * porPagina;
  return storeTutor.misCursos.slice(inicio, inicio + porPagina);
});

const irAPagina = (pagina) => {
  if (pagina >= 1 && pagina <= totalPaginas.value) {
    paginaActual.value = pagina;
  }
};

onMounted(async () => {
  await storeTutor.fetchInicioTutor();
  if (storeTutor.inicio?.tutor?.id) {
    await storeTutor.fetchAlumnosDeMiCursoSinTutorAsignado(storeTutor.inicio.tutor.id);
  }
});

const asignarmeAlumno = async (alumno) => {
  if (!storeTutor.inicio?.tutor?.id) return;
  try {
    await storeTutor.asignarAlumnoATutor(alumno.id, storeTutor.inicio.tutor.id);
    await storeTutor.fetchAlumnosDeMiCursoSinTutorAsignado(storeTutor.inicio.tutor.id);
    message.value = "Alumno asignado correctamente";
    messageType.value = "success";
  } catch (err) {
    console.error(err);
    message.value = "Error al asignar alumno";
    messageType.value = "error";
  }
};
</script>

<template>
  <div class="container my-5">
    <h1 class="text-center mb-4">Ciclos con alumnos sin tutor</h1>

    <!-- Cargando -->
    <div v-if="storeTutor.loading" class="text-center text-primary">
      <div class="spinner-border" role="status"></div>
      <p class="mt-2">Cargando ciclos...</p>
    </div>

    <!-- No hay ciclos -->
    <div v-else-if="storeTutor.misCursos.length === 0" class="text-center text-muted fst-italic">
      No hay alumnos sin tutor asignado.
    </div>

    <!-- Ciclos -->
    <div v-else>
      <div class="accordion" id="ciclosAccordion">
        <div v-for="ciclo in ciclosPaginados" :key="ciclo.id" class="accordion-item">
          <h2 class="accordion-header" :id="'heading' + ciclo.id">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    :data-bs-target="'#collapse' + ciclo.id" aria-expanded="false"
                    :aria-controls="'collapse' + ciclo.id">
              <i class="bi bi-journal-text me-2"></i>
              {{ ciclo.nombre }} - {{ ciclo.grupo }}
              <span class="badge bg-secondary ms-2">Alumnos: {{ ciclo.alumnos.length }}</span>
            </button>
          </h2>

          <div :id="'collapse' + ciclo.id" class="accordion-collapse collapse"
               :aria-labelledby="'heading' + ciclo.id" data-bs-parent="#ciclosAccordion">
            <div class="accordion-body p-0">
              <ul v-if="ciclo.alumnos.length > 0" class="list-group list-group-flush">
                <li v-for="alumno in ciclo.alumnos" :key="alumno.id"
                    class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <i class="bi bi-person-fill text-primary me-2"></i>
                    {{ alumno.nombre }} {{ alumno.apellidos }}
                  </div>
                  <button class="btn btn-sm btn-indigo" @click="asignarmeAlumno(alumno)">
                    <i class="bi bi-person-plus-fill me-1"></i> Asignarme
                  </button>
                </li>
              </ul>
              <div v-else class="p-3 text-muted fst-italic">
                <i class="bi bi-check-circle me-1"></i> Todos los alumnos tienen tutor asignado.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <nav v-if="totalPaginas > 1" class="mt-4" aria-label="Paginación de ciclos">
        <ul class="pagination justify-content-center">

          <!-- Anterior -->
          <li class="page-item" :class="{ disabled: paginaActual === 1 }">
            <button class="page-link" @click="irAPagina(paginaActual - 1)">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>

          <!-- Números de página -->
          <li v-for="pagina in totalPaginas" :key="pagina"
              class="page-item" :class="{ active: pagina === paginaActual }">
            <button class="page-link" @click="irAPagina(pagina)">{{ pagina }}</button>
          </li>

          <!-- Siguiente -->
          <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
            <button class="page-link" @click="irAPagina(paginaActual + 1)">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>

        </ul>

        <!-- Info de página -->
        <p class="text-center text-muted small mt-2">
          Página {{ paginaActual }} de {{ totalPaginas }}
          ({{ storeTutor.misCursos.length }} ciclos en total)
        </p>
      </nav>
    </div>

    <Toast v-if="message" :message="message" :type="messageType" />
  </div>
</template>