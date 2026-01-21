import type { Alumno } from "@/interfaces/Alumno";
import { defineStore } from "pinia";
import { useAuthStore } from "./auth";
import { ref } from "vue";
import type { Asignatura } from "@/interfaces/Asignatura";

export const useAlumnosStore = defineStore("alumnos", () => {
  const alumnos = ref<Alumno[]>([]);
  const alumno = ref<Alumno[]>([]);
  const asignaturas = ref<Asignatura[]>([]);
  const notaCuaderno = ref<number | null>(null);
  const notaCuadernoMsg = ref<string | null>(null);

  const authStore = useAuthStore();

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

  async function fetchAlumnos() {
    const response = await fetch("http://localhost:8000/api/alumnos", {
      method: "GET",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
      },
    });

    const data = await response.json();
    alumnos.value = data as Alumno[];
  }

  async function fetchAlumno() {
    const response = await fetch("http://localhost:8000/api/me/alumno", {
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
      return false;
    }

    alumno.value = Array.isArray(data)
      ? (data as Alumno[])
      : ([data] as Alumno[]);
  }

  async function fetchNotaCuaderno() {
    const response = await fetch("http://localhost:8000/api/me/nota-cuaderno", {
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
      notaCuaderno.value = null;
      notaCuadernoMsg.value = null;
      return false;
    }

    notaCuaderno.value = data.nota ?? null;
    notaCuadernoMsg.value = data.message ?? null;

    return true;
  }

  async function createAlumno(
    nombre: string,
    apellidos: string,
    telefono: number,
    ciudad: string,
  ) {
    const response = await fetch("http://localhost:8000/api/alumnos", {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ nombre, apellidos, telefono, ciudad }),
    });

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    setMessage(data.message || "Alumno creado correctamente", "success");
    return true;
  }

  async function getAsignaturasAlumno(alumno_id: number) {
    const response = await fetch(
      `http://localhost:8000/api/alumnos/${alumno_id}/asignaturas`,
      {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    asignaturas.value = data as Asignatura[];
    return true;
  }

  return {
    alumnos,
    alumno,
    notaCuaderno,
    notaCuadernoMsg,
    asignaturas,
    message,
    messageType,
    fetchAlumnos,
    fetchAlumno,
    getAsignaturasAlumno,
    fetchNotaCuaderno,
    createAlumno,
  };
});
