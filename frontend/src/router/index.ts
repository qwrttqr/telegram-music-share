import {createRouter, createWebHistory} from 'vue-router'
import GeneralLayout from "@/layout/GeneralLayout.vue"
import {useUserStore} from "@/stores/user.ts"

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
            const userStore = useUserStore()
            if (!userStore.isAuthenticated) {
              return {name: 'hello'}
            }
          },
          component: () => import("@/pages/profile/ProfilePage.vue")
        },
        {
          path: 'friends',
          name: 'friends',
          beforeEnter: (to) => {
            const userStore = useUserStore()
            if (!userStore.isAuthenticated) {
              return { name: 'hello', query: { redirect: to.fullPath } }
            }
          },
          component: () => import("@/pages/friends/FriendsPage.vue")
        },
        {
          path: 'friends/accept_invite/:token',
          name: 'friends-accept-invite',
          beforeEnter: (to, from) => {
            const userStore = useUserStore()
            if (!userStore.isAuthenticated) {
              return { name: 'hello' }
            }
          },
          component: () => import("@/pages/friends/FriendsPage.vue")
        },
      ]
    }
  ],
})

export default router
