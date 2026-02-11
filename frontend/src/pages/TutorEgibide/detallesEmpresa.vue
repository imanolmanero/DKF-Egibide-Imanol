<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

interface Instructor {
  id: number;
  nombre: string;
  apellidos: string;
  telefono: string | null;
  ciudad: string | null;
  empresa_id?: number;
  user_id?: number;
}

interface EmpresaDetalle extends Empresa {
  instructores?: Instructor[];
}

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const baseURL = import.meta.env.VITE_API_BASE_URL;

const empresa = ref<EmpresaDetalle | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Modales
const showCrearModal = ref(false);
const showAsignarModal = ref(false);

// Estados de formularios
const isSubmitting = ref(false);
const submitError = ref<string | null>(null);
const submitSuccess = ref<string | null>(null);

// Datos para crear instructor
const nuevoInstructor = ref({
  nombre: "",
  apellidos: "",
  email: "",
  telefono: "",
  ciudad: "",
  password: "",
  confirmarPassword: "",
});

// Datos para asignar instructor
const instructoresDisponibles = ref<Instructor[]>([]);
const instructorSeleccionado = ref<number | null>(null);
const isLoadingInstructores = ref(false);

// Obtener parámetros de la ruta
const empresaId = Number(route.params.empresaId);

onMounted(async () => {
  await cargarDetalleEmpresa();
});

const cargarDetalleEmpresa = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await fetch(
      `${baseURL}/api/tutorEgibide/empresas/${empresaId}`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      }
    );

    if (!response.ok) {
      throw new Error("Error al cargar los datos de la empresa");
    }

    const data = await response.json();
    empresa.value = data;
  } catch (err) {
    console.error(err);
    error.value = "No se pudo cargar la información de la empresa";
  } finally {
    isLoading.value = false;
  }
};

const volver = () => {
  router.back();
};

// Abrir modal de crear instructor
const abrirModalCrear = () => {
  resetFormularioCrear();
  submitError.value = null;
  submitSuccess.value = null;
  showCrearModal.value = true;
};

// Abrir modal de asignar instructor
const abrirModalAsignar = async () => {
  resetFormularioAsignar();
  submitError.value = null;
  submitSuccess.value = null;
  showAsignarModal.value = true;
  await cargarInstructoresDisponibles();
};

// Reset formularios
const resetFormularioCrear = () => {
  nuevoInstructor.value = {
    nombre: "",
    apellidos: "",
    email: "",
    telefono: "",
    ciudad: "",
    password: "",
    confirmarPassword: "",
  };
};

const resetFormularioAsignar = () => {
  instructorSeleccionado.value = null;
  instructoresDisponibles.value = [];
};

// Cargar instructores disponibles
const cargarInstructoresDisponibles = async () => {
  isLoadingInstructores.value = true;
  try {
    const response = await fetch(
      `${baseURL}/api/instructores/disponibles`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      }
    );

    if (!response.ok) {
      throw new Error("Error al cargar instructores");
    }

    const data = await response.json();
    instructoresDisponibles.value = data;
  } catch (err) {
    console.error(err);
    submitError.value = "Error al cargar la lista de instructores";
  } finally {
    isLoadingInstructores.value = false;
  }
};

// Validación de formulario de crear
const validarFormularioCrear = (): boolean => {
  if (!nuevoInstructor.value.nombre.trim()) {
    submitError.value = "El nombre es obligatorio";
    return false;
  }
  if (!nuevoInstructor.value.apellidos.trim()) {
    submitError.value = "Los apellidos son obligatorios";
    return false;
  }
  if (!nuevoInstructor.value.email.trim()) {
    submitError.value = "El email es obligatorio";
    return false;
  }
  // Validación básica de email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(nuevoInstructor.value.email)) {
    submitError.value = "El email no es válido";
    return false;
  }
  if (!nuevoInstructor.value.password) {
    submitError.value = "La contraseña es obligatoria";
    return false;
  }
  if (nuevoInstructor.value.password.length < 8) {
    submitError.value = "La contraseña debe tener al menos 8 caracteres";
    return false;
  }
  if (nuevoInstructor.value.password !== nuevoInstructor.value.confirmarPassword) {
    submitError.value = "Las contraseñas no coinciden";
    return false;
  }
  return true;
};

