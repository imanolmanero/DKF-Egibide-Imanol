<template>
  <div class="importar-container">
    <h2>Importar CSV del Ciclo</h2>

    <!-- Botón de subir archivo -->
    <div class="upload-section">
      <input
        type="file"
        ref="fileInput"
        @change="handleFileUpload"
        accept=".csv"
        style="display: none"
      />

      <button
        @click="$refs.fileInput.click()"
        :disabled="cargando"
        class="btn-upload"
      >
        <span v-if="!archivoSeleccionado">📁 Seleccionar archivo CSV</span>
        <span v-else>📄 {{ archivoSeleccionado.name }}</span>
      </button>

      <button
        v-if="archivoSeleccionado"
        @click="importarCSV"
        :disabled="cargando"
        class="btn-import"
      >
        <span v-if="cargando">⏳ Importando...</span>
        <span v-else>🚀 Importar</span>
      </button>
    </div>

    <!-- Mensajes -->
    <div v-if="mensaje" :class="['alert', mensajeTipo]">
      {{ mensaje }}
    </div>

    <!-- Resultados -->
    <div v-if="resultado" class="resultado">
      <h3>✅ Importación completada</h3>
      <ul>
        <li>
          Filas procesadas: <strong>{{ resultado.filas_procesadas }}</strong>
        </li>
        <li>
          Asignaturas creadas:
          <strong>{{ resultado.asignaturas_creadas }}</strong>
        </li>
        <li>
          Competencias creadas:
          <strong>{{ resultado.competencias_creadas }}</strong>
        </li>
        <li>
          Resultados creados:
          <strong>{{ resultado.resultados_creados }}</strong>
        </li>
        <li>
          Relaciones creadas:
          <strong>{{ resultado.relaciones_creadas }}</strong>
        </li>
      </ul>

      <div
        v-if="resultado.errores && resultado.errores.length > 0"
        class="errores"
      >
        <strong>⚠️ Errores:</strong>
        <ul>
          <li v-for="(error, idx) in resultado.errores" :key="idx">
            {{ error }}
          </li>
        </ul>
      </div>
    </div>

    <!-- Ayuda -->
    <div class="help-box">
      <h4>📋 Formato del CSV:</h4>
      <p>El archivo debe tener 5 columnas en este orden:</p>
      <ol>
        <li>
          <code>codigo_asignatura</code> - Código de la asignatura (ej: DWES,
          DAW)
        </li>
        <li>
          <code>nombre_asignatura</code> - Nombre completo de la asignatura
        </li>
        <li>
          <code>codigo_competencia</code> - Código único (ej: COMP1, COMP2,
          COMP3...)
        </li>
        <li>
          <code>descripcion_competencia</code> - Descripción de la competencia
          técnica
        </li>
        <li>
          <code>descripcion_resultado</code> - Descripción del resultado de
          aprendizaje
        </li>
      </ol>
      <p>
        <strong>💡 Importante:</strong> Usa el mismo
        <code>codigo_competencia</code> para relacionar múltiples RAs con la
        misma competencia.
      </p>

      <button @click="descargarPlantilla" class="btn-secondary">
        ⬇️ Descargar plantilla
      </button>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/auth";
import axios from "axios";

