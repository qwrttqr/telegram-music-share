import {createRouter, createWebHistory} from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [{
    path: '/',
    name: 'hello',
    component: () => import("@/pages/main/HelloPage.vue")
  }],
})

export default router
