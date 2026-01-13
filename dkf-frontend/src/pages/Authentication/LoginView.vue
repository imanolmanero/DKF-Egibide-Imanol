<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import { useAuthStore } from "@/stores/auth";
import { ref } from "vue";

const authStore = useAuthStore();
const email = ref<string>("");
const passwd = ref<string>("");

function login() {
  authStore.login(email.value, passwd.value);
}
</script>

<template>
  <Toast
    v-if="authStore.error"
    :key="authStore.error"
    :message="authStore.error"
    messageType="error"
  />
  <div class="min-vh-100 d-flex justify-content-center align-items-center">
    <div class="card shadow p-3 island" style="width: 25rem">
      <img
        src="@/assets/images/logotipo.png"
        class="card-img-top"
        alt="Egibide Logo"
      />
      <div class="card-body">
        <h5 class="card-title text-center mb-3">Iniciar Sesión</h5>

        <form @submit.prevent="login">
          <div class="input-group mb-3">
            <span class="input-group-text">@</span>
            <input
              type="email"
              class="form-control"
              placeholder="Email"
              v-model="email"
              aria-label="Email"
              required
            />
          </div>
          <div class="input-group mb-3">
            <span class="input-group-text">
              <img
                src="@/assets/images/key.png"
                alt="email"
                width="18"
                height="18"
              />
            </span>
            <input
              type="password"
              class="form-control"
              placeholder="Contrseña"
              v-model="passwd"
              aria-label="Contraseña"
              required
            />
          </div>
          <div class="d-grid">
            <button class="btn btn-primary" type="submit">Entrar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
