import type { Alumno } from "@/interfaces/Alumno";
import { defineStore } from "pinia";
import { useAuthStore } from "./auth";
import { ref } from "vue";
import type { Asignatura } from "@/interfaces/Asignatura";
import type { NotaEgibide } from "@/interfaces/Notas";

const baseURL = import.meta.env.VITE_API_BASE_URL;

export const useAlumnosStore = defineStore("alumnos", () => {
  const authStore = useAuthStore();

  // =========================
  // STATE GENERAL
  // =========================
  const alumnos = ref<Alumno[]>([]);
  const alumno = ref<Alumno[]>([]);
  const asignaturas = ref<Asignatura[]>([]);
  const notaCuaderno = ref<number | null>(null);
  const notasEgibide = ref<NotaEgibide[]>([]);
  const inicio = ref<any | null>(null);
  const loadingInicio = ref(false);

  const alumnoDetalle = ref<Alumno | null>(null);
  const loadingAlumnoDetalle = ref(false);
  const errorAlumnoDetalle = ref<string | null>(null);

  const message = ref<string | null>(null);
  const messageType = ref<"success" | "error">("success");

  // =========================
  // ENTREGAS (LEGACY / tabla antigua)
  // =========================
  const entregas = ref<any[]>([]);
  const loadingEntregas = ref(false);

  // =========================
  // NUEVO FLUJO: ENTREGAS CUADERNO (pendientes + realizadas)
  // =========================
  const pendientes = ref<any[]>([]);
  const realizadas = ref<any[]>([]);
  const loadingPendientes = ref(false);

  // =========================
  // HELPERS
  // =========================
  function setMessage(text: string, type: "success" | "error", timeout = 5000) {
    message.value = text;
    messageType.value = type;

    setTimeout(() => {
      message.value = null;
      messageType.value = "success";
    }, timeout);
  }

  function authHeaders(extra?: Record<string, string>) {
    return {
      Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
      Accept: "application/json",
      ...(extra ?? {}),
    };
  }

  // =========================
  // ENTREGAS - LEGACY
  // =========================
  async function fetchMisEntregas() {
    loadingEntregas.value = true;
    try {
      const response = await fetch(`${baseURL}/api/entregas/mias`, {
        headers: authHeaders(),
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        setMessage(data?.message || "Error al cargar entregas", "error");
        entregas.value = [];
        return false;
      }

      entregas.value = data ?? [];
      return true;
    } finally {
      loadingEntregas.value = false;
    }
  }

  async function eliminarEntrega(id: number) {
    try {
      const response = await fetch(`${baseURL}/api/entregas/${id}`, {
        method: "DELETE",
        headers: authHeaders(),
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        setMessage(data?.message || "No se pudo eliminar la entrega", "error");
        throw new Error(data?.message || "Delete error");
      }

      // refresca la lista legacy
      await fetchMisEntregas();
      setMessage(data?.message || "Entrega eliminada correctamente", "success");
    } catch (e) {
      console.error("Error al eliminar entrega", e);
      throw e;
    }
  }

  // =========================
  // NUEVO FLUJO: Pendientes + Realizadas
  // =========================
  async function fetchMisPendientesYRealizadas() {
    loadingPendientes.value = true;
    try {
      const response = await fetch(`${baseURL}/api/entregas-cuaderno/mias`, {
        headers: authHeaders(),
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        setMessage(data?.message || "Error al cargar entregas", "error");
        pendientes.value = [];
        realizadas.value = [];
        return false;
      }

      pendientes.value = data?.pendientes ?? [];
      realizadas.value = data?.realizadas ?? [];

      // compatibilidad: la tabla antigua de la vista usa `entregas`
      // aquí metemos las realizadas dentro de entregas para no romper el template actual
      entregas.value = realizadas.value;

      return true;
    } finally {
      loadingPendientes.value = false;
    }
  }

  async function subirEntrega(file: File, entregaCuadernoId: number) {
    const fd = new FormData();
    fd.append("archivo", file);
    fd.append("entrega_cuaderno_id", String(entregaCuadernoId));

    const response = await fetch(`${baseURL}/api/entregas`, {
      method: "POST",
      headers: authHeaders(), // NO poner Content-Type en FormData
      body: fd,
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "No se pudo subir la entrega", "error");
      throw new Error(data?.message || "Upload error");
    }

    setMessage("Entrega subida correctamente", "success");

    // refrescar pendientes/realizadas
    await fetchMisPendientesYRealizadas();
  }

  // =========================
  // ALUMNOS / ADMIN / INICIO (sin cambios funcionales)
  // =========================
  async function fetchAlumnos() {
    const response = await fetch(`${baseURL}/api/alumnos`, {
      method: "GET",
      headers: authHeaders(),
    });

    const data = await response.json().catch(() => []);

    alumnos.value = data as Alumno[];
  }

  async function fetchAlumnoDetalleAdmin(alumnoId: number) {
    loadingAlumnoDetalle.value = true;
    errorAlumnoDetalle.value = null;

    try {
      const response = await fetch(`${baseURL}/api/admin/alumnos/${alumnoId}`, {
        method: "GET",
        headers: authHeaders(),
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        alumnoDetalle.value = null;
        errorAlumnoDetalle.value =
          data?.message || "Error al cargar el detalle del alumno";
        return null;
      }

      alumnoDetalle.value = data as Alumno;
      return alumnoDetalle.value;
    } catch (e) {
      console.error(e);
      alumnoDetalle.value = null;
      errorAlumnoDetalle.value = "No se pudo conectar con el servidor";
      return null;
    } finally {
      loadingAlumnoDetalle.value = false;
    }
  }

  async function fetchAlumno() {
    const response = await fetch(`${baseURL}/api/me/alumno`, {
      method: "GET",
      headers: authHeaders(),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
      return false;
    }

    alumno.value = Array.isArray(data) ? (data as Alumno[]) : ([data] as Alumno[]);
    return true;
  }

  async function fetchInicio() {
    loadingInicio.value = true;

    try {
      const response = await fetch(`${baseURL}/api/me/inicio`, {
        method: "GET",
        headers: authHeaders(),
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
        inicio.value = null;
        return false;
      }

      inicio.value = data;
      return true;
    } finally {
      loadingInicio.value = false;
    }
  }

  async function createAlumno(
    nombre: string,
    apellidos: string,
    telefono: number,
    ciudad: string,
    tutor: number,
  ) {
    const response = await fetch(`${baseURL}/api/alumnos`, {
      method: "POST",
      headers: authHeaders({ "Content-Type": "application/json" }),
      body: JSON.stringify({ nombre, apellidos, telefono, ciudad, tutor }),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
      return false;
    }

    setMessage(data?.message || "Alumno creado correctamente", "success");
    return true;
  }

  async function getAsignaturasAlumno(alumno_id: number) {
    const response = await fetch(`${baseURL}/api/alumnos/${alumno_id}/asignaturas`, {
      method: "GET",
      headers: authHeaders(),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
      return false;
    }

    asignaturas.value = data as Asignatura[];
    return true;
  }

  async function guardarNotasEgibideByAlumno(
    alumno_id: number,
    nota: number,
    asignatura_id: number,
  ) {
    const response = await fetch(`${baseURL}/api/notas/alumno/egibide/guardar`, {
      method: "POST",
      headers: authHeaders({ "Content-Type": "application/json" }),
      body: JSON.stringify({ alumno_id, nota, asignatura_id }),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
      return false;
    }

    setMessage(data?.message || "Nota de Egibide insertada correctamente", "success");
    return true;
  }

  async function getNotasEgibideByAlumno(alumno_id: number) {
    const response = await fetch(`${baseURL}/api/notas/alumno/${alumno_id}/egibide`, {
      method: "GET",
      headers: authHeaders(),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
      return false;
    }

    notasEgibide.value = data as NotaEgibide[];
    return true;
  }

  async function guardarNotaCuadernoByAlumno(alumno_id: number, nota: number) {
    const response = await fetch(`${baseURL}/api/notas/alumno/cuaderno/guardar`, {
      method: "POST",
      headers: authHeaders({ "Content-Type": "application/json" }),
      body: JSON.stringify({ alumno_id, nota }),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
      return false;
    }

    setMessage(data?.message || "Nota de cuaderno insertada correctamente", "success");
    return true;
  }

  async function getNotaCuadernoByAlumno(alumno_id: number) {
    const response = await fetch(`${baseURL}/api/notas/alumno/${alumno_id}/cuaderno`, {
      method: "GET",
      headers: authHeaders(),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      setMessage(data?.message || "Error desconocido, inténtalo más tarde", "error");
      return false;
    }

    notaCuaderno.value = Number(data?.nota);
    return true;
  }

  async function fetchEntregasAlumno(alumnoId: number) {
    loadingEntregas.value = true;
    try {
      const response = await fetch(`${baseURL}/api/alumnos/${alumnoId}/entregas`, {
        headers: authHeaders(),
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) throw new Error(data?.message || "Error al cargar entregas");

      entregas.value = data ?? [];
    } catch (err) {
      console.error(err);
      message.value = "Error al cargar las entregas del alumno";
      messageType.value = "error";
    } finally {
      loadingEntregas.value = false;
    }
  }

  return {
    // state
    alumnos,
    alumno,
    asignaturas,
    notaCuaderno,
    notasEgibide,
    inicio,
    loadingInicio,
    alumnoDetalle,
    loadingAlumnoDetalle,
    errorAlumnoDetalle,
    message,
    messageType,

    // legacy entregas
    entregas,
    loadingEntregas,

    // nuevo flujo
    pendientes,
    realizadas,
    loadingPendientes,

    // actions
    setMessage,
    fetchAlumnos,
    fetchAlumno,
    fetchInicio,
    fetchAlumnoDetalleAdmin,
    createAlumno,
    getAsignaturasAlumno,
    guardarNotasEgibideByAlumno,
    getNotasEgibideByAlumno,
    guardarNotaCuadernoByAlumno,
    getNotaCuadernoByAlumno,
    fetchEntregasAlumno,

    // entregas legacy
    fetchMisEntregas,
    eliminarEntrega,

    // nuevo flujo
    fetchMisPendientesYRealizadas,
    subirEntrega,
  };
});
