import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";

export const useAdminStore = defineStore("admin", () => {
  const authStore = useAuthStore();

  const inicio = ref(null);
  const loadingInicio = ref(false);

  async function fetchInicioAdmin() {
    loadingInicio.value = true;

    try {
      const response = await fetch("http://localhost:8000/api/admin/inicio", {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });

      const data = await response.json();

      if (!response.ok) {
        inicio.value = null;
        return false;
      }

      inicio.value = data;
      return true;
    } finally {
      loadingInicio.value = false;
    }
  }

  return {
    inicio,
    loadingInicio,
    fetchInicioAdmin,
  };
});