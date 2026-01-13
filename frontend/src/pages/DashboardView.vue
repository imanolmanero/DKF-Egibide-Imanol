<script setup lang="ts">
import Header from "@/components/Header.vue";
import MainContent from "@/components/MainContent.vue";
import Sidebar from "@/components/Sidebar.vue";
</script>

<template>
  <div class="d-flex flex-column vh-100 gap-3 py-3">
    <Header />
    <div class="d-flex flex-grow-1 gap-3">
      <Sidebar />
      <MainContent />
    </div>
  </div>
</template>

<style scoped></style>
