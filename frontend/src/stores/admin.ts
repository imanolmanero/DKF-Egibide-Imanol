import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { useAuthStore } from "@/stores/auth";

const baseURL = import.meta.env.VITE_API_BASE_URL;

// ---- Tipos ----
export interface Empresa {
  id: number;
  nombre: string;
}

export interface Estancia {
  id: number;
  puesto: string | null;
  fecha_inicio: string | null;
  fecha_fin: string | null;
  horas_totales: number | null;
  empresa_id: number | null;
  empresa?: Empresa | null;
}

export interface Alumno {
  id: number;
  nombre: string;
  apellidos: string;
  telefono?: string | null;
  ciudad?: string | null;
  estancias?: Estancia[];
}

export type AdminInicio = unknown;


export const useAdminStore = defineStore("admin", () => {
  const authStore = useAuthStore();

  // ----------------------------
  // STATE: Inicio admin
  // ----------------------------
  const inicio = ref<AdminInicio | null>(null);
  const loadingInicio = ref(false);
  const errorInicio = ref<string | null>(null);

  // ----------------------------
  // STATE: Alumnos admin (lista + detalle)
  // ----------------------------
  const alumnos = ref<Alumno[]>([]);
  const loadingAlumnos = ref(false);
  const errorAlumnos = ref<string | null>(null);

  const alumnoDetalle = ref<Alumno | null>(null);
  const loadingAlumnoDetalle = ref(false);
  const errorAlumnoDetalle = ref<string | null>(null);

  // ----------------------------
  // GETTERS
  // ----------------------------
  const isAuthenticated = computed(() => !!authStore.token);

  // ----------------------------
  // HELPERS
  // ----------------------------
  const authHeaders = () => {
    const headers: Record<string, string> = {
      Accept: "application/json",
    };
    if (authStore.token) headers.Authorization = `Bearer ${authStore.token}`;
    return headers;
  };

  const parseJsonSafe = async (response: Response) => {
    try {
      return await response.json();
    } catch {
      return null;
    }
  };

  const buildErrorMessage = (data: any, response: Response) => {
    return (
      (data && (data.message || data.error)) ||
      `Error HTTP ${response.status}`
    );
  };

  // ----------------------------
  // ACTIONS: Inicio
  // ----------------------------
  async function fetchInicioAdmin(): Promise<boolean> {
    loadingInicio.value = true;
    errorInicio.value = null;

    try {
      const response = await fetch(`${baseURL}/api/admin/inicio`, {
        method: "GET",
        headers: authHeaders(),
      });

      const data = await parseJsonSafe(response);

      if (!response.ok) {
        inicio.value = null;
        errorInicio.value = String(buildErrorMessage(data, response));
        return false;
      }

      inicio.value = data as AdminInicio;
      return true;
    } catch (e) {
      inicio.value = null;
      errorInicio.value = "No se pudo conectar con el servidor";
      console.error(e);
      return false;
    } finally {
      loadingInicio.value = false;
    }
  }

  // ----------------------------
  // ACTIONS: Alumnos (lista)
  // ----------------------------
  async function fetchAlumnosAdmin(): Promise<boolean> {
    loadingAlumnos.value = true;
    errorAlumnos.value = null;

    try {
      const response = await fetch(`${baseURL}/admin/alumnos`, {
        method: "GET",
        headers: authHeaders(),
      });

      const data = await parseJsonSafe(response);

      if (!response.ok) {
        alumnos.value = [];
        errorAlumnos.value = String(buildErrorMessage(data, response));
        return false;
      }

      alumnos.value = (data ?? []) as Alumno[];
      return true;
    } catch (e) {
      alumnos.value = [];
      errorAlumnos.value = "No se pudo conectar con el servidor";
      console.error(e);
      return false;
    } finally {
      loadingAlumnos.value = false;
    }
  }

  // ----------------------------
  // ACTIONS: Alumno detalle (con estancias y empresa)
  // ----------------------------
  async function fetchAlumnoDetalleAdmin(alumnoId: number): Promise<Alumno | null> {
    loadingAlumnoDetalle.value = true;
    errorAlumnoDetalle.value = null;

    try {
      const response = await fetch(`${baseURL}/admin/alumnos/${alumnoId}`, {
        method: "GET",
        headers: authHeaders(),
      });

      const data = await parseJsonSafe(response);

      if (!response.ok) {
        alumnoDetalle.value = null;
        errorAlumnoDetalle.value = String(buildErrorMessage(data, response));
        return null;
      }

      alumnoDetalle.value = data as Alumno;
      return alumnoDetalle.value;
    } catch (e) {
      alumnoDetalle.value = null;
      errorAlumnoDetalle.value = "No se pudo conectar con el servidor";
      console.error(e);
      return null;
    } finally {
      loadingAlumnoDetalle.value = false;
    }
  }

  // ----------------------------
  // ACTIONS: Clear helpers
  // ----------------------------
  function clearInicio() {
    inicio.value = null;
    errorInicio.value = null;
  }

  function clearAlumnos() {
    alumnos.value = [];
    errorAlumnos.value = null;
  }

  function clearAlumnoDetalle() {
    alumnoDetalle.value = null;
    errorAlumnoDetalle.value = null;
  }

  return {
    // inicio
    inicio,
    loadingInicio,
    errorInicio,
    fetchInicioAdmin,
    clearInicio,

    // alumnos
    alumnos,
    loadingAlumnos,
    errorAlumnos,
    fetchAlumnosAdmin,
    clearAlumnos,

    // detalle alumno
    alumnoDetalle,
    loadingAlumnoDetalle,
    errorAlumnoDetalle,
    fetchAlumnoDetalleAdmin,
    clearAlumnoDetalle,

    // getter
    isAuthenticated,
  };
});
