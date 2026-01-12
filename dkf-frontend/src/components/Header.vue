<script setup lang="ts">
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

function logout() {
  authStore.logout();
}
</script>

<template>
  <nav class="navbar navbar-expand-lg island">
    <div class="container-fluid">
      <img src="../assets/images/logotipo.png" alt="logotipo" height="50" />
      <div class="dropdown">
        <button
          class="btn dropdown-toggle"
          type="button"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <img src="../assets/images/perfil.png" alt="perfil" height="42" />
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a
              class="dropdown-item text-danger fw-bold"
              href=""
              @click="logout"
            >
              Cerrar Sesión
              <img src="@/assets/images/logout.png" alt="Logout" width="25px" />
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped></style>
