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
    }
];

export default routes;
