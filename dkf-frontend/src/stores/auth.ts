import type { User } from "@/interfaces/User";
import { defineStore } from "pinia";
import { ref } from "vue";
import router from "@/router";

export const useAuthStore = defineStore("auth", () => {
  const currentUser = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem("token"));
  const error = ref<string | null>(null);

  async function login(email: string, password: string) {
    try {
      const response = await fetch("http://localhost:8000/api/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (!response.ok) {
        error.value = data.message || "Error desconocido, intentalo más tarde";
        setTimeout(() => {
          error.value = null;
        }, 5000);
        return false;
      }

      token.value = data.token;
      localStorage.setItem("token", data.token);
      currentUser.value = data.user as User;
      error.value = null;
      router.replace("/");
      return true;
    } catch (err) {
      error.value = "Error de conexión";
      setTimeout(() => {
        error.value = null;
      }, 5000);
      return false;
    }
  }

  function logout() {
    localStorage.removeItem("token");
    currentUser.value = null;
  }

  async function fetchCurrentUser() {
    if (!token.value) return;

    try {
      const response = await fetch("http://localhost:8000/api/user", {
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: "application/json",
        },
      });

      if (!response.ok) {
        logout();
        return false;
      }

      const user = await response.json();
      currentUser.value = user as User;
    } catch (error) {
      console.error(error);
      logout();
    }
  }

  return { currentUser, token, error, login, logout, fetchCurrentUser };
});
