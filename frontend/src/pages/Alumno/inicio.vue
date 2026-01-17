<script setup>
  import { ref, onMounted } from 'vue'
  import axios from '@/axios' 

  const nombre = ref('')

  onMounted(async () => {
    const res = await axios.get('/me/alumno')
    nombre.value = res.data.nombre
  })
</script>

<template>
  <h2 class="saludo">Hola, {{ nombre }}</h2>
  <div class="subrayado"></div>
  <p class="descripcion">
    Consulte la empresa asignada, el horario de prácticas, sus calificaciones y
    la información relevante sobre su estancia formativa.
  </p>
</template>

<style scoped></style>
