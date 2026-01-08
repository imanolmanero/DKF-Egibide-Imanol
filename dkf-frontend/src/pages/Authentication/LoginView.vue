<script setup lang="ts"></script>

<template>
  <div class="min-vh-100 d-flex justify-content-center align-items-center">
    <div class="card shadow-lg" style="width: 22rem;">
      <img src="" class="card-img-top" alt="Egibide Logo">
      <div class="card-body">
        <h5 class="card-title text-center mb-3">Iniciar Sesión</h5>

        <form>
          <div class="input-group mb-3">
            <span class="input-group-text">@</span>
            <input type="text" class="form-control" placeholder="Email" aria-label="Server">
          </div>
          <div class="input-group mb-3">
            <span class="input-group-text">
              <img src="@/assets/images/key.png" alt="email" width="18" height="18" />
            </span>
            <input type="password" class="form-control" placeholder="Password">
          </div>
          <div class="d-grid">
            <button class="btn btn-primary">Entrar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>


<style scoped></style>
