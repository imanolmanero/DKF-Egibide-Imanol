import type { Alumno } from "@/interfaces/Alumno";
import { defineStore } from "pinia";
import { useAuthStore } from "./auth";
import { ref } from "vue";
import type { Asignatura } from "@/interfaces/Asignatura";
import type { NotaCuaderno, NotaEgibide } from "@/interfaces/Notas";

export const useAlumnosStore = defineStore("alumnos", () => {
  const alumnos = ref<Alumno[]>([]);
  const alumno = ref<Alumno[]>([]);
  const asignaturas = ref<Asignatura[]>([]);
  const notaCuaderno = ref<number | null>(null);
  const notasEgibide = ref<NotaEgibide[]>([]);
  const inicio = ref<any | null>(null);
  const loadingInicio = ref(false);

  const authStore = useAuthStore();

  const message = ref<string | null>(null);
  const messageType = ref<"success" | "error">("success");

  const entregas = ref<any[]>([]);
  const loadingEntregas = ref(false);

  async function fetchMisEntregas() {
    loadingEntregas.value = true;
    try {
      const response = await fetch("http://localhost:8000/api/entregas/mias", {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });

      if (!response.ok) throw new Error();
      entregas.value = await response.json();
    } finally {
      loadingEntregas.value = false;
    }
  }

  async function subirEntrega(file: File) {
    const fd = new FormData();
    fd.append("archivo", file);

    const response = await fetch("http://localhost:8000/api/entregas", {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
      },
      body: fd,
    });

    if (!response.ok) throw new Error();

    const nueva = await response.json();
    entregas.value.unshift(nueva);
  }

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

  async function eliminarEntrega(id: number) {
    try {
      await fetch(`http://localhost:8000/api/entregas/${id}`, {
        method: "DELETE",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });

      await fetchMisEntregas(); // refresca la lista
    } catch (e) {
      console.error("Error al eliminar entrega", e);
      throw e;
    }
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

  async function fetchInicio() {
    loadingInicio.value = true;

    try {
      const response = await fetch("http://localhost:8000/api/me/inicio", {
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

      inicio.value = data; // aquí tendrás alumno + estancia + empresa + tutor + instructor + horario
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

  async function guardarNotasEgibideByAlumno(
    alumno_id: number,
    nota: number,
    asignatura_id: number,
  ) {
    const response = await fetch(
      `http://localhost:8000/api/notas/alumno/egibide/guardar`,
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ alumno_id, nota, asignatura_id }),
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

    setMessage(
      data.message || "Nota de Egibide insertada correctamente",
      "success",
    );
    return true;
  }

  async function getNotasEgibideByAlumno(alumno_id: number) {
    const response = await fetch(
      `http://localhost:8000/api/notas/alumno/${alumno_id}/egibide`,
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

    notasEgibide.value = data as NotaEgibide[];
    return true;
  }

  async function guardarNotaCuadernoByAlumno(alumno_id: number, nota: number) {
    const response = await fetch(
      `http://localhost:8000/api/notas/alumno/cuaderno/guardar`,
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ alumno_id, nota }),
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

    setMessage(
      data.message || "Nota de Egibide insertada correctamente",
      "success",
    );
    return true;
  }

  async function getNotaCuadernoByAlumno(alumno_id: number) {
    const response = await fetch(
      `http://localhost:8000/api/notas/alumno/${alumno_id}/cuaderno`,
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

    notaCuaderno.value = Number(data.nota);
    return true;
  }

  async function fetchEntregasAlumno(alumnoId: number) {
    loadingEntregas.value = true;
    try {
      const response = await fetch(
        `http://localhost:8000/api/alumnos/${alumnoId}/entregas`,
        {
          headers: {
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
          },
        },
      );

      if (!response.ok) throw new Error("Error al cargar entregas");

      entregas.value = await response.json(); // Guardamos todas las entregas del alumno
    } catch (err) {
      console.error(err);
      message.value = "Error al cargar las entregas del alumno";
      messageType.value = "error";
    } finally {
      loadingEntregas.value = false;
    }
  }

  return {
    alumnos,
    alumno,
    notaCuaderno,
    asignaturas,
    message,
    messageType,
    entregas,
    loadingEntregas,
    inicio,
    loadingInicio,
    notasEgibide,
    eliminarEntrega,
    fetchInicio,
    subirEntrega,
    fetchMisEntregas,
    fetchAlumnos,
    fetchAlumno,
    getAsignaturasAlumno,
    createAlumno,
    fetchEntregasAlumno,
    guardarNotasEgibideByAlumno,
    getNotasEgibideByAlumno,
    guardarNotaCuadernoByAlumno,
    getNotaCuadernoByAlumno,
  };
});
