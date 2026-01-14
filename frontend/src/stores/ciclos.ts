import type { Ciclo } from "@/interfaces/Ciclo";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";

export const useCiclosStore = defineStore('ciclos', () => {
  const ciclos = ref<Ciclo[]>([]);
  const authStore = useAuthStore(); // Para el token si la API es protegida

  // Obtener todos los ciclos
  async function fetchCiclos() {
    const response = await fetch('http://localhost:8000/api/ciclos', {
      headers: authStore.token ? {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      } : {
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    ciclos.value = data as Ciclo[];
  }

  return { ciclos, fetchCiclos };
});
