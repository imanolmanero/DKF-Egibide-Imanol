import type { Empresa } from "@/interfaces/Empresa";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";

export const useEmpresasStore = defineStore('empresas', () => {
  const empresas = ref<Empresa[]>([]);
  const authStore = useAuthStore(); // Para el token si la API es protegida

  // Obtener todos las competencias
  async function fetchEmpresas() {
    const response = await fetch('http://localhost:8000/api/empresas', {
      headers: authStore.token ? {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      } : {
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    empresas.value = data as Empresa[];
  }

  return { empresas, fetchEmpresas };
});
