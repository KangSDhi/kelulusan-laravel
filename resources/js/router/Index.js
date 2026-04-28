import { createRouter, createWebHistory } from "vue-router";
import Routes from "@/router/Routes.js";

const router = createRouter({
    history: createWebHistory(),
    routes: Routes,
})

export default router;
