<script setup>
import { onMounted } from "vue";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";

const tutorStore = useTutorEgibideStore();

onMounted(() => {
  tutorStore.fetchInicioTutor();
  
});

</script>

<template>
  <div>
    <!-- LOADING GLOBAL -->
    <div v-if="tutorStore.loadingInicio" class="text-center py-5">
      <div class="spinner-border text-secondary" role="status"></div>
      <p class="mt-3">Cargando información...</p>
    </div>

    <!-- CONTENIDO SOLO CUANDO YA ESTÁ TODO -->
    <div v-else>
      <h2 class="display-5 fw-bold mb-2">
        Hola, {{ tutorStore.inicio?.tutor?.nombre }}
      </h2>

      <div class="subrayado mb-4"></div>

      <p class="descripcion mb-4">
        Consulta tus datos de tutor, y el número de alumnos y empresas que tienes asignadas.
      </p>

      <p v-if="!tutorStore.inicio?.tutor">
        No tienes tutor egibide asociado.
      </p>

      <div v-else class="row g-3">

      <!-- NOMBRE -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-person-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Nombre</div>
              <div class="fw-bold">{{ tutorStore.inicio.tutor.nombre ?? "—" }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- APELLIDOS -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-person-lines-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Apellidos</div>
              <div class="fw-bold">{{ tutorStore.inicio.tutor.apellidos ?? "—" }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- EMAIL -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Email</div>
              <div class="fw-bold">{{ tutorStore.inicio.tutor.email ?? "—" }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- TELÉFONO -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Teléfono</div>
              <div class="fw-bold">{{ tutorStore.inicio.tutor.telefono ?? "—" }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CIUDAD -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Ciudad</div>
              <div class="fw-bold">{{ tutorStore.inicio.tutor.ciudad ?? "—" }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CONTADOR ALUMNOS -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-people-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Alumnos asignados</div>
              <div class="fw-bold">
                {{ tutorStore.inicio.counts?.alumnos_asignados ?? 0 }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CONTADOR EMPRESAS -->
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-building"></i>
            </div>
            <div>
              <div class="text-muted small">Empresas asignadas</div>
              <div class="fw-bold">
                {{ tutorStore.inicio.counts?.empresas_asignadas ?? 0 }}
              </div>
            </div>
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
