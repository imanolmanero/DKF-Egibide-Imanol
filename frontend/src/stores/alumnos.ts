import type { Alumno } from "@/interfaces/Alumno";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";

export const useAlumnosStore = defineStore('alumnos', () => {
  const alumnos = ref<Alumno[]>([]);
  const authStore = useAuthStore(); // Para el token si la API es protegida

  // Obtener todos las competencias
  async function fetchAlumnos() {
    const response = await fetch('http://localhost:8000/api/alumnos', {
      headers: authStore.token ? {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      } : {
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    alumnos.value = data as Alumno[];
  }

  return { alumnos, fetchAlumnos };
});
