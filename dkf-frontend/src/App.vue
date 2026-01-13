<script setup lang="ts">
import { onMounted } from "vue";
import { useAuthStore } from "./stores/auth";

const authStore = useAuthStore();

onMounted(() => {
  authStore.fetchCurrentUser();
});
</script>

<template>
  <header></header>
  <main class="container-fluid">
    <RouterView />
  </main>
</template>

<style scoped></style>
