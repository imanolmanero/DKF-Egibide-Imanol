<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { useSeguimientosStore } from "@/stores/seguimientos";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const seguimientosStore = useSeguimientosStore();
const { message, messageType } = storeToRefs(tutorEgibideStore);

const alumnoId = Number(route.query.alumnoId);
const alumno = ref<any>(null);
const isLoading = ref(true);
const submitting = ref(false);

// Campos del formulario
const fecha = ref<string | null>(null);
const accion = ref("");
const via = ref(""); // valor por defecto
const descripcion = ref("");

onMounted(async () => {
  try {
    isLoading.value = true;

    // Traer alumnos asignados si no están cargados
    if (!tutorEgibideStore.alumnosAsignados.length) {
      await tutorEgibideStore.fetchAlumnosAsignados(route.query.tutorId as string);
    }

    alumno.value =
      tutorEgibideStore.alumnosAsignados.find(
        a => Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId
      ) || null;

    if (!alumno.value) {
      console.warn("Alumno no encontrado en el store");
    }
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
});

// Función para guardar seguimiento
async function guardarSeguimiento() {
  if (!alumno.value) return;
  if (!fecha.value) {
    //seguimientosStore.message.value = "Debes seleccionar una fecha";
    //seguimientosStore.messageType.value = "danger";
    return;
  }

  submitting.value = true;

  try {
    const payload = {
      alumno_id: alumno.value.id,
      fecha: fecha.value, // ya no es null
      accion: accion.value,
      descripcion: descripcion.value || "",
      via: via.value
    };

    await seguimientosStore.nuevoSeguimiento(payload);

    //seguimientosStore.message.value = "Seguimiento creado correctamente";
    //seguimientosStore.messageType.value = "success";

    // Mostrar toast primero
    setTimeout(() => {
    router.back();
    }, 1000);

  } catch (error: any) {
    console.error(error);
    //seguimientosStore.message.value = "Error al crear seguimiento: " + error.message;
    //seguimientosStore.messageType.value = "danger";
  } finally {
    submitting.value = false;
  }
}


// Funciones de navegación
const volver = () => router.back();
const volverAlumno = () => { router.back(); router.back(); router.back(); };
const volverAlumnos = () => { router.back(); router.back(); router.back(); router.back(); };
const volverSeguimiento = () => { router.back(); router.back(); };
</script>

<template>
  <Toast
    v-if="seguimientosStore.message"
    :message="seguimientosStore.message"
    :messageType="seguimientosStore.messageType"
  />

  <div class="container mt-4">
    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando datos del alumno...</p>
    </div>

    <!-- Alumno no encontrado -->
    <div v-else-if="!alumno" class="alert alert-danger d-flex align-items-center">
      <div>Alumno no encontrado</div>
      <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">Volver</button>
    </div>

    <!-- Formulario de nuevo seguimiento -->
    <div v-else>
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#" @click.prevent="volverAlumnos">Alumnos</a></li>
          <li class="breadcrumb-item"><a href="#" @click.prevent="volverAlumno">{{ alumno?.nombre }} {{ alumno?.apellidos }}</a></li>
          <li class="breadcrumb-item"><a href="#" @click.prevent="volverSeguimiento">Seguimiento</a></li>
          <li class="breadcrumb-item"><a href="#" @click.prevent="volver">General</a></li>
          <li class="breadcrumb-item active text-capitalize">Nuevo seguimiento</li>
        </ol>
      </nav>

      <form @submit.prevent="guardarSeguimiento" class="mt-3">
        <div class="row g-3">
          <div class="col-md-4">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" id="fecha" v-model="fecha" class="form-control" required />
          </div>

          <div class="col-md-4">
            <label for="accion" class="form-label">Acción</label>
            <input type="text" id="accion" v-model="accion" class="form-control" placeholder="General, Revisión..." required />
          </div>

          <div class="col-md-4">
            <label for="via" class="form-label">Vía</label>
            <select id="via" v-model="via" class="form-select" required>
              <option value="">Selecciona vía</option>
              <option value="Mensaje">Mensaje</option>
              <option value="Llamada">Llamada</option>
              <option value="Reunión">Reunión</option>
            </select>
          </div>
        </div>

        <div class="mb-3 mt-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea id="descripcion" v-model="descripcion" class="form-control" rows="4" placeholder="Escribe la descripción del seguimiento..." required></textarea>
        </div>

        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" @click="volver">Cancelar</button>
          <button type="submit" class="btn btn-primary" :disabled="submitting">{{ submitting ? "Guardando..." : "Guardar seguimiento" }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
h4 {
  margin-bottom: 1rem;
}
</style>
