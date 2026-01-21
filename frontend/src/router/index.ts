import DetallesAlumno from "@/pages/Alumno/detallesAlumno.vue";
import LoginView from "@/pages/Authentication/LoginView.vue";
import DashboardView from "@/pages/DashboardView.vue";
import { useAuthStore } from "@/stores/auth";
import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/login",
      name: "login",
      component: LoginView,
      meta: { guest: true },
    },
    {
      path: "/",
      component: DashboardView,
      meta: { requiresAuth: true },
      children: [
        // Grupo de rutas para Alumnos
        {
          path: "alumno/inicio",
          name: "alumno-inicio",
          components: {
            main: () => import("@/pages/Alumno/inicio.vue"),
          },
          meta: { role: "alumno" },
        },
        {
          path: "alumno/informacion",
          name: "alumno-informacion",
          components: {
            main: () => import("@/pages/Alumno/informacion.vue"),
          },
          meta: { role: "alumno" },
        },
        {
          path: "alumno/mis-datos",
          name: "alumno-datos",
          components: {
            main: () => import("@/pages/Alumno/misDatosView.vue"),
          },
          meta: { role: "alumno" },
        },
        {
          path: "alumno/empresa",
          name: "alumno-empresa",
          components: {
            main: () => import("@/pages/Alumno/empresa.vue"),
          },
          meta: { role: "alumno" },
        },
        {
          path: "alumno/seguimiento",
          name: "alumno-seguimiento",
          components: {
            main: () => import("@/pages/Alumno/seguimiento.vue"),
          },
          meta: { role: "alumno" },
        },
        {
          path: "alumno/calificacion",
          name: "alumno-calificacion",
          components: {
            main: () => import("@/pages/Alumno/calificacion.vue"),
          },
          meta: { role: "alumno" },
        },
        // Grupo de rutas para Tutores
        {
          path: "tutor-egibide/inicio",
          name: "tutor_egibide-inicio",
          components: {
            main: () => import("@/pages/TutorEgibide/inicio.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/informacion",
          name: "tutor_egibide-informacion",
          components: {
            main: () => import("@/pages/TutorEgibide/informacion.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/alumnos",
          name: "tutor_egibide-alumnos",
          components: {
            main: () => import("@/pages/TutorEgibide/alumnos.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/empresas",
          name: "tutor_egibide-empresas",
          components: {
            main: () => import("@/pages/TutorEgibide/empresas.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/alumno-empresa/:alumnoId/asignar-empresa",
          name: "tutor_egibide-alumno_empresa",
          components: {
            main: () => import("@/pages/TutorEgibide/alumno_empresa.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/horas-periodo/:alumnoId/asignar-horas-periodo",
          name: "tutor_egibide-horas_periodo",
          components: {
            main: () => import("@/pages/TutorEgibide/horas_periodo.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/seguimiento/:alumnoId",
          name: "tutor_egibide-seguimiento",
          components: {
            main: () => import("@/pages/TutorEgibide/seguimiento.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/seguimiento-general/:alumnoId",
          name: "tutor_egibide-seguimiento-general",
          components: {
            main: () => import("@/pages/TutorEgibide/general.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/seguimiento-cuaderno/:alumnoId",
          name: "tutor_egibide-seguimiento-cuaderno",
          components: {
            main: () => import("@/pages/TutorEgibide/cuaderno.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/alumno-empresa/:alumnoId/competencias",
          name: "tutor_egibide-competencias",
          components: {
            main: () => import("@/pages/TutorEgibide/competencias.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          path: "tutor-egibide/alumno-empresa/:alumnoId/calificaciones",
          name: "tutor_egibide-calificaciones",
          components: {
            main: () => import("@/pages/TutorEgibide/calificaciones.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        {
          name: "tutor_egibide-detalle_alumno",
          path: "tutor-egibide/alumnos-asignados/:alumnoId",
          components: {
            main: () => import("@/pages/Alumno/detallesAlumno.vue"),
          },
          meta: { role: "tutor_egibide" },
        },
        // Grupo de rutas para Empresas
        {
          path: "tutor-empresa/inicio",
          name: "tutor_empresa-inicio",
          components: {
            main: () => import("@/pages/TutorEmpresa/inicio.vue"),
          },
          meta: { role: "tutor_empresa" },
        },
        {
          path: "tutor-empresa/informacion",
          name: "tutor-empresa-informacion",
          components: {
            main: () => import("@/pages/TutorEmpresa/informacion.vue"),
          },
          meta: { role: "tutor_empresa" },
        },
        {
          path: "tutor-empresa/alumnos-asignados",
          name: "tutor_empresa-alumnos_asignados",
          components: {
            main: () => import("@/pages/TutorEmpresa/alumnos_asignados.vue"),
          },
          meta: { role: "tutor_empresa" },
        },
        {
          name: "tutor_empresa-detalle_alumno",
          path: "tutor-empresa/alumnos-asignados/:alumnoId",
          components: {
            main: () => import("@/pages/Alumno/detallesAlumno.vue"),
          },
          meta: { role: "tutor_empresa" },
        },
        {
          path: "tutor-empresa/alumnos-asignados/:alumnoId/competencias",
          name: "tutor_empresa-competencias",
          components: {
            main: () => import("@/pages/TutorEmpresa/competencias.vue"),
          },
          meta: { role: "tutor_empresa" },
        },
        {
          path: "tutor-empresa/alumnos-asignados/:alumnoId/calificacion",
          name: "tutor_empresa-calificacion",
          components: {
            main: () => import("@/pages/TutorEmpresa/calificacion.vue"),
          },
          meta: { role: "tutor_empresa" },
        },

        // Grupo de rutas para Admin
        {
          path: "admin/inicio",
          name: "admin-inicio",
          components: {
            main: () => import("@/pages/Admin/inicio.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/informacion",
          name: "admin-informacion",
          components: {
            main: () => import("@/pages/Admin/informacion.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/ciclos",
          name: "admin-ciclos",
          components: {
            main: () => import("@/pages/Admin/ciclos.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/competencias",
          name: "admin-competencias",
          components: {
            main: () => import("@/pages/Admin/competencias.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/empresas",
          name: "admin-empresas",
          components: {
            main: () => import("@/pages/Admin/empresas.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/alumnos",
          name: "admin-alumnos",
          components: {
            main: () => import("@/pages/Admin/alumnos.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/agregar",
          name: "admin-agregar",
          components: {
            main: () => import("@/pages/Admin/agregar.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/nuevo-ciclo",
          name: "admin-nuevo_ciclo",
          components: {
            main: () => import("@/pages/Admin/añadir_ciclos.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/nueva-competencia",
          name: "admin-nueva_competencia",
          components: {
            main: () => import("@/pages/Admin/añadir_competencias.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/nueva-empresa",
          name: "admin-nueva_empresa",
          components: {
            main: () => import("@/pages/Admin/añadir_empresas.vue"),
          },
          meta: { role: "admin" },
        },
        {
          path: "admin/nuevo-alumno",
          name: "admin-nuevo_alumno",
          components: {
            main: () => import("@/pages/Admin/añadir_alumnos.vue"),
          },
          meta: { role: "admin" },
        },
      ],
    },
  ],
});

router.beforeEach(async (to) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.token) {
    return { name: "login" };
  }

  if (to.meta.guest && auth.token) {
    return { path: "/" };
  }

  if (to.path === "/") {
    if (auth.token && !auth.currentUser) {
      await auth.fetchCurrentUser();
    }

    switch (auth.currentUser?.role) {
      case "alumno":
        return { path: "/alumno/inicio" };
      case "tutor_egibide":
        return { path: "/tutor-egibide/inicio" };
      case "tutor_empresa":
        return { path: "/tutor-empresa/inicio" };
      case "admin":
        return { path: "/admin/inicio" };
      default:
        return { name: "login" };
    }
  }

  if (to.meta.role && auth.currentUser?.role !== to.meta.role) {
    switch (auth.currentUser?.role) {
      case "alumno":
        return { path: "/alumno/inicio" };
      case "tutor_egibide":
        return { path: "/tutor-egibide/inicio" };
      case "tutor_empresa":
        return { path: "/tutor-empresa/inicio" };
      case "admin":
        return { path: "/admin/inicio" };
      default:
        return { name: "login" };
    }
  }
});

export default router;
