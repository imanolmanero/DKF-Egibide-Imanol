<script setup>
import { onMounted } from "vue";
import { useAdminStore } from "@/stores/admin";

const adminStore = useAdminStore();

onMounted(() => {
  adminStore.fetchInicioAdmin();
});
</script>

<template>
  <div>
    <h1 class="display-5 fw-bold mb-2">
      Panel de administración
    </h1>

    <div class="subrayado mb-4"></div>

    <p class="descripcion mb-4">
      Consulta tu email y el recuento global de alumnos, empresas, tutores y ciclos.
    </p>

    <p v-if="adminStore.loadingInicio">Cargando información...</p>

    <p v-else-if="!adminStore.inicio?.admin">
      No autorizado o sin datos.
    </p>

    <div v-else class="row g-3">

      <!-- EMAIL -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Email</div>
              <div class="fw-bold">{{ adminStore.inicio.admin.email ?? "—" }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CONT ALUMNOS -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-people-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Alumnos</div>
              <div class="fw-bold">{{ adminStore.inicio.counts?.alumnos ?? 0 }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CONT EMPRESAS -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-building"></i>
            </div>
            <div>
              <div class="text-muted small">Empresas</div>
              <div class="fw-bold">{{ adminStore.inicio.counts?.empresas ?? 0 }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CONT TUTORES -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Tutores</div>
              <div class="fw-bold">{{ adminStore.inicio.counts?.tutores ?? 0 }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CONT CICLOS -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-diagram-3-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Ciclos</div>
              <div class="fw-bold">{{ adminStore.inicio.counts?.ciclos ?? 0 }}</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.subrayado {
  width: 120px;
  height: 6px;
  background: #80015e;
  border-radius: 10px;
}

.icon-circle {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(111, 45, 168, 0.12);
  display: grid;
  place-items: center;
  color: #80015e;
  font-size: 18px;
}
</style>
