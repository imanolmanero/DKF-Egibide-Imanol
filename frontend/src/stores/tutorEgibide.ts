import type { Alumno } from "@/interfaces/Alumno";
import type { Curso } from "@/interfaces/Curso";
import type { Empresa } from "@/interfaces/Empresa";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";

const baseURL = import.meta.env.VITE_API_BASE_URL;

export const useTutorEgibideStore = defineStore("tutorEgibide", () => {
  const alumnosAsignados = ref<Alumno[]>([]);
  const empresasAsignadas = ref<Empresa[]>([]);
  const misCursos = ref<Curso[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const inicio = ref(null);
  const loadingInicio = ref(false);

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
        `${baseURL}/api/tutorEgibide/${tutorId}/alumnos`,
        {
          headers: {
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
          },
        },
      );

      const data = await response.json();
      if (!response.ok) {
        setMessage(
          data.message || "Error desconocido al cargar alumnos",
          "error",
        );
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
  // Traer alumnos asignados
  async function fetchEmpresasAsignadas(tutorId: string) {
    loading.value = true;
    try {
      const response = await fetch(
        `${baseURL}/api/tutorEgibide/${tutorId}/empresas`,
        {
          headers: {
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
          },
        },
      );

      const data = await response.json();
      if (!response.ok) {
        setMessage(
          data.message || "Error desconocido al cargar empresas",
          "error",
        );
        return false;
      }

      empresasAsignadas.value = data as Empresa[];
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al obtener empresas", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  //Traer grados con alumnos sin tutor asignado
  async function fetchAlumnosDeMiCursoSinTutorAsignado(tutorId: string) {
    loading.value = true;
    try {
      const response = await fetch(
        `${baseURL}/api/tutorEgibide/${tutorId}/cursos/alumnos`,
        {
          headers: {
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
          },
        },
      );

      const data = await response.json();
      if (!response.ok) {
        setMessage(
          data.message || "Error desconocido al cargar cursos",
          "error",
        );
        return false;
      }

      misCursos.value = data as Curso[];
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al obtener empresas", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // Asignar alumno al tutor actual
  async function asignarAlumnoATutor(alumnoId: number, tutorId: number) {
    loading.value = true;
    try {
      const response = await fetch(
        `${baseURL}/api/tutorEgibide/asignarAlumno`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
          },
          body: JSON.stringify({ alumno_id: alumnoId, tutor_id: tutorId }),
        },
      );

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || "Error al asignar alumno", "error");
        return false;
      }

      setMessage(`Alumno asignado correctamente`, "success");
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al asignar alumno", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function fetchInicioTutor() {
    loadingInicio.value = true;

    try {
      const response = await fetch(`${baseURL}/api/tutorEgibide/inicio`, {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });

      const data = await response.json();
      if (!response.ok) {
        setMessage(
          data.message || "Error desconocido, inténtalo más tarde",
          "error",
        );
        inicio.value = null;
        return false;
      }

      inicio.value = data;
      return true;
    } finally {
      loadingInicio.value = false;
    }
  }

  // Guardar horario y periodo de alumno
  async function guardarHorarioAlumno(
    alumnoId: number,
    data: {
      fecha_inicio: string;
      fecha_fin: string | null;
      horario: any[];
    }
  ) {
    if (!data.fecha_inicio) {
      setMessage("Debes indicar fecha de inicio", "error");
      return false;
    }

    try {
      const payload = {
        alumno_id: alumnoId,
        fecha_inicio: data.fecha_inicio,
        fecha_fin: data.fecha_fin,
        horario: data.horario,
      };

      const response = await fetch(`${baseURL}/api/horasperiodo`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: authStore.token
            ? `Bearer ${authStore.token}`
            : "",
          Accept: "application/json",
        },
        body: JSON.stringify(payload),
      });

      const dataResponse = await response.json();

      if (!response.ok) {
        setMessage(
          dataResponse?.message || "Error al guardar horario",
          "error"
        );
        return false;
      }

      setMessage("Horario guardado correctamente", "success");
      return true;

    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al guardar horario", "error");
      return false;
    }
  }

  async function fetchHorarioAlumno(alumnoId: number) {
    loading.value = true;
    try {
      const response = await fetch(`${baseURL}/api/horario/${alumnoId}`, {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || "Error al obtener horario", "error");
        return null;
      }

      return data;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al obtener horario", "error");
      return null;
    } finally {
      loading.value = false;
    }
  }



  async function updateAlumnoEmpresa(alumnoId: number, empresaId: number) {
    const alumnoToUpdate = alumnosAsignados.value.find(
      (a) => a.id === alumnoId,
    );
    if (!alumnoToUpdate) return;

    // Aseguramos que pivot existe
    if (!alumnoToUpdate.estancias) {
      alumnoToUpdate.estancias = {
        alumno_id: alumnoId,
        empresa_id: empresaId,
      } as any;
    } else {
      const estancia = alumnoToUpdate.estancias[0] ?? null;
      if(estancia){
        estancia.empresa_id = empresaId;
      }
    }
  }

  return {
    alumnosAsignados,
    empresasAsignadas,
    misCursos,
    loading,
    error,
    message,
    messageType,
    inicio,
    loadingInicio,
    fetchInicioTutor,
    fetchAlumnosAsignados,
    fetchEmpresasAsignadas,
    guardarHorarioAlumno,
    fetchHorarioAlumno,
    updateAlumnoEmpresa,
    setMessage,
    fetchAlumnosDeMiCursoSinTutorAsignado,
    asignarAlumnoATutor,
  };
});
