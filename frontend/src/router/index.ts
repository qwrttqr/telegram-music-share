import {createRouter, createWebHistory} from 'vue-router'
import GeneralLayout from "@/layout/GeneralLayout.vue"
import {useUserStore} from "@/stores/user.ts";

const userStore = useUserStore()
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: GeneralLayout,
      children: [
        {
          path: '',
          name: 'hello',
          component: () => import("@/pages/main/HelloPage.vue")
        },
        {
          path: 'profile',
          name: 'profile',
          beforeEnter: (to, from) => {
            if (!userStore.isAuthenticated) {
              return {name: 'hello'}
            }
          },
          component: () => import("@/pages/profile/ProfilePage.vue")
        },
      ]
    }
  ],
})

export default router
