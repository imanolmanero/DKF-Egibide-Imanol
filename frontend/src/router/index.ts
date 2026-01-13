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
        },
        {
          path: "alumno/mis-datos",
          name: "alumno-datos",
          components: {
            main: () => import("@/pages/Alumno/misDatosView.vue"),
          },
        },
        {
          path: "alumno/empresa",
          name: "alumno-empresa",
          components: {
            main: () => import("@/pages/Alumno/empresa.vue"),
          },
        },
        {
          path: "alumno/seguimiento",
          name: "alumno-seguimiento",
          components: {
            main: () => import("@/pages/Alumno/seguimiento.vue"),
          },
        },
        {
          path: "alumno/calificacion",
          name: "alumno-calificacion",
          components: {
            main: () => import("@/pages/Alumno/calificacion.vue"),
          },
        },
        // Grupo de rutas para Tutores
        {
          path: "tutor-egibide/inicio",
          name: "tutor_egibide-inicio",
          components: {
            main: () => import("@/pages/TutorEgibide/inicio.vue"),
          },
        },
        {
          path: "tutor-egibide/alumnos",
          name: "tutor_egibide-alumnos",
          components: {
            main: () => import("@/pages/TutorEgibide/alumnos.vue"),
          },
        },
        {
          path: "tutor-egibide/empresas",
          name: "tutor_egibide-empresas",
          components: {
            main: () => import("@/pages/TutorEgibide/empresas.vue"),
          },
        },
        {
          path: "tutor-egibide/alumno-empresa",
          name: "tutor_egibide-alumno_empresa",
          components: {
            main: () => import("@/pages/TutorEgibide/alumno_empresa.vue"),
          },
        },
        {
          path: "tutor-egibide/horario-calendario",
          name: "tutor_egibide-horario_calendario",
          components: {
            main: () => import("@/pages/TutorEgibide/horario_calendario.vue"),
          },
        },
        {
          path: "tutor-egibide/general",
          name: "tutor_egibide-general",
          components: {
            main: () => import("@/pages/TutorEgibide/general.vue"),
          },
        },
        {
          path: "tutor-egibide/cuaderno",
          name: "tutor_egibide-cuaderno",
          components: {
            main: () => import("@/pages/TutorEgibide/cuaderno.vue"),
          },
        },
        // Grupo de rutas para Empresas
        {
          path: "tutor-empresa/inicio",
          name: "tutor_empresa-inicio",
          components: {
            main: () => import("@/pages/TutorEmpresa/inicio.vue"),
          },
        },
        {
          path: "tutor-empresa/alumnos-asignados",
          name: "tutor_empresa-alumnos_asignados",
          components: {
            main: () => import("@/pages/TutorEmpresa/alumnos_asignados.vue"),
          },
        },
        {
          path: "tutor-empresa/competencias",
          name: "tutor_empresa-competencias",
          components: {
            main: () => import("@/pages/TutorEmpresa/competencias.vue"),
          },
        },
        {
          path: "tutor-empresa/calificacion",
          name: "tutor_empresa-calificacion",
          components: {
            main: () => import("@/pages/TutorEmpresa/calificacion.vue"),
          },
        },
        // Grupo de rutas para Admin
        {
          path: "admin/inicio",
          name: "admin-inicio",
          components: {
            main: () => import("@/pages/Admin/inicio.vue"),
          },
        },
        {
          path: "admin/ciclos",
          name: "admin-ciclos",
          components: {
            main: () => import("@/pages/Admin/ciclos.vue"),
          },
        },
        {
          path: "admin/competencias",
          name: "admin-competencias",
          components: {
            main: () => import("@/pages/Admin/competencias.vue"),
          },
        },
        {
          path: "admin/empresas",
          name: "admin-empresas",
          components: {
            main: () => import("@/pages/Admin/empresas.vue"),
          },
        },
        {
          path: "admin/alumnos",
          name: "admin-alumnos",
          components: {
            main: () => import("@/pages/Admin/alumnos.vue"),
          },
        },
        {
          path: "admin/añadir-ciclos",
          name: "admin-añadir_ciclos",
          components: {
            main: () => import("@/pages/Admin/añadir_ciclos.vue"),
          },
        },
        {
          path: "admin/añadir-competencias",
          name: "admin-añadir_competencias",
          components: {
            main: () => import("@/pages/Admin/añadir_competencias.vue"),
          },
        },
        {
          path: "admin/añadir-empresas",
          name: "admin-añadir_empresas",
          components: {
            main: () => import("@/pages/Admin/añadir_empresas.vue"),
          },
        },
        {
          path: "admin/añadir-alumnos",
          name: "admin-añadir_alumnos",
          components: {
            main: () => import("@/pages/Admin/añadir_alumnos.vue"),
          },
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
});

export default router;
