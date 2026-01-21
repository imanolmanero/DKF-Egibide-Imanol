import type { Empresa } from "@/interfaces/Empresa";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";
import { useTutorEgibideStore } from "./tutorEgibide";

export const useEmpresasStore = defineStore("empresas", () => {
  const empresas = ref<Empresa[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

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

  async function fetchEmpresas() {
    const response = await fetch("http://localhost:8000/api/empresas", {
      headers: authStore.token
        ? {
            Authorization: `Bearer ${authStore.token}`,
            Accept: "application/json",
          }
        : {
            Accept: "application/json",
          },
    });

    const data = await response.json();
    empresas.value = data as Empresa[];
  }

  async function fetchMiEmpresa() {
      const response = await fetch("http://localhost:8000/api/me/empresa", {
        headers: authStore.token
          ? {
              Authorization: `Bearer ${authStore.token}`,
              Accept: "application/json",
            }
          : {
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
      empresas.value = Array.isArray(data) ? (data as Empresa[]) : ([data] as Empresa[]);
    }

  async function createEmpresa(
    nombre: string,
    cif: string,
    telefono: number,
    email: string,
    direccion: string,
  ) {
    const response = await fetch("http://localhost:8000/api/empresas", {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ nombre, cif, telefono, email, direccion }),
    });

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    setMessage(data.message || "Empresa creada correctamente", "success");
    return true;
  }

  async function asignarEmpresa(alumno_id: number, empresa_id: number) {
    const response = await fetch(
      "http://localhost:8000/api/empresas/asignar",
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ alumno_id, empresa_id }),
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
      data.message || "Empresa asignada correctamente",
      "success",
    );

    // ⚡ Aquí: instancia del store de tutorEgibide
    const tutorEgibideStore = useTutorEgibideStore();
    // Actualizar el alumno localmente para que se vea en la UI sin recargar
    tutorEgibideStore.updateAlumnoEmpresa(alumno_id, empresa_id);

    return true;
  }

  return { empresas, message, messageType, fetchEmpresas, createEmpresa, fetchMiEmpresa, asignarEmpresa };
});

  