export default {
  name: "ImportarCiclo",

  data() {
    return {
      archivoSeleccionado: null,
      cargando: false,
      mensaje: "",
      mensajeTipo: "",
      resultado: null,
    };
  },

  methods: {
    handleFileUpload(event) {
      this.archivoSeleccionado = event.target.files[0];
      this.mensaje = "";
      this.resultado = null;
    },

    async importarCSV() {
      const authStore = useAuthStore();

      if (!this.archivoSeleccionado) {
        this.mostrarMensaje("Seleccione un archivo", "error");
        return;
      }

      const formData = new FormData();
      formData.append("ciclo_id", 2);
      formData.append("csv_file", this.archivoSeleccionado);

      this.cargando = true;
      this.mensaje = "";

      try {
        const response = await fetch(
          "http://localhost:8000/api/ciclos/importar",
          {
            method: "POST",
            headers: {
              Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
              Accept: "application/json",
              // NO poner Content-Type, el navegador lo hace automáticamente con FormData
            },
            body: formData,
          },
        );

        // Leer el JSON de la respuesta
        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.error || "Error al importar");
        }

        this.resultado = data.datos;
        this.mostrarMensaje(data.message, "success");

        // Limpiar archivo
        this.archivoSeleccionado = null;
        if (this.$refs.fileInput) this.$refs.fileInput.value = "";
      } catch (error) {
        // fetch no tiene error.response, así que usamos error.message
        this.mostrarMensaje(error.message || "Error al importar", "error");
      } finally {
        this.cargando = false;
      }
    },

    mostrarMensaje(texto, tipo) {
      this.mensaje = texto;
      this.mensajeTipo = tipo === "success" ? "alert-success" : "alert-danger";
    },

    async descargarPlantilla() {
      const authStore = useAuthStore();
      try {
        const response = await fetch(
          "http://localhost:8000/api/ciclos/plantilla",
          {
            method: "GET",
            headers: {
              Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
              Accept: "text/csv", // mejor que application/json si es CSV
            },
          },
        );

        if (!response.ok) {
          throw new Error("Error en la descarga");
        }

        // Aquí obtienes directamente el blob
        const blob = await response.blob();

        // Crear el link y descargar
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "plantilla_ciclo.csv";
        document.body.appendChild(link); // necesario para Firefox
        link.click();
        link.remove();
        URL.revokeObjectURL(link.href);
      } catch (error) {
        this.mostrarMensaje("Error al descargar plantilla", "error");
        console.error(error);
      }
    },
  },
};
</script>

<style scoped>
.importar-container {
  max-width: 700px;
  margin: 20px auto;
  padding: 30px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
  margin-top: 0;
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #555;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 2px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
}

.upload-section {
  display: flex;
  gap: 10px;
  margin: 20px 0;
}

.btn-upload,
.btn-import,
.btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s;
}

.btn-upload {
  flex: 1;
  background: #f0f0f0;
  color: #333;
  border: 2px dashed #ccc;
}

.btn-upload:hover:not(:disabled) {
  background: #e8e8e8;
  border-color: #999;
}

.btn-import {
  background: #667eea;
  color: white;
}

.btn-import:hover:not(:disabled) {
  background: #5568d3;
  transform: translateY(-2px);
}

.btn-secondary {
  width: 100%;
  background: #6c757d;
  color: white;
  margin-top: 15px;
}

.btn-secondary:hover {
  background: #5a6268;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.alert {
  padding: 15px;
  margin: 20px 0;
  border-radius: 6px;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-danger {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.resultado {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin: 20px 0;
}

.resultado h3 {
  margin-top: 0;
  color: #155724;
}

.resultado ul {
  list-style: none;
  padding: 0;
}

.resultado li {
  padding: 8px 0;
  border-bottom: 1px solid #e0e0e0;
}

.resultado li:last-child {
  border-bottom: none;
}

.errores {
  background: #fff3cd;
  padding: 15px;
  border-radius: 6px;
  margin-top: 15px;
}

.errores ul {
  list-style: disc;
  padding-left: 20px;
}

.help-box {
  background: #f0f7ff;
  padding: 20px;
  border-radius: 8px;
  border-left: 4px solid #667eea;
  margin-top: 30px;
}

.help-box h4 {
  margin-top: 0;
  color: #333;
}

.help-box code {
  background: #e1e8ed;
  padding: 2px 6px;
  border-radius: 3px;
  font-family: "Courier New", monospace;
  font-size: 0.9rem;
}

.help-box ol {
  margin: 15px 0;
}

.help-box li {
  margin: 8px 0;
}
</style>
