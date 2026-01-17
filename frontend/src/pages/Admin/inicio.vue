<script setup>
  import { useAuthStore } from "@/stores/auth";

  const authStore = useAuthStore();

  const adminNombre = authStore.currentUser.role;
</script>

<template>
  <h2 class="saludo">Hola, {{ adminNombre }}</h2>
  <div class="subrayado"></div>
  <p class="descripcion">
    Gestione usuarios, roles, estructura académica y competencias del sistema,
    con acceso global a la información para su supervisión y mantenimiento.
  </p>
</template>

<style scoped></style>
