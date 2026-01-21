import type { Alumno } from "@/interfaces/Alumno";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";

export const useTutorEgibideStore = defineStore("tutorEgibide", () => {
  const alumnosAsignados = ref<Alumno[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const authStore = useAuthStore();

  // Toast
  const message = ref<string | null>(null);
  const messageType = ref<"success" | "error">("success");

  function setMessage(text: string, type: "success" | "error", timeout = 5000) {
    message.value = text;
    messageType.value = type;

    setTimeout(() => {
      message.value = null;
      messageType.value = "success";
    }, timeout);
  }

  // Traer alumnos asignados
  async function fetchAlumnosAsignados(tutorId: string) {
    loading.value = true;
    try {
      const response = await fetch(
        `http://localhost:8000/api/tutorEgibide/${tutorId}/alumnos`,
        {
          headers: {
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
          },
        }
      );

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || "Error desconocido al cargar alumnos", "error");
        return false;
      }

      alumnosAsignados.value = data as Alumno[];
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al obtener alumnos", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // Guardar horario y periodo de alumno
  async function guardarHorarioAlumno(
    alumnoId: number,
    fechaInicio: string,
    fechaFin: string | null,
    horasTotales: number
  ) {
    if (!fechaInicio || !horasTotales) {
      setMessage("Debes completar la fecha inicio y las horas totales", "error");
      return false;
    }

    try {
      const payload = {
        alumno_id: alumnoId,
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin || null,
        horas_totales: horasTotales,
      };

      const response = await fetch("http://localhost:8000/api/horasperiodo", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        },
        body: JSON.stringify(payload),
      });

      const contentType = response.headers.get("content-type") || "";
      let data: any = null;
      if (contentType.includes("application/json")) {
        data = await response.json();
      }

      if (!response.ok) {
        setMessage(
          data?.message || `Error al guardar la estancia. Código: ${response.status}`,
          "error"
        );
        return false;
      }

      // Actualizar store local
      const alumnoStore = alumnosAsignados.value.find(
        (a) => Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId
      );

      if (alumnoStore) {
        (alumnoStore as any).pivot = {
          ...(alumnoStore.pivot ?? { alumno_id: alumnoId }),
          fecha_inicio: fechaInicio,
          fecha_fin: fechaFin,
          horas_totales: horasTotales,
        };
      }

      setMessage("Horario y calendario guardados correctamente", "success");
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al guardar la estancia", "error");
      return false;
    }
  }

  async function updateAlumnoEmpresa(alumnoId: number, empresaId: number) {
    const alumnoToUpdate = alumnosAsignados.value.find(a => a.id === alumnoId);
    if (!alumnoToUpdate) return;

    // Aseguramos que pivot existe
    if (!alumnoToUpdate.pivot) {
      alumnoToUpdate.pivot = { alumno_id: alumnoId, empresa_id: empresaId } as any;
    } else {
      alumnoToUpdate.pivot.empresa_id = empresaId;
    }
  }

  return {
    alumnosAsignados,
    loading,
    error,
    message,
    messageType,
    fetchAlumnosAsignados,
    guardarHorarioAlumno,
    setMessage,
    updateAlumnoEmpresa
  };
});