// Crear nuevo instructor
const crearInstructor = async () => {
  submitError.value = null;
  
  if (!validarFormularioCrear()) {
    return;
  }

  isSubmitting.value = true;

  try {
    const response = await fetch(`${baseURL}/api/instructores/crear`, {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        empresa_id: empresaId,
        nombre: nuevoInstructor.value.nombre,
        apellidos: nuevoInstructor.value.apellidos,
        email: nuevoInstructor.value.email,
        telefono: nuevoInstructor.value.telefono || null,
        ciudad: nuevoInstructor.value.ciudad || null,
        password: nuevoInstructor.value.password,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Error al crear el instructor");
    }

    submitSuccess.value = "Instructor creado exitosamente";
    
    // Esperar un momento y cerrar modal
    setTimeout(async () => {
      showCrearModal.value = false;
      await cargarDetalleEmpresa(); // Recargar datos
      submitSuccess.value = null;
    }, 1500);

  } catch (err: any) {
    console.error(err);
    submitError.value = err.message || "Error al crear el instructor";
  } finally {
    isSubmitting.value = false;
  }
};

// Asignar instructor existente
const asignarInstructor = async () => {
  submitError.value = null;

  if (!instructorSeleccionado.value) {
    submitError.value = "Debes seleccionar un instructor";
    return;
  }

  isSubmitting.value = true;

  try {
    const response = await fetch(`${baseURL}/api/instructores/asignar`, {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        instructor_id: instructorSeleccionado.value,
        empresa_id: empresaId,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Error al asignar el instructor");
    }

    submitSuccess.value = "Instructor asignado exitosamente";
    
    // Esperar un momento y cerrar modal
    setTimeout(async () => {
      showAsignarModal.value = false;
      await cargarDetalleEmpresa(); // Recargar datos
      submitSuccess.value = null;
    }, 1500);

  } catch (err: any) {
    console.error(err);
    submitError.value = err.message || "Error al asignar el instructor";
  } finally {
    isSubmitting.value = false;
  }
};

// Cerrar modales
const cerrarModalCrear = () => {
  if (!isSubmitting.value) {
    showCrearModal.value = false;
    resetFormularioCrear();
    submitError.value = null;
    submitSuccess.value = null;
  }
};

const cerrarModalAsignar = () => {
  if (!isSubmitting.value) {
    showAsignarModal.value = false;
    resetFormularioAsignar();
    submitError.value = null;
    submitSuccess.value = null;
  }
};
</script>

<template>
  <div class="container mt-4">
    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">
        Cargando información de la empresa...
      </p>
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
          Volver a empresas
        </button>
      </div>
    </div>

    <!-- Sin empresa -->
    <div
      v-else-if="!empresa"
      class="alert alert-warning d-flex align-items-center"
    >
      <i class="bi bi-building-x me-2"></i>
      <div>
        No se encontró información de la empresa
        <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <!-- Contenido principal -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i>
              Empresas
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ empresa.nombre }}
          </li>
        </ol>
      </nav>

      <!-- Cabecera de la empresa -->
      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar-large me-3">
              <i class="bi bi-building"></i>
            </div>
            <div class="flex-grow-1">
              <h3 class="mb-1">{{ empresa.nombre }}</h3>
            </div>
          </div>

          <!-- Información adicional de la empresa -->
          <div class="row g-3 mt-2">
            <div class="col-md-6" v-if="empresa.telefono">
              <div class="info-item">
                <i class="bi bi-telephone-fill text-primary me-2"></i>
                <span class="text-muted">Teléfono:</span>
                <strong class="ms-2">{{ empresa.telefono }}</strong>
              </div>
            </div>
            <div class="col-md-6" v-if="empresa.email">
              <div class="info-item">
                <i class="bi bi-envelope-fill text-primary me-2"></i>
                <span class="text-muted">Email:</span>
                <strong class="ms-2">{{ empresa.email }}</strong>
              </div>
            </div>
            <div class="col-md-6" v-if="empresa.cif">
              <div class="info-item">
                <i class="bi bi-card-text text-primary me-2"></i>
                <span class="text-muted">CIF:</span>
                <strong class="ms-2">{{ empresa.cif }}</strong>
              </div>
            </div>
            <div class="col-md-12" v-if="empresa.direccion">
              <div class="info-item">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                <span class="text-muted">Dirección:</span>
                <strong class="ms-2">{{ empresa.direccion }}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección de Instructores con botones de acción -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
          <i class="bi bi-person-badge-fill me-2"></i>
          Instructores
        </h4>
        <div class="btn-group" role="group">
          <button
            type="button"
            class="btn btn-primary"
            @click="abrirModalCrear"
          >
            <i class="bi bi-plus-circle me-1"></i>
            Crear Nuevo Instructor
          </button>
          <button
            type="button"
            class="btn btn-outline-primary"
            @click="abrirModalAsignar"
          >
            <i class="bi bi-person-plus me-1"></i>
            Asignar Instructor Existente
          </button>
        </div>
      </div>

      <!-- Instructores -->
      <div v-if="empresa.instructores && empresa.instructores.length > 0">
        <div class="row g-3">
          <div
            v-for="instructor in empresa.instructores"
            :key="instructor.id"
            class="col-md-6"
          >
            <div class="card h-100 shadow-sm instructor-card">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="avatar-medium me-3">
                    <i class="bi bi-person-fill"></i>
                  </div>
                  <div>
                    <h5 class="mb-0">
                      {{ instructor.nombre }} {{ instructor.apellidos }}
                    </h5>
                  </div>
                </div>

                <div class="instructor-info">
                  <div v-if="instructor.telefono" class="info-item mb-2">
                    <i class="bi bi-telephone-fill text-primary me-2"></i>
                    <span class="text-muted">Teléfono:</span>
                    <strong class="ms-2">{{ instructor.telefono }}</strong>
                  </div>
                  <div v-if="instructor.ciudad" class="info-item">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                    <span class="text-muted">Ciudad:</span>
                    <strong class="ms-2">{{ instructor.ciudad }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mensaje si no hay instructores -->
      <div v-else class="alert alert-info mt-3">
        <i class="bi bi-info-circle-fill me-2"></i>
        No hay instructores asignados a esta empresa actualmente.
      </div>
    </div>

    <!-- Modal Crear Instructor -->
    <div
      class="modal fade"
      :class="{ show: showCrearModal }"
      :style="{ display: showCrearModal ? 'block' : 'none' }"
      tabindex="-1"
      @click.self="cerrarModalCrear"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-person-plus-fill me-2"></i>
              Crear Nuevo Instructor
            </h5>
            <button
              type="button"
              class="btn-close"
              @click="cerrarModalCrear"
              :disabled="isSubmitting"
            ></button>
          </div>
          <div class="modal-body">
            <!-- Mensajes de error/éxito -->
            <div v-if="submitError" class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              {{ submitError }}
            </div>
            <div v-if="submitSuccess" class="alert alert-success" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              {{ submitSuccess }}
            </div>

            <form @submit.prevent="crearInstructor">
              <!-- Mensaje informativo -->
              <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle-fill me-2"></i>
                <small>
                  <strong>Nota:</strong> Una empresa solo puede tener un instructor. Si {{ empresa?.nombre }} ya tiene uno asignado, será reemplazado automáticamente por este nuevo instructor.
                </small>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="nombre" class="form-label">Nombre *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="nombre"
                    v-model="nuevoInstructor.nombre"
                    :disabled="isSubmitting"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="apellidos" class="form-label">Apellidos *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="apellidos"
                    v-model="nuevoInstructor.apellidos"
                    :disabled="isSubmitting"
                    required
                  />
                </div>
                <div class="col-12">
                  <label for="email" class="form-label">Email *</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    v-model="nuevoInstructor.email"
                    :disabled="isSubmitting"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="telefono" class="form-label">Teléfono</label>
                  <input
                    type="tel"
                    class="form-control"
                    id="telefono"
                    v-model="nuevoInstructor.telefono"
                    :disabled="isSubmitting"
                  />
                </div>
                <div class="col-md-6">
                  <label for="ciudad" class="form-label">Ciudad</label>
                  <input
                    type="text"
                    class="form-control"
                    id="ciudad"
                    v-model="nuevoInstructor.ciudad"
                    :disabled="isSubmitting"
                  />
                </div>
                <div class="col-md-6">
                  <label for="password" class="form-label">Contraseña *</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    v-model="nuevoInstructor.password"
                    :disabled="isSubmitting"
                    minlength="8"
                    required
                  />
                  <small class="text-muted">Mínimo 8 caracteres</small>
                </div>
                <div class="col-md-6">
                  <label for="confirmarPassword" class="form-label"
                    >Confirmar Contraseña *</label
                  >
                  <input
                    type="password"
                    class="form-control"
                    id="confirmarPassword"
                    v-model="nuevoInstructor.confirmarPassword"
                    :disabled="isSubmitting"
                    minlength="8"
                    required
                  />
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="cerrarModalCrear"
              :disabled="isSubmitting"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="crearInstructor"
              :disabled="isSubmitting"
            >
              <span
                v-if="isSubmitting"
                class="spinner-border spinner-border-sm me-2"
              ></span>
              {{ isSubmitting ? "Creando..." : "Crear Instructor" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Asignar Instructor -->
    <div
      class="modal fade"
      :class="{ show: showAsignarModal }"
      :style="{ display: showAsignarModal ? 'block' : 'none' }"
      tabindex="-1"
      @click.self="cerrarModalAsignar"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-person-check-fill me-2"></i>
              Asignar Instructor Existente
            </h5>
            <button
              type="button"
              class="btn-close"
              @click="cerrarModalAsignar"
              :disabled="isSubmitting"
            ></button>
          </div>
          <div class="modal-body">
            <!-- Mensajes de error/éxito -->
            <div v-if="submitError" class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              {{ submitError }}
            </div>
            <div v-if="submitSuccess" class="alert alert-success" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              {{ submitSuccess }}
            </div>

            <!-- Loading instructores -->
            <div v-if="isLoadingInstructores" class="text-center py-3">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando instructores...</span>
              </div>
              <p class="mt-2 text-muted">Cargando instructores...</p>
            </div>

            <!-- Formulario de selección -->
            <div v-else>
              <div class="mb-3">
                <label for="instructorSelect" class="form-label"
                  >Seleccionar Instructor *</label
                >
                <select
                  class="form-select"
                  id="instructorSelect"
                  v-model="instructorSeleccionado"
                  :disabled="isSubmitting"
                  required
                >
                  <option :value="null" selected disabled>
                    -- Selecciona un instructor --
                  </option>
                  <option
                    v-for="instructor in instructoresDisponibles"
                    :key="instructor.id"
                    :value="instructor.id"
                  >
                    {{ instructor.nombre }} {{ instructor.apellidos }}
                    <span v-if="instructor.empresa_id" class="text-muted">
                      (Actualmente en otra empresa)
                    </span>
                  </option>
                </select>
                <small class="text-muted">
                  El instructor será reasignado a esta empresa
                </small>
              </div>

              <!-- Info del instructor seleccionado -->
              <div
                v-if="instructorSeleccionado"
                class="alert alert-warning mt-3"
              >
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Importante:</strong> Si {{ empresa?.nombre }} ya tiene un instructor asignado, este será reemplazado por el instructor seleccionado. El instructor anterior quedará sin empresa asignada.
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="cerrarModalAsignar"
              :disabled="isSubmitting"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="asignarInstructor"
              :disabled="isSubmitting || !instructorSeleccionado"
            >
              <span
                v-if="isSubmitting"
                class="spinner-border spinner-border-sm me-2"
              ></span>
              {{ isSubmitting ? "Asignando..." : "Asignar Instructor" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal backdrop -->
    <div
      v-if="showCrearModal || showAsignarModal"
      class="modal-backdrop fade show"
    ></div>
  </div>
</template>

<style scoped>
.avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f 0%, #2c3e50 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
  flex-shrink: 0;
}

.avatar-medium {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f 0%, #2c3e50 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.8rem;
  flex-shrink: 0;
}

.instructor-card {
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.instructor-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
  border-color: var(--bs-primary);
}

.breadcrumb-item a {
  color: var(--bs-primary);
}

.breadcrumb-item a:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}

.info-item {
  padding: 0.75rem;
  background-color: #f8f9fa;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
}

.instructor-info .info-item {
  background-color: transparent;
  padding: 0.5rem 0;
}

/* Modal styles */
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
  display: block !important;
}

.btn-primary {
  background-color: #81045f;
  border-color: #81045f;
}

.btn-primary:hover {
  background-color: #6a0350;
  border-color: #6a0350;
}

.btn-outline-primary {
  color: #81045f;
  border-color: #81045f;
}

.btn-outline-primary:hover {
  background-color: #81045f;
  border-color: #81045f;
  color: white;
}
</style>