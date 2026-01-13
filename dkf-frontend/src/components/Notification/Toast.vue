<script setup lang="ts">
import { onMounted, ref } from "vue";

const props = defineProps({
  message: String,
  messageType: {
    type: String,
    default: "normal",
  },
});

const bgClass = props.messageType === "error" ? "bg-danger" : "bg-primary";
const toastElement = ref<HTMLElement | null>(null);

onMounted(() => {
  if (toastElement.value) {
    const bootstrap = (window as any).bootstrap;
    if (bootstrap && bootstrap.Toast) {
      const toast = new bootstrap.Toast(toastElement.value, {
        autohide: true,
        delay: 5000,
      });
      toast.show();
    }
  }
});
</script>

<template>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div
      ref="toastElement"
      class="toast align-items-center text-white border-0"
      :class="bgClass"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
    >
      <div class="d-flex">
        <div class="toast-body">{{ props.message }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
