<script setup>
  import { ref, onMounted } from 'vue'
  import axios from '@/axios' 

  const nombre = ref('')

  onMounted(async () => {
    const res = await axios.get('/me/tutor-empresa')
    nombre.value = res.data.nombre
  })
</script>

<template>
  <h2 class="saludo">Hola, {{ nombre }}</h2>
  <div class="subrayado"></div>
  <p class="descripcion">
    Acceda al alumnado asignado, evalúe competencias, revise el cuaderno de
    prácticas y coordine el seguimiento con el centro.
  </p>
</template>

<style scoped></style>
