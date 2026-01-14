import type { Competencia } from "@/interfaces/Competencia";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";

export const useCompetenciasStore = defineStore('competencias', () => {
  const competencias = ref<Competencia[]>([]);
  const authStore = useAuthStore(); // Para el token si la API es protegida

  // Obtener todos las competencias
  async function fetchCompetencias() {
    const response = await fetch('http://localhost:8000/api/competencias', {
      headers: authStore.token ? {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      } : {
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    competencias.value = data as Competencia[];
  }

  return { competencias, fetchCompetencias };
});
