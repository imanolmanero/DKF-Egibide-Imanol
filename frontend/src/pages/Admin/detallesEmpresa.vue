<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

interface Instructor {
  id: number;
  nombre: string;
  apellidos: string;
  telefono: string | null;
  ciudad: string | null;
}

interface EmpresaDetalle extends Empresa {
  instructores?: Instructor[];
}

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const empresa = ref<EmpresaDetalle | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

const empresaId = Number(route.params.empresaId);

onMounted(async () => {
  await cargarDetalleEmpresa();
});

const cargarDetalleEmpresa = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    if (!Number.isFinite(empresaId)) {
      error.value = "ID de empresa inválido";
      empresa.value = null;
      return;
    }

    const response = await fetch(
      `http://localhost:8000/api/admin/empresas/${empresaId}`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.message || "Error al cargar los datos de la empresa");
    }

    empresa.value = data as EmpresaDetalle;
  } catch (err) {
    console.error(err);
    error.value = "No se pudo cargar la información de la empresa";
    empresa.value = null;
  } finally {
    isLoading.value = false;
  }
};

const volver = () => router.back();
</script>

<template>
  <div class="container mt-4">
    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">Cargando información de la empresa...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver a empresas
        </button>
      </div>
    </div>

    <!-- Sin empresa -->
    <div v-else-if="!empresa" class="alert alert-warning d-flex align-items-center">
      <i class="bi bi-building-x me-2"></i>
      <div>
        No se encontró información de la empresa
        <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <!-- Contenido -->
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

      <!-- Card empresa -->
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

      <!-- Instructores -->
      <div v-if="empresa.instructores && empresa.instructores.length > 0">
        <h4 class="mb-3">
          <i class="bi bi-person-badge-fill me-2"></i>
          {{ empresa.instructores.length === 1 ? "Instructor" : "Instructores" }}
        </h4>

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
                    <h5 class="mb-0">{{ instructor.nombre }} {{ instructor.apellidos }}</h5>
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

      <!-- Sin instructores -->
      <div v-else class="alert alert-info mt-3">
        <i class="bi bi-info-circle-fill me-2"></i>
        No hay instructores asignados a esta empresa actualmente.
      </div>
    </div>
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
</style>
