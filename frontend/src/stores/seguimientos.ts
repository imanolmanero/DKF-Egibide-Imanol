import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";
import type { Seguimiento } from "@/interfaces/Seguimiento";

export const useSeguimientosStore = defineStore(
  "seguimientos",
  () => {
    const seguimientos = ref<Seguimiento[]>([]);
    const authStore = useAuthStore();

    async function fetchSeguimientos(alumnoId: number) {
      const response = await fetch(
        "http://localhost:8000/api/seguimientos",
        {
          headers: authStore.token
            ? {
                Authorization: `Bearer ${authStore.token}`,
                Accept: "application/json",
              }
            : {
                Accept: "application/json",
              },
        },
      );

      if (!response.ok) return false;

      const data = await response.json();
      seguimientos.value = data as Seguimiento[];
      return true;
    }

    return { seguimientos, fetchSeguimientos };
  },
);
