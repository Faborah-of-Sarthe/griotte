import { createRouter, createWebHistory } from 'vue-router'
import { isLoggedIn } from '../utils'


const ListView = () => import('../views/ListView.vue')
const HomeView = () => import('../views/HomeView.vue')

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),


  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: {
        middleware: "guest"
      }
    },
    {
      path: '/my-list',
      name: 'my-list',
      component: ListView,
      meta: {
        middleware: "auth"
      }
    }
  ]
})

// Handle auth for each route
router.beforeEach((to, from, next) => { 
  // Check if the route has a middleware auth and if the user is logged in
  if (to.meta.middleware === 'auth' && !isLoggedIn()) {
    next({ name: 'home' })
    return
  }
  // Check if the route has a middleware guest and if the user is logged in
  if (to.meta.middleware === 'guest' && isLoggedIn()) {
    next({ name: 'my-list' })
    return
  }

  next()
  return

})

export default router
