<script setup>
  import { ref, onMounted } from 'vue'
  import axios from '@/axios' 

  const nombre = ref('')

  onMounted(async () => {
    const res = await axios.get('/me/tutor-egibide')
    nombre.value = res.data.nombre
  })
</script>

<template>
  <h2 class="saludo">Hola, {{ nombre }}</h2>
  <div class="subrayado"></div>
  <p class="descripcion">
    Consulte y gestione el alumnado en prácticas, asigne empresas, realice el
    seguimiento y coordínese con los tutores de empresa.
  </p>
</template>

<style scoped></style>
