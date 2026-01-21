<script setup>
import { onMounted } from "vue";
import { useAlumnosStore } from "@/stores/alumnos"; // <-- usa tu ruta real

const alumnosStore = useAlumnosStore();

onMounted(() => {
  alumnosStore.fetchInicio();
});

function formatDate(iso) {
  if (!iso) return "—";
  // iso tipo "2026-01-05"
  const d = new Date(iso);
  if (isNaN(d.getTime())) return iso;
  return d.toLocaleDateString("es-ES", { day: "numeric", month: "long", year: "numeric" });
}
</script>

<template>
  <div>
    <h1 class="display-5 fw-bold mb-2">
      Hola, {{ alumnosStore.inicio?.alumno?.nombre ?? "" }}
    </h1>
    
    <div class="subrayado mb-4"></div>

    <p class="descripcion mb-4">
      Consulte la empresa asignada, el horario de prácticas, sus calificaciones y
      la información relevante sobre su estancia formativa.
    </p>

    <p v-if="alumnosStore.loadingInicio">Cargando información...</p>

    <p v-else-if="!alumnosStore.inicio?.estancia">
      No tienes estancia asignada.
    </p>

    <div v-else class="row g-3">

      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Tutor (centro)</div>
              <div class="fw-bold">
                {{ alumnosStore.inicio.estancia.tutor
                  ? alumnosStore.inicio.estancia.tutor.nombre + " " + alumnosStore.inicio.estancia.tutor.apellidos
                  : "—" }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Teléfono tutor</div>
              <div class="fw-bold">
                {{ alumnosStore.inicio.estancia.tutor?.telefono ?? "—" }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-person-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Instructor (empresa)</div>
              <div class="fw-bold">
                {{ alumnosStore.inicio.estancia.instructor
                  ? alumnosStore.inicio.estancia.instructor.nombre + " " + alumnosStore.inicio.estancia.instructor.apellidos
                  : "—" }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Teléfono instructor</div>
              <div class="fw-bold">
                {{ alumnosStore.inicio.estancia.instructor?.telefono ?? "—" }}
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Empresa</div>
              <div class="fw-bold">
                {{ alumnosStore.inicio.estancia.empresa?.nombre ?? "—" }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon-circle">
              <i class="bi bi-briefcase-fill"></i>
            </div>
            <div>
              <div class="text-muted small">Periodo</div>
              <div class="fw-bold">
                {{ formatDate(alumnosStore.inicio.estancia.fecha_inicio) }}
                -
                {{ formatDate(alumnosStore.inicio.estancia.fecha_fin) }}
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