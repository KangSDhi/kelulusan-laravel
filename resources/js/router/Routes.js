const routes = [
    {
        path: "/",
        name: "Home Page",
        component: () => import('../page/Home.vue')
    },
    {
        path: "/login",
        name: "Login Page",
        component: () => import("../page/Login.vue")
    },
    {
        path: "/dashboard",
        name: "Dashboard Page",
        component: () => import("../page/Dashboard.vue")
    }
];

export default routes;
