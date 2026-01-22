import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";
import type { Seguimiento } from "@/interfaces/Seguimiento";

export const useSeguimientosStore = defineStore("seguimientos", () => {
  const seguimientos = ref<Seguimiento[]>([]);
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

  // Traer seguimientos de un alumno
  async function fetchSeguimientos(alumnoId: number) {
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(`http://localhost:8000/api/seguimientos/alumno/${alumnoId}`, {
        headers: authStore.token
          ? { Authorization: `Bearer ${authStore.token}`, Accept: "application/json" }
          : { Accept: "application/json" },
      });

      const data = await res.json();

      if (!res.ok) {
        setMessage(data.message || `Error ${res.status} al cargar seguimientos`, "error");
        seguimientos.value = [];
        return false;
      }

      seguimientos.value = data as Seguimiento[];
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al obtener seguimientos", "error");
      seguimientos.value = [];
      return false;
    } finally {
      loading.value = false;
    }
  }

  // Crear un nuevo seguimiento
  async function nuevoSeguimiento(payload: {
    alumno_id: number;
    fecha: string | null;
    accion: string;
    descripcion?: string;
    via?: string;
  }) {
    if (!payload.fecha) {
      setMessage("La fecha es obligatoria", "error");
      return false;
    }

    loading.value = true;
    error.value = null;

    try {
      const res = await fetch("http://localhost:8000/api/nuevo-seguimiento", {
        method: "POST",
        headers: authStore.token
          ? { "Content-Type": "application/json", Authorization: `Bearer ${authStore.token}` }
          : { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const contentType = res.headers.get("content-type") || "";
      let data: any = null;
      if (contentType.includes("application/json")) {
        data = await res.json();
      }

      if (!res.ok) {
        setMessage(data?.message || `Error ${res.status} al crear seguimiento`, "error");
        return false;
      }

      // Añadir el seguimiento creado al array local
      seguimientos.value.unshift(data.seguimiento);
      setMessage("Seguimiento creado correctamente", "success");
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión al crear seguimiento", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  return {
    seguimientos,
    loading,
    error,
    message,
    messageType,
    fetchSeguimientos,
    nuevoSeguimiento,
    setMessage,
  };
});
